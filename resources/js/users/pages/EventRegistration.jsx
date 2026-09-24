import React from 'react';
import { useTranslation } from 'react-i18next';
import { useParams, useNavigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { useApiUrl } from '../components/Context/ApiContext';
import EventForm from '../components/Forms/EventForm';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import AppHeader from '../components/Layout/AppHeader';
import AppShell from '../components/Layout/AppShell';
import { getImageUrl } from '../components/Helper/imagePath';
import EventLoader from '../components/Loader/EventLoader';


export default function EventDetails() {
  const apiUrl = useApiUrl();
  const { id } = useParams();
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
        const registrationRes = await axios.get(`${apiUrl}/api/events/check-registration/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });        

        setHasRegistered(registrationRes.data.registered);

        if (registrationRes.data.registered) {
          showAlert({
            icon: 'warning',
            title: t('event.alreadyRegistered'),
            text: t('event.alreadyRegisteredText'),
            timer: 2000,
            showConfirmButton: false,
          }).then(() => {
            navigate(`/event/${id}`);
          });
          return; // Exit early if already registered
        }

        // Fetch event details
        const eventRes = await axios.get(`${apiUrl}/api/events/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        setEvent(eventRes.data);
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
  }, [id, apiUrl, token, navigate]);

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
  const formattedDate = `${day} ${month} ${year}`;  
  const startTime = event.time_start.replace(/:/g, ':').slice(0, 5);
  const endTime = event.time_end ? event.time_end.replace(/:/g, ':').slice(0, 5) : t('date.end');

  return (
    <AppShell>
      <AppHeader title={t('event.registrationForm')} backTo="/event" />
      <div className="px-5 pt-4">
        <div className="grid grid-cols-1 gap-5 rail:grid-cols-[320px_minmax(0,1fr)] rail:items-start">
            {event.image && <img className="w-full object-cover rounded-lg" src={getImageUrl(apiUrl, event.image)} />}

            {/* <img className="w-full object-cover rounded-lg" src={getImageUrl(apiUrl, event.image)} /> */}
            <div className="flex flex-col gap-5">
            <div className='flex flex-col gap-2'>
                <div className="self-stretch justify-start text-red-700 text-lg font-semibold">{event.title}</div>
                <div className="flex gap-2">
                {(Array.isArray(event.businessUnit) ? event.businessUnit : [t('event.allBu')]).map((index, i) => (
                    <div key={i} data-color="light" data-size="H6" data-type="normal" className="bg-white/0">
                        <div className="px-2 py-1 bg-zinc-300 rounded inline-flex justify-start items-center overflow-hidden">
                            <div className="text-center justify-start text-stone-600 text-xs font-medium leading-3">{index}</div>
                        </div>
                    </div>
                ))}
                </div>
                <div className="self-stretch flex flex-col justify-start items-start gap-2">
                  <div className="self-stretch inline-flex justify-between items-center">
                    <div className="flex-1 flex justify-start items-center gap-1 text-stone-700">
                      <i className="ri-calendar-line"></i>
                      <div className="flex-1 justify-start text-sm font-normal leading-none">{formattedDate}</div>
                    </div>
                    <div className="flex-1 flex justify-start items-center gap-0.5 text-stone-700">
                      <i className="ri-time-line"></i>
                      <div className="flex-1 justify-start text-sm font-normal leading-none">{`${startTime} - ${endTime}`}</div>
                    </div>
                  </div>
                  <div className="self-stretch inline-flex justify-start items-center gap-4">
                    <div className="flex justify-start items-center gap-0.5 text-stone-700">
                      <i className="ri-map-pin-line"></i>
                      <div className="justify-start text-sm font-normal leading-none">{event.event_location}</div>
                    </div>
                  </div>
                </div>
            </div>
            <div className="w-full mb-4">
              <div className="flex flex-col gap-3 text-stone-700">
                {/* Name */}
                <div className="grid grid-cols-5 gap-2 items-center">
                  <p className="font-semibold col-span-1">{t('common.name')}</p>
                  <p className="col-span-4">{user?.fullname || '-'}</p>
                </div>

                {/* ID */}
                <div className="grid grid-cols-5 gap-2 items-center">
                  <p className="font-semibold col-span-1">{t('common.id')}</p>
                  <p className="col-span-4">{user?.employee_id || '-'}</p>
                </div>

                {/* Email */}
                <div className="grid grid-cols-5 gap-2 items-center">
                  <p className="font-semibold col-span-1">{t('common.email')}</p>
                  <p className="col-span-4">{user?.email || '-'}</p>
                </div>
              </div>
            </div>
            <div>
              {/* Form */}
              <EventForm />
            </div>
            </div>
        </div>
      </div>
    </AppShell>
  );
}
