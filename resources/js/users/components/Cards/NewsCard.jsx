import React from "react";

export default function NewsCard({ image, date, title, tags = [] }) {
  return (
    <div className="flex bg-white rounded-xl shadow-sm overflow-hidden cursor-pointer hover:shadow-md transition">
        <div className="min-w-16 basis-1/4">
        <img
            src={image}
            alt={title}
            className="w-full h-full object-cover"
        />
        </div>
        <div className="p-2 flex flex-col justify-between flex-1 gap-1">
            <div>
                <div className="text-xs text-gray-500">{date}</div>
                <div className="text-sm font-semibold text-gray-800 line-clamp-1">
                    {title}
                </div>
            </div>
            <div className="flex gap-1 mt-1 overflow-x-auto whitespace-nowrap min-h-[10px]">
                {tags.map((tag, idx) => (
                    <span
                    key={idx}
                    className="text-[10px] bg-gray-200 text-gray-700 px-1 py-0 rounded-md inline-block"
                    >
                    {tag}
                    </span>
                ))}
            </div>
        </div>
    </div>
  );
}
