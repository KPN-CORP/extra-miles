import React from "react";
import { useApiUrl } from "../context/ApiContext";
import { showAlert } from "../Helper/alertHelper";
import { useNavigate } from "react-router-dom";
import { dateTimeHelper } from "../Helper/dateTimeHelper";
import { getImageUrl } from "../Helper/imagePath";

const EventCard = ({ event, onAction, buttonText, buttonClass }) => {
  const apiUrl = useApiUrl();
  const { day, month, year, startTime, endTime, isOngoing, totalDay, endDay, endMonth, endYear } = dateTimeHelper(event); 
  
  const date = new Date(event.start_date);
  const formattedDate = totalDay > 1 ? `${day} ${month} ${year}, ${startTime} - ${endDay} ${endMonth} ${endYear}, ${endTime}` : `${day} ${month} ${year}`;
  
  const navigate = useNavigate()
  const today = new Date();
  // Strip time portion
  const eventDateOnly = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const todayOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());  
  const eventDay =  eventDateOnly <= todayOnly;  

  return (
    <div className="w-full h-14 p-3 bg-white rounded-lg flex items-center gap-1">
      <div
        className="flex-1 flex flex-col gap-1 overflow-hidden"
        onClick={() => navigate(`/event/${event.encrypted_id}`)}
      >
        <div className="flex items-center gap-1">
          <img
            className="w-3 h-3"
            src={getImageUrl(apiUrl, event.logo)}
            alt={event.title}
          />
          <div className="text-stone-700 text-sm font-semibold truncate flex-1">{event.title}</div>
        </div>

        <div className="flex items-center gap-2 pl-4 text-stone-600 text-xs font-base truncate whitespace-nowrap">
          <div className="inline-block animate-marquee">
            {`${formattedDate} `}<span className={`font-medium ${totalDay > 1 ? 'd-none' : ''}`}>| {` ${startTime} - ${endTime} `}</span><span className="font-medium">|</span>{` ${event.event_location}`}
          </div>
        </div>
      </div>

      {event.status === 'Ongoing' && isOngoing && event.event_participant?.[0]?.attending_status === 'Attending' ? (
        <p className="p-2 pe-0">
        <span className="p-2 py-1 flex items-center justify-center rounded-full ring-1 ring-green-400 text-green-700 text-xl font-medium">
          <i className="ri-check-double-line"></i>
        </span>
      </p>      
      ) : (
        <button
          onClick={() => {
            if (eventDay || event.event_participant?.[0]?.status === 'Confirmation') {
              onAction(event);
            } else {
              showAlert({
                icon: 'info',
                title: 'Event Not Started',
                text: 'This event has not started yet.',
                confirmButtonText: 'OK',
                customClass: {
                  popup: 'rounded-lg',
                  confirmButton: 'bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded'
                }
              });
            }
          }}
          className={`px-3 py-2 rounded-lg shadow-md flex items-center justify-center ${buttonClass}`}
        >
          {buttonText}
        </button>
      )}
    </div>
  );
};

export default EventCard;