// App.jsx
import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ApiProvider } from './components/Context/ApiContext';
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
import { AuthProvider } from './components/Context/AuthContext';
import News from './pages/News';
import NewsDetails from './pages/NewsDetails';
import Social from './pages/Social';
import LoginFailed from './pages/LoginFailed';
import EvoRegistration from './pages/EvoRegistration';
import Wellness from './pages/Wellness';
import WellnessDetails from './pages/WellnessDetails';
import MyWellness from './pages/MyWellness';
import MyEvents from './pages/MyEvents';

const AnimatedRoutes = () => {
  const location = useLocation();

  return (
    <AnimatePresence mode="wait">
      <Routes location={location} key={location.pathname}>
        <Route path="/login-success" element={<ConfirmLogin />} />
        <Route path="/login-failed" element={<LoginFailed />} />
        <Route path="/" element={<Home />} />
        <Route path="/event" element={<Event />} />
        <Route path="/my-events" element={<MyEvents />} />
        <Route path="/event/:id" element={<EventDetails />} />
        <Route path="/event-registration/:id" element={<EventRegistration />} />
        <Route path="/survey" element={<Survey />} />
        <Route path="/survey/:id" element={<SurveyDetails />} />
        <Route path="/vote/:id" element={<VoteDetails />} />
        <Route path="/news" element={<News />} />
        <Route path="/news/:id" element={<NewsDetails />} />
        <Route path="/social" element={<Social />} />
        <Route path="/evo" element={<EvoRegistration />} />
        <Route path="/wellness" element={<Wellness />} />
        <Route path="/wellness/my-registrations" element={<MyWellness />} />
        <Route path="/wellness/:id" element={<WellnessDetails />} />
      </Routes>
    </AnimatePresence>
  );
};
// Daftar media query yang menentukan kelayakan perangkat. Sengaja TIDAK
// memakai lebar layar: lebar berubah saat ponsel diputar (iPhone 15 Pro jadi
// 852px di landscape), dan dulu itu membuat aplikasi tiba-tiba menampilkan
// layar "Mobile Only" hanya karena perangkat dimiringkan.
const MOBILE_QUERIES = ["(pointer: coarse)", "(hover: none)"];

// Probe dukungan fitur. Bila browser mengenal pointer/hover, salah satu nilai
// pasti cocok; bila tidak ada yang cocok, fiturnya memang tidak dipahami.
// Cara ini dipakai alih-alih memeriksa `media === "not all"` karena Chrome
// mengembalikan query yang tidak dikenal apa adanya, bukan "not all".
const MOBILE_SUPPORT_PROBE =
  "(pointer: coarse), (pointer: fine), (pointer: none), (hover: hover), (hover: none)";

// Fallback browser lama yang tidak mendukung pointer/hover sama sekali.
const hasTouchPoints = () =>
  "ontouchstart" in window ||
  navigator.maxTouchPoints > 0 ||
  navigator.msMaxTouchPoints > 0;

const checkIsMobile = () => {
  if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
    return hasTouchPoints();
  }

  if (!window.matchMedia(MOBILE_SUPPORT_PROBE).matches) {
    return hasTouchPoints();
  }

  // OR, bukan AND. Sebagian Android lama salah melaporkan salah satu fitur;
  // memblokir ponsel karyawan jauh lebih merugikan daripada meloloskan laptop
  // layar sentuh, jadi satu sinyal positif sudah cukup.
  return MOBILE_QUERIES.some((query) => window.matchMedia(query).matches);
};

const AppContent = () => {
  const { t } = useTranslation();
  const [isMobile, setIsMobile] = useState(checkIsMobile);

  useEffect(() => {
    if (typeof window.matchMedia !== "function") {
      return undefined;
    }

    const handleChange = () => {
      setIsMobile(checkIsMobile());
    };

    handleChange();

    // Jenis pointer bisa berubah di tengah sesi, misalnya saat mouse atau
    // keyboard dilepas dari tablet, jadi kedua query tetap didengarkan.
    const watchers = MOBILE_QUERIES.map((query) => window.matchMedia(query));

    watchers.forEach((media) => {
      if (media.addEventListener) {
        media.addEventListener("change", handleChange);
      } else {
        media.addListener(handleChange); // fallback browser lama
      }
    });

    return () => {
      watchers.forEach((media) => {
        if (media.removeEventListener) {
          media.removeEventListener("change", handleChange);
        } else {
          media.removeListener(handleChange);
        }
      });
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
      <div className="app-bg flex flex-col items-center justify-center min-h-screen px-8 text-center">
        <i className="ri-smartphone-line text-6xl text-brand-700 mb-4"></i>
        <h1 className="text-2xl font-bold text-gray-800">{t('app.mobileOnly')}</h1>
        <p className="text-gray-600 mt-2">
          {t('app.mobileOnlyText')}
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