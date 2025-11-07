import React from 'react'

function InputTitleUp({ title, value, setValue, className }) {

    return (
        <label className={`w-full inline-flex flex-col ${className}`}>
            <span className='font-medium text-md md:text-lg'>{title}</span>
            <input value={value} onChange={(e)=>setValue(e.target.value)} type="text" className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg outline-0 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required />
        </label>
    )
}

export default InputTitleUp
