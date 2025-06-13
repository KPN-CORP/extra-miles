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

    const validateToken = async () => {
      try {
        const response = await axios.get(`${apiUrl}/api/verify`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        if (response.status === 200) {
          saveToken(token);
          navigate('/');
        } else {
          setError(true);
        }
      } catch (err) {
        setError(true);
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
