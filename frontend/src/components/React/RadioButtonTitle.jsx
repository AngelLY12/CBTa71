import React from 'react'

function RadioButtonTitle({ title, options = [], sizeW = false}) {
    return (
        <div className='mt-2 md:mt-1'>
            <label className='text-md md:text-lg font-medium block'>{title}</label>
            <div className={`flex flex-wrap justify-between md:justify-start md:gap-x-4`}>
                {options.length > 0 && options.map((option,i) => (
                    <div key={i} className={!sizeW ? "w-5/12 md:w-[7.4rem]" : sizeW}>
                        <label className='inline-flex gap-2 items-center'>
                            <input type="radio" className='size-4' name={"option-" + title} value={"Editar"} />
                            <span>{option}</span>
                        </label>
                    </div>
                ))}
            </div>
        </div>
    )
}

export default RadioButtonTitle
