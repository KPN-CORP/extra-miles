import React from "react";
import ContentLoader from "react-content-loader";

const NewsSectionLoader = (props) => {
  return (
    <ContentLoader
      speed={1}
      width="100%"
      height={175}
      viewBox="0 0 400 175"
      backgroundColor="#FCF9F4"
      foregroundColor="#F5E3B8"
      {...props}
    >

      {/* Judul Event */}
      <rect x="0" y="0" rx="4" ry="4" width="30%" height="18" />

      {/* Gambar Thumbnail Kiri */}
      <rect x="0" y="24" rx="8" ry="8" width="48%" height="145" />
      <rect x="52%" y="24" rx="8" ry="8" width="48%" height="145" />
    </ContentLoader>
  );
};

export default NewsSectionLoader;
