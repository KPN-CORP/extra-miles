import React from "react";
import { useNavigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import 'react-calendar/dist/Calendar.css';
import '../../../css/calendar-custom.css';
import { useApiUrl } from "../components/context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import { dateTimeHelper } from "../components/Helper/dateTimeHelper";
import { getImageUrl } from "../components/Helper/imagePath";
import SurveyLoader from "../components/Loader/SurveyLoader";
import { motion } from "framer-motion";

const pageVariants = {
    initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: 0 },       // Keluar ke kiri
};

const pageVariants2 = {
    initial: { opacity: 0, y: 100 },     // Masuk dari kanan
    animate: { opacity: 1, y: 0 },       // Diam di tengah
    exit: { opacity: 0, y: 100 },       // Keluar ke kiri
};

export default function Survey() {

    const navigate = useNavigate()
    const [datas, setData] = useState([]);
    const [dataEventParticipant, setDataEventParticipant] = useState([]);
    const [loading, setLoading] = useState(true);
    const apiUrl = useApiUrl();
    const [selectedDate, setSelectedDate] = useState(new Date());
    const [selectedBU, setSelectedBU] = useState("All BU");
    const { token } = useAuth();  
    
    const mergedData = [
        ...new Map([
            ...datas.map(d => [d.encrypted_id, d]),
            ...dataEventParticipant.map(d => [d.encrypted_id, d])
        ]).values()
    ];

    useEffect(() => {
        const fetchData = async () => {
            try {
                const res = await axios.get(`${apiUrl}/api/survey-vote`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });                        
                
                setData(
                    res.data.map(item => {
                        // Safely parse businessUnit from JSON string if needed
                        let businessUnit = [];
                        try {
                          businessUnit = typeof item.businessUnit === 'string'
                            ? JSON.parse(item.businessUnit)
                            : Array.isArray(item.businessUnit)
                              ? item.businessUnit
                              : [];
                        } catch (e) {
                          businessUnit = [];
                        }
                    
                        // Normalize event_participant as an array
                        const event_participant = Array.isArray(item.event_participant)
                          ? item.event_participant.filter(p => p.status != 'Registered')
                          : (item.event_participant ? [item.event_participant] : []).filter(p => p.status != 'Registered');
                    
                        return {
                          ...item,
                          businessUnit,
                          event_participant
                        };
                    }).filter(e => e.event_participant.length > 0)
                );

                setDataEventParticipant(
                    res.data.map(item => {
                      // Safely parse businessUnit from JSON string if needed
                      let businessUnit = [];
                      try {
                        businessUnit = typeof item.businessUnit === 'string'
                          ? JSON.parse(item.businessUnit)
                          : Array.isArray(item.businessUnit)
                            ? item.businessUnit
                            : [];
                      } catch (e) {
                        businessUnit = [];
                      }
                  
                      // Normalize event_participant as an array
                      const event_participant = Array.isArray(item.event_participant)
                        ? item.event_participant.filter(p => p.status === 'Registered')
                        : (item.event_participant ? [item.event_participant] : []).filter(p => p.status === 'Registered');
                  
                      return {
                        ...item,
                        businessUnit,
                        event_participant
                      };
                    }).filter(e => e.event_participant.length > 0) // optional: exclude events with no registered participants
                );
                                
                  
            } catch (err) {
                showAlert({
                    icon: 'warning',
                    title: 'Connection Ended',
                    text: 'Unablet to connect to the server. Please try again later.',
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
            fetchData();
        }
    }, [token]);
    

    const handleDateChange = (date) => {
        // Toggle: if clicked date is same as current, unselect it
        if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
          setSelectedDate(null); // unselect
        } else {
          setSelectedDate(date); // select new
        }
      };    

    if (!datas) {
        // No event found after loading
        return (
          <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 p-5">
            <p className="text-red-700 text-xl font-semibold mb-4">Survey/Vote not found.</p>
            <button
              onClick={() => navigate('/')}
              className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
            >
              Go Back Home
            </button>
          </div>
        );
    }  
  
    return (
        <div className="w-full min-h-screen flex flex-col bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto">
        {loading ? (
            <SurveyLoader />
          ) : (
            <>
            <motion.div
            variants={pageVariants}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.3, ease: "easeInOut" }}
            className="p-5"
            >
                <div className="flex items-center justify-between mb-2">
                    <div className="flex-1">
                        <button
                            onClick={() => navigate(`/`)}
                            className="text-red-700 text-xl font-bold flex items-center gap-1 pr-2 py-1"
                        >
                            <i className="ri-arrow-left-line"></i>
                        </button>
                    </div>
                </div>
                <div className="flex items-start justify-between mb-2 px-2 gap-2">
                    <div className="flex-1 inline-flex flex-col justify-center items-start gap-2">
                        <div className="self-stretch inline-flex justify-start items-center gap-1">
                            <div className="flex-1 justify-start text-red-700 text-lg font-semibold leading-tight">Your Voice Matters</div>
                        </div>
                        <div className="self-stretch justify-start text-stone-600 text-sm font-medium leading-tight">Take part in active surveys and vote for your favorites!</div>
                    </div>
                    <div className="w-1/3 relative rounded-lg overflow-hidden">
                    <img
                        className="w-full h-full object-cover"
                        src={getImageUrl(apiUrl, 'assets/images/surveys/banner-survey-img.png')}
                        alt="Banner"
                        />
                    </div>
                </div>
            </motion.div>
            <motion.div
            variants={pageVariants2}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.2, ease: "easeInOut" }}
            className="flex-1 w-full bg-red-700 rounded-t-3xl p-5 overflow-auto"
            >
                <div className="flex flex-col justify-start items-start gap-3 w-full">
                {mergedData.length === 0 ? (
                    <div className="w-full justify-center text-center text-white font-medium py-4">
                        No Survey / Vote available.
                    </div>
                    ) : (
                        mergedData.map((data, index) => {    
                            const { daysUntil } = dateTimeHelper(data);  
                            const participated = Array.isArray(data.survey_participant) && data.survey_participant.length > 0;
                            
                            return (
                                <div onClick={() => navigate(`/${data.category}/${data.encrypted_id}`, {
                                    state: { participated }
                                })} key={data.encrypted_id} className={`w-full px-3 py-2 bg-white rounded-xl shadow-md flex justify-start items-center gap-3 overflow-hidden ${daysUntil === 'Ended' ? 'hidden' : ''}`}>
                                    <div className="relative overflow-hidden">
                                    <img
                                        className="w-10 h-12 object-cover"
                                        src={getImageUrl(apiUrl, data.icon)}
                                        alt="Survey"
                                        />
                                    </div>
                                    <div className="flex-1 py-2 flex justify-start items-center overflow-hidden">
                                        <div className="self-stretch flex flex-col justify-start items-start gap-1">
                                            <div className="min-w-40 justify-start text-stone-700 text-sm font-semibold leading-none capitalize">
                                                {data.category}: {data.title}
                                            </div>
                                            <div className="self-stretch flex flex-col justify-center items-start">
                                                <div className="self-stretch inline-flex justify-start items-center gap-4">
                                                    <div className="flex justify-start items-center gap-0.5">
                                                        <div className="justify-start text-stone-600 text-xs font-normal leading-none"><i className="ri-time-line me-1"></i>Ends in: {daysUntil}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {participated ? (
                                        <div className="px-2 py-1 rounded-full shadow-md flex justify-center items-center gap-2 overflow-hidden bg-white ring-1 ring-green-600">
                                            <i className="ri-check-double-line text-xl text-green-600"></i>
                                        </div>
                                        ) : (
                                        <button
                                            className="px-3 py-2 rounded-lg shadow-md flex justify-center items-center gap-2 overflow-hidden"
                                            style={{ backgroundColor: '#DEBD69' }}
                                        >
                                            <div className="text-white text-sm font-semibold leading-tight">Join In</div>
                                        </button>
                                        )}
                                </div>
                            )
                        })
                    )}
                </div>
            </motion.div>
            </>
          )
        }
        </div>
    );
}