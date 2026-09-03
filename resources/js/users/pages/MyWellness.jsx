import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { motion } from 'motion/react';

import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import CardLoader from '../components/Loader/CardLoader';
import WellnessQrScannerModal from '../components/Helper/WellnessQrScannerModal';
import { formatSession, statusStyle } from '../components/Helper/wellnessHelper';

const pageVariants = {
    initial: { opacity: 0, x: 0 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: 0 },
};

export default function MyWellness() {
    const navigate = useNavigate();
    const apiUrl = useApiUrl();
    const { token } = useAuth();

    const [registrations, setRegistrations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [busyId, setBusyId] = useState(null);
    const [scannerOpen, setScannerOpen] = useState(false);

    const fetchRegistrations = useCallback(async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/wellness/my-registrations`, {
                headers: { Authorization: `Bearer ${token}` },
            });
            setRegistrations(res.data);
        } catch (err) {
            showAlert({
                icon: 'warning',
                title: 'Connection Ended',
                text: 'We could not load your registrations. Please try again.',
                timer: 2500,
                showConfirmButton: false,
            });
        } finally {
            setLoading(false);
        }
    }, [apiUrl, token]);

    useEffect(() => {
        fetchRegistrations();
    }, [fetchRegistrations]);

    const handleCancel = async (registration) => {
        const confirmed = await showAlert({
            icon: 'warning',
            title: 'Cancel your registration?',
            text: 'Your seat will be released to the next person on the waitlist.',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'Keep it',
        });

        if (!confirmed.isConfirmed) return;

        setBusyId(registration.id);

        try {
            await axios.post(
                `${apiUrl}/api/wellness/registrations/cancel`,
                { registration_id: registration.id },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: 'Cancelled',
                text: 'Your registration has been cancelled.',
                timer: 2200,
                showConfirmButton: false,
            });

            fetchRegistrations();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: 'Could not cancel',
                text: err.response?.data?.error || 'Something went wrong. Please try again.',
            });
        } finally {
            setBusyId(null);
        }
    };

    const checkInAvailable = registrations.some((r) => r.can_check_in);

    return (
        <motion.div
            variants={pageVariants}
            initial="initial"
            animate="animate"
            exit="exit"
            transition={{ duration: 0.25 }}
            className="w-full min-h-screen bg-gradient-to-br from-stone-50 to-orange-200 overflow-auto"
        >
            <div className="px-5 pt-5 pb-24 flex flex-col gap-4">
                <div className="flex items-center gap-2">
                    <button onClick={() => navigate('/wellness')} className="text-red-700 text-xl" aria-label="Back">
                        <i className="ri-arrow-left-line"></i>
                    </button>
                    <div className="flex-1 text-center text-red-700 text-base font-semibold">My Wellness</div>
                    <div className="w-6"></div>
                </div>

                {checkInAvailable && (
                    <button
                        onClick={() => setScannerOpen(true)}
                        className="w-full p-3 bg-red-700 rounded-lg shadow-md text-white text-xs font-semibold flex items-center justify-center gap-2"
                    >
                        <i className="ri-qr-scan-2-line"></i> Scan QR to check in
                    </button>
                )}

                {loading ? (
                    <>
                        <CardLoader />
                        <CardLoader />
                    </>
                ) : registrations.length === 0 ? (
                    <div className="bg-white rounded-xl p-6 text-center shadow-sm">
                        <i className="ri-calendar-line text-3xl text-stone-300"></i>
                        <p className="mt-2 text-stone-500 text-xs">You have no wellness registrations yet.</p>
                        <button
                            onClick={() => navigate('/wellness')}
                            className="mt-3 px-4 py-2 rounded-lg bg-red-700 text-white text-[10px] font-semibold"
                        >
                            Browse activities
                        </button>
                    </div>
                ) : (
                    registrations.map((registration) => {
                        const when = formatSession(registration.schedule);
                        const busy = busyId === registration.id;

                        return (
                            <div key={registration.id} className="bg-white rounded-xl shadow-sm p-3">
                                <div className="flex gap-3">
                                    <div className="w-14 shrink-0 rounded-lg bg-red-700 text-white flex flex-col items-center justify-center py-2">
                                        <span className="text-lg font-bold leading-none">{when.day}</span>
                                        <span className="text-[10px] uppercase">{when.month}</span>
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <div className="text-stone-800 text-sm font-semibold truncate">
                                            {registration.activity?.name}
                                        </div>
                                        {registration.activity?.type && (
                                            <div className="text-[10px] text-red-700 font-medium">
                                                {registration.activity.type}
                                            </div>
                                        )}
                                        <div className="mt-1 text-[10px] text-stone-500 flex flex-col gap-0.5">
                                            <span><i className="ri-time-line me-1"></i>{when.time}</span>
                                            {registration.schedule?.location && (
                                                <span className="truncate">
                                                    <i className="ri-map-pin-line me-1"></i>{registration.schedule.location}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-3 flex items-center gap-2 flex-wrap">
                                    <span className={`px-2 py-1 rounded-full text-[10px] font-semibold ${statusStyle(registration.status)}`}>
                                        {registration.status_label}
                                    </span>

                                    {registration.attended_at && (
                                        <span className="px-2 py-1 rounded-full text-[10px] font-semibold bg-green-100 text-green-700">
                                            <i className="ri-check-double-line me-1"></i>Attended
                                        </span>
                                    )}

                                    <div className="flex-1"></div>

                                    {registration.can_check_in && (
                                        <button
                                            onClick={() => setScannerOpen(true)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-red-700 text-white"
                                        >
                                            <i className="ri-qr-scan-2-line me-1"></i>Check in
                                        </button>
                                    )}

                                    {registration.can_cancel && !registration.attended_at && (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleCancel(registration)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-white text-red-700 ring-1 ring-red-700 ring-inset disabled:opacity-50"
                                        >
                                            {busy ? 'Please wait...' : 'Cancel'}
                                        </button>
                                    )}
                                </div>

                                {registration.status === 'waitlisted' && (
                                    <p className="mt-2 text-[10px] text-stone-500 leading-relaxed">
                                        You are on the waitlist. If a seat frees up we will move you up automatically,
                                        and the admin will review your registration.
                                    </p>
                                )}
                            </div>
                        );
                    })
                )}
            </div>

            <WellnessQrScannerModal
                isOpen={scannerOpen}
                onClose={() => setScannerOpen(false)}
                onScanSuccess={fetchRegistrations}
            />
        </motion.div>
    );
}
