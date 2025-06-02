import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import YouTubePlayer from '../components/Helper/youtubeHelper';

export default function LiveContent({ isOpen, onClose, id }) {
    return (
      <AnimatePresence>
        {isOpen && (
          <motion.div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-2"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
          >
            <motion.div
              className="relative rounded-xl shadow-lg ring-2 ring-black max-w-md w-full bg-black"
              initial={{ scale: 0.9, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.9, opacity: 0 }}
              transition={{ duration: 0.3 }}
            >
              <button
                className="absolute top-2 right-2 p-2 px-3 text-white font-semibold"
                style={{ zIndex: 9999 }}
                onClick={onClose}
              >
                ✕
              </button>
              <YouTubePlayer videoId={id} />
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    );
}