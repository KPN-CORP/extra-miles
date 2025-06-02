import React from "react";
import ContentLoader from "react-content-loader";

const EventLoader = (props) => {
  return (
    <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 p-5">
      <ContentLoader
        speed={1}
        width="100%"
        height={630}
        backgroundColor="#FCF9F4"
        foregroundColor="#F5E3B8"
        viewBox="0 0 400 630"
        {...props}
      >        
        {/* Event Card 1 */}
        <rect x="0" y="0" rx="8" ry="8" width="100%" height="480" />
        {/* Space between button */}
        <rect x="0" y="500" rx="10" ry="10" width="50%" height="30" />
        <rect x="0" y="550" rx="10" ry="10" width="100%" height="30" />
      </ContentLoader>
    </div>
  );
};

export default EventLoader;
