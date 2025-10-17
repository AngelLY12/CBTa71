import React from 'react'

function ButtonPrimary({ onClick, children, title, showText = false, className }) {
    return (
        <button onClick={onClick} className={`cursor-pointer w-auto h-10 select-none inline-flex gap-1 items-center border-[1px] rounded-md px-2 hover:bg-green-500 hover:text-white hover:font-semibold active:bg-green-500 active:text-white active:font-semibold ${className}`} title={title}>
            {children}
            <p className={`text-sm visible block ${!showText && "max-[330px]:hidden"}`}>{title}</p>
        </button>
    )
}

export default ButtonPrimary
