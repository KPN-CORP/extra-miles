import { useFormikContext } from 'formik';

export default function StarRatingField({ name, label }) {
  const { values, setFieldValue } = useFormikContext();

  const rating = Number(values[name] || 0);

  return (
    <>
    <div key={name} className="w-full justify-center text-center px-5 py-4 bg-stone-50 rounded-xl mb-4">
      <label className="block text-gray-700 mb-2">{label}</label>
      <div className="flex gap-2 justify-center text-center">
        {[1, 2, 3, 4, 5].map((val) => (
          <button
            type="button"
            key={val}
            onClick={() => setFieldValue(name, val)}
            className="text-3xl focus:outline-none transition transform hover:scale-125"
          >
            <i
              className={
                val <= rating ? 'ri-star-fill text-yellow-500' : 'ri-star-line text-gray-400'
              }
            ></i>
          </button>
        ))}
      </div>
    </div>
    </>
  );
}
