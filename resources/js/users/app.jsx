// App.jsx
import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import { ApiProvider } from './components/Context/ApiContext';
import { AnimatePresence } from "framer-motion";
import { NavigationProvider } from './components/Context/NavigationProvider'; // sesuaikan path

// Pages
import Home from './pages/Home';
import Event from './pages/Event';
import EventDetails from './pages/EventDetails';
import EventRegistration from './pages/EventRegistration';
import ConfirmLogin from './pages/ConfirmLogin';
import Survey from './pages/Survey';
import SurveyDetails from './pages/SurveyDetails';
import VoteDetails from './pages/VoteDetails';
import { AuthProvider } from './components/context/AuthContext';

const AnimatedRoutes = () => {
  const location = useLocation();

  return (
    <AnimatePresence mode="wait">
      <Routes location={location} key={location.pathname}>
        <Route path="/login-success" element={<ConfirmLogin />} />
        <Route path="/" element={<Home />} />
        <Route path="/event" element={<Event />} />
        <Route path="/event/:id" element={<EventDetails />} />
        <Route path="/event-registration/:id" element={<EventRegistration />} />
        <Route path="/survey" element={<Survey />} />
        <Route path="/survey/:id" element={<SurveyDetails />} />
        <Route path="/vote/:id" element={<VoteDetails />} />
      </Routes>
    </AnimatePresence>
  );
};

const AppContent = () => {
  const [isMobile, setIsMobile] = useState(window.innerWidth < 450);

  useEffect(() => {
    const handleResize = () => {
      setIsMobile(window.innerWidth < 450);
    };

    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);

  useEffect(() => {
    const lockOrientation = async () => {
      if (isMobile && screen.orientation && typeof screen.orientation.lock === 'function') {
        try {
          await screen.orientation.lock('portrait');
        } catch (error) {
          console.warn('Orientation lock failed:', error);
        }
      }
    };

    lockOrientation();
  }, [isMobile]);

  if (!isMobile) {
    return (
      <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100 text-center">
        <i className="ri-smartphone-line text-6xl text-red-500 mb-4"></i>
        <h1 className="text-2xl font-bold text-gray-800">Mobile Only</h1>
        <p className="text-gray-600 mt-2">
          Please open this app on a mobile device.
        </p>
      </div>
    );
  }

  return (
    <Router>
      <NavigationProvider>
        <AnimatedRoutes />
      </NavigationProvider>
    </Router>
  );
};

const AppWrapper = () => (
  <AuthProvider>
    <ApiProvider>
      <AppContent />
    </ApiProvider>
  </AuthProvider>
);

export default AppWrapper;