import React from 'react';
import { Formik, Form, Field, ErrorMessage } from 'formik';
import * as Yup from 'yup';
import { PulseLoader } from 'react-spinners';
import { useApiUrl } from '../context/ApiContext';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom'; // Import useParams to get id from endpoint
import { showAlert } from '../Helper/alertHelper';
import { useAuth } from '../context/AuthContext';

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

export default function SurveyForm() {
    const [formFields, setFormFields] = React.useState([]);
    const [initialValues, setInitialValues] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const [isSubmitting, setIsSubmitting] = React.useState(false);
    const apiUrl = useApiUrl();
    const { id } = useParams();
    const { token } = useAuth();
    const navigate = useNavigate();
  
    React.useEffect(() => {
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
          text: success ? 'Your response has been recorded. Thanks a bunch for joining the survey!' : 'Please try again later.',
          timer: 2500,
          showConfirmButton: false,
        }).then(() => navigate(`/`, { replace: true }));
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
          initialValues={initialValues}
          validationSchema={validationSchema}
          onSubmit={onSubmit}
          enableReinitialize
        >
          {({ values, handleChange, handleBlur, isSubmitting }) => (
            <Form>
              {formFields.map((field) => (
                <div key={field.name} className="w-full px-5 py-4 bg-stone-50 rounded-xl mb-4">
                  <label className="block text-gray-700 mb-2" htmlFor={field.name}>{field.label}</label>
                  {field.type === 'checkbox' && field.options ? (
                    <div className="flex flex-col gap-2">
                      {field.options.map((option, idx) => (
                        <label key={idx} className="flex items-center">
                          <Field type="checkbox" name={field.name} value={option} className="mr-2" />
                          {option}
                        </label>
                      ))}
                    </div>
                  ) : field.type === 'checkbox' ? (
                    <label className="flex items-center">
                      <Field type="checkbox" name={field.name} className="mr-2" /> I agree
                    </label>
                  ) : field.type === 'select' ? (
                    <Field as="select" name={field.name} className="w-full border rounded p-2">
                      <option value="">Select an option</option>
                      {field.options.map((option, index) => (
                        <option key={index} value={option}>{option}</option>
                      ))}
                    </Field>
                  ) : field.type === 'textarea' ? (
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
                  ) : field.type === 'radio' ? (
                    <div role="group" aria-labelledby={`${field.name}-label`}>
                      {field.options.map((option, idx) => (
                        <label key={idx} className="flex items-center mb-1">
                          <Field
                            type="radio"
                            name={field.name}
                            value={option}
                            className="mr-2"
                          />
                          {option}
                        </label>
                      ))}
                    </div>
                  ) : (
                    <Field type={field.type} name={field.name} className="w-full border rounded p-2" />
                  )}
  
                  <ErrorMessage name={field.name} component="div" className="text-red-700 text-sm mt-1" />
                </div>
              ))}
  
              <button type="submit" disabled={isSubmitting} className="w-full px-5 py-2.5 rounded-lg shadow-md text-sm font-semibold text-red-700" style={{ backgroundColor: '#DEBD69' }}>
                Submit
              </button>
            </Form>
          )}
        </Formik>
      </div>
    );
}
