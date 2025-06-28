import React, { createContext, useContext, useState, useEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';

const NavigationContext = createContext();

export const NavigationProvider = ({ children }) => {
  const location = useLocation();
  const [direction, setDirection] = useState(1);
  const prevKey = useRef(location.key); // Simpan key sebelumnya

  useEffect(() => {
    const currentKey = location.key;

    if (prevKey.current) {
      const historyKeys = JSON.parse(sessionStorage.getItem('navHistoryKeys') || '[]');
      const currentIndex = historyKeys.indexOf(currentKey);
      const prevIndex = historyKeys.indexOf(prevKey.current);

      if (currentIndex === -1) {
        // Jika belum ada → forward
        historyKeys.push(currentKey);
        sessionStorage.setItem('navHistoryKeys', JSON.stringify(historyKeys));
        setDirection(1);
      } else if (currentIndex < prevIndex) {
        // Back
        setDirection(-1);
      } else {
        // Forward
        setDirection(1);
      }
    }

    prevKey.current = currentKey;
  }, [location]);

  return (
    <NavigationContext.Provider value={{ direction }}>
      {children}
    </NavigationContext.Provider>
  );
};

export const useNavigationDirection = () => useContext(NavigationContext);