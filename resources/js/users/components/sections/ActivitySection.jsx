import React, { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import MyEventCard from "../Cards/MyEventCard";
import { useApiUrl } from "../Context/ApiContext";
import { showAlert } from "../Helper/alertHelper";
import { useAuth } from "../Context/AuthContext";
import axios from 'axios';
import { useNavigate } from "react-router-dom";
import ActivityLoader from "../Loader/ActivityLoader";
import QRScannerModal from '../Helper/QrScannerModal';
import SectionHeader from "../Layout/SectionHeader";
import EmptyState from "../Layout/EmptyState";
import {
  bySoonest,
  hasAttended,
  isAwaitingResponse,
  isConfirmedUpcoming,
} from "../Helper/eventRules";

// Halaman tujuan "Show All". Bukan /event: halaman itu memuat /api/events
// (seluruh event, dengan filter kalender), bukan daftar milik karyawan ini.
const ALL_PATH = '/my-events';

/**
 * `limit` membatasi jumlah kartu per bagian dan memunculkan tautan "Show All".
 * Beranda selalu memakainya; /my-events punya daftar tergabung sendiri dengan
 * filter, jadi tidak memanggil komponen ini.
 */
const ActivitySection = ({ limit = null }) => {
  const apiUrl = useApiUrl();
  const [loading, setLoading] = useState(true);
  const [events, setEvents] = useState([]);
  const [isQRModalOpen, setIsQRModalOpen] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const { token } = useAuth();
  const navigate = useNavigate();
  const { t } = useTranslation();

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
        title: t('alerts.connectionEnded'),
        text: t('alerts.connectionEndedText'),
        timer: 2500,
        showConfirmButton: false,
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEvent();
  }, []);

  const handleConfirm = (event) => navigate(`/event/${event.encrypted_id}`);

  const handleScanQR = (event) => {
    if (!hasAttended(event)) {
      setIsQRModalOpen(true);
      setSelectedEvent(event);
    }
  };

  const registeredEvents = events.filter(isConfirmedUpcoming).sort(bySoonest);

  if (loading) {
    return <ActivityLoader />;
  }

  const pending = events.filter(isAwaitingResponse).sort(bySoonest);

  const visiblePending = limit ? pending.slice(0, limit) : pending;
  const visibleRegistered = limit ? registeredEvents.slice(0, limit) : registeredEvents;

  // Tautan hanya muncul kalau memang ada yang tersembunyi.
  const showAllFor = (count) =>
    limit && count > limit
      ? { actionLabel: t('common.showAll'), onAction: () => navigate(ALL_PATH) }
      : {};

  return (
    <div className="flex flex-col gap-6">
      {/* Section 1: Waiting for Your Response */}
      <section className="flex flex-col gap-3">
        <SectionHeader
          title={t('activity.waitingForResponse')}
          badge={pending.length}
          {...showAllFor(pending.length)}
        />

        {pending.length > 0 ? (
          <>
            <div className="flex gap-2 p-3 rounded-2xl bg-amber-50 border border-amber-200">
              <i className="ri-alarm-warning-line text-amber-600 text-base shrink-0" aria-hidden="true" />
              <p className="text-amber-900 text-[11.5px] leading-relaxed">
                {t('activity.confirmationDeadline')}
              </p>
            </div>

            <div className="flex flex-col gap-2">
              {visiblePending.map((event) => (
                <MyEventCard
                  key={event.encrypted_id}
                  event={event}
                  onConfirm={handleConfirm}
                  onScan={handleScanQR}
                />
              ))}
            </div>
          </>
        ) : (
          <EmptyState
            tone="success"
            icon="ri-checkbox-circle-line"
            title={t('activity.allCaughtUpTitle')}
            description={t('activity.allCaughtUpText')}
          />
        )}
      </section>

      {/* Section 2: Events You're Invited To Join */}
      <section className="flex flex-col gap-3">
        <SectionHeader
          title={t('activity.invitedToJoin')}
          badge={registeredEvents.length}
          {...showAllFor(registeredEvents.length)}
        />

        {registeredEvents.length > 0 ? (
          <div className="flex flex-col gap-2">
            {visibleRegistered.map((event) => (
              <MyEventCard
                key={event.encrypted_id}
                event={event}
                onConfirm={handleConfirm}
                onScan={handleScanQR}
              />
            ))}
          </div>
        ) : (
          <EmptyState
            icon="ri-calendar-2-line"
            description={t('activity.noPendingEvent')}
          />
        )}
      </section>

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
