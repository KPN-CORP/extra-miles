// utils/alertHelper.js
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const MySwal = withReactContent(Swal);

export const showAlert = (options) => {
    const defaultOptions = {
      icon: 'info',
      confirmButtonText: 'OK',
      customClass: {
        confirmButton: 'bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded'
      }
    };
  
    return MySwal.fire({
      ...defaultOptions,
      ...options
    });
  };