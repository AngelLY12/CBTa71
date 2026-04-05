import React, { useEffect, useState } from 'react'

function CardInfoMovil({ item, index, indexDelete, deleteAprob, onClickDelete, onClickEdit, info = [], cardClick = false, onClickCard, deleteItem = true, textFuction = [] }) {
    const [showOption, setShowOption] = useState(false);
    const nameComplete = `${item?.nombre ?? item?.name ?? ""} ${item?.apellidos ?? item?.last_name ?? ""}`

    const openOption = (e) => {
        e.stopPropagation();
        setShowOption(!showOption);
    }

    const editClick = () => {
        setShowOption(false);
        onClickEdit(item);
    }

    const deleteClick = () => {
        setShowOption(false);
        onClickDelete(index)
    }

    const clickCard = () => {
        if (!cardClick) return;
        onClickCard(item);
    }

    // Añadir o remover la clase 'no-scroll' del body cuando openMovilSearch cambie
    useEffect(() => {
        if (showOption) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    }, [showOption]);

    return (
        <div className={`w-full bg-white border-b-[1px] border-green-200 text-sm relative transition-opacity duration-300 ease-out ${(index === indexDelete && deleteAprob) && 'opacity-0'}`}>
            <div onClick={clickCard} className={`flex items-center justify-between px-0.5 pt-1 ${cardClick && "hover:bg-gray-500/20 active:bg-gray-500/20"}`}>
                <div className='flex flex-col w-5/12'>
                    <p className='max-w-full overflow-hidden font-semibold'>{nameComplete.length >= 10 ? `${nameComplete.slice(0, 14)}` + "..." : `${item.name ?? !info[0].includes(".") ? item[info[0]] : info[0].split(".").reduce((acc, key) => acc?.[key], item)}`}</p>
                    <p>{info.length > 0 ? !info[1].includes(".") ? item[info[1]] : info[1].split(".").reduce((acc, key) => acc?.[key], item) : item.roles}</p>
                </div>
                {
                    (info[2])
                        ?
                        <div className={`w-4/12 font-semibold`}>
                            {
                                !info[2].includes(".") ? item[info[2]] : info[2].split(".").reduce((acc, key) => acc?.[key], item)
                            }
                        </div>
                        :
                        <div className={`w-4/12 font-semibold ${(item.status == "activo" || "active" || "aprobado") ? "text-green-500" : "text-red-500"}`}>
                            {
                                item.status
                            }
                        </div>
                }
                <div className='w-auto relative'>
                    <button onClick={openOption} className='disabled:pointer-events-none flex justify-center items-center p-1 rounded-full hover:bg-gray-500/20 active:bg-gray-500/40'>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                        </svg>
                    </button>
                </div>
            </div>
            {
                showOption &&
                <div className='flex items-end z-50 fixed inset-0'>
                    <div className='bg-white w-full h-auto pb-12 px-2 pt-2 z-50'>
                        <button onClick={editClick} className='flex w-full h-9 justify-center items-center active:bg-gray-300/50'>
                            {textFuction[0] ?? "Editar"}
                        </button>
                        {deleteItem &&
                            <button onClick={deleteClick} className='flex w-full h-9 justify-center items-center active:bg-gray-300/50'>
                                {textFuction[1] ?? "Eliminar"}
                            </button>
                        }
                    </div>
                    <div onClick={openOption} className='z-40 absolute bg-black/20 inset-0'>
                    </div>
                </div>
            }
        </div>
    )
}

export default CardInfoMovil
