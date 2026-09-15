import React from 'react';
import { useTranslation } from 'react-i18next';
import LanguageToggle from '../components/Layout/LanguageToggle';
import AppHeader from '../components/Layout/AppHeader';
import { useParams, useNavigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import { getImageUrl } from '../components/Helper/imagePath';
import EventLoader from '../components/Loader/EventLoader';
import parse from "html-react-parser";
import EvoForm from '../components/Forms/EvoForm';

export default function EvoRegistration() {
  const apiUrl = useApiUrl();
  const [event, setEvent] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { token, user } = useAuth();
  const [hasRegistered, setHasRegistered] = useState(false);
  const { t, i18n } = useTranslation();
  
  useEffect(() => {
    const checkRegistrationAndFetchEvent = async () => {
      
      try {
        // Check registration status
        const registrationRes = await axios.get(`${apiUrl}/api/evo/check-registration`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });              

        setHasRegistered(registrationRes.data.registered);
        
        // Fetch event details
        const eventRes = await axios.get(`${apiUrl}/api/evo`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        
        setEvent(eventRes.data[0]);
      } catch (err) {
        if (err.response && err.response.status === 401) {
          console.error('Unauthorized or token expired. Redirecting to login.');
          navigate('/', { state: { from: window.location.pathname } });
        } else {
          console.error('Failed to fetch event or check registration:', err);
        }
      } finally {
        setLoading(false);
      }
    };

    checkRegistrationAndFetchEvent();
  }, [apiUrl, token, navigate]);

  if (loading) {
    return <EventLoader />;
  }
  
  if (!event) {
    // No event found after loading
    return (
      <div className="flex flex-col items-center justify-center app-surface app-bg p-5">
        <p className="text-red-700 text-xl font-semibold mb-4">{t('event.notFound')}</p>
        <button
          onClick={() => navigate('/')}
          className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
        >
          {t('common.goBackHome')}
        </button>
      </div>
    );
  }    

  const date = new Date(event.start_date);
  const month = date.toLocaleString(i18n.resolvedLanguage === 'id' ? 'id-ID' : 'en-US', { month: 'short' });
  const day = date.getDate();
  const year = date.getFullYear();  

  return (
    <div className="w-full relative app-surface app-bg overflow-auto">
      <AppHeader
        title={t('evo.title')}
        trailing={<LanguageToggle />}
      />
      <div className="app-container px-5 pt-4 pb-10">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
            {event.image && <img className="w-full object-cover rounded-lg" src={getImageUrl(apiUrl, event.image)} />}

            <div className='grid grid-cols-1 md:grid-cols-3 gap-2 text-sm'>
                <div className="self-stretch justify-start text-red-700 text-lg font-semibold">{event.title}</div>
                <div className="prose prose-sm leading-relaxed text-stone-800 max-w-none [&>p mb-0]:mb-4 [&>h1]:mb-8 [&>h2]:mb-6 [&>h3]:mb-4 [&>h4]:mb-4 [&>h1]:font-semibold [&>h2]:font-semibold [&>h3]:font-semibold [&>h4]:font-semibold [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:mb-4 [&>li]:mb-1">
                  {parse(event.description)}
                </div>
            </div>
            <div className="w-full mb-4 text-sm">
              <div className="flex flex-col gap-3 text-stone-700">
                {/* Name */}
                <div className="grid grid-cols-5 gap-2 items-center">
                  <p className="font-semibold col-span-1">{t('common.name')}</p>
                  <p className="col-span-4">: {user?.fullname ? `${user.fullname} (${user.employee_id})` : '-'}</p>
                </div>

                {/* Email */}
                <div className="grid grid-cols-5 gap-2 items-center">
                  <p className="font-semibold col-span-1">{t('common.email')}</p>
                  <p className="col-span-4">: {user?.email || '-'}</p>
                </div>
              </div>
            </div>
            <div>
              {/* Form */}
              <EvoForm encryptedID={event.encrypted_id} registered={hasRegistered} />
            </div>
        </div>
      </div>
    </div>
  );
}
