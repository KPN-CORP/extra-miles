// contexts/ProfileContext.js
import { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from './AuthContext';

const ProfileContext = createContext();

export const ProfileProvider = ({ children }) => {
  const { token } = useAuth();
  const [employees, setEmployees] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchProfile = async () => {
    try {
      const response = await axios.get(
        `${import.meta.env.VITE_API_URL}/api/profiles`,
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      setEmployees(response.data);
    } catch (err) {
      Swal.fire({
        icon: 'warning',
        title: 'Connection Ended',
        text: 'Unable to connect to the server. Please try again later.',
        timer: 2500,
        showConfirmButton: false,
      }).then(() => {
        // window.location.href = 'https://kpncorporation.darwinbox.com/';
        window.history.back();

      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (token) {
      fetchProfile();
    } else {
      setLoading(false);
    }
  }, [token]);

  return (
    <ProfileContext.Provider value={{ employees, loading, refetch: fetchProfile }}>
      {children}
    </ProfileContext.Provider>
  );
};

export const useProfile = () => useContext(ProfileContext);