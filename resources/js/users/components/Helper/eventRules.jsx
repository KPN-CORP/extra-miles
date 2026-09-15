import { dateTimeHelper } from './dateTimeHelper';

/**
 * Aturan yang menentukan bagaimana satu event milik karyawan ditampilkan.
 *
 * Dipakai bersama oleh ActivitySection (beranda, dua bagian terpisah) dan
 * halaman /my-events (satu daftar yang bisa difilter). Keduanya harus memakai
 * definisi yang sama tentang "belum dikonfirmasi" dan "sudah dikonfirmasi" --
 * kalau tidak, jumlah di beranda dan isi halaman penuh bisa berbeda.
 *
 * `/api/my-event` sudah menyaring peserta ke employee_id yang login, jadi
 * event_participant selalu berisi maksimal satu baris: baris milik orang itu.
 */

// Nilai status pada event_participants.
export const AWAITING = 'Confirmation';
export const CONFIRMED = 'Registered';

export const participantOf = (event) => event?.event_participant?.[0] ?? null;

export const participantStatus = (event) => participantOf(event)?.status ?? null;

export const hasAttended = (event) => participantOf(event)?.attending_status === 'Attending';

/**
 * Undangan yang menunggu jawaban. Status event-nya ikut diperiksa: undangan
 * pada event yang sudah ditutup tidak bisa dijawab lagi, jadi tidak ditawarkan.
 */
export const isAwaitingResponse = (event) =>
    participantStatus(event) === AWAITING &&
    (event.status === 'Open Registration' || event.status === 'Full Booked');

/** Sudah dikonfirmasi dan tanggalnya belum lewat. */
export const isConfirmedUpcoming = (event) =>
    participantStatus(event) === CONFIRMED && !dateTimeHelper(event).isClosed;

/** Terdekat lebih dulu -- yang paling mendesak ada di atas. */
export const bySoonest = (a, b) => new Date(a.start_date) - new Date(b.start_date);

/** Tombol pindai baru menyala pada hari pelaksanaan atau sesudahnya. */
export const isEventDay = (event) => {
    const date = new Date(event.start_date);
    const today = new Date();

    return (
        new Date(date.getFullYear(), date.getMonth(), date.getDate()) <=
        new Date(today.getFullYear(), today.getMonth(), today.getDate())
    );
};
