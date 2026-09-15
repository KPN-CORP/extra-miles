import React from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";

import { showAlert } from "../Helper/alertHelper";
import { dateTimeHelper } from "../Helper/dateTimeHelper";

/**
 * Kartu event ringkas untuk daftar di beranda.
 *
 * `onAction` / `buttonText` / `buttonClass` menentukan tombol di kanan
 * (konfirmasi kehadiran atau pindai QR), sedangkan bagian kiri selalu membuka
 * detail event.
 */
const EventCard = ({ event, onAction, buttonText, buttonClass }) => {
  const navigate = useNavigate();
  const { t } = useTranslation();

  const { day, month, startTime, endTime, totalDay, endDay, endMonth } = dateTimeHelper(event);

  const date = new Date(event.start_date);
  const today = new Date();
  // Bandingkan tanggalnya saja; jam tidak menentukan apakah hari-H sudah tiba.
  const eventDateOnly = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const todayOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const eventDay = eventDateOnly <= todayOnly;

  const attending = event.event_participant?.[0]?.attending_status === 'Attending';
  const range = totalDay > 1 ? `${day} ${month} - ${endDay} ${endMonth}` : `${startTime} - ${endTime}`;

  return (
    <div className="w-full bg-white rounded-2xl shadow-card p-2.5 flex items-center gap-3">
      <button
        type="button"
        className="tap flex-1 min-w-0 flex items-center gap-3 text-left"
        onClick={() => navigate(`/event/${event.encrypted_id}`)}
      >
        <span className="w-12 shrink-0 rounded-xl bg-brand-50 text-brand-700 flex flex-col items-center justify-center py-1.5">
          <span className="text-[19px] font-extrabold leading-none">{day}</span>
          <span className="text-[9px] font-extrabold uppercase tracking-[0.07em] leading-none mt-0.5">{month}</span>
        </span>

        <span className="flex-1 min-w-0">
          <span className="block text-stone-800 text-[14px] font-extrabold tracking-[-0.012em] leading-tight clamp-1">
            {event.title}
          </span>
          <span className="mt-1 flex flex-col gap-0.5 text-stone-500 text-[11px] leading-tight">
            <span className="clamp-1">
              <i className="ri-time-line me-1" aria-hidden="true" />
              {range}
            </span>
            <span className="clamp-1">
              <i className="ri-map-pin-line me-1" aria-hidden="true" />
              {event?.event_location ?? '-'}
            </span>
          </span>
        </span>
      </button>

      {attending ? (
        <span
          className="shrink-0 w-9 h-9 grid place-items-center rounded-full bg-emerald-50 ring-1 ring-emerald-300 text-emerald-600 text-lg"
          title={t('activity.attended')}
        >
          <i className="ri-check-double-line" />
        </span>
      ) : (
        <button
          type="button"
          onClick={() => {
            if (eventDay || event.event_participant?.[0]?.status === 'Confirmation') {
              onAction(event);
            } else {
              showAlert({
                icon: 'info',
                title: t('event.notStarted'),
                text: t('event.notStartedText'),
                confirmButtonText: t('common.ok'),
                customClass: {
                  popup: 'rounded-lg',
                  confirmButton: 'bg-brand-700 hover:bg-brand-800 text-white px-4 py-2 rounded'
                }
              });
            }
          }}
          className={`tap shrink-0 px-3 py-2 rounded-xl shadow-sm flex items-center justify-center ${buttonClass}`}
        >
          {buttonText}
        </button>
      )}
    </div>
  );
};

export default EventCard;
