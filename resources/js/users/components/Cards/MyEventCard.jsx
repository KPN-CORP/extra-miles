import React from 'react';
import { useTranslation } from 'react-i18next';

import EventCard from './EventCard';
import { isAwaitingResponse, isEventDay } from '../Helper/eventRules';

/**
 * Kartu event milik karyawan, dengan tombol yang dipilih dari status pesertanya.
 *
 * Tiga keadaan:
 *   belum dijawab  -> tombol "Confirm" (amber), membuka detail event
 *   sudah dijawab  -> tombol "Scan"; merah pada hari-H, abu-abu sebelum itu
 *   sudah hadir    -> EventCard sendiri yang menggantinya dengan centang hijau
 *
 * Dipisahkan supaya beranda dan /my-events tidak masing-masing menyimpan versi
 * pemilihan tombol ini; sebelumnya logikanya hanya ada di ActivitySection dan
 * halaman penuhnya ikut memanggil komponen itu hanya demi logika tersebut.
 */
export default function MyEventCard({ event, onConfirm, onScan }) {
    const { t } = useTranslation();

    if (isAwaitingResponse(event)) {
        return (
            <EventCard
                event={event}
                onAction={onConfirm}
                buttonText={t('activity.confirm')}
                buttonClass="bg-amber-400 text-white text-[11px] font-bold"
            />
        );
    }

    return (
        <EventCard
            event={event}
            onAction={onScan}
            buttonText={
                <>
                    {t('activity.scan')} <i className="ms-1 ri-qr-scan-line"></i>
                </>
            }
            buttonClass={`${isEventDay(event) ? 'bg-brand-700' : 'bg-stone-300'} text-white text-[11px] font-semibold`}
        />
    );
}
