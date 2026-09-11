import React from 'react';
import { useTranslation } from 'react-i18next';

const Footer = () => {
  const { t } = useTranslation();

  return (
    <footer className="bg-gray-800 text-white py-4 px-6 text-center">
      {t('app.footer')}
    </footer>
  );
};

export default Footer;