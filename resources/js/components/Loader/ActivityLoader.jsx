import React from "react";
import ContentLoader from 'react-content-loader';

const ActivityLoader = (props) => {
  return (
    <ContentLoader 
      speed={2}
      width="100%"
      height={240}
      backgroundColor="#FCF9F4"
      foregroundColor="#F5E3B8"
      {...props}
    >
        {/* Title bar */}
        <rect x="0" y="0" rx="4" ry="4" width="250" height="20" />
        {/* event card */}
        <rect x="0" y="30" rx="6" ry="6" width="100%" height="60" />

        {/* Title bar */}
        <rect x="0" y="110" rx="4" ry="4" width="250" height="20" />
        {/* event card */}
        <rect x="0" y="140" rx="6" ry="6" width="100%" height="60" />
    </ContentLoader>
  );
};

export default ActivityLoader;