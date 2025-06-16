import React, { useEffect, useState, useRef } from 'react';
import axios from 'axios';
import { motion, AnimatePresence } from 'framer-motion';
import { useAuth } from '../context/AuthContext';
import { useApiUrl } from '../context/ApiContext';

const heartColors = [
  'text-red-500',
  'text-pink-500',
  'text-rose-500',
  'text-fuchsia-500',
  'text-[#ff66cc]',
  'text-[#ff3366]',
  'text-[#ff1493]',
];

const NewsInteraction = ({ newsIdEncrypted, isLikedInitial, triggerLikeExternally }) => {
  const [hasViewed, setHasViewed] = useState(false);
  const [isLiked, setIsLiked] = useState(false);
  const [showBigHeart, setShowBigHeart] = useState(false);
  const [likeBounceKey, setLikeBounceKey] = useState(0);
  const [rotateKey, setRotateKey] = useState(0);
  const [randomColor, setRandomColor] = useState('text-red-500');  

  const buttonRef = useRef(null);
  const { token } = useAuth();
  const apiUrl = useApiUrl();

  useEffect(() => {
    const timer = setTimeout(() => {
      if (!hasViewed) {
        axios.post(`${apiUrl}/api/news/${newsIdEncrypted}/view`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        }).then(() => setHasViewed(true))
          .catch((err) => console.error('View error:', err));
      }
    }, 5000);

    return () => clearTimeout(timer);
  }, [hasViewed, newsIdEncrypted, token]);
  
  useEffect(() => {
    if (typeof triggerLikeExternally === 'function') {      
      
      triggerLikeExternally(() => handleLike(true)); // Force like
    }
  }, [triggerLikeExternally]);

  const handleLike = async (forceLike = false) => {
    setRotateKey(prev => prev + 1);
  
    if (forceLike && !isLiked) {
      setIsLiked(true);
      setLikeBounceKey(prev => prev + 1);
  
      // Ambil warna acak dari daftar
      const random = heartColors[Math.floor(Math.random() * heartColors.length)];
      setRandomColor(random);
      setShowBigHeart(true)      
  
      // Store like ke backend
      try {
        await axios.post(`${apiUrl}/api/news/${newsIdEncrypted}/like`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          }
        });
      } catch (err) {
        console.error('Gagal menyimpan like:', err);
      }
  
      setTimeout(() => setShowBigHeart(false), 1000);
    } else {
      setIsLiked(false);
  
      // Hapus like di backend
      try {
        await axios.delete(`${apiUrl}/api/news/${newsIdEncrypted}/like`, {
          headers: {
            Authorization: `Bearer ${token}`,
          }
        });
      } catch (err) {
        console.error('Gagal menghapus like:', err);
      }
    }
  };  
  
  return (
    <>
      {/* Like Button */}
      <motion.button
        onClick={handleLike}
        ref={buttonRef}
        className="p-1 px-2 rounded-full border border-red-700 text-red-700"
      >
        <motion.i
          key={`${isLiked}-${likeBounceKey}-${rotateKey}`}
          className={`${isLiked ? 'ri-heart-3-fill' : 'ri-heart-3-line'} text-xl`}
          initial={{ y: 0, scale: 1, rotate: 0 }}
          animate={{
            y: isLiked ? -8 : 0,
            scale: isLiked ? 1.4 : 1,
            rotate: [0, -10, 10, -5, 0], // efek shake
          }}
          transition={{
            type: 'spring',
            stiffness: 500,
            damping: 12,
            rotate: { duration: 0.5 },
          }}
        />
      </motion.button>

      {/* Big Center Heart with color variation */}
      <AnimatePresence>
        {showBigHeart && (
          <motion.div
            key="big-heart"
            className="fixed inset-0 flex items-center justify-center z-50 pointer-events-none"
            initial={{ scale: 0, opacity: 0 }}
            animate={{
              scale: [0, 1.2, 1],
              opacity: 1,
              rotate: [0, -5, 5, -5, 0], // shake effect
            }}
            exit={{ scale: 0, opacity: 0 }}
            transition={{ duration: 0.3, ease: 'easeInOut' }}
          >
            <div className={`${randomColor}`} style={{ fontSize: '45vw' }}>
              <i className="ri-heart-3-fill"></i>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
};

export default NewsInteraction;