import React, { useState } from 'react'
import InputSearch from '../../../components/React/InputSearch'
import SelectInput from '../../../components/React/SelectInput'
import Table from '../../../components/React/Table'
import { userStore } from '../../../data/userStore'
import { urlGlobal } from '../../../data/global'
import useSWR from 'swr'
import { routes } from '../../../data/routes'
import api from '../../../components/React/api'

const fetcher = async ([url, params, token]) => {
  const res = await api.get(url, {
    params, // 👈 aquí van page, perPage, status
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
    },
  });
  return res.data; // devuelve la respuesta completa
};

function RoleSeccion1() {
  const [searchPersonal, setSearchPersonal] = useState("")
  const [filtreSelect, setFiltreSelect] = useState()
  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [showDelete, setShowDelete] = useState(false)
  const [valuesPersonal, setValuesPersonal] = useState([])
  const headNames = ["ID", "Nombre", "Apellidos", "Correo", "Contraseña", "Rol", "Estatus", "Permisos", "Editar / Eliminar"]
  const optionsSelect = [
    { id: 1, value: "Nombre", valueReal: "name" },
    { id: 2, value: "ID", valueReal: "id" },
    { id: 3, value: "Correo", valueReal: "email" },
    { id: 4, value: "Apellido", valueReal: "last_name" }
  ]
  const [page, setPage] = useState(1);
  const perPage = 15;
  const status = 'activo';

  const { data, error, isLoading } = useSWR(
    [
      `${urlGlobal}/admin-actions/show-users`,
      { page, perPage, status },
      userStore.tokens?.access_token,
    ],
    fetcher
  );

  const users = data?.data?.users?.data ?? [];
  const lastPage = data?.data?.users?.last_page ?? 1;

  const closeModalDelete = () => {
    setShowDelete(false)
  }

  const showModalDelete = (i) => {
    setIndexDelete(i)
    setShowDelete(true)
  }

  const editUser = (i) => {
    window.location.href = `${routes.rolesAdd.url}?id=${i.id}`
  }


  const clickAdd = () => {
    window.location.href = routes.rolesAdd.url;
  }

  const getPersonal = async () => {
    if (searchPersonal === "") { setValuesPersonal([""]); return }
    try {
      const response = await api.post(`${urlGlobal}/admin-actions/show-users-by-colum`, { "column": filtreSelect.valueReal, "value": searchPersonal }, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${userStore.tokens?.access_token}`
        },
      });
      setValuesPersonal(response.data.data.users);
    } catch (error) {
      console.error(error.response?.data);
    }
  }

  const getPersonalCategory = () => {
    setValuesPersonal([]);
  }

  const getPages = () => {
    const pages = [];
    const maxVisible = 5; // máximo de botones visibles
    let start = Math.max(1, page - 2);
    let end = Math.min(lastPage, page + 2);

    // Ajustar si estamos al inicio o al final
    if (page <= 2) {
      end = Math.min(lastPage, start + maxVisible - 1);
    }
    if (page >= lastPage - 1) {
      start = Math.max(1, lastPage - maxVisible + 1);
    }

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    return pages;
  };


  const deleteValuePersonal = async () => {
    setDeleteAprob(true)
    closeModalDelete()

    setTimeout(() => {
      setValuesPersonal(prev => prev.filter(item => item.id !== indexDelete));
      setIndexDelete(-1)
      setDeleteAprob(false);
      try {
        const response = api.post(`${urlGlobal}/admin-actions/delete-users`, { "ids": [indexDelete] }, {
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${userStore.tokens?.access_token}`
          },
        });
        console.log(response)
      } catch (error) {
        console.error(error.response?.data);
      }
    }, 300)
  }

  return (
    <div className='mb-2'>
      <div className="w-full flex justify-between mt-4">
        <div className="flex md:gap-2 justify-start gap-0.5 w-9/12">
          <InputSearch valueSearch={filtreSelect ? filtreSelect.valueReal : "name"} className={"md:w-full md:h-11"} getOptions={getPersonal} options={valuesPersonal} value={searchPersonal} setValue={setSearchPersonal} title="Buscar personal" />
          <SelectInput className={"md:w-full md:h-11"} valueOption='value' options={optionsSelect} setValue={setFiltreSelect} setOption={getPersonalCategory} />
        </div>
        <button onClick={clickAdd} className='flex items-center gap-0.5 select-none cursor-pointer ml-1 md:w-24 w-auto bg-green-900 px-2 text-white rounded-md transition duration-75 ease-out hover:ring-2 hover:ring-green-900 hover:font-semibold hover:shadow-lg active:ring-2 active:ring-green-900 active:font-semibold active:shadow-lg'>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="md:size-6 size-5">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          <p className='md:text-md text-sm'>Agregar</p>
        </button>
      </div>

      {
        (valuesPersonal.length > 0 || users.length > 0) &&
        <>
          <Table Heads={headNames} values={searchPersonal ? valuesPersonal : users} clickEdit={editUser} showModalDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} deleteValue={deleteValuePersonal} closeModalDelete={closeModalDelete} showDelete={showDelete} />
          {(!searchPersonal && users) &&
            < div className="mt-2 flex items-center justify-end space-x-2">
              <button
                disabled={page === 1}
                onClick={() => setPage(page - 1)}
                className={`px-3 py-1 rounded border ${page === 1
                  ? "bg-gray-200 text-gray-500 cursor-not-allowed"
                  : "bg-white hover:bg-yellow-200 border-yellow-600"
                  }`}
              >
                Anterior
              </button>

              {getPages().map((p) => (
                <button
                  key={p}
                  onClick={() => setPage(p)}
                  className={`px-3 py-1 rounded border ${p === page
                    ? "bg-yellow-400 border-yellow-600 font-bold"
                    : "bg-white hover:bg-yellow-200 border-yellow-600"
                    }`}
                >
                  {p}
                </button>
              ))}

              <button
                disabled={page === lastPage}
                onClick={() => setPage(page + 1)}
                className={`px-3 py-1 rounded border ${page === lastPage
                  ? "bg-gray-200 text-gray-500 cursor-not-allowed"
                  : "bg-white hover:bg-yellow-200 border-yellow-600"
                  }`}
              >
                Siguiente
              </button>
            </div>
          }
        </>
      }

    </div >
  )
}

export default RoleSeccion1
