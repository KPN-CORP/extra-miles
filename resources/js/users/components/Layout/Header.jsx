import React from 'react';

const Header = ({ title }) => {
  return (
    <header class="w-96 h-16 p-5 left-0 top-0 absolute bg-stone-50 inline-flex justify-start items-center gap-2">
        <div class="flex-1 flex justify-start items-center gap-1">
            <div class="flex-1 text-center justify-center text-red-700 text-base font-semibold  leading-none">{title}</div>
        </div>
        <div class="w-6 h-6 left-[20px] top-[22px] absolute overflow-hidden">
            <div class="w-5 h-3.5 left-[1.50px] top-[5.25px] absolute bg-red-700"></div>
        </div>
    </header>
  );
};

export default Header;