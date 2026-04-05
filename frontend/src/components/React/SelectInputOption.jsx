import React, { useEffect, useRef, useState } from 'react'
import ButtonPrimary from './ButtonPrimary';

const SelectInputOption = ({ className = "", nameValue = "", options = [], valueOption = "", value = null, setValue, title = "", titleSelector = "" }) => {
    const [opentOption, setOpenOptions] = useState(false)
    const [openMovilSelect, setOpenMovilSelect] = useState(false);
    const [indexSelect, setIndexSelect] = useState(-1)
    const [valueSelect, setValueSelect] = useState("")
    const [isMovil, setIsMovil] = useState(false);
    const wrapperRef = useRef(null);
    const buttonRef = useRef(null);
    const buttonRef2 = useRef(null);

    const handleKeyDown = (e) => {
        if (e.key === 'Escape') {
            setOpenOptions(false);
            buttonRef.current?.blur();
        }
    }

    const showOption = () => {
        setOpenOptions(!opentOption)
    }

    const showOptionMovil = () => {
        setOpenMovilSelect(true)
    }

    const closeOption = () => {
        setOpenOptions(false)
        setOpenMovilSelect(false)
    }

    const optionClick = (i) => {
        const valueSelect = options[i]
        nameValue ? setValue(nameValue, valueSelect.toLowerCase()) : setValue(valueSelect);
        setValueSelect(valueOption ? valueSelect[valueOption] : valueSelect)
        setIndexSelect(i)
        closeOption()
    }

    function handleClickOutside(e) {
        if (wrapperRef.current && !wrapperRef.current.contains(e.target)) {
            closeOption();
        }
    }

    const removeEventListener = () => {
        document.removeEventListener("mousedown", handleClickOutside);
        document.removeEventListener("keydown", handleKeyDown);
    }

    // Añadir o remover la clase 'no-scroll' del body cuando openMovilSearch cambie
    useEffect(() => {
        if (openMovilSelect && isMovil) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    }, [openMovilSelect]);

    useEffect(() => {
        const manejarResize = () => {
            setIsMovil(window.innerWidth <= 767); // 425px es el ancho común para móviles
        };

        manejarResize(); // Verifica el tamaño inicial
        window.addEventListener('resize', manejarResize);

        return () => {
            window.removeEventListener('resize', manejarResize);
        };
    }, []);

    useEffect(() => {
        document.addEventListener("mousedown", handleClickOutside);
        document.addEventListener("keydown", handleKeyDown)
        return () => removeEventListener();
    }, [closeOption]);

    useEffect(() => {
        function handleClickOutside(e) {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target)) {
                closeOption();
            }
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, [closeOption]);

    useEffect(() => {
        if (value) {
            // Normaliza el valor a minúsculas
            let lowerValue;
            let index;
            let valueArray;

            if (typeof value == "string") {
                lowerValue = value.toLowerCase();
            } else {
                lowerValue = value;
            }

            if (valueOption) {
                // Busca el índice sin importar mayúsculas/minúsculas
                if (typeof value == "string") {
                    index = options.findIndex(opt => opt[valueOption].toLowerCase() === lowerValue);
                } else {
                    index = options.findIndex(opt => opt[valueOption] === lowerValue);
                }
                valueArray = options[index];
            } else {
                // Busca el índice sin importar mayúsculas/minúsculas
                if (typeof value == "string") {
                    index = options.findIndex(opt => opt.toLowerCase() === lowerValue);
                } else {
                    index = options.findIndex(opt => opt === lowerValue);
                }
                valueArray = options[index];
            }
            if (valueArray) {
                valueOption ? setValueSelect(valueArray[valueOption]) : setValueSelect(valueArray);
                setIndexSelect(index)
            }
        }
    }, []);

    useEffect(() => {
        if (!value) {
            setIndexSelect(-1);
            setValueSelect("");
        }
    }, [value])

    const focusSelect = () => {
        buttonRef.current.focus()
        buttonRef2.current.focus()
    }

    return (
        <div className={`w-full ${className}`}>
            <p onClick={focusSelect} className='font-medium text-md md:text-lg mb-0.5'>{title}</p>

            <div className="group md:hidden h-auto w-auto visible block">
                <ButtonPrimary ref={buttonRef2} type="button" showText={true} className={"w-full focus:border-blue-600"} title={!valueSelect ? titleSelector : valueSelect} onClick={showOptionMovil} />
            </div>

            {
                openMovilSelect &&
                <div onClick={closeOption} className='z-50 bg-neutral-700/40 fixed inset-0'>
                </div>
            }

            <div ref={wrapperRef} className={`md:relative ${className} ${openMovilSelect && isMovil ? "flex flex-col pt-2 active overflow-hidden fixed bottom-0 inset-x-0 h-1/2 z-50 bg-white" : "md:h-12 relative bg-transparent"}`}>
                <div className='flex w-full md:h-full'>
                    <div className={`w-full md:h-full md:visible md:block hidden`}>
                        <button type='button' ref={buttonRef} className="flex w-full h-full outline-0 group" onClick={showOption}>
                            <div className='flex w-full items-center px-2 border-[1px] rounded-s group-focus:border-blue-600  group-focus:outline-2 group-focus:outline-indigo-600'>
                                <p className='line-clamp-2'>{!valueSelect ? titleSelector : ""} <span className='font-semibold'>{valueSelect}</span></p>
                            </div>
                            <div className='flex w-12 h-full justify-center items-center border-[1px] rounded-e group-focus:border-blue-600  group-focus:outline-2 group-focus:outline-indigo-600'>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </button>
                    </div>

                    <div className={`w-full ${openMovilSelect ? "visible flex gap-2" : "hidden"}`}>
                        <button type='button' onClick={closeOption} className='w-6 h-full flex items-center justify-center rounded-full active:bg-gray-400/50' title='Salir de los filtros'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <p>{titleSelector}: </p>
                    </div>
                </div>

                {
                    (opentOption || openMovilSelect) &&
                    <div className='z-10 md:absolute p-2 md:inset-x-0 md:max-h-48 w-full md:w-auto bg-white md:shadow-lg overflow-y-auto'>
                        {options.map((option, i) => (
                            <button type='button' value={option} onClick={() => optionClick(i)} className={`flex justify-start p-2 rounded-lg w-full items-center gap-1 active:bg-neutral-600/15 hover:bg-neutral-600/15 ${indexSelect == i && "bg-neutral-600/15 font-bold"}`} key={i}>
                                <div className={`md:hidden w-5 h-5 rounded-full border-2 ${indexSelect == i ? "bg-green-500 border-green-500" : "border-neutral-950"}`}>
                                </div>
                                <p className={`w-full text-start ${indexSelect == i && "text-green-600 md:text-black"}`}>{valueOption ? option[valueOption] : option}</p>
                            </button>
                        ))}
                    </div>
                }
            </div>
        </div>
    )
}

export default SelectInputOption
