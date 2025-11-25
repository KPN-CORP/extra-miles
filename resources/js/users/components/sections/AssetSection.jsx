import React from "react";
import { useApiUrl } from "../context/ApiContext";

export default () => {

  const apiUrl = useApiUrl();

  return (
    <a
      href="https://drive.google.com/drive/folders/15u5SUk96Te5AdxzQpVM_WZ5MeG4YkiMf"
      target="_blank"
      rel="noopener noreferrer"
      title="Open News & Events Assets in Google Drive"
      className="inline-flex w-fit px-6 py-1 rounded-lg ring-1 ring-red-700 justify-center items-center shadow-lg bg-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
      aria-label="News & Events Assets - open Google Drive folder"
    >
      <div className="text-center text-red-700 text-xs sm:text-sm font-bold leading-tight">
        News & Events<br />Assets
      </div>
    </a>
  );
};