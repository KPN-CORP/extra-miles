import React, { createContext, useContext } from "react";

// Create a Context
const ApiContext = createContext();

// Base URL backend. Default-nya origin halaman itu sendiri, BUKAN
// import.meta.env.VITE_API_URL.
//
// Vite menanam nilai env saat build, dan public/build di-commit lalu disalin ke
// dua environment sekaligus (extramiles.hcis.live -> ~/extra-miles,
// qr.hcis.live -> ~/extra-miles-stage). URL absolut apa pun yang ditanam pasti
// salah di salah satu host. Karena SPA dan API selalu dilayani dari origin yang
// sama, origin runtime selalu benar di mana pun bundle ini dideploy.
//
// VITE_API_URL tetap dihormati kalau memang diisi eksplisit -- berguna kalau
// suatu saat frontend dijalankan terpisah dari backend.
export function resolveApiUrl() {
  const configured = import.meta.env.VITE_API_URL;

  if (typeof window === "undefined") {
    return configured ?? "";
  }

  // localhost yang ikut ter-build dari mesin dev bukan tujuan yang valid di
  // perangkat pengguna -- perlakukan seperti tidak diset.
  if (!configured || /^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i.test(configured)) {
    return window.location.origin;
  }

  return configured;
}

// Provide the API URL to the entire app
export function ApiProvider({ children }) {
  const apiUrl = resolveApiUrl();
  return <ApiContext.Provider value={apiUrl}>{children}</ApiContext.Provider>;
}

// Custom hook to access the API URL
export function useApiUrl() {
  return useContext(ApiContext);
}
