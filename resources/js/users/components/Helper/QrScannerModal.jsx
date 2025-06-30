import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Scanner } from '@yudiel/react-qr-scanner';
import { useApiUrl } from '../context/ApiContext';
import { useAuth } from '../context/AuthContext';

export default function QRScannerModal({ isOpen, onClose, event, onScanSuccess }) {
  const [result, setResult] = useState('');
  const [isVisible, setIsVisible] = useState(false);
  const encryptedId = event?.encrypted_id;
  const apiUrl = useApiUrl();
  const { token } = useAuth();  

  useEffect(() => {
    if (isOpen) setIsVisible(true);
  }, [isOpen]);

  const handleScan = useCallback(
    async (text, id) => {
      if (text && id) {
        try {
          const response = await axios.post(
            `${apiUrl}/api/event-attendance`,
            {
              qrCode: text,
              eventId: id,
            },
            {
              headers: {
                Authorization: `Bearer ${token}`,
              },
            }
          );

          setResult(response.data.message);

          if (onScanSuccess) {
            onScanSuccess(); // ✅ Calls fetchEvent() in ActivitySection.jsx
          }

        } catch (error) {
          console.error('Failed to submit attendance:', error.response?.data || error.message);
        }
      }
    },
    [encryptedId, onScanSuccess, apiUrl, token]
  );

  const handleError = useCallback((error) => {
    console.error('QR Scan Error:', error);
  }, []);

  useEffect(() => {
    let timer;
    if (result) {
      timer = setTimeout(() => {
        handleClose();
  
        // ✅ Trigger the callback passed from ActivitySection
        if (onScanSuccess) {
          onScanSuccess();
        }
  
      }, 5000);
    }
    return () => clearTimeout(timer);
  }, [result, onScanSuccess]);

  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === 'Escape') handleClose();
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const handleClose = () => {
    setIsVisible(false);
    setTimeout(() => {
      setResult('');
      onClose();
    }, 200);
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-end bg-black bg-opacity-70 transition-opacity duration-200">
      <div
        className={`bg-white w-full h-[85vh] rounded-t-2xl p-4 relative shadow-lg transform transition-all duration-200 ${
          isVisible ? 'animate-slideUp' : 'animate-slideDown'
        }`}
      >
        {/* Close Button */}
        <div className="mb-2 flex items-start justify-between">
          <div className="w-10"></div>
          <div className="flex flex-col w-full text-center justify-start px-4 gap-2 mb-2">
            <div className="row">
              {result ? (
                <>
                <div className=" text-green-800 rounded align-text-bottom text-lg">
                  <strong>Successful</strong>
                </div>
                </>
              ) : (
                <span className="text-gray-600 text-sm font-normal leading-none"><i className='ri-alert-line text-yellow-500'></i> Please point your camera at the barcode to scan and confirm your attendance.</span>
              )}
            </div>
          </div>
          <button
            onClick={handleClose}
            className="w-10 text-gray-500 hover:text-gray-800 text-2xl flex justify-end"
            aria-label="Close modal"
          >
            <i className="ri-close-line"></i>
          </button>
        </div>

        {/* QR Scanner */}
        {result ? (
        <div className="w-full aspect-[4/3] overflow-hidden flex items-center justify-center mb-4 bg-green-100 rounded-md">
          <div className="animate-success-pop scale-0 flex text-center justify-center">
            <svg className="w-1/2 text-green-600" viewBox="0 0 52 52">
              <circle
                className="stroke-current text-green-300"
                cx="26"
                cy="26"
                r="25"
                fill="none"
                strokeWidth="2"
              />
              <path
                className="stroke-current"
                fill="none"
                strokeWidth="3"
                d="M14 27l7 7 16-16"
                style={{
                  strokeDasharray: 48,
                  strokeDashoffset: 48,
                  animation: 'dash 0.5s ease-out forwards 0.3s',
                }}
              />
            </svg>
          </div>
        </div>
        ) : (
          <div className="w-full aspect-[4/3] border border-gray-300 rounded-md overflow-hidden bg-black flex items-center justify-center mb-4">
            <Scanner
              onScan={(data) => handleScan(data[0].rawValue, encryptedId)}
              onError={handleError}
              render={(previewId) => (
                <div className="w-full h-full flex items-center justify-center text-white">
                  <video id={previewId} className="w-full h-full object-cover" />
                  {!result && <p className="absolute">Align QR Code</p>}
                </div>
              )}
              constraints={{ facingMode: 'environment' }}
            />
          </div>
        )}

        {/* Scanned Result */}
        {result ? (
          <>
          <div className="flex flex-col w-full text-center justify-start px-4 gap-2 mb-2">
            <div className="row">
              <span className="text-gray-600 text-base font-normal leading-tight">Hi, you're attending the event 🎉 </span>
              <span className="text-red-700 text-base font-bold leading-tight">{event?.title} </span>
            </div>
          </div>
          {/* <div className="mt-4 p-2 bg-green-100 text-green-800 rounded text-center text-sm">
            <strong>Scanned:</strong> {result}
          </div> */}
          </>
        ) : (
          <div className="w-full text-center justify-start text-gray-600 text-sm font-normal leading-none">Make sure the barcode is clearly visible within the scanning area!</div>
        )}
      </div>
    </div>
  );
}