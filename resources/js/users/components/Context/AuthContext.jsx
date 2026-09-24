// contexts/AuthProvider.js
import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useApiUrl } from './ApiContext';
import { SSO_URL } from '../Helper/ssoRedirect';
import { translate } from '../Helper/localeHelper';

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
        title: translate('alerts.connectionEnded'),
        text: translate('alerts.connectionEndedText'),
        timer: 2500,
        showConfirmButton: false,
      }).then(() => {
        if (document.referrer) {
          window.history.back();
        } else {
          window.location.href = SSO_URL;
        }

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
