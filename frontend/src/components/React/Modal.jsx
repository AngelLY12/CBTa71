import React, { useEffect } from 'react'
import ButtonPrimary from './ButtonPrimary'
import "../../styles/scrollHidden.css"

function Modal({ show, onDisable, text, overlap = false, onClickAccept, children, aceptModal = true, className, fullScreen }) {

    const handleKeyDown = (e) => {
        if (e.key === 'Escape') {
            onDisable()
        }
    }

    useEffect(() => {
        if (show) {
            document.body.classList.add("overflow-y-hidden");
        } else {
            if (!overlap) {
                document.body.classList.remove("overflow-y-hidden");
            }
        }
    }, [show]);

    useEffect(() => {
        document.addEventListener("keydown", handleKeyDown)
        return () => document.removeEventListener("keydown", handleKeyDown);
    }, [onDisable]);

    return (
        <>
            {
                show &&
                <div id="popup-modal" tabIndex="-1" className={`overflow-hidden fixed inset-0 z-50 justify-center items-center w-full ${show ? "visible flex" : "hidden"}`}>
                    <div className={`lg:flex lg:items-center relative z-10 lg:relative lg:w-full lg:max-w-max lg:max-h-full lg:p-4 ${fullScreen ? "relative w-full lg:h-auto h-full" : "w-full max-w-max max-h-full p-4"}`}>
                        <div className={`w-full pb-4 relative bg-white rounded-lg shadow-sm lg:h-auto lg:pt-4 ${fullScreen && "h-full rounded-none md:rounded-lg"}`}>
                            <button onClick={onDisable} type="button" className={`z-50 inline-flex justify-center items-center absolute ${fullScreen ? "top-0 end-0" : "-top-4 -end-4"} lg:-top-4 lg:-end-4 font-bold rounded-lg bg-gray-200 ring-1 ring-gray-200 w-8 h-8 ms-auto hover:bg-gray-400 hover:text-white hover:ring-gray-400 hover:ring-3 active:bg-gray-400 active:text-white active:ring-gray-400 active:ring-3`}>
                                <svg className="size-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span className="sr-only">Cerrar modal</span>
                            </button>
                            <div className={`flex justify-center overflow-auto h-full lg:max-h-[32rem] pb-2 ` + className}>
                                {
                                    aceptModal
                                        ?
                                        <div className="p-4 lg:p-5 text-center">
                                            <svg className="mx-auto mb-4 w-12 h-12 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <h3 className="mb-5 text-lg font-normal">{text}</h3>
                                            <ButtonPrimary showText={true} onClick={onDisable} title={"Cancelar"} />
                                            <ButtonPrimary className={"hover:bg-red-600 active:bg-red-600 ml-2"} onClick={onClickAccept} showText={true} title={"Aceptar"} />
                                        </div>
                                        :
                                        children
                                }
                            </div>
                        </div>
                    </div>
                    <div onClick={onDisable} className='bg-black/50 absolute inset-0'>
                    </div>
                </div>
            }
        </>
    )
}

export default Modal
