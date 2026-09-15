import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { useAuth } from "../Context/AuthContext";
import { useApiUrl } from "../Context/ApiContext";
import axios from "axios";

export default () => {

  const apiUrl = useApiUrl();
  const { token } = useAuth();
  const [datas, setData] = useState({});
  const [loading, setLoading] = useState(true);
  const { t } = useTranslation();

  useEffect(() => {
    const fetchData = async () => {
        try {
            const res = await axios.get(`${apiUrl}/api/quotes`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            setData(res.data);
        } catch (err) {
            // Kutipan hanya pemanis halaman -- kalau gagal, blok ini cukup
            // disembunyikan tanpa mengganggu pengguna dengan alert.
            console.log(err);
        } finally {
            setLoading(false);
        }
    };
    if(token) {
        fetchData();
    }
  }, [apiUrl, token]);

  if (loading) {
    return <div className="h-24 rounded-2xl skeleton" />;
  }

  if (!datas?.quotes) {
    return null;
  }

  return (
    // Blok kutipan duduk di permukaan tint yang sama dengan tile Quick Access,
    // jadi halaman punya dua bahasa yang jelas: kartu putih untuk data, blok
    // tint untuk suara brand. Garis aksen di tepi kiri sengaja dibuang -- itu
    // pola lama yang membuat kutipan terlihat seperti callout dokumentasi.
    <figure className="relative m-0 overflow-hidden rounded-2xl bg-brand-75 px-5 pt-7 pb-5">
      {/* Tanda kutip besar sebagai tekstur, bukan ikon. Ditaruh di belakang
          teks dengan warna brand yang jauh lebih terang. */}
      <i
        className="ri-double-quotes-l absolute -left-1 -top-2 text-[64px] leading-none text-brand-200"
        aria-hidden="true"
      />

      <blockquote className="relative m-0 text-brand-850 text-[14px] font-medium leading-[1.65]">
        {datas.quotes}
      </blockquote>

      {datas.author && (
        <figcaption className="relative mt-3 text-brand-850/70 text-[11px] font-semibold italic leading-snug">
          &mdash; {datas.author}
        </figcaption>
      )}
    </figure>
  );
};
