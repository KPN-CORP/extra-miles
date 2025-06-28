import React, { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import axios from "axios"
import { useApiUrl } from "../context/ApiContext";
import LiveContent from "../../pages/LiveContent";
import { useAuth } from "../context/AuthContext";
import { showAlert } from "../Helper/alertHelper";

export default () => {

  const navigate = useNavigate();
  const [user, setUser] = useState(null);
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const { token } = useAuth();
  const apiUrl = useApiUrl();

  const handleNavigate = (e, path) => {
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
    } else {
      // Ambil data user dari backend via API Gateway      
      axios
        .get(`${apiUrl}/api/user`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        })
        .then((res) => {
          setUser(res.data)
        })
        .catch((err) => {
          console.error(err)
          localStorage.removeItem("token")
          navigate("/")
        })
        .finally(() => {
          setLoading(false)
        })
    }
  }, [navigate])

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
          title: "Content Not Available",
          text: `Please check back later, thankyou!`,
          icon: "info",
          timer: 2500,
          showConfirmButton: false
      });
  };

return (
    <div className="self-stretch flex flex-col justify-start items-start gap-3">
        <div className="self-stretch inline-flex justify-center items-start gap-3">
          <button onClick={(e) => handleNavigate(e, "/event")} className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
            🎉 Upcoming Events
          </button>
          <button
            onClick={() => {
              if (data.content_link) {
                setIsModalOpen(true);
              } else {
                handleOffAir();
              }
            }}
            className={`flex-1 min-w-fit p-3 rounded-lg shadow-md flex justify-center items-center gap-2 text-[10px] font-semibold
              ${data.content_link ? 'bg-white text-red-700 ring-1 ring-red-700 ring-inset' : 'bg-red-700 text-white'}`}
          >
            <span className={`${data.content_link ? 'on-pulse' : 'off-pulse'} me-1`}></span>
            LIVE NOW{data.content_link ? '!' : ''}
          </button>
        </div>
        <div className="self-stretch inline-flex justify-center items-start gap-3">
          <button onClick={(e) => handleNavigate(e, "/survey")} className="flex-1 min-w-fit w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
              🗳️ Your Voice Matters!
          </button>
          <button onClick={(e) => handleNavigate(e, "/social")} className="flex-1 min-w-fit p-3 bg-red-700 rounded-lg shadow-md flex justify-center items-center gap-2 text-white text-[10px] font-semibold">
              🔗 Social Media
          </button>
        </div>
        <LiveContent isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} id={data.content_link}>
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