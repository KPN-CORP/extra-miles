import { useLocation, useNavigate } from 'react-router-dom';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/context/AuthContext';
import { useEffect } from 'react';
import axios from 'axios';
import { SyncLoader } from 'react-spinners';

function ConfirmLogin() {
  const { saveToken } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const apiUrl = useApiUrl();

  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    const token = searchParams.get('token');        

    const validateToken = async (token) => {        
      try {
        const response = await axios.get(`${apiUrl}/api/verify`, {
          headers: { Authorization: `Bearer ${token}` },
        });        

        if (response.status === 200) {
            
          saveToken(token); // pakai fungsi dari context
          navigate('/');
        } else {
          // window.location.href = 'https://kpncorporation.darwinbox.com/';
          console.log(err);
          
        }
      } catch {
        // window.location.href = 'https://kpncorporation.darwinbox.com/';
        console.log(err);
      }
    };

    if (token && token.startsWith('eyJ')) {
      validateToken(token);
    } else {
      window.location.href = 'https://kpncorporation.darwinbox.com/';
    }
  }, [location, navigate, saveToken, apiUrl]);

  return (
    <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 overflow-hidden">
       <h1 className="mb-4 text-red-700 text-4xl font-bold italic">EXTRA MILE</h1>
        <SyncLoader color="#B91C1C" size={15} />
    </div>
  );
}


export default ConfirmLogin;
