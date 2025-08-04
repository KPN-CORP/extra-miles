import { useLocation, useNavigate } from 'react-router-dom';
import { useApiUrl } from '../components/context/ApiContext';
import { useAuth } from '../components/context/AuthContext';
import { useEffect, useState } from 'react';
import axios from 'axios';
import { SyncLoader } from 'react-spinners';

function ConfirmLogin() {
  const { saveToken } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const apiUrl = useApiUrl();
  const [error, setError] = useState(false);

  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    const token = searchParams.get('token');

    // Example using Fetch API (instead of axios) in ConfirmLogin.js
    const validateToken = async () => {
      try {
        const response = await fetch(`${apiUrl}/api/verify`, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json' // Always good to explicitly ask for JSON
          },
        });

        if (response.ok) { // response.ok is true for 2xx status codes
          // const data = await response.json(); // Only if you expect a JSON body
          saveToken(token);
          navigate('/');
        } else {
          const errorData = await response.json().catch(() => ({ message: `Server error: ${response.status}` }));
          console.error("Token verification failed:", response.status, errorData);
          setError(true);
          navigate(`/login-failed?error=${encodeURIComponent(errorData.message || 'Token verification failed.')}`);
        }
      } catch (err) {
        console.error("Network or unexpected error:", err);
        setError(true);
        navigate(`/login-failed?error=${encodeURIComponent('Network error or unexpected issue. Please try again.')}`);
      }
    };

    if (token && token.startsWith('eyJ')) {
      validateToken();
    } else {
      setError(true);
    }
  }, [location, navigate, saveToken, apiUrl]);

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-red-100 overflow-hidden">
        <h1 className="mb-4 text-red-700 text-4xl font-bold italic">EXTRA MILE</h1>
        <p className="text-center text-red-700 text-lg font-semibold">
          Service is currently unavailable now.
        </p>
        <p className="text-sm text-gray-700 mt-2">Please try again in a few moments.</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 overflow-hidden">
      <h1 className="mb-4 text-red-700 text-4xl font-bold italic">EXTRA MILE</h1>
      <SyncLoader color="#B91C1C" size={15} />
    </div>
  );
}

export default ConfirmLogin;
