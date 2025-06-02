// utils/alertHelper.js
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const MySwal = withReactContent(Swal);

export const showAlert = (options) => {
    const defaultOptions = {
      icon: 'info',
      confirmButtonText: 'OK',
      reverseButtons: true,
      customClass: {
        confirmButton: 'bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded',
        cancelButton: 'bg-stone-500 hover:bg-stone-600 text-white px-4 py-2 rounded'
      }
    };
  
    return MySwal.fire({
      ...defaultOptions,
      ...options
    });
  };