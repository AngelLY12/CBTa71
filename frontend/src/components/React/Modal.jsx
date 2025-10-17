import React, { useEffect } from 'react'
import ButtonPrimary from './ButtonPrimary'
import SecondaryButton from './SecondaryButton'
import "../../styles/scrollHidden.css"

function Modal({ show, onDisable, text, onClickAccept }) {

    const handleKeyDown = (e) => {
        if (e.key === 'Escape') {
            onDisable()
        }
    }

    // useEffect(() => {
    //     if (show) {
    //         document.body.classList.add("overflow-y-hidden");
    //     } else {
    //         document.body.classList.remove("overflow-y-hidden");
    //     }
    // }, [show]);

    useEffect(() => {
        if (show) {
            document.body.classList.add( "overflow-y-hidden");
        } else {
            document.body.classList.remove("overflow-y-hidden");
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
                <div id="popup-modal" tabIndex="-1" className={`overflow-y-auto overflow-hidden fixed inset-0 z-50 justify-center items-center w-full ${show ? "visible flex" : "hidden"}`}>
                    <div className="relative p-4 w-full max-w-md max-h-full z-10">
                        <div className="relative bg-white rounded-lg shadow-sm">
                            <button onClick={onDisable} type="button" className="absolute top-3 end-2.5 bg-transparent hover:bg-gray-200 hover:text-gray-700 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg className="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span className="sr-only">Cerrar modal</span>
                            </button>
                            <div className="p-4 md:p-5 text-center">
                                <svg className="mx-auto mb-4 w-12 h-12 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 className="mb-5 text-lg font-normal">{text}</h3>
                                <ButtonPrimary showText={true} onClick={onDisable} title={"Cancelar"} />
                                <ButtonPrimary className={"ml-2"} onClick={onClickAccept} showText={true} title={"Aceptar"} />
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
