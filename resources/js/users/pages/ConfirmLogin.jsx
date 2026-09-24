import { useLocation, useNavigate } from 'react-router-dom';
import { useApiUrl } from '../components/Context/ApiContext';
import { useAuth } from '../components/Context/AuthContext';
import { useEffect } from 'react';
import { SyncLoader } from 'react-spinners';
import { useTranslation } from 'react-i18next';

function ConfirmLogin() {
  const { saveToken } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const apiUrl = useApiUrl();
  const { t } = useTranslation();

  useEffect(() => {
    const searchParams = new URLSearchParams(location.search);
    const token = searchParams.get('token');

    // `replace` di semua cabang: URL /login-success?token=... tidak boleh
    // tertinggal di riwayat. Sebelumnya tombol back membawa pengguna kembali ke
    // token yang sudah mati, gagal lagi, dan berputar di halaman gagal login.
    const fail = (reason) =>
      navigate(`/login-failed?error=${encodeURIComponent(reason)}`, { replace: true });

    const validateToken = async () => {
      try {
        const response = await fetch(`${apiUrl}/api/verify`, {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json' // Always good to explicitly ask for JSON
          },
        });

        if (response.ok) { // response.ok is true for 2xx status codes
          saveToken(token);
          navigate('/', { replace: true });
          return;
        }

        const errorData = await response.json().catch(() => null);

        // Pesan backend (mis. "Unauthenticated.") selalu berbahasa Inggris,
        // jadi hanya dicatat di console; pengguna melihat teks terjemahan.
        console.error('Token verification failed:', response.status, errorData);
        fail(t('login.verificationFailed'));
      } catch (err) {
        console.error('Network or unexpected error:', err);
        fail(t('login.networkIssue'));
      }
    };

    if (token && token.startsWith('eyJ')) {
      validateToken();
    } else {
      // Tanpa token yang berbentuk JWT tidak ada yang bisa diverifikasi.
      // Dulu ini berhenti di layar statis tanpa jalan keluar.
      fail(t('login.missingToken'));
    }
  }, [location, navigate, saveToken, apiUrl, t]);

  return (
    <div className="app-surface app-bg flex flex-col items-center justify-center overflow-hidden">
      <h1 className="mb-4 text-brand-700 text-4xl font-bold italic tracking-wide">{t('app.brand')}</h1>
      <SyncLoader color="#B91C1C" size={15} />
    </div>
  );
}

export default ConfirmLogin;
