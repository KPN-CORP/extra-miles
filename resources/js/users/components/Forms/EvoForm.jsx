import React, { useEffect, useState } from 'react';
import { Formik, Form } from 'formik';
import { PulseLoader } from 'react-spinners';
import { useApiUrl } from '../context/ApiContext';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { showAlert } from '../Helper/alertHelper';
import { useAuth } from '../context/AuthContext';
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

    return {
        countryCode: '+62',
        whatsapp_number: fullNumber?.replace(/^\+?62?0?/, '') || ''
    };
}

export default function EvoForm({ encryptedID, registered }) {
    const [formFields, setFormFields] = useState([]);
    const [initialValues, setInitialValues] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showRegistrationButton, setShowRegistrationButton] = useState(false);

    const apiUrl = useApiUrl();
    const id = encryptedID;
    const { token, user } = useAuth();
    const navigate = useNavigate();
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [quotaStatus, setQuotaStatus] = useState({});

    useEffect(() => {
        const fetchFormSchema = async () => {
            try {
                const response = await axios.get(`${apiUrl}/api/event-form/${id}`, {
                    headers: {
                        Accept: 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });

                const formSchema = response.data;

                const quotaMap = {};
                response.data.programQuota?.forEach(p => {
                    quotaMap[p.program] = p.quota_full;
                });
                
                setQuotaStatus(quotaMap);

                setFormFields(Array.isArray(formSchema.fields) ? formSchema.fields : []);

                let initialValues = {};
                if (Array.isArray(formSchema?.fields)) {
                    formSchema.fields.forEach((field) => {
                        if (field.type === 'checkbox') {
                            initialValues[field.name] = Array.isArray(field.options) ? [] : false;
                        } else if (field.type === 'radio') {
                            initialValues[field.name] = '';
                        } else {
                            initialValues[field.name] = '';
                        }
                    });
                }

                if (registered?.form_data) {
                    const parsedData = JSON.parse(registered.form_data);
                    initialValues = { ...initialValues, ...parsedData };
                }

                const cleanNumber = (user?.whatsapp_number ? user?.whatsapp_number : (user?.personal_mobile_number ?? '')).replace(/'/g, '');
                const parsedWhatsapp = parsePhoneNumber(user ? cleanNumber : '');

                initialValues.countryCode = parsedWhatsapp.countryCode;
                initialValues.whatsapp_number = parsedWhatsapp.whatsapp_number;

                setInitialValues(initialValues);
            } catch (err) {
                console.error("Error fetching form schema:", err);
            } finally {
                setLoading(false);
            }
        };

        fetchFormSchema();
    }, [apiUrl]);

    const onSubmit = async (values, { setSubmitting }) => {
        setIsSubmitting(true);
        try {
            const normalizedNumber = `${values.countryCode}${values.whatsapp_number.replace(/^0+/, '')}`;

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
                return;
            }

            const payload = {
                eventId: id,
                personalMobileNumber: normalizedNumber,
                formData: { ...values },
            };

            const url = registered
                ? `${apiUrl}/api/evo-registration-update`
                : `${apiUrl}/api/evo-registration`;

            const response = await axios.post(url, payload, {
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    Authorization: `Bearer ${token}`,
                },
            });

            const isOK = response.status === 201 || response.status === 200;

            showAlert({
                icon: isOK ? 'success' : 'error',
                title: isOK ? 'Registration Successful' : 'Registration Failed',
                text: response.data.message,
                timer: 2500,
                showConfirmButton: false,
            }).then(() => navigate(`/`, { replace: true }));
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
                <PulseLoader margin={2} size={8} color="#B91C1C" speedMultiplier={0.75} />
            </div>
        );
    }

    return (
        <div className="w-full mb-2">
            {initialValues && (
                <Formik
                    initialValues={initialValues}
                    validationSchema={generateValidationSchema(formFields)}
                    onSubmit={onSubmit}
                    enableReinitialize
                >
                    {({ values, handleChange, handleBlur, touched, errors, setFieldValue, isSubmitting }) => (
                        <Form>
                            {/* PHONE NUMBER */}
                            <div className="mb-6">
                                <label className="block text-gray-700 mb-2">
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

                            {/* FORM FIELDS */}
                            <div className="bg-red-700 p-4 mb-6 shadow-md">
                                <div className="bg-white rounded-lg p-5">
                                    <h3 className="text-red-700 font-light text-lg mb-4">Registration Form</h3>

                                    {formFields.map((field) => (
                                        <div key={field.name} className="mb-5">
                                            <label className="block text-gray-800 font-medium mb-4">
                                                {field.label}
                                            </label>

                                            {/** CHECKBOX FIELD */}
                                            {field.type === "checkbox" && Array.isArray(field.options) ? (
                                                <div className="space-y-3">
                                                    {field.options.map((option) => {
                                                        const regData = registered?.form_data
                                                            ? JSON.parse(registered.form_data)
                                                            : {};
                                                        const alreadySelected = regData[field.name].includes(option);     
                                                                                                
                                                        const optionKey = `${field.name}-${option}`;
                                                        
                                                        return (
                                                            <label key={optionKey} className="flex text-sm items-center space-x-2">
                                                                <input
                                                                    type="checkbox"
                                                                    name={field.name}
                                                                    value={option}
                                                                    disabled={quotaStatus[option] === true}
                                                                    checked={
                                                                        values[field.name].includes(option)
                                                                    }
                                                                    onChange={(e) => {
                                                                        const currentValue = Array.isArray(values[field.name])
                                                                            ? values[field.name]
                                                                            : [];
                                                                        const set = new Set(currentValue);
                                                                        if (e.target.checked) set.add(option);
                                                                        else set.delete(option);
                                                                        setFieldValue(field.name, Array.from(set));
                                                                    }}
                                                                    className="accent-red-700 w-4 h-4"
                                                                />
                                                                <span
                                                                    className={`text-gray-800 ${
                                                                        alreadySelected ? "text-gray-500 italic" : ""
                                                                    }`}
                                                                >
                                                                    {option}
                                                                    {/* REGISTRATION STATUS */}
                                                                    {alreadySelected && (
                                                                        <span className="ml-1 text-xs text-gray-400">(registered)</span>
                                                                    )}

                                                                    {/* QUOTA FULL — only shown if NOT registered */}
                                                                    {!alreadySelected && quotaStatus[option] && (
                                                                        <span className="ml-1 text-xs text-red-600">(Quota Full)</span>
                                                                    )}
                                                                </span>
                                                            </label>
                                                        );
                                                    })}
                                                </div>
                                            ) : null}

                                            {/** RADIO FIELD */}
                                            {field.type === "radio" && Array.isArray(field.options) ? (
                                                <div className="space-y-2">
                                                    {field.options.map((option) => {
                                                        const regData = registered?.form_data
                                                            ? JSON.parse(registered.form_data)
                                                            : {};
                                                        const alreadySelected = regData[field.name] === option;
                                                        const optionKey = `${field.name}-${option}`;

                                                        return (
                                                            <label key={optionKey} className="flex items-center space-x-2 cursor-pointer">
                                                                <input
                                                                    type="radio"
                                                                    name={field.name}
                                                                    value={option}
                                                                    disabled={quotaStatus[option] === true}
                                                                    checked={values[field.name] === option}
                                                                    onChange={() => setFieldValue(field.name, option)}
                                                                    className="accent-red-700 w-4 h-4"
                                                                />
                                                                <span
                                                                    className={`text-gray-800 
                                                                        ${alreadySelected ? "text-gray-500 italic" : ""} 
                                                                        ${!alreadySelected && quotaStatus[option] ? "text-gray-400 line-through" : ""}`}
                                                                >
                                                                    {option}

                                                                    {/* REGISTRATION STATUS */}
                                                                    {alreadySelected && (
                                                                        <span className="ml-1 text-xs text-gray-400">(registered)</span>
                                                                    )}

                                                                    {/* QUOTA FULL — only shown if NOT registered */}
                                                                    {!alreadySelected && quotaStatus[option] && (
                                                                        <span className="ml-1 text-xs text-red-600">(Quota Full)</span>
                                                                    )}
                                                                </span>
                                                            </label>
                                                        );
                                                    })}
                                                </div>
                                            ) : null}

                                            {/** TEXTAREA */}
                                            {field.type === "textarea" ? (
                                                <textarea
                                                    id={field.name}
                                                    name={field.name}
                                                    value={values[field.name]}
                                                    onChange={handleChange}
                                                    onBlur={handleBlur}
                                                    className="w-full border rounded p-2"
                                                />
                                            ) : null}

                                            {/** DEFAULT INPUT */}
                                            {field.type !== "checkbox" &&
                                            field.type !== "radio" &&
                                            field.type !== "textarea" ? (
                                                <input
                                                    id={field.name}
                                                    name={field.name}
                                                    type={field.type}
                                                    value={values[field.name]}
                                                    onChange={handleChange}
                                                    onBlur={handleBlur}
                                                    className="w-full border rounded p-2"
                                                />
                                            ) : null}

                                            {touched[field.name] && errors[field.name] && (
                                                <p className="text-red-700 text-sm mt-1">{errors[field.name]}</p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className="w-full px-5 py-2.5 bg-red-700 rounded-lg shadow-md inline-flex justify-center items-center text-white text-sm font-semibold"
                            >
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
