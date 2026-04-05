import React, { useEffect, useState } from 'react'
import SelectInput from '../../components/React/SelectInput'
import InputSearch from '../../components/React/InputSearch';
import Table from '../../components/React/Table';
import { routes } from '../../data/routes';
import api from '../../components/React/api';
import { urlGlobal } from '../../data/global';
import { userStore } from '../../data/userStore';

const contentMatricule = () => {
  const [optionSelectShow, setOptionSelectShow] = useState({ id: 0, value: "Todos", valueReal: "all" });
  const [searchMatricule, setSearchMatricule] = useState("");
  const [optionsShowCell, setOptionsShowCell] = useState([
    { id: 0, value: "Todos", valueReal: "all" },
    { id: 1, value: "Semestre", valueReal: "semestre" },
    { id: 3, value: "Nombre", valueReal: "name" },
    { id: 4, value: "Apellidos", valueReal: "last_name" },
    { id: 5, value: "Matricula", valueReal: "n_control" }
  ]);
  const [headValue, setHeadValue] = useState(["Id", "Matricula", "Nombre", "Apellidos", "Carrera", "Semestre", "Grupo", "Taller", "Usuario", "Contraseña inicial", "Editar/Eliminar"])
  const [datesValue, setDatesValue] = useState(["id_details", "n_control", "name", "last_name", "career_name", "semestre", "group_name", "workshop_name", "email", "initial_password"])
  const [values, setValues] = useState([]);
  const [loading, setLoading] = useState(true);

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
      try {
        const response = api.post(`${urlGlobal}/admin-actions/delete-users`, { ids: [indexDelete] }, {
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${userStore.tokens?.access_token}`
          },
        })
        console.log(response.data.data);
      }
      catch (error) {
        console.log(error)
      }
      setIndexDelete(-1)
      setDeleteAprob(false);
    }, 300)
  }

  const getSelectShowOption = () => {
    if (optionSelectShow.value != "Todos") {
      console.log(optionSelectShow)
      setHeadValue(["Id", optionSelectShow.value, "Usuario", "Contraseña inicial", "Editar/Eliminar"])
      setDatesValue(["id", optionSelectShow.valueReal.toLocaleLowerCase(), "email", "initial_password"])
    } else {
      setHeadValue(["Id", "Matricula", "Nombre", "Apellidos", "Carrera", "Semestre", "Grupo", "Taller", "Usuario", "Contraseña inicial", "Editar/Eliminar"])
      setDatesValue(["id_details", "n_control", "name", "last_name", "career_name", "semestre", "group_name", "workshop_name", "email", "initial_password"])
    }
  }

  const getSearchMatricule = async () => {
    if (searchMatricule == "") {
      getMatriculeValues();
      return;
    }
    try {
      const response = await api.get(`${urlGlobal}/admin-actions/search-student-datasAll`, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${userStore.tokens?.access_token}`
        },
        params: {
          search: searchMatricule
        }
      })
      setValues(response.data.data.students)
    }
    catch (error) {
      console.log(error)
    }
  }

  const editStudent = (data) => {
    window.location.href = `${routes.matriculeAdd.url}?id=${data.id_details}`
  }

  const getMatriculeValues = async () => {
    try {
      const response = await api.get(`${urlGlobal}/admin-actions/matricule`, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${userStore.tokens?.access_token}`
        },
      })
      setLoading(false);
      setValues(response.data.data.students);
    }
    catch (error) {
      console.log(error)
    }
  }

  useEffect(() => {
    getMatriculeValues();
  }, [])

  return (
    !loading
      ?
      <div>
        <div className='flex gap-2'>
          <SelectInput valueOption='value' className={"md:w-3/12"} setValue={setOptionSelectShow} options={optionsShowCell} setOption={getSelectShowOption} titleEnter={false} title='Mostrar' titleMovil={"Celdas a mostrar"}>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
              <path strokeLinecap="round" strokeLinejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
          </SelectInput>
          <InputSearch valueSearch={"full_name"} className={"md:w-3/12"} options={values} value={searchMatricule} setValue={setSearchMatricule} getOptions={getSearchMatricule}></InputSearch>
          <a className='gap-1 text-sm md:text-base flex justify-center items-center p-2 bg-green-700 text-white ring-1 ring-green-700 hover:ring-3 rounded' href={routes.matriculeAdd.url}>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
              <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Agregar
          </a>
        </div>

        <Table deleteAprob={deleteAprob} closeModalDelete={closeModalDelete} showModalDelete={showModalDelete} indexDelete={indexDelete} showDelete={showDelete} clickEdit={editStudent} deleteValue={deletePersonal} datesCard={["matricula", "usuario"]} dates={datesValue} values={values} Heads={headValue} id='id' />
      </div>
      :
      <div className='mt-4 flex justify-center items-center text-gray-300'>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-8  animate-spin">
          <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
      </div>

  )
}

export default contentMatricule
