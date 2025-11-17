import React from "react";
import { useApiUrl } from "../context/ApiContext";
import { useNavigate } from "react-router-dom";

export default function EvoBanner() {

  const apiUrl = useApiUrl();
  const navigate = useNavigate();  

  return (
    <div className="flex items-center justify-between bg-white rounded-xl px-3 py-2 shadow w-full">
      <div className="flex flex-col text-left mr-4">
        <span className="text-sm font-semibold text-gray-900">
          Ready to Evolve?
        </span>
        <span className="text-xs text-red-700 font-semibold">
          Come & Join EVO Program
        </span>
      </div>
      <button 
      onClick={() => navigate(`/evo`)}
      className="bg-red-700 hover:bg-red-800 text-white text-xs font-semibold p-3 rounded-lg transition">
        Register Now
      </button>
    </div>
  );
}