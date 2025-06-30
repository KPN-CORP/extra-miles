import React from "react";
import { useApiUrl } from "../context/ApiContext";

export default () => {

  const apiUrl = useApiUrl();

  return (
    <div className="inline-flex w-fit px-6 py-1 rounded-lg ring-1 ring-red-700 justify-center items-center shadow-lg bg-white">
      <div className="text-center text-red-700 text-xs sm:text-sm font-bold leading-tight">
        Extra Mile<br />Assets
      </div>
    </div>
  );
};