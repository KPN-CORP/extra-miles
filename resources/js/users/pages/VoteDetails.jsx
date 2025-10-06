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
import { motion } from "motion/react";
import SurveyLoader from "../components/Loader/SurveyLoader";
import CountdownTimer from "../components/Helper/countdownTImer";
import parse from "html-react-parser";

const pageVariants = {
    // initial: { opacity: 0, x: 0 },     // Masuk dari kanan
    // animate: { opacity: 1, x: 0 },       // Diam di tengah
    // exit: { opacity: 0, x: 0 },       // Keluar ke kiri
    initial: { opacity: 0, y: 100 },     // Masuk dari kanan
    animate: { opacity: 1, y: 0 },       // Diam di tengah
    exit: { opacity: 0, y: 100 },       // Keluar ke kiri
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
    const participate = location.state?.participated ?? false;
    const [participated, setParticipated] = useState(participate);
    
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
                    if (document.referrer) {
                    window.history.back();
                    } else {
                    window.location.href = 'https://kpncorporation.darwinbox.com/';
                    }

                });
            } finally {
                setLoading(false);
            }
        };
        if(token) {
            fetchEvent();
        }

        const localStatus = localStorage.getItem(`voted-${id}`) ?? participate;
        setParticipated(localStatus);
    }, [token, id]);

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
        
        <div className="w-full h-screen relative bg-red-700 overflow-auto min-h-screen p-5">
            <motion.div
                variants={pageVariants}
                initial="initial"
                animate="animate"
                exit="exit"
                transition={{ duration: 0.3, ease: "easeInOut" }}
            >
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
                        <div className="prose prose-sm leading-relaxed text-white max-w-none [&>p]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1 pl-2 pr-1 mb-2">
                            {parse(data.description)}
                        </div>

                        {data.content_link && (
                        <>
                            <div className="mb-2">
                            <p className="text-white font-medium text-sm">
                                📹Before you vote, make sure to watch this video:
                            </p>
                            </div>
                            <YouTubePlayer videoId={data.content_link} />
                        </>
                        )}
                        {data.other_link && (
                        <>
                            <div className="mb-2">
                            <a
                                href={data.other_link}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-white underline font-medium text-sm"
                            >
                                📎 Click here for more information
                            </a>
                            </div>
                        </>
                        )}
                    </div>
                    )}

                    <div>
                        {/* Form */}
                        {!eventEnded ? <VotingForm participated={participated} setParticipated={setParticipated} eventEnded={eventEnded} /> 
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
            </motion.div>
        </div>
    );
}