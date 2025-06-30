import React from "react";
import ContentLoader from "react-content-loader";

const SocialLoader = (props) => {
  return (
    <div className="flex flex-col h-screen bg-red-700 p-5">
      <ContentLoader
        speed={1}
        width="100%"
        height={420}
        backgroundColor="#FCF9F4"
        foregroundColor="#F5E3B8"
        viewBox="0 0 400 420"
        {...props}
      >
        {/* Header bar */}
        <rect x="0" y="20" rx="4" ry="4" width="50%" height="30" />
        <rect x="0" y="60" rx="4" ry="4" width="100%" height="40" />
        
        {/* Event Card 1 */}
        <rect x="0" y="140" rx="8" ry="8" width="100%" height="100" />
        {/* Space between cards */}
        <rect x="0" y="300" rx="8" ry="8" width="100%" height="100" />
      </ContentLoader>
    </div>
  );
};

export default SocialLoader;
