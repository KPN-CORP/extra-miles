import React from "react";
import { useTranslation } from "react-i18next";
import { useApiUrl } from "../context/ApiContext";
import { useNavigate } from "react-router-dom";

export default function EvoBanner() {

  const apiUrl = useApiUrl();
  const navigate = useNavigate();
  const { t } = useTranslation();

  return (
    <div className="flex items-center justify-between bg-white rounded-xl px-3 py-2 shadow w-full">
      <div className="flex flex-col text-left mr-4">
        <span className="text-sm font-semibold text-gray-900">
          {t('evoBanner.readyToEvolve')}
        </span>
        <span className="text-xs text-red-700 font-semibold">
          {t('evoBanner.joinProgram')}
        </span>
      </div>
      <button 
      onClick={() => navigate(`/evo`)}
      className="bg-red-700 hover:bg-red-800 text-white text-xs font-semibold p-3 rounded-lg transition">
        {t('evoBanner.registerNow')}
      </button>
    </div>
  );
}