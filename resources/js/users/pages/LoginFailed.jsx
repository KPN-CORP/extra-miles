import React, { useEffect, useState } from 'react';
import { SyncLoader } from 'react-spinners';
import { useSearchParams } from 'react-router-dom';
import { showAlert } from '../components/Helper/alertHelper';

function LoginFailed() {
  const [searchParams] = useSearchParams();
  const [errorText, setErrorText] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const errorParam = searchParams.get('error');
    const message = errorParam ? decodeURIComponent(errorParam) : 'Your session has ended.';

    setErrorText(message);

    showAlert({
      icon: 'error',
      title: 'Login Failed',
      text: message,
      timer: 3000,
      showConfirmButton: false,
    }).then(() => {
      window.location.href = "https://kpncorporation.darwinbox.com/";
    });
  }, [searchParams]);

  return (
    <div className="flex flex-col items-center justify-center h-screen bg-gradient-to-br from-stone-50 to-orange-200 overflow-hidden">
      <h1 className="mb-4 text-red-700 text-4xl font-bold italic">EXTRA MILE</h1>
      <SyncLoader color="#B91C1C" size={15} />
    </div>
  );
}

export default LoginFailed;