import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import { useApiUrl } from "../context/ApiContext";
import LiveContent from "../../pages/LiveContent";
import { useAuth } from "../context/AuthContext";
import { showAlert } from "../Helper/alertHelper";

export default () => {
  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { token } = useAuth();
  const apiUrl = useApiUrl();
  const [events, setEvents] = useState([]);  

  const handleNavigate = (e, path) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const bounds = { top: rect.top, left: rect.left, width: rect.width, height: rect.height };
    navigate(path, { state: { bounds } });
  };

  useEffect(() => {
    const fetchEvo = async () => {
      try {
        const res = await axios.get(`${apiUrl}/api/get-evo`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        
        const data = Array.isArray(res.data) ? res.data : [res.data];

        setEvents(
          data
            .filter(e => e.category === 'EVO') // filter hanya EVO
            .map(e => ({
              ...e,
              businessUnit: Array.isArray(e.businessUnit)
                ? e.businessUnit
                : (e.businessUnit ? [e.businessUnit] : []), // null jadi []
            }))
        );
      } catch (err) {
        showAlert({
          icon: 'warning',
          title: 'Connection Ended',
          text: 'Unable to connect to the server. Please try again later.',
          timer: 2500,
          showConfirmButton: false,
        });
      } finally {
        setLoading(false);
      }
    };
    fetchEvo();
  }, []);

  useEffect(() => {
    if (!token) {
      navigate("/");
    } else {
      axios
        .get(`${apiUrl}/api/user`, { headers: { Authorization: `Bearer ${token}` } })
        .then((res) => setUser(res.data))
        .catch((err) => {
          console.error(err);
          localStorage.removeItem("token");
          navigate("/");
        })
        .finally(() => setLoading(false));
    }
  }, [navigate]);

  useEffect(() => {
    const fetchEvent = async () => {
      try {
        const res = await axios.get(`${apiUrl}/api/live-content`, {
          headers: { Authorization: `Bearer ${token}` },
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

  const handleLiveClick = async () => {
    if (loading) return;
    if (data?.content_link) {
      setIsModalOpen(true);
    } else {
      await showAlert({
        title: "Content Not Available",
        text: "Please check back later, thank you!",
        icon: "info",
        timer: 2200,
        showConfirmButton: false,
      });
    }
  };

  return (
    <div className="self-stretch flex flex-col justify-start items-start gap-3">

      {
      // data?.content_link && 
      // (
      //   <button
      //     onClick={handleLiveClick}
      //     className="self-stretch p-2 rounded-lg shadow-md border border-red-700 bg-white text-red-700 text-[10px] font-semibold flex justify-center items-center gap-2"
      //   >
      //     <span className="relative inline-flex items-center">
      //       {/* ping bulat kecil di belakang emoji */}
      //       <span className="absolute w-3 h-3 rounded-full bg-red-600 animate-ping" />
      //       <span role="img" aria-label="live">🔴</span>
      //     </span>
      //     LIVE NOW!
      //   </button>
      // )
      }

      {/* Group buttons */}
      <div className="self-stretch inline-flex justify-center items-start gap-3">
        <button
          onClick={(e) => handleNavigate(e, "/event")}
          className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold"
        >
          🎉 Upcoming Events
        </button>
        {/* <button
          onClick={(e) => handleNavigate(e, `/evo/${events?.[0]?.encrypted_id}`)}
          className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold"
        >
          👥 EVO
        </button> */}
        {data?.content_link && (
            <button
              onClick={handleLiveClick}
              className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold"
            >
              <span className="relative inline-flex items-center">
                {/* ping bulat kecil di belakang emoji */}
                <span className="absolute w-3 h-3 rounded-full bg-red-600 animate-ping" />
                <span role="img" aria-label="live">🔴</span>
              </span>
              LIVE NOW!
            </button>
          )}
      </div>

      <div className="self-stretch inline-flex justify-center items-start gap-3">
        <button
          onClick={(e) => handleNavigate(e, "/survey")}
          className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold"
        >
          🗳️ Your Voice Matters!
        </button>
        <button
          onClick={(e) => handleNavigate(e, "/social")}
          className="flex-1 min-w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold"
        >
          🔗 Social Media
        </button>
      </div>

      {/* Modal LiveContent */}
      <LiveContent isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} id={data?.content_link}>
        <h2 className="text-lg font-semibold mb-4">Live Event Info</h2>
        <button
          onClick={() => setIsModalOpen(false)}
          className="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
        >
          Tutup
        </button>
      </LiveContent>
    </div>
  );
};
