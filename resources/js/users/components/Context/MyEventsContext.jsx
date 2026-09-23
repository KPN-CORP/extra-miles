import React, { createContext, useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';

import { useApiUrl } from './ApiContext';
import { useAuth } from './AuthContext';
import { showAlert } from '../Helper/alertHelper';

/**
 * Daftar event milik karyawan (/api/my-event).
 *
 * Dipisah jadi context karena beranda memecah daftar yang sama jadi dua seksi
 * yang TIDAK bersebelahan: "Waiting for Your Response" duduk paling atas,
 * sementara "Events You're Invited To Join" tetap di bawah Quick Access. Dua
 * <ActivitySection> yang masing-masing fetch sendiri berarti dua request dan
 * dua alert error untuk satu kegagalan yang sama, jadi datanya diangkat ke
 * sini dan dibagi.
 */
const MyEventsContext = createContext(null);

export const MyEventsProvider = ({ children }) => {
    const apiUrl = useApiUrl();
    const { token } = useAuth();
    const { t } = useTranslation();

    const [events, setEvents] = useState([]);
    const [loading, setLoading] = useState(true);

    const refetch = async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/my-event`, {
                headers: { Authorization: `Bearer ${token}` },
            });
            setEvents(
                res.data.map((e) => ({
                    ...e,
                    businessUnit: Array.isArray(e.businessUnit) ? e.businessUnit : [e.businessUnit],
                }))
            );
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
        refetch();
    }, []);

    return (
        <MyEventsContext.Provider value={{ events, loading, refetch }}>
            {children}
        </MyEventsContext.Provider>
    );
};

export const useMyEvents = () => {
    const ctx = useContext(MyEventsContext);

    if (!ctx) {
        throw new Error('useMyEvents harus dipakai di dalam <MyEventsProvider>.');
    }

    return ctx;
};
