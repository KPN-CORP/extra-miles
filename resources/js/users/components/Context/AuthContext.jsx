// contexts/AuthProvider.js
import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useApiUrl } from './ApiContext';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const apiUrl = useApiUrl();
  const [token, setToken] = useState(sessionStorage.getItem('token'));
  const [user, setUser] = useState(null); // user profile state

  const saveToken = (newToken) => {
    sessionStorage.setItem('token', newToken);
    setToken(newToken);
  };

  const clearToken = () => {
    sessionStorage.removeItem('token');
    setToken(null);
    setUser(null);
  };
  

  const fetchUserProfile = async () => {
    if (!token) return;

    try {
      const response = await axios.get(`${apiUrl}/api/profile`, {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });

      setUser(response.data); 
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
    }
  };

  useEffect(() => {
    if (token) {
      fetchUserProfile();
    }
  }, [token]);

  return (
    <AuthContext.Provider value={{ token, saveToken, clearToken, user, refetchUser: fetchUserProfile }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
