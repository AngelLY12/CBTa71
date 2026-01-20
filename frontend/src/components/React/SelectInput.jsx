import React, { useEffect, useRef, useState } from 'react'
import ButtonPrimary from './ButtonPrimary';

function SelectInput({ className, options = [], setOption = null, setValue, titleEnter = true, title = "Buscar por", titleMovil, children, topTitle = false, filtre = true, upperCase = false, widthText }) {
    const [opentOption, setOpenOptions] = useState(false)
    const [openMovilSelect, setOpenMovilSelect] = useState(false);
    const [indexSelect, setIndexSelect] = useState(0)
    const [valueSelect, setValueSelect] = useState(options[0])
    const [isMovil, setIsMovil] = useState(false);
    const wrapperRef = useRef(null);
    const buttonRef = useRef(null)

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
        const value = options[i]
        setValue(value)
        setValueSelect(value)
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

    const focusButton = () => {
        buttonRef.current.focus();
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
        setValue(options[0])
    }, [])

    useEffect(() => {
        if (!filtre) return;
        if (indexSelect != -1) {
            setOption()
        }
    }, [valueSelect])

    return (
        <>
            <div className="md:hidden h-auto visible block">
                {!filtre
                    &&
                    <p onClick={focusButton} className='font-semibold text-sm'>{title}</p>
                }
                <ButtonPrimary className={`${topTitle && "w-full justify-center"}`} title={!filtre ? "" : title} onClick={showOptionMovil}>
                    {!filtre
                        ?
                        <p className={`max-w-32 ${upperCase && "uppercase"}`}>{`${valueSelect}`}</p>
                        :
                        children != null ? children :
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" className="size-6 bi bi-filter" viewBox="0 0 16 16">
                                <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5" />
                            </svg>
                    }
                </ButtonPrimary>
            </div>

            {
                openMovilSelect &&
                <div onClick={closeOption} className='z-50 bg-neutral-700/40 fixed inset-0'>
                </div>
            }

            <div ref={wrapperRef} className={`md:relative ${className} ${openMovilSelect && isMovil ? "flex flex-col pt-2 active overflow-hidden fixed bottom-0 inset-x-0 h-1/2 bg-white z-50" : "relative bg-transparent hidden md:block md:visible"}`}>
                <div className={`flex w-full md:h-full items-center gap-3 ${topTitle && "flex-col items-stretch"}`}>
                    {!titleEnter &&
                        <p onClick={focusButton} className='lg:text-lg font-semibold hidden md:block md:visible'>{title}</p>
                    }
                    <div className={`flex-grow w-full h-full md:visible md:block hidden`}>
                        <button ref={buttonRef} className="flex h-full w-full cursor-pointer hover:bg-green-100/60 outline-0 group" onClick={showOption}>
                            <div className='flex h-full w-11/12 items-center px-2 border-[1px] rounded-s group-focus:border-[0.1rem] overflow-hidden'>
                                <p className={`text-start w-full whitespace-nowrap overflow-hidden overflow-ellipsis text-sm xl:text-lg ${widthText}`}>{titleEnter ? <>{title}: <span className={`font-semibold ${upperCase && "uppercase"}`}>{valueSelect}</span></> : valueSelect}</p>
                            </div>
                            <div className='flex h-full w-12 justify-center items-center -ml-[1px] border-[1px] rounded-e group-focus:border-[0.1rem]'>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </button>
                    </div>

                    <div className={`w-full flex items-center ${openMovilSelect ? "visible flex gap-2" : "hidden"}`}>
                        <button onClick={closeOption} className='w-6 h-full flex items-center justify-center rounded-full active:bg-gray-400/50' title='Salir de los filtros'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <p className='text-sm'>{titleMovil != null ? titleMovil : "Filtrar por:"}</p>
                    </div>
                </div >
                {
                    (opentOption || openMovilSelect) &&
                    <div className='md:right-0 md:absolute md:max-h-40 p-2 w-full bg-white md:shadow-lg overflow-y-auto z-10'>
                        {options.map((option, i) => (
                            <button value={option} onClick={() => optionClick(i)} className={`flex p-2 rounded-lg w-full items-center gap-1 active:bg-neutral-600/15 hover:bg-neutral-600/15 ${indexSelect == i && "bg-neutral-600/15 font-bold"}`} key={i}>
                                <div className={`visible block md:hidden w-5 h-5 rounded-full border-2 ${indexSelect == i ? "bg-green-500 border-green-500" : "border-neutral-950"}`}>
                                </div>
                                <p className={`text-start md:w-full ${indexSelect == i && "text-green-600 md:text-black"} ${upperCase && "uppercase"}`}>{option}</p>
                            </button>
                        ))}
                    </div>
                }
            </div>
        </>
    )
}

export default SelectInput
