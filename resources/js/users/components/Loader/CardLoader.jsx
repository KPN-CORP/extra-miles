import React from "react";
import ContentLoader from "react-content-loader";

const CardLoader = (props) => {
  return (
    <ContentLoader
      speed={1}
      width="100%"
      height={100}
      viewBox="0 0 400 100"
      backgroundColor="#FCF9F4"
      foregroundColor="#F5E3B8"
      {...props}
    >
      {/* Gambar Thumbnail Kiri */}
      <rect x="10" y="10" rx="8" ry="8" width="80" height="80" />

      {/* Judul Event */}
      <rect x="100" y="17" rx="4" ry="4" width="50%" height="16" />

      {/* Waktu */}
      <rect x="100" y="44" rx="4" ry="4" width="70%" height="14" />

      {/* Lokasi */}
      <rect x="100" y="66" rx="4" ry="4" width="70%" height="14" />
    </ContentLoader>
  );
};

export default CardLoader;
