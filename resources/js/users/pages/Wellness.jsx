import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { motion } from 'motion/react';

import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { showAlert } from '../components/Helper/alertHelper';
import CardLoader from '../components/Loader/CardLoader';
import { formatSession, seatLabel } from '../components/Helper/wellnessHelper';

const pageVariants = {
    initial: { opacity: 0, x: 0 },
    animate: { opacity: 1, x: 0 },
    exit: { opacity: 0, x: 0 },
};

export default function Wellness() {
    const navigate = useNavigate();
    const apiUrl = useApiUrl();
    const { token } = useAuth();

    const [activities, setActivities] = useState([]);
    const [types, setTypes] = useState([]);
    const [selectedType, setSelectedType] = useState('All');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchActivities = async () => {
            try {
                const res = await axios.get(`${apiUrl}/api/wellness/activities`, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                setActivities(res.data);
                setTypes(['All', ...new Set(res.data.map((a) => a.type).filter(Boolean))]);
            } catch (err) {
                showAlert({
                    icon: 'warning',
                    title: 'Connection Ended',
                    text: 'We could not load the wellness activities. Please try again.',
                    timer: 2500,
                    showConfirmButton: false,
                });
            } finally {
                setLoading(false);
            }
        };

        fetchActivities();
    }, [apiUrl, token]);

    const visible = selectedType === 'All'
        ? activities
        : activities.filter((activity) => activity.type === selectedType);

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
                {/* Header */}
                <div className="flex items-center gap-2">
                    <button onClick={() => navigate(-1)} className="text-red-700 text-xl" aria-label="Back">
                        <i className="ri-arrow-left-line"></i>
                    </button>
                    <div className="flex-1 text-center text-red-700 text-base font-semibold">Wellness</div>
                    <button
                        onClick={() => navigate('/wellness/my-registrations')}
                        className="text-red-700 text-xl"
                        aria-label="My registrations"
                    >
                        <i className="ri-calendar-check-line"></i>
                    </button>
                </div>

                <p className="text-stone-600 text-xs leading-relaxed">
                    Join a wellness session, keep an eye on your seat, and check in with the QR code on the day.
                </p>

                {/* Type filter */}
                {types.length > 1 && (
                    <div className="flex gap-2 overflow-x-auto pb-1">
                        {types.map((type) => (
                            <button
                                key={type}
                                onClick={() => setSelectedType(type)}
                                className={`px-3 py-1.5 rounded-full text-[10px] font-semibold whitespace-nowrap shadow-sm ${
                                    selectedType === type
                                        ? 'bg-red-700 text-white'
                                        : 'bg-white text-red-700 ring-1 ring-red-700 ring-inset'
                                }`}
                            >
                                {type}
                            </button>
                        ))}
                    </div>
                )}

                {/* List */}
                {loading ? (
                    <>
                        <CardLoader />
                        <CardLoader />
                    </>
                ) : visible.length === 0 ? (
                    <div className="bg-white rounded-xl p-6 text-center shadow-sm">
                        <i className="ri-heart-pulse-line text-3xl text-stone-300"></i>
                        <p className="mt-2 text-stone-500 text-xs">
                            No wellness activities are open right now. Please check back later.
                        </p>
                    </div>
                ) : (
                    visible.map((activity) => {
                        const next = activity.next_session;
                        const when = formatSession(next);

                        return (
                            <button
                                key={activity.id}
                                onClick={() => navigate(`/wellness/${encodeURIComponent(activity.id)}`)}
                                className="w-full bg-white rounded-xl shadow-sm p-3 flex gap-3 text-left"
                            >
                                <div className="w-16 shrink-0 rounded-lg bg-red-700 text-white flex flex-col items-center justify-center py-2">
                                    <span className="text-lg font-bold leading-none">{when.day || '-'}</span>
                                    <span className="text-[10px] uppercase">{when.month}</span>
                                </div>

                                <div className="flex-1 min-w-0">
                                    <div className="text-stone-800 text-sm font-semibold truncate">{activity.name}</div>
                                    {activity.type && (
                                        <div className="text-[10px] text-red-700 font-medium">{activity.type}</div>
                                    )}
                                    {next && (
                                        <div className="mt-1 text-[10px] text-stone-500 flex flex-col gap-0.5">
                                            <span><i className="ri-time-line me-1"></i>{when.time}</span>
                                            {next.location && (
                                                <span className="truncate"><i className="ri-map-pin-line me-1"></i>{next.location}</span>
                                            )}
                                            <span><i className="ri-group-line me-1"></i>{seatLabel(next)}</span>
                                        </div>
                                    )}
                                    {activity.upcoming_count > 1 && (
                                        <div className="mt-1 text-[10px] text-stone-400">
                                            +{activity.upcoming_count - 1} more session(s)
                                        </div>
                                    )}
                                </div>

                                <i className="ri-arrow-right-s-line text-stone-300 self-center"></i>
                            </button>
                        );
                    })
                )}
            </div>
        </motion.div>
    );
}
