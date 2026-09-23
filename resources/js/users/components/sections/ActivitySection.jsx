import React, { useState } from "react";
import { useTranslation } from "react-i18next";
import MyEventCard from "../Cards/MyEventCard";
import { useNavigate } from "react-router-dom";
import { useMyEvents } from "../Context/MyEventsContext";
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
 *
 * `only` memilih satu bagian saja ('pending' atau 'registered'). Beranda
 * memakainya karena kedua bagian itu tidak lagi bersebelahan: yang belum
 * dijawab naik ke paling atas, di atas News Update, sedangkan undangan yang
 * sudah dikonfirmasi tetap di bawah Quick Access. Datanya sendiri satu, dibagi
 * lewat <MyEventsProvider>.
 *
 * `hideWhenEmpty` membuang seksi ini sepenuhnya kalau tidak ada isinya —
 * termasuk judul dan EmptyState-nya. Dipakai untuk bagian "belum dijawab" di
 * paling atas beranda: kalau tidak ada yang perlu dijawab, kotak "all caught
 * up" hanya mendorong berita turun tanpa menyampaikan apa pun, jadi News
 * Update yang kembali jadi seksi pertama.
 */
const ActivitySection = ({ limit = null, only = null, hideWhenEmpty = false }) => {
  const { events, loading, refetch } = useMyEvents();
  const [isQRModalOpen, setIsQRModalOpen] = useState(false);
  const [selectedEvent, setSelectedEvent] = useState(null);
  const navigate = useNavigate();
  const { t } = useTranslation();

  const showPending = only === null || only === 'pending';
  const showRegistered = only === null || only === 'registered';

  const handleConfirm = (event) => navigate(`/event/${event.encrypted_id}`);

  const handleScanQR = (event) => {
    if (!hasAttended(event)) {
      setIsQRModalOpen(true);
      setSelectedEvent(event);
    }
  };

  if (loading) {
    // Seksi yang boleh hilang tidak menampilkan skeleton: menaruh rangka di
    // atas berita lalu menghapusnya begitu data datang justru membuat halaman
    // melompat untuk karyawan yang memang tidak punya undangan tertunda.
    if (hideWhenEmpty) {
      return null;
    }

    return <ActivityLoader height={only ? 100 : 240} />;
  }

  const pending = events.filter(isAwaitingResponse).sort(bySoonest);
  const registeredEvents = events.filter(isConfirmedUpcoming).sort(bySoonest);

  const visiblePending = limit ? pending.slice(0, limit) : pending;
  const visibleRegistered = limit ? registeredEvents.slice(0, limit) : registeredEvents;

  const shownCount =
    (showPending ? pending.length : 0) + (showRegistered ? registeredEvents.length : 0);

  if (hideWhenEmpty && shownCount === 0) {
    return null;
  }

  // Tautan hanya muncul kalau memang ada yang tersembunyi.
  const showAllFor = (count) =>
    limit && count > limit
      ? { actionLabel: t('common.showAll'), onAction: () => navigate(ALL_PATH) }
      : {};

  return (
    <div className="flex flex-col gap-6">
      {/* Section 1: Waiting for Your Response */}
      {showPending && (
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
      )}

      {/* Section 2: Events You're Invited To Join */}
      {showRegistered && (
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
      )}

      <QRScannerModal
        isOpen={isQRModalOpen}
        event={selectedEvent}
        onClose={() => setIsQRModalOpen(false)}
        onScanSuccess={refetch}
      />
    </div>
  );
};

export default ActivitySection;
