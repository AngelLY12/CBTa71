import React from 'react'

const Button = ({ className, children, onClick = () => { }, ...props }) => {
    return (
        <button {...props} onClick={onClick} className={`select-none flex items-center justify-center gap-1 p-2 cursor-pointer ${className}`}>
            {children}
        </button>
    )
}

export default Button
