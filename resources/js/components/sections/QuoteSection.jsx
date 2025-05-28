import React, { useEffect, useState } from "react";
import { useAuth } from "../context/AuthContext";
import { PulseLoader } from "react-spinners";
import { showAlert } from "../Helper/alertHelper";
import axios from "axios";

export default () => {

  const apiUrl = import.meta.env.VITE_API_URL;
  const { token } = useAuth();  
  const [datas, setData] = useState([]);
  const [loading, setLoading] = useState(true);



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
            showAlert({
                icon: 'warning',
                title: 'Connection Ended',
                text: 'Unable to connect to the server. Please try again later.',
                timer: 2500,
                showConfirmButton: false,
            }).then(() => {
                console.log(err);
                
                // window.location.href = "https://kpncorporation.darwinbox.com/";
            });
        } finally {
            setLoading(false);
        }
    };
    if(token) {
        fetchData();
    }
  }, [token]);

  if (loading) {
    return <PulseLoader className='w-full justify-center text-center' margin={2} size={8} color="#FFF" speedMultiplier={0.75} />;
  }  

  return (
    <div className="w-60 flex flex-col justify-start items-start gap-1 mb-4">
        <div className="self-stretch justify-start text-stone-800 text-sm leading-none">“{datas.quotes}”</div>
        <div className="justify-start text-stone-500 text-sm font-normal leading-none italic">- {datas.author}</div>
    </div>
  );
};