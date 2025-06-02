import React, { createContext, useContext, useState, useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';

const NavigationContext = createContext();

export const NavigationProvider = ({ children }) => {
  const location = useLocation();
  const [direction, setDirection] = useState(1);
  const prevPath = useRef('');

  useEffect(() => {
    const currentPath = location.pathname;

    if (prevPath.current) {
      // Deteksi apakah ini back atau forward berdasarkan urutan history
      const isBack = sessionStorage.getItem('navHistory')?.includes(currentPath);
      setDirection(isBack ? -1 : 1);
    }

    // Simpan path ini ke history session
    let navHistory = JSON.parse(sessionStorage.getItem('navHistory') || '[]');
    if (!navHistory.includes(currentPath)) {
      navHistory.push(currentPath);
      sessionStorage.setItem('navHistory', JSON.stringify(navHistory));
    }

    prevPath.current = currentPath;
  }, [location]);

  return (
    <NavigationContext.Provider value={{ direction }}>
      {children}
    </NavigationContext.Provider>
  );
};

export const useNavigationDirection = () => useContext(NavigationContext);