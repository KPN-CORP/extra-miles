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
import { MyEventsProvider } from '../components/Context/MyEventsContext';
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

            {/* pt-6 = gap-6: jarak di atas seksi pertama sama dengan jarak
                antar-seksi, jadi ritmenya satu ukuran dari atas ke bawah. */}
            <MyEventsProvider>
                <div className="px-5 pt-6 flex flex-col gap-6">
                    {/* Undangan yang belum dijawab naik ke paling atas: itu
                        satu-satunya seksi beranda yang menunggu tindakan
                        karyawan, dan tenggatnya lewat kalau tidak terlihat.
                        Kalau kosong seksinya hilang sama sekali (hideWhenEmpty)
                        sehingga berita kembali jadi yang pertama. */}
                    <ActivitySection limit={2} only="pending" hideWhenEmpty />

                    {/* Berita di depan sisanya: isinya yang paling sering
                        berubah. Quick Access sudah dihafal karyawan, jadi boleh
                        di bawahnya. */}
                    <NewsSection />
                    <MenuSection />
                    <ActivitySection limit={2} only="registered" />
                    <QuoteSection />
                    <AssetSection />
                </div>
            </MyEventsProvider>
        </AppShell>
    );
};

export default Home;
