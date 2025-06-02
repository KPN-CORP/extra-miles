import React from "react";
import { SyncLoader } from "react-spinners";

const PageLoader = (props) => {
    return (
        <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 overflow-hidden">
          <h1 className="mb-4 text-red-700 text-4xl font-bold italic">EXTRA MILE</h1>
          <SyncLoader color="#B91C1C" size={15} />
        </div>
    );
};

export default PageLoader;