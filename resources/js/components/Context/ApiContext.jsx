import React, { createContext, useContext } from "react";

// Create a Context
const ApiContext = createContext();

// Provide the API URL to the entire app
export function ApiProvider({ children }) {
  const apiUrl = import.meta.env.VITE_API_URL;
  return <ApiContext.Provider value={apiUrl}>{children}</ApiContext.Provider>;
}

// Custom hook to access the API URL
export function useApiUrl() {
  return useContext(ApiContext);
}