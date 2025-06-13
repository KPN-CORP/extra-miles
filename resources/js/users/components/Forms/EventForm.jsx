import React, { useEffect, useMemo, useState } from 'react';
import { Formik, Form, Field } from 'formik';
import { PulseLoader } from 'react-spinners';
import { useApiUrl } from '../context/ApiContext';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom'; // Import useParams to get id from endpoint
import { showAlert } from '../Helper/alertHelper';
import { useAuth } from '../context/AuthContext';
import PageLoader from '../Loader/PageLoader';

import { generateValidationSchema } from '../Helper/generateValidationSchema';

function parsePhoneNumber(fullNumber) {
    const knownCountryCodes = ['+62', '+60', '+65', '+63', '+66', '+84', '+91', '+966', '+81', '+1'];
    for (const code of knownCountryCodes) {
        if (fullNumber.startsWith(code)) {
        return {
            countryCode: code,
            whatsapp_number: fullNumber.slice(code.length).replace(/^0+/, '')
        };
        }
    }
    
    // Default fallback (anggap Indonesia)
    return {
        countryCode: '+62',
        whatsapp_number: fullNumber?.replace(/^\+?62?0?/, '') || ''
    };
  }
  

export default function EventForm() {
    const [formFields, setFormFields] = useState([]);
    const [initialValues, setInitialValues] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showRegistrationButton, setShowRegistrationButton] = useState(false); // New state
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token, user } = useAuth();
    const navigate = useNavigate();
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {        
        
        const fetchFormSchema = async () => {
            try {
                const response = await axios.get(`${apiUrl}/api/event-form/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });                   

                const formSchema = response.data;                

                    // Set form fields
                    setFormFields(Array.isArray(formSchema.fields) ? formSchema.fields : []);

                    // Set initial values
                    const initialValues = {};
                    if (Array.isArray(formSchema?.fields)) {
                        formSchema.fields.forEach(field => {
                            if (Array.isArray(field.options)) {
                            initialValues[field.name] = field.type === 'checkbox' ? [] : '';
                            } else {
                            initialValues[field.name] = field.type === 'checkbox' ? false : '';
                            }
                        });
                    }

                    const cleanNumber = (user?.whatsapp_number ? user?.whatsapp_number : (user?.personal_mobile_number ?? '')).replace(/'/g, '');                    

                    const parsedWhatsapp = parsePhoneNumber(user? cleanNumber : '');

                    initialValues.countryCode = parsedWhatsapp.countryCode;
                    initialValues.whatsapp_number = parsedWhatsapp.whatsapp_number;

                    setInitialValues(initialValues);
            } catch (error) {
                console.error("Error fetching form schema:", error);
            } finally {
                setLoading(false);
            }
        };

        fetchFormSchema();
    }, [apiUrl]);

    const onSubmit = async (values, { setSubmitting }) => {
        setIsSubmitting(true);
        try {
            const normalizedNumber = `${values.countryCode}${values.whatsapp_number.replace(/^0+/, '')}`; // Normalisasi nomor WhatsApp

            const result = await showAlert({
                title: 'Confirmation',
                html: `Pastikan nomor WhatsApp Anda sudah benar:<br><strong>${normalizedNumber}</strong><br><br>Lanjutkan registrasi?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
            });
        
            if (!result.isConfirmed) {
                setSubmitting(false);
                return; // batal submit
            }
        
            const payload = {
                eventId: id,
                personalMobileNumber: normalizedNumber,
                formData: {
                    ...values,
                },
            };
            
            const response = await axios.post(`${apiUrl}/api/event-registration`, payload, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    Authorization: `Bearer ${token}`,
                },
            });            

            if (response.status === 201) {
                showAlert({
                    icon: 'success',
                    title: 'Registration Successful',
                    text: response.data.message || 'You have successfully registered for the event. Thank you!',
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => {
                    navigate(`/event`, { replace: true });
                });
            } else {
                showAlert({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: response.data.message || 'An error occurred during registration. Please try again.',
                    timer: 2500,
                    showConfirmButton: false,
                }).then(() => {
                    navigate(`/event`, { replace: true });
                });
            }
        } catch (error) {
            console.error("Error submitting form:", error);
            alert("An error occurred while submitting the form.");
        } finally {
            setSubmitting(false);
            setIsSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="w-full inline-flex justify-center items-center overflow-hidden">
                <PulseLoader cssOverride={{}} margin={2} size={8} color="#B91C1C" speedMultiplier={0.75} />
            </div>
        )
    }

    if (showRegistrationButton) {
        const handleSimpleRegister = async () => {
            
            setIsSubmitting(true);
            try {
                const response = await axios.post(
                    `${apiUrl}/api/event-registration`,
                    {
                        eventId: id,
                        formData: {} // empty form data
                    },
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }
                );
    
                if (response.status === 201) {
                    showAlert({
                        icon: 'success',
                        title: 'Registration Successful',
                        text: response.data.message || 'You have successfully registered for the event.',
                        timer: 2500,
                        showConfirmButton: false,
                    }).then(() => {
                        navigate(`/event`, { replace: true });
                    });
                } else {
                    showAlert({
                        icon: 'error',
                        title: 'Registration Failed',
                        text: response.data.message || 'Something went wrong.',
                        timer: 2500,
                        showConfirmButton: false,
                    }).then(() => {
                        navigate(`/event`, { replace: true });
                    });
                }
            } catch (error) {
                console.error("Error submitting registration:", error);
                showAlert({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to connect to the server. Please try again later.',
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
            setIsSubmitting(false);
        };
    
        return (
            <>
            <div className='w-full mb-2'>

            </div>
            <div className='w-full mb-2'>
                <button
                    onClick={handleSimpleRegister}
                    className="w-full px-5 py-2.5 bg-red-700 rounded-lg shadow-md inline-flex justify-center items-center overflow-hidden text-white text-sm font-semibold"
                >
                    {isSubmitting ? (
                        <PulseLoader size={8} color="#fff" margin={2} speedMultiplier={0.75} />
                    ) : (
                        'Submit'
                    )}
                </button>
            </div>
            </>
        );
    }    

    let schema;
try {
  schema = generateValidationSchema(formFields);
} catch (err) {
  console.error("Validation Schema Error:", err);
}

    return (
        <div className='w-full mb-2'>
            {initialValues && (
            <Formik
                initialValues={initialValues}
                validationSchema={generateValidationSchema(formFields)}
                onSubmit={onSubmit}
                enableReinitialize
            >
                {({ values, handleChange, handleBlur, isSubmitting, touched, errors, setFieldValue }) => (
                    <Form>
                        <div className="mb-6">
                            <label className="block text-gray-700 mb-2" htmlFor="whatsapp_number">
                                WhatsApp Number <span className="text-red-600">*</span>
                            </label>
                            <div className="flex">
                                <select
                                id="countryCode"
                                value={values.countryCode}
                                onChange={handleChange}
                                className="border rounded-l p-2 me-1 bg-white"
                                >
                                    <option value="+62">🇮🇩 +62</option>
                                    <option value="+60">🇲🇾 +60</option>
                                    <option value="+65">🇸🇬 +65</option>
                                    <option value="+63">🇵🇭 +63</option>
                                    <option value="+66">🇹🇭 +66</option>
                                    <option value="+84">🇻🇳 +84</option>
                                    <option value="+91">🇮🇳 +91</option>
                                    <option value="+966">🇸🇦 +966</option>
                                    <option value="+81">🇯🇵 +81</option>
                                    <option value="+1">🇺🇸 +1</option>
                                </select>
                                <input
                                type="tel"
                                name="whatsapp_number"
                                value={values.whatsapp_number}
                                onChange={handleChange}
                                onBlur={handleBlur}
                                placeholder="phone number"
                                className="w-full border rounded-r p-2"
                                />
                            </div>
                            {touched.whatsapp_number && errors.whatsapp_number && (
                                <p className="text-red-700 text-sm mt-1">{errors.whatsapp_number}</p>
                            )}
                        </div>

                        {formFields.map((field) => (
                            <div key={field.name} className="mb-4">
                                <label className="block text-gray-700 mb-2" htmlFor={field.name}>
                                    {field.label}
                                </label>
    
                                {field.type === 'checkbox' && Array.isArray(field.options) ? (
                                    <div className="space-y-2">
                                        {field.options.map((option, index) => (
                                        <label key={index} className="flex items-center">
                                            <input
                                            type="checkbox"
                                            name={field.name}
                                            value={option}
                                            checked={Array.isArray(values[field.name]) && values[field.name].includes(option)}
                                            onChange={(e) => {
                                                const currentValue = Array.isArray(values[field.name]) ? values[field.name] : [];
                                                const set = new Set(currentValue);
                                                if (e.target.checked) {
                                                set.add(option);
                                                } else {
                                                set.delete(option);
                                                }
                                                setFieldValue(field.name, Array.from(set));
                                            }}
                                            className="mr-2"
                                            />
                                            {option}
                                        </label>
                                        ))}
                                    </div>
                                    ) : field.type === 'checkbox' ? (
                                    <label className="flex items-center">
                                        <Field 
                                        id={field.name}
                                        name={field.name}
                                        type="checkbox"
                                        checked={values[field.name]}
                                        onChange={handleChange}
                                        onBlur={handleBlur}
                                        className="mr-2" 
                                        />
                                        I agree
                                    </label>
                                ) : field.type === 'radio' ? (
                                    <div className="space-y-2">
                                      {field.options.map((option, index) => (
                                        <label key={index} className="flex items-center">
                                          <input
                                            type="radio"
                                            name={field.name}
                                            value={option}
                                            checked={values[field.name] === option}
                                            onChange={handleChange}
                                            onBlur={handleBlur}
                                            className="mr-2"
                                          />
                                          {option}
                                        </label>
                                      ))}
                                    </div>
                                  ) : field.type === 'select' ? (
                                    <select
                                        id={field.name}
                                        name={field.name}
                                        value={values[field.name]}
                                        onChange={handleChange}
                                        onBlur={handleBlur}
                                        className="w-full border rounded p-2"
                                    >
                                        <option value="" label="Select an option" />
                                        {field.options.map((option, index) => (
                                            <option key={index} value={option} label={option}>
                                                {option}
                                            </option>
                                        ))}
                                    </select>
                                ) : (field.type === 'textarea' ? (
                                    <textarea
                                        id={field.name}
                                        name={field.name}
                                        type={field.type}
                                        value={values[field.name]}
                                        onChange={handleChange}
                                        onBlur={handleBlur}
                                        className="w-full border rounded p-2"
                                    />
                                ) : (
                                    <input
                                        id={field.name}
                                        name={field.name}
                                        type={field.type}
                                        value={values[field.name]}
                                        onChange={handleChange}
                                        onBlur={handleBlur}
                                        className="w-full border rounded p-2"
                                    />
                                ))}
    
                                {touched[field.name] && errors[field.name] && (
                                    <p className="text-red-700 text-sm mt-1">{errors[field.name]}</p>
                                )}
                            </div>
                        ))}
    
                        <button type="submit" disabled={isSubmitting} className="w-full px-5 py-2.5 bg-red-700 rounded-lg shadow-md inline-flex justify-center items-center overflow-hidden text-white text-sm font-semibold">
                            {isSubmitting ? (
                            <PulseLoader size={8} color="#fff" margin={2} speedMultiplier={0.75} />
                            ) : (
                                'Submit'
                            )}
                        </button>
                    </Form>
                )}
            </Formik>
            )}
        </div>
    );
}
