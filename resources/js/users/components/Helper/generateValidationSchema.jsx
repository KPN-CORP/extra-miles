import * as Yup from 'yup';

import { translate } from './localeHelper';

export function generateValidationSchema(fields) {
  const shape = {};
  const confirmationPairs = [];

  // Tambahkan semua field ke shape (tanpa validasi kondisional dulu)
  fields.forEach((field) => {
    let schema;

    switch (field.type) {
      case 'text':
      case 'textarea':
        schema = Yup.string();
        if (field.required) {
          schema = schema.required(translate('validation.required'));
        }
        if (field.validation?.includes('max')) {
          const max = parseInt(field.validation.split('max:')[1]);
          schema = schema.max(max, translate('validation.maxChars', { max }));
        }
        break;

      case 'radio':
      case 'select':
        schema = Yup.string();
        if (field.required) {
          schema = schema.required(translate('validation.required'));
        }
        break;

      case 'checkbox':
        if (Array.isArray(field.options)) {
          schema = Yup.array();
          if (field.required) {
            schema = schema.min(1, translate('validation.required'));
          }
        } else {
          schema = Yup.boolean();
          if (field.required) {
            schema = schema.oneOf([true], translate('validation.required'));
          }
        }
        break;

      default:
        schema = Yup.string();
        break;
    }

    shape[field.name] = schema.label(field.label);

    // Simpan pasangan confirmation_X dan reason_X
    const confirmationMatch = field.name.match(/^confirmation_(\d+)$/);
    if (confirmationMatch) {
      const index = confirmationMatch[1];
      const reasonField = fields.find((f) => f.name === `confirmation_${index}_reason`);
      if (reasonField && Array.isArray(field.options) && field.options.length >= 2) {
        confirmationPairs.push({
          field: field.name,
          reason: reasonField.name,
          requiredValue: field.options[1],
        });

        // Inisialisasi reason field agar tidak error di step 2
        shape[reasonField.name] = Yup.string().label(reasonField.label);
      }
    }
  });

  confirmationPairs.forEach(({ field, reason, requiredValue }) => {
    shape[reason] = Yup.string()
  .max(200, translate('validation.maxChars', { max: 200 }))
  .test(
    'conditional-required',
    translate('validation.requiredIfSelected', { value: requiredValue }),
    function (value) {
      const fieldValue = this.parent[field];
      if (fieldValue === requiredValue) {
        return value !== undefined && value !== '';
      }
      return true;
    }
  );
  });

  // Validasi nomor WhatsApp
  shape['whatsapp_number'] = Yup.string()
    .required(translate('validation.whatsappRequired'))
    .matches(/^[0-9]{6,15}$/, translate('validation.numberInvalid'));

  return Yup.object().shape(shape);
}