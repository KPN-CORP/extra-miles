import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import AppShell from '../components/Layout/AppShell';
import HomeHero from '../components/sections/HomeHero';
import NewsSection from '../components/sections/NewsSection';
import MenuSection from '../components/sections/MenuSection';
import ActivitySection from '../components/sections/ActivitySection';
import QuoteSection from '../components/sections/QuoteSection';
import AssetSection from '../components/sections/AssetSection';
import { showAlert } from '../components/Helper/alertHelper';
import { useAuth } from '../components/Context/AuthContext';
import PageLoader from '../components/Loader/PageLoader';
import { SSO_URL } from '../components/Helper/ssoRedirect';

const Home = () => {
    const [loading, setLoading] = useState(true);
    const { token } = useAuth();
    const { t } = useTranslation();

    useEffect(() => {
        if (!token) {
            showAlert({
                icon: 'warning',
                title: t('alerts.sessionEnded'),
                text: t('alerts.sessionEndedText'),
                timer: 2500,
                showConfirmButton: false,
            }).then(() => {
                if (document.referrer) {
                    window.history.back();
                } else {
                    window.location.href = SSO_URL;
                }
            });
            return;
        }

        setLoading(false);
    }, []);

    if (loading) {
        return <PageLoader />;
    }

    return (
        <AppShell nav watermark>
            {/* Sapaan + identitas karyawan, menyatu dengan tepi atas layar.
                Tanpa sudut membulat dan tanpa bayangan: potongan diagonal pita
                sapaan yang jadi pemisahnya, dan bayangan sepanjang garis miring
                justru terbaca seperti salah render. */}
            <HomeHero />

            <div className="px-5 pt-2.5 flex flex-col gap-6">
                {/* Berita paling depan: isinya yang paling sering berubah.
                    Quick Access sudah dihafal karyawan, jadi boleh di bawahnya. */}
                <NewsSection />
                <MenuSection />
                <ActivitySection limit={2} />
                <QuoteSection />
                <AssetSection />
            </div>
        </AppShell>
    );
};

export default Home;
