import React, { useEffect, useState } from "react"
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom"
import axios from "axios"
import { useApiUrl } from "../Context/ApiContext";
import LiveContent from "../../pages/LiveContent";
import { useAuth } from "../Context/AuthContext";
import { showAlert } from "../Helper/alertHelper";
import SectionHeader from "../Layout/SectionHeader";
import { ACTIVITIES_KEY, prefetchWellness } from "../Helper/wellnessCache";

// Semua tile memakai satu permukaan: brand-75 (#ffe7e7), dengan ikon versi
// `-fill` dan label yang sama-sama brand-850 (#812121). Warnanya diset sekali
// di tombolnya dan diwarisi keduanya, jadi tidak ada dua nilai yang bisa
// berselisih. Yang membedakan tile bukan warna lagi, tapi bentuk ikonnya --
// jadi ikon harus tetap berbeda jelas satu sama lain. Bobot merah halaman ini
// sekarang ada di pita sapaan, bukan di enam blok merah di bawahnya.
//
// Labelnya dipatahkan jadi dua baris lewat \n di berkas terjemahan, bukan di
// sini, supaya penerjemah yang menentukan titik patahnya (istilah Indonesia
// tidak selalu pecah di tempat yang sama).
//
// `evo` hanya muncul untuk karyawan yang punya hasEvoPermission, sehingga grid
// bisa berisi 5 atau 6 tile. "Wellness Saya" tidak lagi di sini; pintunya ada di
// halaman Wellness.
// Urutan array ini = urutan di grid 3 kolom, jadi baris pertama adalah tiga
// entri pertama. Catatan: `evo` bergantung hasEvoPermission, jadi bagi karyawan
// yang tidak punya izin itu grid tinggal 5 tile dan sisanya bergeser naik --
// tidak ada cara mempertahankan susunan 3+3 dengan lima item.
const TILES = [
  // Baris 1
  { key: "event", labelKey: "menu.upcomingEvents", icon: "ri-calendar-event-fill", path: "/event" },
  { key: "evo", labelKey: "menu.evoProgram", icon: "ri-rocket-2-fill", path: "/evo", requiresEvo: true },
  { key: "wellness", labelKey: "menu.wellness", icon: "ri-heart-pulse-fill", path: "/wellness", prefetch: ACTIVITIES_KEY },
  // Baris 2
  { key: "live", labelKey: "menu.liveNow", icon: "ri-live-fill" },
  { key: "survey", labelKey: "menu.yourVoiceMatters", icon: "ri-questionnaire-fill", path: "/survey" },
  { key: "social", labelKey: "menu.socialMedia", icon: "ri-share-forward-fill", path: "/social" },
];

export default () => {

  const navigate = useNavigate();
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { token, user } = useAuth();
  const apiUrl = useApiUrl();
  const { t } = useTranslation();

  const isLive = Boolean(data.content_link);
  const tiles = TILES.filter((tile) => !tile.requiresEvo || user?.hasEvoPermission);

  // AnimatePresence runs mode="wait", so the target page only mounts once this
  // one has finished animating out. Warming the wellness endpoints here lets the
  // request run during that animation instead of after it.
  const handleNavigate = (e, path, prefetchKey = null) => {
    if (prefetchKey) prefetchWellness(prefetchKey, apiUrl, token);

    const rect = e.currentTarget.getBoundingClientRect();
    const bounds = {
      top: rect.top,
      left: rect.left,
      width: rect.width,
      height: rect.height,
    };
    navigate(path, { state: { bounds } });
  };

  useEffect(() => {
    if (!token) {
      navigate("/")
    }
  }, [navigate, token])

  useEffect(() => {
    const fetchEvent = async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/live-content`, {
                headers: {
                Authorization: `Bearer ${token}`,
                },
            });
            setData(res.data);
        } catch (err) {
            console.log(err);

        } finally {
            setLoading(false);
        }
    };
    fetchEvent();
}, []);

  const handleOffAir = async () => {
      await showAlert({
          title: t('menu.contentNotAvailable'),
          text: t('menu.contentNotAvailableText'),
          icon: "info",
          timer: 2500,
          showConfirmButton: false
      });
  };

  const handleTile = (e, tile) => {
    if (tile.key === "live") {
      return isLive ? setIsModalOpen(true) : handleOffAir();
    }

    handleNavigate(e, tile.path, tile.prefetch);
  };

return (
    <section className="flex flex-col gap-3">
        <SectionHeader title={t('home.quickAccess')} />

        <div className="grid grid-cols-3 gap-4 md:grid-cols-4 rail:grid-cols-6">
          {tiles.map((tile) => {
            return (
              <button
                key={tile.key}
                type="button"
                onClick={(e) => handleTile(e, tile)}
                // h-24 + justify-center menempatkan ikon dan label di tengah
                // tile, sementara min-h pada label menjaga ikon tetap satu garis
                // meski labelnya pecah dua baris ("Upcoming Events").
                className="tap relative h-24 rounded-2xl bg-brand-75 text-brand-850 shadow-tile px-[5px] py-2 flex flex-col items-center justify-center gap-2"
              >
                {tile.key === "live" && !loading && (
                  // Titik status di kanan atas menggantikan pil "LIVE" dan teks
                  // "Off air" di bawah: labelnya sudah menyebut "Live Now", jadi
                  // yang perlu ditandai cuma sedang siaran atau tidak. Cincin
                  // yang memuai lebih cepat tertangkap sudut mata daripada teks
                  // 9px, dan tidak menambah baris di dalam tile.
                  //
                  // Titik padatnya tetap ada saat animasi dimatikan (lihat blok
                  // prefers-reduced-motion di global.css), dan statusnya
                  // dituliskan untuk pembaca layar -- warna plus gerak saja
                  // tidak menyampaikan apa pun ke sana.
                  <span
                    className="absolute top-2 right-2 grid place-items-center"
                    title={isLive ? t('menu.liveBadge') : t('menu.offAir')}
                  >
                    {isLive && (
                      <span
                        className="absolute w-2.5 h-2.5 rounded-full bg-brand-700 opacity-75 animate-ping"
                        aria-hidden="true"
                      />
                    )}
                    <span
                      className={`relative w-2 h-2 rounded-full ${isLive ? 'bg-brand-700' : 'bg-brand-850/30'}`}
                      aria-hidden="true"
                    />
                    <span className="sr-only">{isLive ? t('menu.liveBadge') : t('menu.offAir')}</span>
                  </span>
                )}

                <i className={`${tile.icon} text-[25px] leading-none`} aria-hidden="true" />

                {/* Span dalam memastikan \n benar-benar jadi baris baru: anak
                    langsung sebuah flex container tidak mematuhi pre-line. */}
                <span className="min-h-[30px] flex items-center justify-center">
                  <span className="whitespace-pre-line text-center text-[11.5px] font-bold tracking-[-0.005em] leading-tight">
                    {t(tile.labelKey)}
                  </span>
                </span>
              </button>
            );
          })}
        </div>

        <LiveContent isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} id={data.content_link}>
          <h2 className="text-lg font-semibold mb-4">{t('menu.liveEventInfo')}</h2>
          <button
            onClick={() => setIsModalOpen(false)}
            className="mt-4 px-4 py-2 bg-brand-600 text-white rounded hover:bg-brand-700"
          >
            {t('common.close')}
          </button>
        </LiveContent>
    </section>
);
};
