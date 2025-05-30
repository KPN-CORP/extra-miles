import React, { useEffect, useState } from 'react';
import { Formik, Form, Field, ErrorMessage } from 'formik';
import * as Yup from 'yup';
import { PulseLoader } from 'react-spinners';
import { useApiUrl } from '../Context/ApiContext';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom'; // Import useParams to get id from endpoint
import { showAlert } from '../Helper/alertHelper';
import { useAuth } from '../context/AuthContext';
import { getImageUrl } from '../Helper/imagePath';
import VoteProgressBar from '../Helper/progressBar';
import StarRatingField from '../Helper/starRatingField';

export function generateValidationSchema(fields) {
    const shape = {};
  
    fields.forEach((field) => {
      let schema;
  
      const validation = typeof field.validation === 'string' ? field.validation : '';
      const isRequired = validation.includes('required') || field.required;
  
      const minMatch = validation.match(/min:(\d+)/);
      const maxMatch = validation.match(/max:(\d+)/);
      const min = minMatch ? parseInt(minMatch[1], 10) : null;
      const max = maxMatch ? parseInt(maxMatch[1], 10) : null;
  
      switch (field.type) {
        case 'text':
        case 'textarea':
        case 'select':
        case 'radio':
          schema = Yup.string();
          if (isRequired) schema = schema.required('This field is required');
          if (min !== null) schema = schema.min(min, `Minimum ${min} characters`);
          if (max !== null) schema = schema.max(max, `Maximum ${max} characters`);
          break;
  
        case 'checkbox':
          if (field.options) {
            schema = Yup.array();
            if (isRequired || min !== null) {
              schema = schema.min(min || 1, `Select at least ${min || 1} option(s)`);
            }
          } else {
            schema = Yup.boolean();
            if (isRequired) schema = schema.oneOf([true], 'Must be checked');
          }
          break;
  
        default:
          schema = Yup.string(); // fallback
      }
  
      shape[field.name] = schema.label(field.label || field.name);
    });
  
    return Yup.object().shape(shape);
}

export default function VotingForm({ participated }) {  
    const [formFields, setFormFields] = useState([]);
    const [initialValues, setInitialValues] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token } = useAuth();
    const navigate = useNavigate();
    const [voteResults, setVoteResults] = useState([]);

    const getVotePercentage = (option) => {
      if (!voteResults || voteResults.length === 0) return 0;
    
      const result = voteResults; // Gunakan data survei pertama (atau satu-satunya)
      const totalVotes = result.total || 0;
      const optionVotes = result.votes?.[option] || 0;
    
      return totalVotes > 0 ? Math.round((optionVotes / totalVotes) * 100) : 0;
    };    

    useEffect(() => {
      const fetchEvent = async () => {
          try {
              const res = await axios.get(`${apiUrl}/api/voting-result/${id}`, {
                  headers: {
                    Authorization: `Bearer ${token}`,
                  },
              });                      
              setVoteResults(res.data);
          } catch (err) {
              console.error('Error fetching form schema:', err);
          } finally {
              setLoading(false);
          }
      };
      fetchEvent();
    }, []);
  
    useEffect(() => {
      const fetchFormSchema = async () => {
        try {
            const response = await axios.get(`${apiUrl}/api/survey-form/${id}`, {
              headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${token}`,
              },
            });
    
            const formSchema = response.data;
            const fields = formSchema.fields || [];
  
            setFormFields(fields);
  
            const initial = {};
            fields.forEach(field => {
              if (field.type === 'checkbox' && field.options) {
                initial[field.name] = [];
              } else if (field.type === 'checkbox') {
                initial[field.name] = false;
              } else {
                initial[field.name] = '';
              }
            });
            setInitialValues(initial);
        } catch (error) {
          console.error('Error fetching form schema:', error);
        } finally {
          setLoading(false);
        }
      };
  
      fetchFormSchema();
    }, [apiUrl, id, token]);
  
    const validationSchema = formFields.length > 0 ? generateValidationSchema(formFields) : null;
  
    const onSubmit = async (values, { setSubmitting }) => {
      try {
        const payload = {
          surveyId: id,
          formData: values,
        };
  
        const response = await axios.post(`${apiUrl}/api/survey`, payload, {
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        });
  
        const success = response.status === 201;
        showAlert({
          icon: success ? 'success' : 'error',
          title: success ? 'Success!' : 'Something went wrong',
          text: success ? 'Your response has been recorded. Thanks a bunch for your vote!' : 'Please try again later.',
          timer: 2500,
          showConfirmButton: false,
        }).then(() => navigate(`/survey`, { replace: true }));
      } catch (error) {
        console.error('Submission error:', error);
      } finally {
        setSubmitting(false);
      }
    };
  
    if (loading) {
      return <PulseLoader className='w-full justify-center text-center' margin={2} size={8} color="#FFF" speedMultiplier={0.75} />;
    }    
  
    return (
      <div className="w-full mb-8">
        <Formik
          initialValues={{
            [initialValues]: [], // checkbox field as array
          }}
          validationSchema={validationSchema}
          onSubmit={onSubmit}
          enableReinitialize
        >
          {({ values, handleChange, handleBlur, isSubmitting }) => (
            <Form>
              {formFields.map((field, index) => (
                <div key={index}>
                  {field.type === 'checkbox' && field.options && !participated ? (
                    <div key={field.name} className="w-full px-5 py-4 bg-stone-50 rounded-xl mb-4">
                      <label className="block text-gray-700 mb-2" htmlFor={field.name}>{field.label}</label>
                      <div className="flex flex-col gap-2">
                        {field.options.map((option, idx) => (
                          <label key={idx} className="flex items-center">
                            <Field type="checkbox" name={field.name} value={option} className="mr-2" />
                            {option}
                          </label>
                        ))}
                      </div>
                      <ErrorMessage name={field.name} component="div" className="text-red-700 text-sm mt-1" />
                    </div>
                  ) : field.type === 'textarea' && !participated ? (
                    <div key={field.name} className="w-full px-5 py-4 bg-stone-50 rounded-xl mb-4">
                      <label className="block text-gray-700 mb-2" htmlFor={field.name}>{field.label}</label>
                      <Field 
                          as="textarea" 
                          id={field.name} 
                          name={field.name} 
                          type={field.type}
                          value={values[field.name]}
                          onChange={handleChange}
                          onBlur={handleBlur} 
                          placeholder="isi disini..."
                          className="w-full border rounded p-2" 
                      />
                      <ErrorMessage name={field.name} component="div" className="text-red-700 text-sm mt-1" />
                      </div>
                  ) : field.type === 'radio' ? (
                    <>
                    <label className="block text-white font-medium mb-2" htmlFor={field.name}>{voteResults.total === 1 ? 'Voter' : 'Voters'}: {voteResults.total}
                    </label>
                    <div className="w-full p-3 bg-red-700 rounded-xl border border-white inline-flex flex-col justify-center items-center gap-4 mb-4">
                      <div className="self-stretch flex flex-col justify-start items-start gap-5">
                        {field.options.map((option, index) => (
                          <div
                            key={index}
                            className="w-full p-4 bg-white rounded-xl ring-1 ring-inset ring-red-700 shadow-lg flex items-center gap-3 overflow-hidden"
                          >
                            <img
                              src={getImageUrl(apiUrl, 'surveys', 'vote', 'candidate-img.jpg')}
                              alt="Profile"
                              className="w-12 h-12 rounded-full"
                            />
                            <div className="flex-1 flex flex-col justify-center gap-2">
                              <div className="text-stone-600 text-sm font-semibold">
                                {option}
                              </div>
                              {participated && <VoteProgressBar percentage={getVotePercentage(option)} />}
                            </div>
                            {!participated && 
                              <label className="inline-flex items-center cursor-pointer">
                                <Field
                                  type="radio"
                                  name={field.name}
                                  value={option}
                                  className="hidden peer"
                                />
                                <div className="w-7 h-7 rounded-full border border-red-700 flex items-center justify-center peer-checked:bg-red-700 transition">
                                  <i className="ri-check-line text-white text-lg font-semibold"></i>
                                </div>
                              </label>
                            }
                          </div>
                        ))}
                      </div>
                    </div>
                    <ErrorMessage
                      name={field.name}
                      render={() => (
                        <div className="w-full bg-red-100 rounded-xl shadow-lg flex items-center gap-2 mb-4 p-2 px-3 text-red-700 font-medium text-sm">
                          <i className="ri-error-warning-line text-lg"></i>
                          {`Kamu belum pilih ${field.label}`}
                        </div>
                      )}
                    />
                    </>
                  ) : field.type === 'rating' && !participated ? (
                      <StarRatingField name={field.name} label={field.label} />
                  ) : null}
                </div>
              ))}
              {!participated && 
                <button type="submit" disabled={isSubmitting} className="w-full px-5 py-2.5 rounded-lg shadow-md text-sm font-semibold text-red-700" style={{ backgroundColor: '#DEBD69' }}>
                  Submit
                </button>
              }
            </Form>
          )}
        </Formik>
      </div>
    );
}
