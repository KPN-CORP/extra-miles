// App.jsx
import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import { ApiProvider } from './components/context/ApiContext';
import { AnimatePresence } from "motion/react";
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
import News from './pages/News';
import NewsDetails from './pages/NewsDetails';
import Social from './pages/Social';
import LoginFailed from './pages/LoginFailed';
import EvoRegistration from './pages/EvoRegistration';

const AnimatedRoutes = () => {
  const location = useLocation();

  return (
    <AnimatePresence mode="wait">
      <Routes location={location} key={location.pathname}>
        <Route path="/login-success" element={<ConfirmLogin />} />
        <Route path="/login-failed" element={<LoginFailed />} />
        <Route path="/" element={<Home />} />
        <Route path="/event" element={<Event />} />
        <Route path="/event/:id" element={<EventDetails />} />
        <Route path="/event-registration/:id" element={<EventRegistration />} />
        <Route path="/survey" element={<Survey />} />
        <Route path="/survey/:id" element={<SurveyDetails />} />
        <Route path="/vote/:id" element={<VoteDetails />} />
        <Route path="/news" element={<News />} />
        <Route path="/news/:id" element={<NewsDetails />} />
        <Route path="/social" element={<Social />} />
        <Route path="/evo" element={<EvoRegistration />} />
      </Routes>
    </AnimatePresence>
  );
};
const checkIsMobile = () => {
  const isMobileScreen = window.matchMedia("(max-width: 768px)").matches;
  const isTouchDevice =
    "ontouchstart" in window ||
    navigator.maxTouchPoints > 0 ||
    navigator.msMaxTouchPoints > 0;

  return isMobileScreen && isTouchDevice;
};

const AppContent = () => {
  const [isMobile, setIsMobile] = useState(checkIsMobile);

  useEffect(() => {
    const media = window.matchMedia("(max-width: 768px)");

    const handleChange = () => {
      setIsMobile(checkIsMobile());
    };

    handleChange();

    if (media.addEventListener) {
      media.addEventListener("change", handleChange);
    } else {
      media.addListener(handleChange); // fallback browser lama
    }

    window.addEventListener("resize", handleChange);

    return () => {
      if (media.removeEventListener) {
        media.removeEventListener("change", handleChange);
      } else {
        media.removeListener(handleChange);
      }

      window.removeEventListener("resize", handleChange);
    };
  }, []);

  useEffect(() => {
    const lockOrientation = async () => {
      if (
        isMobile &&
        screen.orientation &&
        typeof screen.orientation.lock === "function"
      ) {
        try {
          await screen.orientation.lock("portrait");
        } catch (e) {
          // Beberapa browser memang tidak mengizinkan lock orientation
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
    <ApiProvider>
      <AuthProvider>
        <AppContent />
      </AuthProvider>
    </ApiProvider>
);

export default AppWrapper;