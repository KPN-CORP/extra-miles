import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { useApiUrl } from '../components/Context/ApiContext';
import { PuffLoader, SyncLoader } from 'react-spinners';
import { useAuth } from '../components/context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import PageLoader from '../components/Loader/PageLoader';
import { dateTimeHelper } from '../components/Helper/dateTimeHelper';
import { getImageUrl } from '../components/Helper/imagePath';

export default function EventDetails() {
  const apiUrl = useApiUrl();
  const { id } = useParams();
  const [event, setEvent] = useState(null);
  const [loading, setLoading] = useState(true);
  const { token } = useAuth();
  const navigate = useNavigate();
  const [hasRegistered, setHasRegistered] = useState(false);
  const [eventParticipant, setEventParticipant] = useState(null);
  
  const handleConfirm = async (event) => {
    const result = await showAlert({
      icon: 'question',
      title: 'Confirmation',
      text: `Are you sure you can join?`,
      confirmButtonText: `Yes, of course!`,
      showCancelButton: true,
      reverseButtons: true,
      customClass: {
        popup: 'rounded-lg',
        confirmButton: 'bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded',
        cancelButton: 'bg-stone-500 hover:bg-stone-600 px-4 py-2 rounded'
      }
    });
  
    if (result.isConfirmed) {
      try {
        await axios.post(`${apiUrl}/api/event-confirmation`, {
          eventId: event.encrypted_id,
          status: 'confirm'
        }, {
          headers: { Authorization: `Bearer ${token}` },
        });
  
        await showAlert({
          title: "Confirmed!",
          text: "Thank you! We’ll see you there! 🎉",
          icon: "success"
        });
  
        navigate('/');
  
      } catch (error) {
        showAlert({
          title: "Error",
          text: "Failed to confirm your attendance. Please try again.",
          icon: "error"
        });
      }
    }
  };
  
  const handleCancel = async (event) => {
    const reasonResult = await showAlert({
      input: "textarea",
      inputLabel: "Please type your reason",
      inputPlaceholder: "Type your reason here...",
      inputAttributes: {
        "aria-label": "Type your reason here"
      },
      showCancelButton: true,
      reverseButtons: true,
      confirmButtonText: 'Submit',
      icon: "question",
      customClass: {
        popup: 'rounded-lg',
        confirmButton: 'bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded',
        cancelButton: 'bg-stone-500 hover:bg-stone-600 px-4 py-2 rounded'
      },
      inputValidator: (value) => {
        if (!value || value.trim() === '') {
          return 'Please provide a reason before submitting.';
        }
      }
    })
    
    if (reasonResult.isConfirmed) {
      try {
        await axios.post(`${apiUrl}/api/event-confirmation`, {
          eventId: event.encrypted_id,
          status: 'cancel',
          messages: reasonResult.value,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        });

        await showAlert({
          title: 'Canceled',
          text: "No worries — thank you for letting us know. We hope to see you at our next event!",
          icon: "info"
        });

        navigate('/');

      } catch (error) {
        showAlert({
          title: "Error",
          text: "Failed to submit your reason. Please try again.",
          icon: "error"
        });
      }
    }
  };
    
  useEffect(() => {
    
    const fetchEvent = async () => {
      try {
        // Check registration status
        const registrationRes = await axios.get(`${apiUrl}/api/events/check-registration/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });         

        setHasRegistered(
          registrationRes.data.registered && typeof registrationRes.data.registered === 'object' && !Array.isArray(registrationRes.data.registered)
        );
        
        setEventParticipant(registrationRes.data.registered);
        const res = await axios.get(`${apiUrl}/api/events/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });                    
        setEvent(res.data);
      } catch (err) {       
            console.log(err);
      } finally {
        setLoading(false);
      }
    };
    
    fetchEvent();
  }, [id]);  

  if (loading) {
    return <PageLoader />;
  }
  
  if (!event) {
    // No event found after loading
    return (
      <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 p-5">
        <p className="text-red-700 text-xl font-semibold mb-4">Event not found.</p>
        <button
          onClick={() => navigate('/')}
          className="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800"
        >
          Go Back Home
        </button>
      </div>
    );
  }  

  const { day, month, year, startTime, endTime, eventStatus, isClosed, isOngoing  } = dateTimeHelper(event); 
  const formattedDate = `${day} ${month} ${year}`;  
  const getStatusColors = (status) => {
    if (isClosed) {
        return { text: "text-black", bg: "bg-gray-100" };
    } else if (isOngoing) {
        return { text: "text-white", bg: "bg-blue-500" };
    } else {
        if (hasRegistered) {
          if (eventParticipant.status === 'Waiting List' || eventParticipant.status === 'Confirmation') {
            return { text: "text-white", bg: "bg-yellow-400" };
          }
          if (eventParticipant.status === 'Registered') {
            return { text: "text-white", bg: "bg-green-600" };
          }
          if (eventParticipant.status === 'Canceled') {
            return { text: "text-gray-600", bg: "bg-gray-200" };
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
    <div className="w-full h-screen relative bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto min-h-screen p-5">
        {/* Header Section */}
        <div className="flex items-center justify-between mb-2">
            <div className="flex-1">
                <button
                    onClick={() => window.history.back()}
                    className="text-red-700 text-xl font-bold flex items-center gap-1 px-2 py-1"
                >
                    <i className="ri-arrow-left-line"></i>
                </button>
            </div>
            <div className="flex-2 text-center text-red-700 text-lg font-bold">Upcoming Events</div>
            <div className="flex-1" /> {/* Spacer to balance layout */}
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <img
                src={getImageUrl(apiUrl, 'events', '', event.image)}
                alt={event.title}
                className="w-full object-fill rounded-lg"
            />
            <div className='grid grid-cols-1 md:grid-cols-3 gap-2'>
                <div className="self-stretch inline-flex justify-start items-center gap-4">
                  <div data-color="primary" data-size="H6" data-type="normal" className="bg-white/0 inline-flex flex-col justify-center items-center">
                    <div className={`px-2 py-1 ${statusColors.bg} rounded inline-flex justify-center items-center overflow-hidden`}>
                      <div className={`text-center justify-center ${statusColors.text} text-[10px] font-medium  leading-[10px]`}>{isClosed || isOngoing ? eventStatus : (eventParticipant?.status ?? event.status) }
                      </div>
                      {(event.status === "Ongoing" || isOngoing) && (
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
                </div>
                <div className="flex gap-2">
                {(Array.isArray(event.businessUnit) ? event.businessUnit : ['All BU']).map((unit, i) => (
                    <div key={i} data-color="light" data-size="H6" data-type="normal" className="bg-white/0">
                        <div className="px-2 py-1 bg-zinc-300 rounded inline-flex justify-start items-center overflow-hidden">
                            <div className="text-center justify-start text-stone-600 text-xs font-medium leading-3">{unit}</div>
                        </div>
                    </div>
                ))}
                </div>
                <div className="self-stretch justify-start text-red-700 text-lg font-semibold">{event.title}</div>
                <div className="self-stretch flex flex-col justify-start items-start gap-2">
                  <div className="self-stretch inline-flex justify-between items-center">
                    <div className="flex-1 flex justify-start items-center gap-0.5 text-stone-700">
                      <i className="ri-calendar-line"></i>
                      <div className="flex-1 justify-start text-sm font-normal leading-none">{formattedDate}</div>
                    </div>
                    <div className="flex-1 flex justify-start items-center gap-0.5 text-stone-700">
                      <i className="ri-time-line"></i>
                      <div className="flex-1 justify-start text-sm font-normal leading-none">{`${startTime} - ${endTime}`}</div>
                    </div>
                  </div>
                  <div className="self-stretch inline-flex justify-start items-center">
                    <div className="flex-1 flex justify-start items-center gap-0.5 text-stone-700">
                      <i className="ri-map-pin-line"></i>
                      <div className="flex-1 justify-start text-sm font-normal leading-none">{event.event_location}</div>
                    </div>
                    <div className="flex-1 flex justify-start items-center gap-0.5 text-stone-700">
                      <i className="ri-user-line"></i>
                      <div className="flex-1 justify-start text-sm font-normal leading-none">{event.quota}</div>
                    </div>
                  </div>
                </div>
            </div>
            {event.event_participant?.[0]?.status !== 'Confirmation' && (
            <div className='mb-2'>
                <p className="text-stone-700">{event.description}</p>
            </div>
            )}
            {!hasRegistered && event.status === "Open Registration" && (
                <button
                  onClick={() => navigate(`/event-registration/${event.encrypted_id}`)}
                  className="self-stretch px-5 py-2.5 bg-red-700 rounded-lg shadow-md inline-flex justify-center items-center gap-2 overflow-hidden"
                  type="button" // optional but good practice
                >
                  <div className="justify-start text-white text-sm font-semibold leading-tight">
                    Register Now!
                  </div>
                </button>
            )}
            {hasRegistered && event.event_participant?.[0]?.status === 'Confirmation' && (
              <>
              <div className=''>
                <p className="text-stone-700 font-medium">{`We’d love to have you at ${event.title}. Are you free to join?`}</p>
              </div>
              <div className='inline-flex justify-start items-center gap-4'>
                <button
                  onClick={() => handleCancel(event)}
                  className={`flex-1 flex px-3 py-2 rounded-lg shadow-md items-center justify-center bg-stone-400 text-white text-sm font-semibold`}
                >
                  No, I can't
                </button>
                <button
                  onClick={() => handleConfirm(event)}
                  className={`flex-1 flex px-3 py-2 rounded-lg shadow-md items-center justify-center bg-red-700 text-white text-sm font-semibold`}
                >
                  Yes, I can join
                </button>
              </div>
              </>
            )}
        </div>
    </div>
  );
}
