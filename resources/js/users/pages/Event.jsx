import React from "react";
import { useNavigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';
import '../../../css/calendar-custom.css';
import { useApiUrl } from "../components/context/ApiContext";
import { BarLoader, PuffLoader, SyncLoader } from "react-spinners";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import PageLoader from "../components/Loader/PageLoader";
import { dateTimeHelper } from "../components/Helper/dateTimeHelper";
import { getImageUrl } from "../components/Helper/imagePath";
import { useSwipeable } from 'react-swipeable';
import EventLoader from "../components/Loader/EventLoader";
import { motion } from "framer-motion";

const pageVariants = {
    initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: 0 },       // Keluar ke kiri
};

export default function Event() {

    const navigate = useNavigate()
    const [events, setEvent] = useState([]);
    const [loading, setLoading] = useState(true);
    const apiUrl = useApiUrl();
    const [selectedDate, setSelectedDate] = useState(null);
    const [activeMonth, setActiveMonth] = useState(new Date().getMonth());
    const [activeYear, setActiveYear] = useState(new Date().getFullYear());
    const [selectedCategory, setSelectedCategory] = useState("All");
    const [categories, setCategories] = useState([]);
    const { token } = useAuth(); 
    const handleSwipeLeft = () => {
        const newMonth = new Date(activeYear, activeMonth + 1, 1);
        setActiveMonth(newMonth.getMonth());
        setActiveYear(newMonth.getFullYear());
    };
      
    const handleSwipeRight = () => {
        const newMonth = new Date(activeYear, activeMonth - 1, 1);
        setActiveMonth(newMonth.getMonth());
        setActiveYear(newMonth.getFullYear());
    };
      
    const swipeHandlers = useSwipeable({
        onSwipedLeft: handleSwipeLeft,
        onSwipedRight: handleSwipeRight,
    });

    useEffect(() => {
        const fetchEvent = async () => {
            try {
                const res = await axios.get(`${apiUrl}/api/events`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });

                const allEvents = res.data;
                setEvent(allEvents);

                // Ambil semua kategori dan flatten jika nested
                const allCategories = allEvents.flatMap((event) => {
                    try {
                    const parsed = JSON.parse(event.category);
                    return Array.isArray(parsed) ? parsed : [parsed];
                    } catch {
                    return event.category ? [event.category] : [];
                    }
                });

                 // Ambil yang unik
                const uniqueCategories = [...new Set(allCategories)];
                setCategories(["All", ...uniqueCategories]);

            } catch (err) {
                showAlert({
                    icon: 'warning',
                    title: 'Connection Ended',
                    text: 'Unable to connect to the server. Please try again later.',
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => {
                    console.log(err);
                    
                    // window.location.href = "https://kpncorporation.darwinbox.com/";
                });
            } finally {
                setLoading(false);
            }
        };
        if(token) {
            fetchEvent();
        }
    }, [apiUrl, token]);
    

    const handleDateChange = (date) => {
        // Toggle: if clicked date is same as current, unselect it
        if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
          setSelectedDate(null); // unselect
        } else {
          setSelectedDate(date); // select new
        }
      };

    const filteredEvents = events.filter((event) => {
        const eventDate = new Date(event.start_date);
        
        const matchCategory =
        selectedCategory === "All" ||
        (() => {
            try {
            const parsed = JSON.parse(event.category);
            return Array.isArray(parsed)
                ? parsed.includes(selectedCategory)
                : parsed === selectedCategory;
            } catch {
            return event.category === selectedCategory;
            }
        })();

        const matchDate =
            !selectedDate || eventDate.toDateString() === selectedDate.toDateString();
        
        const matchYear =
            selectedDate !== null ? true : // only apply month filter if date is unselected
            eventDate.getFullYear() === activeYear;
        
        return matchCategory && matchDate && matchYear;
    });    
  
    return (
        <>
            {/* Header Section */}
            {loading ? (
                <EventLoader />
              ) : (
                <motion.div
                    variants={pageVariants}
                    initial="initial"
                    animate="animate"
                    exit="exit"
                    transition={{ duration: 0.2, ease: "easeInOut" }}
                >
                            <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen p-5">

                <div className="flex items-center justify-between mb-2">
                    <div className="flex-1">
                        <button
                            onClick={() => navigate(`/`)}
                            className="text-red-700 text-xl font-bold flex items-center gap-1 px-2 py-1"
                        >
                            <i className="ri-arrow-left-line"></i>
                        </button>
                    </div>
                    <div className="flex-2 text-center text-red-700 text-lg font-bold">Upcoming Events</div>
                    <div className="flex-1" /> {/* Spacer to balance layout */}
                </div>
                {/* Main Content */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Left Column: Calendar */}
                    <div className="calendar-container">
                        <div {...swipeHandlers}>
                            <Calendar
                            activeStartDate={new Date(activeYear, activeMonth, 1)}
                            onChange={handleDateChange}
                            value={selectedDate}
                            view="month"
                            onActiveStartDateChange={({ activeStartDate }) => {
                                setActiveMonth(activeStartDate.getMonth());
                                setActiveYear(activeStartDate.getFullYear());
                            }}
                            tileClassName={({ date }) => {
                                const today = new Date();
                                const isToday = date.toDateString() === today.toDateString();
                                
                                const isSelected =
                                  selectedDate && date.toDateString() === today.toDateString();
                            
                                if (isToday && !isSelected) {
                                  return "today-tile";
                                }
                            
                                return null;
                            }}
                            tileContent={({ date, view }) => {
                                const found = events.find(
                                (e) => new Date(e.start_date).toDateString() === date.toDateString()
                                );
                                return found ? <span className="event-emoji">
                                    <img className="w-4 h-4" src={getImageUrl(apiUrl, found.logo)} alt={found.title} />
                                </span> : null;
                            }}
                            className="w-full rounded-lg shadow-md p-4"
                            />
                        </div>
                    </div>

                    {/* Right Column: Filters and Event Cards */}
                    <div className="flex flex-wrap gap-2">
                    {categories.map((item) => (
                        <button
                        key={item}
                        onClick={() => setSelectedCategory(item)}
                        className={`px-2 py-1 rounded-full ${
                            selectedCategory === item
                            ? "bg-red-700 text-white text-sm"
                            : "bg-transparent outline outline-1 text-sm outline-stone-400 text-gray-600"
                        }`}
                        >
                        {item}
                        </button>
                    ))}
                    </div>
                    <div className="space-y-3">
                        {filteredEvents.map((event, index) => {      
                            const registeredStatus = event.event_participant?.[0]?.status || null;   
                            const { month, startTime, endTime, eventStatus, isClosed, isOngoing, closedRegistration  } = dateTimeHelper(event);   
                            
                            const getStatusColors = (status) => {
                                if (isClosed || closedRegistration) {
                                    return { text: "text-black", bg: "bg-gray-100" };
                                } else if (isOngoing) {
                                    return { text: "text-white", bg: "bg-blue-500" };
                                } else {
                                    if (registeredStatus) {
                                    if (registeredStatus === 'Waiting List' || registeredStatus === 'Confirmation') {
                                        return { text: "text-white", bg: "bg-yellow-400" };
                                    }
                                    if (registeredStatus === 'Registered') {
                                        return { text: "text-white", bg: "bg-green-400" };
                                    }
                                    if (registeredStatus === 'Canceled') {
                                        return { text: "text-white", bg: "bg-gray-400" };
                                    }
                                    }
                                    switch (status) {
                                        case "Open Registration":
                                        return { text: "text-white", bg: "bg-green-500" };
                                        case "Full Booked":
                                        return { text: "text-gray-600", bg: "bg-gray-200" };
                                        case "Ongoing":
                                        return { text: "text-white", bg: "bg-blue-500" };
                                        case "Closed":
                                        return { text: "text-black", bg: "bg-gray-100" };
                                        default:
                                        return { text: "text-black", bg: "bg-gray-100" };
                                    }
                                }
                            };
                            const statusColors = getStatusColors(event.status);
                            return (
                                <div onClick={() => navigate(`/event/${event.encrypted_id}`)} key={index} className="w-full min-h-20 bg-white rounded-lg shadow-md inline-flex justify-start items-center overflow-hidden cursor-pointer">
                                    <div className="flex-1 ps-2 flex justify-start items-center gap-3 overflow-hidden">
                                        <div className="w-16 px-2.5 py-2 bg-stone-400 rounded-lg inline-flex flex-col justify-center items-center">
                                            <div className="self-stretch text-center justify-start text-white text-base font-medium ">{event.start_date.split("-")[2]}<br/>{month}</div>
                                        </div>
                
                                        {/* Content */}
                                        <div className="w-24 flex-1 flex flex-col justify-start gap-0.5 py-0.5">
                                            <div data-color="primary" data-type="normal" className="bg-white/0 inline-flex flex-col justify-start items-start">
                                                <div className={`px-2 py-0.5 ${statusColors.bg} rounded inline-flex justify-center items-center overflow-hidden`}>
                                                    <div className={`text-center justify-center ${statusColors.text} text-[8px] font-medium leading-[10px]`}>{isClosed || isOngoing || closedRegistration ? eventStatus : (registeredStatus ?? event.status) }
                                                    </div>
                                                    {(event.status === "Ongoing" && isOngoing) && (
                                                        <div className="ms-1">
                                                            <PuffLoader
                                                            cssOverride={{
                                                                margin: 'auto'
                                                            }}
                                                            color="#fff"
                                                            size={10}
                                                            speedMultiplier={1}
                                                            />
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="text-xs font-bold text-gray-800 truncate">{event.title}</div>
                                            <div className="flex-row items-center text-[10px] text-gray-600 gap-2">
                                                <div className="flex justify-start items-center gap-0.5">
                                                    <i className="ri-time-line"></i>
                                                    <div className="justify-start font-normal leading-none">{`${startTime}-${endTime}`}</div>
                                                </div>
                                                <div className="flex justify-start items-center gap-0.5">
                                                    <i className="ri-map-pin-line"></i>
                                                    <div className="justify-start font-normal leading-none truncate">{event.event_location}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <img
                                            src={getImageUrl(apiUrl, event.image)}
                                            alt="Event Thumbnail"
                                            className="object-cover w-20 min-h-20"
                                        />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
                </div>
                </motion.div>
              )}
        </>
    );
}