import React, { useState } from 'react'
import SelectInput from '../../components/React/SelectInput'
import InputSearch from '../../components/React/InputSearch';
import Table from '../../components/React/Table';
import { routes } from '../../data/routes';

const contentMatricule = () => {
  const [optionSelectShow, setOptionSelectShow] = useState("Todo");
  const [searchMatricule, setSearchMatricule] = useState("")
  const [optionsShowCell, setOptionsShowCell] = useState(["Todo", "Carrera", "Semestre"]);
  const [headValue, setHeadValue] = useState(["Id", "Matricula", "Nombre", "Apellidos", "Carrera", "Semestre", "Grupo", "Taller", "Usuario", "Contraseña", "Editar/Eliminar"])
  const [datesValue, setDatesValue] = useState(["id", "matricula", "nombre", "apellidos", "carrera", "semestre", "grupo", "taller", "usuario", "contrasena"])
  const [values, setValues] = useState(
    [
      { id: 1, matricula: "ADSSD2131", nombre: "Jhoana Lizbeth", apellidos: "Yañez Villanueva", carrera: "Informatica", semestre: 1, grupo: 2, taller: "Computo", usuario: "jhoana341", contrasena: "fkfnkkd" },
      { id: 2, matricula: "KDDAS312S", nombre: "Juan", apellidos: "Perez Sanchez", carrera: "Informatica", semestre: 1, grupo: 2, taller: "Computo", usuario: "perez331", contrasena: "ldmasd_" }
    ]
  )

  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [showDelete, setShowDelete] = useState(false)

  const closeModalDelete = () => {
    setShowDelete(false)
  }

  const showModalDelete = (i) => {
    setIndexDelete(i)
    setShowDelete(true)
  }

  const deletePersonal = async () => {
    setDeleteAprob(true)
    closeModalDelete()
    setTimeout(() => {
      setValues(prev => prev.filter(item => item.id !== indexDelete));
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

  const getSelectShowOption = () => {
    if (optionSelectShow != "Todo") {
      console.log(optionSelectShow)
      setHeadValue(["Id", optionSelectShow, "Usuario", "Contrasena", "Editar/Eliminar"])
      setDatesValue(["id", optionSelectShow.toLocaleLowerCase(), "usuario", "contrasena"])
    } else {
      setHeadValue(["Id", "Matricula", "Nombre", "Apellidos", "Carrera", "Semestre", "Grupo", "Taller", "Usuario", "Contraseña", "Editar/Eliminar"])
      setDatesValue(["id", "matricula", "nombre", "apellidos", "carrera", "semestre", "grupo", "taller", "usuario", "contrasena"])
    }
  }

  const getSearchMatricule = () => {

  }

  return (
    <div>
      <div className='flex gap-2'>
        <SelectInput className={"md:w-3/12"} setValue={setOptionSelectShow} options={optionsShowCell} setOption={getSelectShowOption} titleEnter={false} title='Mostrar' titleMovil={"Celdas a mostrar"}>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
            <path strokeLinecap="round" strokeLinejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
          </svg>
        </SelectInput>
        <InputSearch valueSearch={"matricula"} className={"md:w-3/12"} options={values} value={searchMatricule} setValue={setSearchMatricule} getOptions={getSearchMatricule}></InputSearch>
        <a className='gap-1 text-sm md:text-base flex justify-center items-center p-2 bg-green-700 text-white ring-1 ring-green-700 hover:ring-3 rounded' href={routes.matriculeAdd.url}>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Agregar
        </a>
      </div>

      <Table deleteAprob={deleteAprob} closeModalDelete={closeModalDelete} showModalDelete={showModalDelete} indexDelete={indexDelete} showDelete={showDelete} deleteValue={deletePersonal} datesCard={["matricula", "usuario"]} dates={datesValue} values={values} Heads={headValue} id='id' />
    </div>
  )
}

export default contentMatricule
