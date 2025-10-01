import React, { useEffect, useState } from 'react'

function InputSearch({ value, setValue, title, options = [] }) {
    const [openOption, setOpenOption] = useState(false);

    const handleWrite = (value) => {
        setValue(value)
        if (value == "") {
            setOpenOption(false)
        } else {
            setOpenOption(true)
        }
    }

    const eraseButton = () => {
        setValue("")
    }

    const closeOption = () => {
        setOpenOption(false)
    }

    return (
        <div className="w-96 relative h-10">
            <input
                type="text"
                className="bg-white w-full h-full rounded-md py-2 pl-8 pr-2 outline-1 outline-gray-600 focus:outline-indigo-600"
                value={value}
                onChange={(e) => handleWrite(e.target.value)}
                placeholder={title}
            />
            <div className="flex items-center absolute left-1 inset-y-0">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    strokeWidth="1.5"
                    stroke="currentColor"
                    className="size-6"
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
                    ></path>
                </svg>
            </div>

            {
                value != "" &&
                <div className="flex items-center absolute right-1 inset-y-0">
                    <button onClick={eraseButton}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            }
            {
                openOption &&
                <div
                    onClick={closeOption}
                    className={`p-2 absolute inset-x-0 mt-1.5 h-36 rounded-md shadow-xl overflow-y-auto bg-white ${value == "" ? "hidden" : "block"} `}
                >
                    {options.map((option, i) => {
                        return (
                            <button
                                key={i}
                                className="w-full flex p-2 hover:bg-neutral-600/15 rounded-md cursor-pointer"
                            >
                                {option}
                            </button>
                        )
                    })}
                </div>
            }
        </div>
    )
}

export default InputSearch
