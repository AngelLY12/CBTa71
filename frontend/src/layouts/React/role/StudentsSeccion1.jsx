import React, { useState } from 'react'
import Modal from '../../../components/React/Modal';
import InputSearch from '../../../components/React/InputSearch';
import SelectInput from '../../../components/React/SelectInput';
import CardPersonal from '../../../components/React/CardPersonal';
import Table from '../../../components/React/Table';

function StudentsSeccion1() {
  const [searchPersonal, setSearchPersonal] = useState("")
  const [filtreSelect, setFiltreSelect] = useState("")
  const [optionsPersonal, setOptionsPersonal] = useState([
    { id: 0, nombre: "Jhoana Lizbeth", apellidos: "Yañez Villanueva", edad: "22", fechaNacimiento: "11/03/2003", correo: "jhoana@gmail.com", telefono: "7358901222", sexo: "F", curp: "LKAMOPM120122", direccion: "Col. Inventada Sin numero calle sin avitar", entidad: "Morelos" },
    { id: 1, nombre: "Marco", apellidos: "Perez Lopez", edad: "22", fechaNacimiento: "11/03/2003", correo: "marco2@gmail.com", telefono: "7358901222", sexo: "M", curp: "LKAMOPM120122", direccion: "Col. Inventada Sin numero calle sin avitar", entidad: "Morelos" },
    { id: 2, nombre: "Jose", apellidos: "Martinez Herrera", edad: "22", fechaNacimiento: "11/03/2003", correo: "martin4@gmail.com", telefono: "7358901222", sexo: "M", curp: "LKAMOPM120122", direccion: "Col. Inventada Sin numero calle sin avitar", entidad: "Morelos" }])
  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [showDelete, setShowDelete] = useState(false)
  const headNames = ["Nombre", "Apellidos", "Edad", "Fecha de nacimiento", "Correo", "Telefono", "Sexo", "CURP", "Dirección", "Entidad", "Editar / Eliminar"]
  const dates = ["nombre", "apellidos", "edad", "fechaNacimiento", "correo", "telefono", "sexo", "curp","direccion","entidad"]
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
      setOptionsPersonal(prev => prev.filter(item => item.id !== indexDelete));
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
          <div className="flex md:gap-2 justify-start gap-0.5 w-9/12">
            <SelectInput title='Mostrar por' className={"md:w-full md:h-11"} options={optionsSelect} setValue={setFiltreSelect} setOption={getPersonalCategory} />
            <InputSearch valueSearch={"nombre"} className={"md:w-full md:h-11"} getOptions={getPersonal} options={optionsPersonal} value={searchPersonal} setValue={setSearchPersonal} title="Buscar personal" />
          </div>
        </div>

        <Table Heads={headNames} optionsPersonal={optionsPersonal} dates={dates} showModalDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} deletePersonal={deletePersonal} closeModalDelete={closeModalDelete} showDelete={showDelete} />
      </div>
    </>
  )
}

export default StudentsSeccion1
