import React from 'react'

const Input = ({ className, value, setValue, ...props }) => {
    return (
        <input {...props} value={value} onChange={(e) => setValue(e.target.value)} className={`bg-gray-50 border border-transparent text-gray-900 text-sm outline-0 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ${className}`} required />
    )
}

export default Input
