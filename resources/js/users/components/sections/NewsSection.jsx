import React, { useState } from "react";
import { Swiper, SwiperSlide } from "swiper/react";
import "swiper/css";
import "swiper/css/bundle";
import { FreeMode } from "swiper/modules";
import BannerLoader from "../Loader/BannerLoader";
import { getImageUrl } from "../Helper/imagePath";

export default () => {
  const cards = [
    {
      id: 1,
      date: "Rabu, 03 April 2025",
      title: "KPN Corp Gelar Buka Puasa dan Berbagi Kepedulian serta Kebahagiaan",
      image: "assets/images/news/News-img-1.png",
    },
    {
      id: 2,
      date: "Selasa, 02 April 2025",
      title: "Operasi Pasar KPN Corp-Kemendag Sediakan Ribuan Liter Minyak Goreng",
      image: "assets/images/news/News-img-2.png",
    },
    {
      id: 3,
      date: "Senin, 27 Maret 2025",
      title: "Jembatan Penghubung Mimpi",
      image: "assets/images/news/News-img-3.png",
    },
    // Add more cards as needed
  ];

  const apiUrl = import.meta.env.VITE_API_URL;

  return (
    <div className="flex flex-col gap-2 mb-4">
      {/* Header Section */}
      <div className="flex items-center justify-between">
        <div className="text-red-700 text-sm font-bold">News Update!</div>
        <div className="text-stone-600 text-xs font-medium cursor-pointer">Show All</div>
      </div>

      {/* Swiper Container */}
      <div className="overflow-x-scroll whitespace-nowrap">
        <Swiper
          modules={[FreeMode]}
          spaceBetween={10}
          slidesPerView={2}
          followFinger={true}
          speed={600}
        >
          {/* Slides */}
          {cards.map((card) => {
            const [isImageLoaded, setIsImageLoaded] = useState(false); // State to track image load

            return (
              <SwiperSlide key={card.id}>
                <div className="w-full sm:w-1/2 md:w-1/3 lg:w-1/4 aspect-[4/3] relative rounded-lg overflow-hidden">
                  {/* Container for Image + Loader */}
                  <div className="absolute inset-0 flex items-center justify-center">
                    {/* Lazy Loaded Image */}
                    <img
                      className={`w-full h-full object-cover transition-opacity duration-300 ${
                        isImageLoaded ? 'opacity-100' : 'opacity-0'}`}
                      src={getImageUrl(apiUrl, card.image)}
                      alt={card.title}
                      loading="lazy"
                      onLoad={() => setIsImageLoaded(true)} // Trigger when image loads
                    />

                    {/* Preloader (only if image not loaded) */}
                    {!isImageLoaded && (
                      <div className="absolute inset-0 flex items-center justify-center bg-orange-50 z-10">
                        <BannerLoader />
                      </div>
                    )}
                  </div>

                  {/* Gradient Overlay */}
                  <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent pointer-events-none"></div>

                  {/* Content */}
                  <div className="p-2 absolute bottom-0 left-0 right-0 text-white pointer-events-none z-10">
                    <div className="text-[10px] font-medium leading-[10px]">{card.date}</div>
                    <div className="text-xs font-semibold leading-3">{card.title}</div>
                  </div>
                </div>
              </SwiperSlide>
            );
          })}
        </Swiper>
      </div>
    </div>
  );
};