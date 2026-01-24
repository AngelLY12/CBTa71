import React, { useEffect, useRef, useState } from 'react'
import ButtonPrimary from './ButtonPrimary';

function InputSearch({ className, value, setValue, title = "Buscar", options = [], getOptions, valueSearch }) {
    const [openOption, setOpenOption] = useState(false);
    const [openMovilSearch, setOpenMovilSearch] = useState(false);
    const [isMovil, setIsMovil] = useState(false);
    const wrapperRef = useRef(null);
    const inputRef = useRef(null)

    const handleKeyDown = (e) => {
        if (e.key === 'Escape') {
            setOpenOption(false);
            inputRef.current?.blur();
        }
    }

    const handleWrite = (value) => {
        setValue(value)
        if (value == "") {
            setOpenOption(false)
        } else {
            setOpenOption(true)
        }
    }

    const selectOption = (value) => {
        setValue(value)
        closeOption()
    }

    const eraseButton = () => {
        setValue('')
        setOpenOption(false)
    }

    const closeOption = () => {
        setOpenOption(false)
        setOpenMovilSearch(false)
    }

    const showOption = () => {
        if (isMovil) {
            setOpenMovilSearch(true)
        }
        if (value != "") {
            setOpenOption(true)
        }
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
        if (openMovilSearch && isMovil) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    }, [openMovilSearch]);

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
        if (value != "") {
            getOptions()
        }
    }, [value]);

    return (
        <>
            <div className="md:hidden h-auto w-auto visible block">
                <ButtonPrimary title="Buscar" onClick={showOption}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </ButtonPrimary>
            </div>

            <div ref={wrapperRef} className={`md:relative ${className} ${openMovilSearch && isMovil ? "flex flex-col gap-1 active overflow-hidden fixed inset-0 z-50 bg-white" : "relative bg-transparent"}`}>
                <div className='flex w-full md:h-full'>
                    <div className={`w-full md:h-full md:visible md:block h-14 ${openMovilSearch ? 'visible flex py-2 pl-0.5 pr-2' : 'hidden'}`}>
                        <div className='flex items-center h-full mr-1 md:hidden visible'>
                            <button onClick={closeOption} className='w-9 h-9 flex items-center justify-center rounded-full active:bg-gray-400/50' title='Salir de la busqueda'>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                        </div>

                        <div className="w-full h-full relative">
                            <input
                                id={"searh-" + title}
                                type="text"
                                className="bg-white w-full h-full md:rounded-md py-2 px-8 outline-2 outline-gray-400 rounded-xl md:outline-1 md:outline-gray-600 md:focus:outline-indigo-600"
                                value={value}
                                ref={inputRef}
                                onFocus={showOption}
                                onChange={(e) => handleWrite(e.target.value)}
                                placeholder={title}
                            />
                            <div className="flex items-center absolute left-1 inset-y-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>

                            {value !== "" && (
                                <div className="flex items-center absolute right-1 inset-y-0">
                                    <button className="hover:bg-neutral-300 active:bg-neutral-500/40 rounded-full p-0.5" onClick={eraseButton} title="Borrar búsqueda">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {openMovilSearch &&
                    < div className='w-full h-1 bg-green-300/50 rounded md:hidden'></div>
                }

                {openOption && (
                    <div className="z-10 p-2 md:absolute md:inset-x-0 md:mt-1.5 md:max-h-36 md:rounded-md md:shadow-xl md:overflow-y-auto bg-white">
                        {options.map((option) => (
                            <button
                                key={option.id}
                                value={!Array.isArray(valueSearch) ? option[valueSearch] : `${option[valueSearch[0]]} ${option[valueSearch[1]]}`}
                                className="select-none w-full flex items-center p-2 hover:bg-neutral-600/15 active:bg-neutral-600/15 rounded-md cursor-pointer"
                                onClick={(e) => selectOption(e.target.value)}
                            >
                                {!Array.isArray(valueSearch) ? option[valueSearch] : `${option[valueSearch[0]]} ${option[valueSearch[1]]}`}
                            </button>
                        ))}
                    </div>
                )}
            </div >
        </>
    );
}


export default InputSearch
