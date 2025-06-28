import React from "react";
import { useNavigate, useLocation } from 'react-router-dom';
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
import { AnimatePresence, motion } from "motion/react";
import CardLoader from "../components/Loader/CardLoader";
import { useNavigationDirection } from "../components/Context/NavigationProvider";

const pageVariants = {
    initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: 0 },       // Keluar ke kiri
};

export default function Event() {
    const navigate = useNavigate();
    const location = useLocation();
    const bounds = location.state?.bounds;
    const { direction } = useNavigationDirection();
    const [skipExit, setSkipExit] = useState(false);        

    const [initialStyle, setInitialStyle] = useState(null);
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
        if (bounds) {
            const scaleX = bounds.width / window.innerWidth;
            const scaleY = bounds.height / window.innerHeight;
            const offsetX = bounds.left + bounds.width / 2 - window.innerWidth / 2;
            const offsetY = bounds.top + bounds.height / 2 - window.innerHeight / 2;
      
            setInitialStyle({
              scaleX,
              scaleY,
              offsetX,
              offsetY,
              borderRadius: 16,
            });
          }
    }, [apiUrl, token, bounds]);
    
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

    if (!initialStyle) return null; 
  
    return (
        <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen p-5">
        <motion.div
        initial={direction < 0 ? {} : {
            opacity: 0,
            scaleX: initialStyle.scaleX,
            scaleY: initialStyle.scaleY,
            x: initialStyle.offsetX,
            y: initialStyle.offsetY,
            borderRadius: initialStyle.borderRadius,
          }}
          animate={{
            opacity: 1,
            scaleX: 1,
            scaleY: 1,
            x: 0,
            y: 0,
            borderRadius: 0,
          }}
          exit={skipExit ? {} : {
            opacity: 0,
            scaleX: initialStyle.scaleX,
            scaleY: initialStyle.scaleY,
            x: initialStyle.offsetX,
            y: initialStyle.offsetY,
            borderRadius: initialStyle.borderRadius,
          }}
          transition={{ duration: 0.3, type: "tween", ease: "easeOut" }}
        >
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
            <AnimatePresence mode="wait">
                {loading ? (
                    <motion.div
                    key="loader"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    >
                    <CardLoader />
                    </motion.div>
                ) : (
                    <motion.div
                    key="event-content"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    layout
                    className="space-y-3"
                    >
                    {filteredEvents.map((event) => {
                        const registeredStatus = event.event_participant?.[0]?.status || null;
                        const { month, startTime, endTime, eventStatus, isClosed, isOngoing, closedRegistration } = dateTimeHelper(event);

                        const getStatusColors = (status) => {
                        if (isClosed || closedRegistration) return { text: "text-black", bg: "bg-gray-100" };
                        if (isOngoing) return { text: "text-white", bg: "bg-blue-500" };

                        if (registeredStatus) {
                            if (registeredStatus === 'Waiting List' || registeredStatus === 'Confirmation') return { text: "text-white", bg: "bg-yellow-400" };
                            if (registeredStatus === 'Registered') return { text: "text-white", bg: "bg-green-400" };
                            if (registeredStatus === 'Canceled') return { text: "text-white", bg: "bg-gray-400" };
                        }

                        switch (status) {
                            case "Open Registration": return { text: "text-white", bg: "bg-green-500" };
                            case "Full Booked": return { text: "text-gray-600", bg: "bg-gray-200" };
                            case "Ongoing": return { text: "text-white", bg: "bg-blue-500" };
                            case "Closed": return { text: "text-black", bg: "bg-gray-100" };
                            default: return { text: "text-black", bg: "bg-gray-100" };
                        }
                        };

                        const statusColors = getStatusColors(event.status);
                        const showEvent = eventStatus !== 'Ended';

                        return showEvent ? (
                        <motion.div
                            key={event.encrypted_id}
                            layout
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -10 }}
                            transition={{ duration: 0.2 }}
                            onClick={(e) => {
                                setSkipExit(true);
                                const bounds = e.currentTarget.getBoundingClientRect();
                                navigate(`/event/${event.encrypted_id}`, {
                                    state: {
                                      bounds: {
                                        width: bounds.width,
                                        height: bounds.height,
                                        top: bounds.top,
                                        left: bounds.left
                                      }
                                    }
                                  });
                            }}
                            className="w-full min-h-20 bg-white rounded-lg shadow-md inline-flex justify-start items-center overflow-hidden cursor-pointer"
                        >
                            <div className="flex-1 ps-2 flex justify-start items-center gap-3 overflow-hidden">
                            {/* Date */}
                            <div className="w-16 px-2.5 py-2 bg-stone-400 rounded-lg inline-flex flex-col justify-center items-center">
                                <div className="self-stretch text-center justify-start text-white text-base font-medium">
                                {event.start_date.split("-")[2]}<br />{month}
                                </div>
                            </div>

                            {/* Content */}
                            <div className="w-24 flex-1 flex flex-col justify-start gap-0.5 py-0.5">
                                <div className="inline-flex flex-col justify-start items-start">
                                <div className={`px-2 py-0.5 ${statusColors.bg} rounded inline-flex justify-center items-center`}>
                                    <div className={`text-center ${statusColors.text} text-[8px] font-medium`}>
                                    {isClosed || isOngoing || closedRegistration ? eventStatus : (registeredStatus ?? event.status)}
                                    </div>
                                    {(event.status === "Ongoing" && isOngoing) && (
                                    <div className="ms-1">
                                        <PuffLoader cssOverride={{ margin: 'auto' }} color="#fff" size={10} speedMultiplier={1} />
                                    </div>
                                    )}
                                </div>
                                </div>
                                <div className="text-xs font-bold text-gray-800 truncate">{event.title}</div>
                                <div className="flex-row items-center text-[10px] text-gray-600 gap-2">
                                <div className="flex items-center gap-0.5">
                                    <i className="ri-time-line"></i>
                                    <div className="font-normal leading-none">{`${startTime}-${endTime}`}</div>
                                </div>
                                <div className="flex items-center gap-0.5">
                                    <i className="ri-map-pin-line"></i>
                                    <div className="font-normal leading-none truncate">{event.event_location}</div>
                                </div>
                                </div>
                            </div>

                            {/* Thumbnail */}
                            <img src={getImageUrl(apiUrl, event.image)} alt="Event Thumbnail" className="object-cover w-20 h-20" />
                            </div>
                        </motion.div>
                        ) : null;
                    })}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
        </motion.div>
        </div>
    );
}