import React from "react";
import { useLocation, useNavigate, useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { useApiUrl } from "../components/context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import YouTubePlayer from "../components/Helper/youtubeHelper";
import { getImageUrl } from "../components/Helper/imagePath";
import VotingForm from '../components/Forms/VotingForm';
import BannerLoader from "../components/Loader/BannerLoader";
import { motion } from "framer-motion";
import SurveyLoader from "../components/Loader/SurveyLoader";
import CountdownTimer from "../components/Helper/countdownTImer";

const pageVariants = {
    initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    animate: { opacity: 1, x: 0 },       // Diam di tengah
    exit: { opacity: 0, x: 0 },       // Keluar ke kiri
};

export default function VoteList() {

    const navigate = useNavigate()
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token } = useAuth(); 
    const [animatedWidth, setAnimatedWidth] = useState("0%");
    const [loadingBanner, setLoadingBanner] = useState(true);
    const location = useLocation();
    const participated = location.state?.participated ?? false;
    const [eventEnded, setEventEnded] = useState(false);

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
                <div className="flex items-center justify-between mb-3">
                    <div className="flex-1">
                        <button
                            onClick={() => window.history.back()}
                            className="text-white text-xl font-bold flex items-center gap-1 pr-2 py-1"
                        >
                            <i className="ri-arrow-left-line"></i>
                        </button>
                    </div>
                    <div className="flex-2 text-center text-white text-lg font-bold">Let's Vote</div>
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
                        <span className="text-white text-base font-medium">🗳️ Voting Ends In...</span>
                        <CountdownTimer
                            endDateTime={`${data.end_date} ${data.time_end}`}
                            onEnd={() => setEventEnded(true)}
                        />
                    </div>
                    {participated ? (
                    <p className="text-white text-base font-normal">
                        Thanks for voting, you voted, we noted <span className="font-semibold">{data.title}</span> gonna be lit! 🔥
                    </p>
                    ) : eventEnded ? (
                    <div className="text-white text-base font-semibold">
                        Voting has ended. Thank you for your interest! 🛑
                    </div>
                    ) : (
                    <div className="w-full text-justify justify-start">
                        <div className="mb-2">
                        <p className="text-white font-medium text-sm">
                            🌟We’re excited to showcase the amazing talents here at KPN! Now it’s your turn to support them — vote for your favorite star! ✨
                        </p>
                        </div>

                        {data.content_link && (
                        <>
                            <div className="mb-2">
                            <p className="text-white font-medium text-sm">
                                📹Before you vote, make sure to watch the video of each talent here:
                            </p>
                            </div>
                            <YouTubePlayer videoId={data.content_link} />
                        </>
                        )}
                    </div>
                    )}

                    <div>
                        {/* Form */}
                        {!eventEnded ? <VotingForm participated={participated} eventEnded={eventEnded} /> 
                        : (
                            <div className="flex flex-col items-center justify-center">
                                <button
                                onClick={() => navigate('/survey')}
                                className="w-full px-5 py-2.5 rounded-lg shadow-md text-sm font-semibold text-red-700"
                                style={{ backgroundColor: '#DEBD69' }}
                                >
                                Go Back
                                </button>
                            </div> 
                        )
                        }
                    </div>
                </div>
            </div>
        </motion.div>
    );
}