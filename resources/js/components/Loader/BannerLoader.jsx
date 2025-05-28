import React from "react";
import ContentLoader from 'react-content-loader';

const BannerLoader = (props) => {
  return (
    <ContentLoader 
      speed={2}
      backgroundColor="#FCF9F4"
      foregroundColor="#F5E3B8"
      {...props}
    >
        {/* Banner */}
        <rect x="0" y="0" rx="4" ry="4" width="100%" height="100%" />
    </ContentLoader>
  );
};

export default BannerLoader;