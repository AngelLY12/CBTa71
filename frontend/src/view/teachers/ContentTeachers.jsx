import React, { useState } from 'react'
import InputSearch from '../../components/React/InputSearch';
import Table from '../../components/React/Table';

const ContentTeachers = () => {
    const [searchTeacher, setSearchTeacher] = useState("")
    const [filtreSelect, setFiltreSelect] = useState("")
    const [valuesPersonal, setValuesPersonal] = useState([{ id: 0, nombre: "Jhoana Lizbeth", apellidos: "Yañez Villanueva", correo: "jhoana@gmail.com", telefono: "735231201" }, { id: 1, nombre: "a", apellidos: "a", correo: "a@gmail.com", telefono: "735231201" }, { id: 2, nombre: "b", apellidos: "b", correo: "cb@gmail.com", telefono: "735231201" }])
    const [indexDelete, setIndexDelete] = useState(-1);
    const [deleteAprob, setDeleteAprob] = useState(false)
    const [showDelete, setShowDelete] = useState(false)
    const headNames = ["ID", "Nombre", "Apellidos", "Correo", "Teléfono", "Editar / Eliminar"]
    const dates = ["id", "nombre", "apellidos", "correo", "telefono",]
    const optionsSelect = ["Nombre", "ID", "Administrador"]


    const closeModalDelete = () => {
        setShowDelete(false)
    }

    const showModalDelete = (i) => {
        setIndexDelete(i)
        setShowDelete(true)
    }

    const clickAdd = () => {
        window.location.href = '/teachersAdd';
    }

    const getPersonal = async () => {
        // try {
        //     const response = await fetch(`/api/personal?search=${searchTeacher}`);
        //     if (!response.ok) {
        //         throw new Error(`HTTP error! status: ${response.status}`);
        //     }
        //     const data = await response.json();
        //     setPersonalResponse(data);
        // } catch (error) {
        //     console.error("Error fetching personal data:", error);
        // }
    }

    const getPersonalCategory = async () => {
        // try {
        //     const response = await fetch(`/api/personal?category=${filtreSelect == "" ? optionsSelect[0] : filtreSelect}`);
        //     if (!response.ok) {
        //         throw new Error(`HTTP error! status: ${response.status}`);
        //     }
        //     const data = await response.json();
        //     setPersonalResponse(data);
        // } catch (error) {
        //    console.error("Error fetching personal data:", error);
        // }
    }

    const deletePersonal = async () => {
        setDeleteAprob(true)
        closeModalDelete()
        setTimeout(() => {
            setValuesPersonal(prev => prev.filter(item => item.id !== indexDelete));
            setIndexDelete(-1)
            setDeleteAprob(false);
            // try {
            //     const response = await fetch(`/api/personal/${indexDelete}`, {
            //         method: 'DELETE',
            //     });
            //     if (!response.ok) {
            //         throw new Error(`HTTP error! status: ${response.status}`);
            //     }
            //     const data = await response.json();
            //     console.log("Deleted:", data);
            // } catch (error) {
            //     console.error("Error deleting personal data:", error);
            // }
        }, 300)
    }

    return (
        <>
            <div>
                <div className="w-full flex justify-between mt-4">
                    <div className="flex md:gap-5 justify-start gap-0.5 w-9/12">
                        <button onClick={clickAdd} className='flex items-center gap-0.5 select-none cursor-pointer ml-1 md:w-24 w-auto bg-green-900 px-2 text-white rounded-md transition duration-75 ease-out hover:ring-2 hover:ring-green-900 hover:font-semibold hover:shadow-lg active:ring-2 active:ring-green-900 active:font-semibold active:shadow-lg'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="md:size-6 size-5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <p className='md:text-md text-sm'>Agregar</p>
                        </button>
                        <InputSearch valueSearch={"nombre"} className={"md:w-1/2 md:h-11"} getOptions={getPersonal} options={valuesPersonal} value={searchTeacher} setValue={setSearchTeacher} title="Buscar personal" />
                    </div>
                </div>

                <Table Heads={headNames} datesCard={["apellidos", "telefono"]} deleteValue={deletePersonal} values={valuesPersonal} dates={dates} showModalDelete={showModalDelete} deleteAprob={deleteAprob} closeModalDelete={closeModalDelete} showDelete={showDelete} indexDelete={indexDelete}></Table>
            </div>
        </>
    )
}

export default ContentTeachers
