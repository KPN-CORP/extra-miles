import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [token, setToken] = useState(sessionStorage.getItem('token'));  

  const saveToken = (newToken) => {
    sessionStorage.setItem('token', newToken);
    setToken(newToken);
  };

  const clearToken = () => {
    sessionStorage.removeItem('token');
    setToken(null);
  };

  // Optional: listen perubahan storage dari tab lain
  useEffect(() => {
    const onStorage = () => {
      setToken(sessionStorage.getItem('token'));
    };
    window.addEventListener('storage', onStorage);
    return () => window.removeEventListener('storage', onStorage);
  }, []);

  return (
    <AuthContext.Provider value={{ token, saveToken, clearToken }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
