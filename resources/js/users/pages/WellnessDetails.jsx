import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import parse from 'html-react-parser';
import { motion } from 'motion/react';

import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import PageLoader from '../components/Loader/PageLoader';
import { formatSession, seatLabel, statusStyle } from '../components/Helper/wellnessHelper';

const pageVariants = {
    initial: { opacity: 0, x: 0 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: 0 },
};

export default function WellnessDetails() {
    const { id } = useParams();
    const navigate = useNavigate();
    const apiUrl = useApiUrl();
    const { token } = useAuth();

    const [activity, setActivity] = useState(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(null);

    const fetchActivity = useCallback(async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/wellness/activities/${encodeURIComponent(id)}`, {
                headers: { Authorization: `Bearer ${token}` },
            });
            setActivity(res.data);
        } catch (err) {
            showAlert({
                icon: 'warning',
                title: 'Not Available',
                text: err.response?.data?.error || 'This activity could not be loaded.',
                timer: 2500,
                showConfirmButton: false,
            }).then(() => navigate('/wellness'));
        } finally {
            setLoading(false);
        }
    }, [apiUrl, id, token, navigate]);

    useEffect(() => {
        fetchActivity();
    }, [fetchActivity]);

    const handleRegister = async (schedule) => {
        const when = formatSession(schedule);

        const confirmed = await showAlert({
            icon: schedule.is_full ? 'warning' : 'question',
            title: schedule.is_full ? 'Session is full' : 'Register for this session?',
            html: schedule.is_full
                ? `This session is full. You can join the <b>waitlist</b> and we will move you up when a seat frees up.<br/><br/>${when.full}`
                : `${when.full}${schedule.location ? `<br/>${schedule.location}` : ''}<br/><br/>Your registration will be reviewed by the admin.`,
            showCancelButton: true,
            confirmButtonText: schedule.is_full ? 'Join waitlist' : 'Register',
            cancelButtonText: 'Not now',
        });

        if (!confirmed.isConfirmed) return;

        setSubmitting(schedule.id);

        try {
            const res = await axios.post(
                `${apiUrl}/api/wellness/registrations`,
                { schedule_id: schedule.id },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: 'Done',
                text: res.data.message,
                timer: 2600,
                showConfirmButton: false,
            });

            fetchActivity();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: 'Could not register',
                text: err.response?.data?.error || 'Something went wrong. Please try again.',
            });
        } finally {
            setSubmitting(null);
        }
    };

    const handleCancel = async (schedule) => {
        const confirmed = await showAlert({
            icon: 'warning',
            title: 'Cancel your registration?',
            text: 'Your seat will be released to the next person on the waitlist.',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'Keep it',
        });

        if (!confirmed.isConfirmed) return;

        setSubmitting(schedule.id);

        try {
            await axios.post(
                `${apiUrl}/api/wellness/registrations/cancel`,
                { registration_id: schedule.my_registration_id },
                { headers: { Authorization: `Bearer ${token}` } }
            );

            await showAlert({
                icon: 'success',
                title: 'Cancelled',
                text: 'Your registration has been cancelled.',
                timer: 2200,
                showConfirmButton: false,
            });

            fetchActivity();
        } catch (err) {
            showAlert({
                icon: 'error',
                title: 'Could not cancel',
                text: err.response?.data?.error || 'Something went wrong. Please try again.',
            });
        } finally {
            setSubmitting(null);
        }
    };

    if (loading) return <PageLoader />;
    if (!activity) return null;

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
                    <div className="flex-1 text-center text-red-700 text-base font-semibold truncate">
                        {activity.name}
                    </div>
                    <div className="w-6"></div>
                </div>

                {activity.image && (
                    <img
                        src={`${apiUrl}/images/${activity.image}`}
                        alt={activity.name}
                        className="w-full h-40 object-cover rounded-xl shadow-sm"
                    />
                )}

                <div className="bg-white rounded-xl shadow-sm p-4">
                    {activity.type && (
                        <span className="inline-block px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-[10px] font-semibold mb-2">
                            {activity.type}
                        </span>
                    )}
                    <h1 className="text-stone-800 text-base font-semibold">{activity.name}</h1>
                    {activity.description && (
                        // Admin-authored rich text from CKEditor, same as News.
                        <div className="mt-2 text-stone-600 text-xs leading-relaxed wellness-richtext">
                            {parse(activity.description)}
                        </div>
                    )}
                </div>

                <div className="text-stone-700 text-xs font-semibold">Available Sessions</div>

                {activity.schedules.length === 0 ? (
                    <div className="bg-white rounded-xl p-6 text-center shadow-sm">
                        <p className="text-stone-500 text-xs">No upcoming sessions for this activity.</p>
                    </div>
                ) : (
                    activity.schedules.map((schedule) => {
                        const when = formatSession(schedule);
                        const busy = submitting === schedule.id;
                        const registered = Boolean(schedule.my_status);
                        const canCancel = registered
                            && ['pending', 'approved', 'waitlisted'].includes(schedule.my_status);

                        return (
                            <div key={schedule.id} className="bg-white rounded-xl shadow-sm p-3">
                                <div className="flex gap-3">
                                    <div className="w-14 shrink-0 rounded-lg bg-red-700 text-white flex flex-col items-center justify-center py-2">
                                        <span className="text-lg font-bold leading-none">{when.day}</span>
                                        <span className="text-[10px] uppercase">{when.month}</span>
                                    </div>

                                    <div className="flex-1 min-w-0 text-[11px] text-stone-600 flex flex-col gap-0.5">
                                        <span className="text-stone-800 font-semibold text-xs">{when.weekday}</span>
                                        <span><i className="ri-time-line me-1"></i>{when.time}</span>
                                        {schedule.location && (
                                            <span className="truncate"><i className="ri-map-pin-line me-1"></i>{schedule.location}</span>
                                        )}
                                        <span><i className="ri-group-line me-1"></i>{seatLabel(schedule)}</span>
                                    </div>
                                </div>

                                <div className="mt-3 flex items-center gap-2">
                                    {registered && (
                                        <span className={`px-2 py-1 rounded-full text-[10px] font-semibold ${statusStyle(schedule.my_status)}`}>
                                            {schedule.my_status_label}
                                        </span>
                                    )}

                                    <div className="flex-1"></div>

                                    {canCancel ? (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleCancel(schedule)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-white text-red-700 ring-1 ring-red-700 ring-inset disabled:opacity-50"
                                        >
                                            {busy ? 'Please wait...' : 'Cancel'}
                                        </button>
                                    ) : !schedule.registration_open ? (
                                        <span className="text-[10px] text-stone-400">Registration closed</span>
                                    ) : (
                                        <button
                                            disabled={busy}
                                            onClick={() => handleRegister(schedule)}
                                            className="px-3 py-1.5 rounded-lg text-[10px] font-semibold bg-red-700 text-white shadow-sm disabled:opacity-50"
                                        >
                                            {busy ? 'Please wait...' : schedule.is_full ? 'Join waitlist' : 'Register'}
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })
                )}
            </div>
        </motion.div>
    );
}
