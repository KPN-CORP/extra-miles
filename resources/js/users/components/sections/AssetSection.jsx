import React from "react";
import { useApiUrl } from "../Context/ApiContext";

export default () => {

  const apiUrl = useApiUrl();

  return (
    <div className="inline-flex px-4 sm:px-8 rounded-[10px] outline outline-1 outline-offset-[-1px] outline-red-700 flex-col justify-center items-center shadow-lg bg-white">
        <div className="p-1.5 rounded-[30px] flex flex-col justify-center items-center">
            <div className="text-center text-red-700 text-xs sm:text-sm font-bold leading-none">
            Extra Mile<br />Assets
            </div>
        </div>
    </div>
  );
};