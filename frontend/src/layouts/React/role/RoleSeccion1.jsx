import React, { useState } from 'react'
import InputSearch from '../../../components/React/InputSearch'
import SelectInput from '../../../components/React/SelectInput'
import Table from '../../../components/React/Table'

function RoleSeccion1() {
  const [searchPersonal, setSearchPersonal] = useState("")
  const [filtreSelect, setFiltreSelect] = useState("")
  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [showDelete, setShowDelete] = useState(false)
  const [valuesPersonal, setValuesPersonal] = useState([{ id: 0, nombre: "Jhoana Lizbeth", apellidos: "Yañez Villanueva", correo: "jhoana@gmail.com", rol: "Admin", status: "Activo", permisos: "leer, escribir" }, { id: 1, nombre: "a", apellidos: "a", correo: "a@gmail.com", rol: "Admin", status: "Activo", permisos: "leer, escribir" }, { id: 2, nombre: "b", apellidos: "b", correo: "cb@gmail.com", rol: "Admin", status: "Activo", permisos: "leer, escribir" }])
  const headNames = ["ID", "Nombre", "Apellidos", "Correo", "Contraseña", "Rol", "Estatus", "Permisos", "Editar / Eliminar"]
  const dates = ["id", "nombre", "apellidos", "correo", "nombre", "rol", "status", "permisos"]
  const optionsSelect = ["Nombre", "ID", "Administrador"]

  const closeModalDelete = () => {
    setShowDelete(false)
  }

  const showModalDelete = (i) => {
    setIndexDelete(i)
    setShowDelete(true)
  }

  const clickAdd = () => {
    window.location.href = '/rolesAdd';
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

  const deleteValuePersonal = async () => {
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
    <div>
      <div className="w-full flex justify-between mt-4">
        <div className="flex md:gap-2 justify-start gap-0.5 w-9/12">
          <InputSearch valueSearch={"nombre"} className={"md:w-full md:h-11"} getOptions={getPersonal} options={valuesPersonal} value={searchPersonal} setValue={setSearchPersonal} title="Buscar personal" />
          <SelectInput className={"md:w-full md:h-11"} options={optionsSelect} setValue={setFiltreSelect} setOption={getPersonalCategory} />
        </div>
        <button onClick={clickAdd} className='flex items-center gap-0.5 select-none cursor-pointer ml-1 md:w-24 w-auto bg-green-900 px-2 text-white rounded-md transition duration-75 ease-out hover:ring-2 hover:ring-green-900 hover:font-semibold hover:shadow-lg active:ring-2 active:ring-green-900 active:font-semibold active:shadow-lg'>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="md:size-6 size-5">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <p className='md:text-md text-sm'>Agregar</p>
        </button>
      </div>

      <Table Heads={headNames} values={valuesPersonal} dates={dates} showModalDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} deleteValue={deleteValuePersonal} closeModalDelete={closeModalDelete} showDelete={showDelete} />
    </div>
  )
}

export default RoleSeccion1
