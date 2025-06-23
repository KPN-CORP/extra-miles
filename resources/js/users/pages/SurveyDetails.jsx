import React from "react";
import { useLocation, useNavigate, useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';

import 'react-calendar/dist/Calendar.css';
import '../../../css/calendar-custom.css';
import { useApiUrl } from "../components/context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import { getImageUrl } from "../components/Helper/imagePath";
import SurveyForm from '../components/Forms/SurveyForm';
import BannerLoader from "../components/Loader/BannerLoader";
import SurveyLoader from "../components/Loader/SurveyLoader";
import { motion } from "framer-motion";
import CountdownTimer from "../components/Helper/countdownTImer";
import parse from "html-react-parser";

const pageVariants = {
    initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: 0 },       // Keluar ke kiri
};

export default function VoteList() {

    const navigate = useNavigate();
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingBanner, setLoadingBanner] = useState(true);
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token } = useAuth(); 
    const location = useLocation();
    const participated = location.state?.participated ?? false;  
    const [eventEnded, setEventEnded] = useState(false);
      

    const [selectedItem, setSelectedItem] = useState(null);

    const handleChange = (index) => {
        setSelectedItem(index);
    };

    const handleVoting = async (index) => {
        await showAlert({
            title: "Success!",
            text: `Your response has been recorded. Thanks a bunch for joining the survey!`,
            icon: "success",
            timer: 5000,
            showConfirmButton: false
        });
    };

    useEffect(() => {
        const fetchEvent = async () => {
            try {
                const res = await axios.get(`${apiUrl}/api/survey-vote/${id}`, {
                    headers: {
                      Authorization: `Bearer ${token}`,
                    },
                });                    
                setData(res.data);
            } catch (err) {
                showAlert({
                    icon: 'warning',
                    title: 'Connection Ended',
                    text: 'Unable to connect to the server. Please try again later.',
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => {
                    window.location.href = "https://kpncorporation.darwinbox.com/";
                });
            } finally {
                setLoading(false);
            }
        };
        if(token) {
            fetchEvent();
        }
    }, [token]);
    
    if (loading) {
        return (
            <div className="w-full h-screen relative bg-red-700 overflow-auto min-h-screen">
                <SurveyLoader />
            </div>
        );
      }      

    if (!data) {
        // No event found after loading
        return (
          <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 p-5">
            <p className="text-red-700 text-xl font-semibold mb-4">Survey/Vote not found.</p>
            <button
              onClick={() => window.history.back()}
              className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
            >
              Go Back Home
            </button>
          </div>
        );
    }  

    const handleImageLoad = () => {
        setLoadingBanner(false);
    };
  
    return (
        <motion.div
            variants={pageVariants}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.3, ease: "easeInOut" }}
        >
            <div className="w-full h-screen relative bg-red-700 overflow-auto min-h-screen p-5">
            {/* Header Section */}
                <div className="flex items-center justify-between mb-4">
                    <div className="flex-1">
                        <button
                            onClick={() => window.history.back()}
                            className="text-white text-xl font-bold flex items-center gap-1 pr-2 py-1"
                        >
                            <i className="ri-arrow-left-line"></i>
                        </button>
                    </div>
                    <div className="flex-2 text-center text-white text-lg font-bold">Ongoing Survey</div>
                    <div className="flex-1" /> {/* Spacer to balance layout */}
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {data.banner && (
                        <>
                            {loadingBanner && <BannerLoader className='w-full object-fill rounded-3xl' />}
                            <img
                            src={getImageUrl(apiUrl, data.banner)}
                            alt="Banner"
                            onLoad={handleImageLoad}
                            onError={handleImageLoad}
                            className={`w-full object-fill rounded-3xl border-2 border-white transition-opacity duration-500 ${
                                loadingBanner ? 'opacity-0' : 'opacity-100'
                            }`}
                            />
                        </>
                    )}
                    <div className="flex-col flex w-full text-center justify-center text-white text-2xl font-bold gap-1">
                        <span className="text-white text-base font-medium">📝 Survey Closes In...</span>
                        <CountdownTimer
                            endDateTime={`${data.end_date} ${data.time_end}`}
                            onEnd={() => setEventEnded(true)}
                        />
                    </div>
                    <div className="w-full text-justify justify-start">
                        <p className="text-white text-base font-semibold mb-2">Hi, {data.fullname}!👋</p>
                        { participated ? (
                            <p className="text-white text-sm font-normal">Thanks a bunch for taking part in
                            <span className="font-semibold"> {data.title}</span> survey!</p>
                        ) : eventEnded ? (
                            <div className="text-white text-base font-semibold">
                                Survey has closed. Thank you for your interest! 🛑
                            </div>
                        ): (
                            <div className="prose prose-sm leading-relaxed text-white max-w-none [&>p]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1 pl-2 pr-1">Thanks for attending the <span className="font-semibold">{data.title}</span> {parse(data.description)}</div>
                        )}
                    </div>
                    <div>
                        {/* Form */}
                        { participated || eventEnded ? (
                            <button onClick={() => window.history.back()} className="w-full flex flex-col text-center text-red-700 font-medium shadow-lg px-3 py-2 rounded-lg" style={{ backgroundColor: '#DEBD69' }}>Go Back</button>
                        ) : (
                            <SurveyForm />
                        )}
                    </div>
                </div>
            </div>
        </motion.div>
    );
}