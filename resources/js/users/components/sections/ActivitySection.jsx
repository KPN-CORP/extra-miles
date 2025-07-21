import React, { useState, useEffect } from "react";
import EventCard from "../Cards/EventCard";
import { useApiUrl } from "../context/ApiContext";
import { showAlert } from "../Helper/alertHelper";
import { useAuth } from "../context/AuthContext";
import axios from 'axios';
import { useNavigate } from "react-router-dom";
import ActivityLoader from "../Loader/ActivityLoader";
import QRScannerModal from '../Helper/QrScannerModal';
import { dateTimeHelper } from "../Helper/dateTimeHelper";

const ActivitySection = () => {
  const apiUrl = useApiUrl();
  const [loading, setLoading] = useState(true);
  const [events, setEvents] = useState([]);
  const [isQRModalOpen, setIsQRModalOpen] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const { token } = useAuth();
  const navigate = useNavigate();
  const bounds = location.state?.bounds;

  // Fungsi fetch dipisah agar bisa dipanggil ulang
  const fetchEvent = async () => {
    try {
      const res = await axios.get(`${apiUrl}/api/my-event`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      setEvents(res.data.map(e => ({
        ...e,
        businessUnit: Array.isArray(e.businessUnit) ? e.businessUnit : [e.businessUnit]
      })));
    } catch (err) {
      showAlert({
        icon: 'warning',
        title: 'Connection Ended',
        text: 'Unable to connect to the server. Please try again later.',
        timer: 2500,
        showConfirmButton: false,
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
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
      fetchEvent();
  }, [bounds]);

  const handleConfirm = async (event) => {

    navigate(`/event/${event.encrypted_id}`, {
          state: {
            bounds: {
              width: bounds?.width ?? null,
              height: bounds?.height ?? null,
              top: bounds?.top ?? null,
              left: bounds?.left ?? null
            }
          }
        });
    
  };
  

  const handleScanQR = (event) => {
    if (event.event_participant?.[0]?.attending_status !== 'Attending') {
      setIsQRModalOpen(true);
      setSelectedEvent(event);
    }
  };

  const confirmationEvents = events.filter(
    (event) => event.event_participant?.[0]?.status === "Confirmation"
  );

  const registeredEvents = events.filter(
    (event) =>
      event.event_participant?.[0]?.status === "Registered" &&
    !dateTimeHelper(event).isClosed
  );

  if (loading) {
    return <ActivityLoader />;
  }

  return (
    <div className="flex flex-col gap-4">
      {/* Section 1: Waiting for Your Response */}
      <div className="flex flex-col gap-2">
        <div className="flex items-center text-red-700 text-sm font-bold leading-tight">
          Waiting for Your Response ⏳
        </div>
        {confirmationEvents.length > 0 ? (
          <>
            <div className="p-2 bg-rose-200 rounded outline outline-1 outline-offset-[-1px] outline-red-200 mb-2">
              <div className="text-red-900 text-xs font-normal leading-none">
                If we don’t receive your confirmation by D-2 (two days before the event), your spot
                will be automatically canceled and given to someone on the waiting list.
              </div>
            </div>
            {confirmationEvents
              .filter((event) => event.status === "Open Registration" || event.status === "Full Booked")
              .map((event) => (
                <EventCard
                  key={event.encrypted_id}
                  event={event}
                  onAction={handleConfirm}
                  buttonText="Confirm"
                  buttonClass="bg-yellow-400 text-white text-sm font-semibold"
                />
              ))}
          </>
        ) : (
          <div className="p-2 bg-green-200 rounded outline outline-1 outline-offset-[-1px] outline-green-400 mb-2">
            <div className="text-green-700 text-xs font-normal leading-none">
              You’re all caught up! 🎉 No pending invitations at the moment. Stay tuned for upcoming events!
            </div>
          </div>
        )}
      </div>

      {/* Section 2: Events You’re Invited To Join */}
      <div className="self-stretch flex flex-col justify-start items-start gap-2">
          <div className="flex-1 justify-center text-red-700 text-sm font-bold leading-tight">
            Events You’re Invited To Join 👀
          </div>
        {registeredEvents.length > 0 ? (
          <div className="w-full flex flex-col justify-start items-start gap-2">
            {registeredEvents.map((event) => {
              const eventDate = new Date(event.start_date);
              const today = new Date();
              const eventDateOnly = new Date(eventDate.getFullYear(), eventDate.getMonth(), eventDate.getDate());
              const todayOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());
              const eventDay = eventDateOnly <= todayOnly;              

              return (
                <EventCard
                  key={event.encrypted_id}
                  event={event}
                  onAction={handleScanQR}
                  buttonText={
                    <>
                      Scan <i className="ms-1 ri-qr-scan-line"></i>
                    </>
                  }
                  buttonClass={`${
                    eventDay ? "bg-red-700" : "bg-stone-300"
                  } text-white text-xs font-medium`}
                />
              );
            })}
          </div>
        ) : (
          <div className="p-2 bg-stone-100 rounded outline outline-1 outline-offset-[-1px] outline-stone-300 mb-2">
            <div className="text-stone-700 text-xs font-normal leading-none">
              You’ve no pending event at the moment. Stay tuned for upcoming events!
            </div>
          </div>
        )}
      </div>

      <QRScannerModal
        isOpen={isQRModalOpen}
        event={selectedEvent}
        onClose={() => setIsQRModalOpen(false)}
        onScanSuccess={fetchEvent}
      />
    </div>
  );
};

export default ActivitySection;
