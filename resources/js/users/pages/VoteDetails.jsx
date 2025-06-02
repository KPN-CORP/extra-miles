import React from "react";
import { useLocation, useNavigate, useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { useApiUrl } from "../components/Context/ApiContext";
import { showAlert } from "../components/Helper/alertHelper";
import { useAuth } from "../components/context/AuthContext";
import YouTubePlayer from "../components/Helper/youtubeHelper";
import { getImageUrl } from "../components/Helper/imagePath";
import VotingForm from '../components/Forms/VotingForm';
import BannerLoader from "../components/Loader/BannerLoader";
import { motion } from "framer-motion";
import SurveyLoader from "../components/Loader/SurveyLoader";

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

    const [selectedItem, setSelectedItem] = useState(null);

    const handleChange = (index) => {
        setSelectedItem(index);
    };

    const handleVoting = async (index) => {
        if (index) {
            await showAlert({
                title: "🙌 We’ve Got your vote!",
                text: `Thanks for participating. #${index + 1}`,
                icon: "success",
                customClass: {
                    title: 'text-xl font-bold', // Tailwind style to resize title
                    popup: 'p-6 rounded-xl',
                    confirmButton: 'bg-red-700 text-white rounded hover:bg-red-700',
                }
            });
        } else {
            await showAlert({
                title: "⚠️ No Nominee selected",
                text: "Please vote, Your vote matters",
                icon: "warning",
                customClass: {
                  title: 'text-lg font-bold',
                  popup: 'p-6 rounded-xl',
                  confirmButton: 'bg-yellow-500 text-white rounded hover:bg-yellow-600',
                }
              });
        }
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
                    { participated ? (
                            <p className="text-white text-base font-normal">Thanks for voting, you voted, we noted <span className="font-semibold"> {data.title}</span> gonna be lit! 🔥</p>
                    ) : (
                        <div className="w-full text-justify justify-start">
                            <div className="mb-2">
                                <p className="text-white font-medium text-sm">🌟We’re excited to showcase the amazing talents here at KPN! Now it’s your turn to support them — vote for your favorite star! ✨</p>
                            </div>
                            {data.content_link && (
                            <>
                                <div className="mb-2">
                                    <p className="text-white font-medium text-sm">📹Before you vote, make sure to watch the video of each talent here:</p>
                                </div>
                                <YouTubePlayer videoId={data.content_link} />
                            </>
                            )}
                        </div>
                    )}
                    <div>
                        {/* Form */}
                        <VotingForm participated={participated} />
                    </div>
                </div>
            </div>
        </motion.div>
    );
}