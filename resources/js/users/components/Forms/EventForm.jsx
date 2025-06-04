import React from 'react';
import { Formik, Form } from 'formik';
import * as Yup from 'yup';
import { PulseLoader } from 'react-spinners';
import { useApiUrl } from '../context/ApiContext';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom'; // Import useParams to get id from endpoint
import { showAlert } from '../Helper/alertHelper';
import { useAuth } from '../context/AuthContext';
import PageLoader from '../Loader/PageLoader';

function generateValidationSchema(fields) {
    const shape = {};
  
    fields.forEach(field => {
      let schema;
  
      // Determine base schema type
      switch (field.type) {
        case 'text':
          schema = Yup.string();
          break;
        case 'checkbox':
          schema = Yup.boolean();
          break;
        default:
          schema = Yup.string(); // fallback
      }
  
      // Apply required
      if (typeof field.validation === 'string' && field.validation.includes('required')) {
        schema = schema.required(`This field is required`);
      }
  
      // Apply min
      const minMatch = typeof field.validation === 'string'
        ? field.validation.match(/min:(\d+)/)
        : null;
      if (minMatch) {
        const min = parseInt(minMatch[1]);
        schema = schema.min(min, `This field must be at least ${min}`);
      }
  
      // Apply max
      const maxMatch = typeof field.validation === 'string'
        ? field.validation.match(/max:(\d+)/)
        : null;
      if (maxMatch) {
        const max = parseInt(maxMatch[1]);
        schema = schema.max(max, `This field must not exceed ${max}`);
      }
  
      // Set label for better error messages
      schema = schema.label(field.label);
  
      shape[field.name] = schema;
    });
  
    return Yup.object().shape(shape);
}

export default function EventForm() {
    const [formFields, setFormFields] = React.useState([]);
    const [initialValues, setInitialValues] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const [showRegistrationButton, setShowRegistrationButton] = React.useState(false); // New state
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token } = useAuth();
    const navigate = useNavigate();
    const [isSubmitting, setIsSubmitting] = React.useState(false);


    React.useEffect(() => {        
        
        const fetchFormSchema = async () => {
            try {
                const response = await axios.get(`${apiUrl}/api/event-form/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        Authorization: `Bearer ${token}`,
                    },
                });                   

                const formSchema = response.data;                

                if (!response.data.fields) {                    
                    setShowRegistrationButton(true); // Show only the registration button
                } else {
                    // Set form fields
                    setFormFields(formSchema.fields);

                    // Set initial values
                    const initialValues = {};
                    formSchema.fields.forEach(field => {
                        initialValues[field.name] = field.type === 'checkbox' ? false : '';
                    });
                    setInitialValues(initialValues);
                }
            } catch (error) {
                console.error("Error fetching form schema:", error);
            } finally {
                setLoading(false);
            }
        };

        fetchFormSchema();
    }, [apiUrl]);

    const validationSchema = formFields.length > 0 ? generateValidationSchema(formFields) : null;

    const onSubmit = async (values, { setSubmitting }) => {
        try {
            const payload = {
                eventId: id,
                formData: { ...values },
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
            <div style={{ width: '100%', marginBottom: '2rem' }}>
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
        );
    }

    return (
        <div style={{ width: '100%', marginBottom: '2rem' }}>
            <Formik
                initialValues={initialValues}
                validationSchema={validationSchema}
                onSubmit={onSubmit}
                enableReinitialize
            >
                {({ values, handleChange, handleBlur, isSubmitting, touched, errors }) => (
                    <Form>
                        {formFields.map((field) => (
                            <div key={field.name} className="mb-4">
                                <label className="block text-gray-700 mb-2" htmlFor={field.name}>
                                    {field.label}
                                </label>
    
                                {field.type === 'checkbox' ? (
                                    <label className="flex items-center">
                                        <input
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
    
                        <button type="submit" disabled={isSubmitting} className="w-full px-5 py-2.5 bg-red-700 rounded-lg shadow-md inline-flex justify-center items-center overflow-hidden text-white text-sm font-semibold">Submit
                        </button>
                    </Form>
                )}
            </Formik>
        </div>
    );
}
