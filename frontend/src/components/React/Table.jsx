import React from 'react'
import CardInfoMovil from './CardInfoMovil'
import Modal from './Modal'

function Table({ Heads = [], textModal = "¿Estas seguro de eliminar este elemento?", values = [], dates = [], datesCard = ["correo", "status"], showModalDelete, indexDelete, deleteAprob, deleteValue, clickEdit, closeModalDelete, showDelete, svgDeleteButton = [], svgAcceptButton = [], id = "id" }) {
    return (
        <div className='w-full mt-6 overflow-auto'>
            {
                values.length > 0
                    ?
                    <>
                        <table className='hidden md:visible md:table text-sm md:text-md w-full border-separate border-spacing-1.5 border rounded-md border-gray-600 table-|'>
                            <thead>
                                <tr>
                                    {Heads.map((head, i) => (
                                        <th key={i} className={head.className ? head.className : ""}>{head}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {values.map((option) => (
                                    <tr className={`text-center transition-opacity duration-300 ease-out ${(option[id] == indexDelete && deleteAprob) && 'opacity-0'}`} key={option[id]}>
                                        {dates.length > 0
                                            ?
                                            dates.map((date, i) => (
                                                <td key={i}>{option[date]}</td>
                                            ))
                                            :
                                            Object.values(option).map((value, i) => (
                                                <td key={i}>{value}</td>
                                            ))
                                        }
                                        <td className='flex items-end justify-center'>
                                            <button onClick={() => clickEdit(option)} className='mr-2 cursor-pointer group hover:text-green-400' title='Editar elemento'>
                                                {svgAcceptButton.length > 0 ?
                                                    svgAcceptButton.map((svg, i) => (
                                                        <div className={`${i == 0 ? "size-6 group-hover:hidden" : "size-6 hidden group-hover:visible group-hover:block"}`}>
                                                            {svg}
                                                        </div>
                                                    ))
                                                    :
                                                    <>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 group-hover:hidden">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6 hidden group-hover:visible group-hover:block">
                                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                                            <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                                        </svg>
                                                    </>}
                                            </button>
                                            <button onClick={() => showModalDelete(option[id])} title='Eliminar elemento' className='ml-2 cursor-pointer group hover:text-red-500'>
                                                {svgDeleteButton.length > 0 ?
                                                    svgDeleteButton.map((svg, i) => (
                                                        <div className={`${i == 0 ? "size-6 group-hover:hidden" : "size-6 hidden group-hover:visible group-hover:block"}`}>
                                                            {svg}
                                                        </div>
                                                    ))
                                                    :
                                                    <>
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 group-hover:hidden">
                                                            <path strokeLinecap="round" strokeLinejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6 hidden group-hover:visible group-hover:block">
                                                            <path fillRule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clipRule="evenodd" />
                                                        </svg>
                                                    </>}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <div className='flex flex-col visible md:hidden'>
                            {
                                values.map((option) => (
                                    <CardInfoMovil info={[datesCard[0], datesCard[1]]} key={option[id]} item={option} index={option[id]} onClickDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} />
                                ))
                            }
                        </div>
                    </>
                    :
                    <div>
                        <p className='font-bold text-center text-md md:text-xl'>No se encuentran datos aun</p>
                    </div>
            }
            <Modal onClickAccept={deleteValue} show={showDelete} onDisable={closeModalDelete} text={textModal} />
        </div>
    )
}

export default Table
