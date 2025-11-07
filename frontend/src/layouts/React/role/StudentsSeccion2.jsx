import React, { useState } from 'react'
import Modal from '../../../components/React/Modal';
import InputSearch from '../../../components/React/InputSearch';
import SelectInput from '../../../components/React/SelectInput';
import CardPersonal from '../../../components/React/CardPersonal';

function StudentsSeccion2() {
  const [searchPersonal, setSearchPersonal] = useState("")
  const [filtreSelect, setFiltreSelect] = useState("")
  const headsName = [{ value: "Numero de solicitud" }, { value: "Nombre del aspirantes" }, { value: "Carrera preferida" }, { value: "Aceptar / Rechazar" }]
  const [optionsPersonal, setOptionsPersonal] = useState([
    { numeroSolicitud: 0, nombre: "Jhoana Lizbeth", carreraPrefed: "Ofimatica" },
    { numeroSolicitud: 1, nombre: "Marco Perez Lopez", carreraPrefed: "Ofimatica" },
    { numeroSolicitud: 2, nombre: "Jose Martinez Herrera", carreraPrefed: "Ofimatica" }])
  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [showDelete, setShowDelete] = useState(false)
  const optionsSelect = ["Nombre", "ID", "Administrador"]

  const closeModalDelete = () => {
    setShowDelete(false)
  }

  const showModalDelete = (i) => {
    setIndexDelete(i)
    setShowDelete(true)
  }


  const getPersonal = async () => {
    // try {
    //     const response = await fetch(`/api/personal?search=${searchPersonal}`);
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
      setOptionsPersonal(prev => prev.filter(item => item.numeroSolicitud !== indexDelete));
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
        <div className='w-full mt-6 md:overflow-hidden'>
          {
            optionsPersonal.length > 0
              ?
              <>
                <table className='hidden md:visible md:table text-sm md:text-md w-full border-separate border-spacing-1.5 border rounded-md border-gray-600 table-auto'>
                  <thead>
                    <tr>
                      <th>Numero de solicitud</th>
                      <th>Nombre del aspirantes</th>
                      <th>Carrera preferida</th>
                      <th>Aceptar / Rechazar</th>
                    </tr>
                  </thead>
                  <tbody>
                    {optionsPersonal.map((option) => (
                      <tr className={`text-center transition-opacity duration-300 ease-out ${(option.numeroSolicitud == indexDelete && deleteAprob) && 'opacity-0'}`} key={option.numeroSolicitud}>
                        <td>{option.numeroSolicitud}</td>
                        <td><a href="1" className='text-indigo-600 hover:underline active:underline'>{option.nombre}</a></td>
                        <td>{option.carreraPrefed}</td>
                        <td>
                          <button className='mr-2 cursor-pointer group hover:text-green-400' title='Editar elemento'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 group-hover:hidden">
                              <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6 hidden group-hover:visible group-hover:block">
                              <path fillRule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clipRule="evenodd" />
                            </svg>
                          </button>
                          <button onClick={() => showModalDelete(option.numeroSolicitud)} title='Eliminar elemento' className='ml-2 cursor-pointer group hover:text-red-500'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 group-hover:hidden">
                              <path strokeLinecap="round" strokeLinejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6 hidden group-hover:visible group-hover:block">
                              <path fillRule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clipRule="evenodd" />
                            </svg>
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>

                <div className='flex flex-col visible md:hidden'>
                  {
                    optionsPersonal.map((option) => (
                      <CardPersonal info={["numeroSolicitud", "carreraPrefed"]} key={option.numeroSolicitud} item={option} index={option.numeroSolicitud} onClickDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} />
                    ))
                  }
                </div>
              </>
              :
              <div>
                <p className='font-bold text-center text-md md:text-xl'>No se encuentran datos aun</p>
              </div>
          }
          <Modal onClickAccept={deletePersonal} show={showDelete} onDisable={closeModalDelete} text={"¿ Esta seguro de querer rechazar a este estudiante ?"} />
        </div>
      </div>
    </>
  )
}

export default StudentsSeccion2
