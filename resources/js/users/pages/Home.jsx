import React, { useEffect, useState } from 'react';
import { useNavigate } from "react-router-dom"
import { useApiUrl } from "../components/context/ApiContext"; // Assuming you have a context for API URL
import NewsSection from '../components/sections/NewsSection'; // Assuming you have a NewsCard component
import MenuSection from '../components/sections/MenuSection'; // Assuming you have a NewsCard component
import axios from 'axios';
import ActivitySection from '../components/sections/ActivitySection';
import QuoteSection from '../components/sections/QuoteSection';
import AssetSection from '../components/sections/AssetSection';
import { showAlert } from '../components/Helper/alertHelper';
import { useAuth } from '../components/context/AuthContext';
import PageLoader from '../components/Loader/PageLoader';
import { getImageUrl } from '../components/Helper/imagePath';

const Home = () => {
    const apiUrl = useApiUrl(); // Get the API URL from context
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();
    const { token, user } = useAuth(); 
    const [loadingBanner, setLoadingBanner] = useState(true);    

    useEffect(() => {        
        if (!token) {
            showAlert({
                icon: 'warning',
                title: 'Session Ended',
                text: 'Your session has ended.',
                timer: 2500,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = "https://kpncorporation.darwinbox.com/";
            });
            return;
        } else {
            setLoading(false);
        }    
      }, []);

      if (loading) {
        return <PageLoader />;
      }

    const handleImageLoad = () => {
        setLoadingBanner(false);
    };

    return (
        <>
            <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen p-5">
                <div className="fixed bottom-0 right-0 w-44 h-40 overflow-hidden">
                    <img
                    className="w-full h-full object-cover"
                    src={getImageUrl(apiUrl, 'assets/images/Element Extra Mile 1.png')}
                    alt="attribute"
                    />
                </div>
                <img className={`w-full h-36 left-0 top-0 absolute`} src={getImageUrl(apiUrl, 'assets/images/img-banner.png')} />
                <div className="w-full px-5 py-4 left-0 top-[150px] absolute rounded-tl-[30px] rounded-tr-[30px] flex flex-col gap-6 overflow-hidden">
                    <div className="flex flex-col gap-2">
                    {/* Employee Section */}
                        <div className="flex items-center justify-between">
                            {user?.fullname && (
                                <div className="text-red-700 text-xs font-bold">Welcome back, {user?.fullname}!</div>
                            )}
                        </div>
                    </div>
                    <NewsSection />
                    <MenuSection />
                    <ActivitySection />
                    <QuoteSection />
                    <AssetSection />
                </div>
            </div>
        </>
    );
};

export default Home;