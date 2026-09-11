import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import axios from 'axios';
import { Scanner } from '@yudiel/react-qr-scanner';

import { useApiUrl } from '../Context/ApiContext';
import { useAuth } from '../Context/AuthContext';

/**
 * Wellness attendance check-in.
 *
 * The QR encodes the session's qr_token; the backend decides whether this
 * employee may check in (approved registration, inside the check-in window),
 * so the scanner only forwards what it read.
 */
export default function WellnessQrScannerModal({ isOpen, onClose, onScanSuccess }) {
    const apiUrl = useApiUrl();
    const { token } = useAuth();
    const { t } = useTranslation();

    const [isVisible, setIsVisible] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (isOpen) {
            setIsVisible(true);
            setResult(null);
            setError(null);
        }
    }, [isOpen]);

    const handleClose = useCallback(() => {
        setIsVisible(false);
        setTimeout(() => {
            setResult(null);
            setError(null);
            onClose();
        }, 200);
    }, [onClose]);

    const handleScan = useCallback(
        async (codes) => {
            const value = codes?.[0]?.rawValue;

            // Ignore repeat frames while a scan is already in flight or done.
            if (!value || busy || result) return;

            setBusy(true);
            setError(null);

            try {
                const res = await axios.post(
                    `${apiUrl}/api/wellness/check-in`,
                    { qrCode: value },
                    { headers: { Authorization: `Bearer ${token}` } }
                );

                setResult(res.data);

                if (onScanSuccess) onScanSuccess();
            } catch (err) {
                setError(err.response?.data?.error || t('wellness.qr.failed'));
            } finally {
                setBusy(false);
            }
        },
        [apiUrl, token, busy, result, onScanSuccess]
    );

    useEffect(() => {
        if (!result) return;
        const timer = setTimeout(handleClose, 4000);
        return () => clearTimeout(timer);
    }, [result, handleClose]);

    useEffect(() => {
        const onKeyDown = (e) => {
            if (e.key === 'Escape') handleClose();
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [handleClose]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-end bg-black bg-opacity-70 transition-opacity duration-200">
            <div
                className={`bg-white w-full h-[85vh] rounded-t-2xl p-4 relative shadow-lg transform transition-all duration-200 ${
                    isVisible ? 'animate-slideUp' : 'animate-slideDown'
                }`}
            >
                <div className="mb-2 flex items-start justify-between">
                    <div className="w-10"></div>
                    <div className="flex flex-col w-full text-center px-4 gap-2 mb-2">
                        {result ? (
                            <div className="text-green-800 text-lg"><strong>{t('wellness.qr.checkedIn')}</strong></div>
                        ) : (
                            <span className="text-gray-600 text-sm leading-none">
                                <i className="ri-alert-line text-yellow-500"></i> {t('wellness.qr.pointCamera')}
                            </span>
                        )}
                    </div>
                    <button
                        onClick={handleClose}
                        className="w-10 text-gray-500 hover:text-gray-800 text-2xl flex justify-end"
                        aria-label={t('common.closeModal')}
                    >
                        <i className="ri-close-line"></i>
                    </button>
                </div>

                {result ? (
                    <div className="w-full aspect-[4/3] flex items-center justify-center mb-4 bg-green-100 rounded-md">
                        <i className="ri-checkbox-circle-line text-green-600 text-6xl"></i>
                    </div>
                ) : (
                    <div className="w-full aspect-[4/3] border border-gray-300 rounded-md overflow-hidden bg-black flex items-center justify-center mb-4">
                        <Scanner
                            onScan={handleScan}
                            onError={(e) => console.error('QR Scan Error:', e)}
                            render={(previewId) => (
                                <div className="w-full h-full flex items-center justify-center text-white">
                                    <video id={previewId} className="w-full h-full object-cover" />
                                    <p className="absolute">{t('qr.align')}</p>
                                </div>
                            )}
                            constraints={{ facingMode: 'environment' }}
                        />
                    </div>
                )}

                {result ? (
                    <div className="text-center px-4">
                        <p className="text-gray-600 text-base">{t('wellness.qr.attending')}</p>
                        <p className="text-red-700 text-base font-bold">{result.activity}</p>
                        <p className="text-gray-400 text-xs mt-1">{result.attended_at}</p>
                    </div>
                ) : error ? (
                    <div className="mx-4 p-3 rounded-md bg-red-50 text-red-700 text-sm text-center">
                        {error}
                    </div>
                ) : (
                    <div className="w-full text-center text-gray-600 text-sm">
                        {busy ? t('wellness.qr.checkingIn') : t('wellness.qr.keepInFrame')}
                    </div>
                )}
            </div>
        </div>
    );
}
