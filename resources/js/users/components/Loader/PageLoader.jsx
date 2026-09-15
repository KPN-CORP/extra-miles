import React from "react";
import { SyncLoader } from "react-spinners";

const PageLoader = (props) => {
    return (
        <div className="flex flex-col items-center justify-center app-surface app-bg overflow-hidden">
          <h1 className="mb-4 text-brand-700 text-4xl font-bold italic tracking-wide">EXTRA MILE</h1>
          <SyncLoader color="#B91C1C" size={15} />
        </div>
    );
};

export default PageLoader;