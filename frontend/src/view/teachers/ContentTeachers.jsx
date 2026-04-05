import React, { useEffect, useState } from 'react'
import InputSearch from '../../components/React/InputSearch';
import Table from '../../components/React/Table';
import { urlGlobal } from '../../data/global';
import { userStore } from '../../data/userStore';
import api from '../../components/React/api';
import { routes } from '../../data/routes';

const ContentTeachers = () => {
    const [searchTeacher, setSearchTeacher] = useState("")
    const [valuesPersonal, setValuesPersonal] = useState([])
    const [indexDelete, setIndexDelete] = useState(-1);
    const [deleteAprob, setDeleteAprob] = useState(false)
    const [showDelete, setShowDelete] = useState(false)
    const headNames = ["ID", "Nombre", "Apellidos", "Correo", "Teléfono", "Editar / Eliminar"]
    const dates = ["id", "name", "last_name", "email", "phone_number"]

    const getTeacher = async () => {
        try {
            const response = await api.get(`${urlGlobal}/teachers/byPag`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                },
            });
            setValuesPersonal(response.data.data.teachers);
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    const getSearchTeacher = async () => {
        if(searchTeacher == ""){ 
            getTeacher();
         }
        try {
            const response = await api.get(`${urlGlobal}/teachers/search`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                },
                params: {
                    search: searchTeacher
                }
            });
            setValuesPersonal(response.data.data.teachers);
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    const deletePersonal = async () => {
        setDeleteAprob(true)
        closeModalDelete()
        setTimeout(() => {
            setValuesPersonal(prev => prev.filter(item => item.id !== indexDelete));
            setIndexDelete(-1)
            setDeleteAprob(false);
            try {
                const response = api.post(`${urlGlobal}/admin-actions/disable-users`, { ids: [indexDelete] }, {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                        "Content-Type": "application/json",
                    },
                });
                console.log(response.data)
            } catch (error) {
                console.error("Error fetching personal data:", error);
            }
        }, 300)
    }

    const closeModalDelete = () => {
        setShowDelete(false)
    }

    const showModalDelete = (i) => {
        setIndexDelete(i)
        setShowDelete(true)
    }

    const editTeacher = (data) => {
        window.location.href = `${routes.teachersForm.url}?id=${data.id}`;
    }

    useEffect(() => {
        getTeacher();
    }, [])

    return (
        <>
            <div>
                <div className="w-full flex justify-between mt-4">
                    <div className="flex md:gap-5 justify-start gap-0.5 w-9/12">
                        <InputSearch valueSearch={"full_name"} className={"md:w-1/2 md:h-11"} getOptions={getSearchTeacher} options={valuesPersonal} value={searchTeacher} setValue={setSearchTeacher} title="Buscar personal" />
                    </div>
                </div>

                {
                    valuesPersonal.length > 0 &&
                    <Table clickEdit={editTeacher} Heads={headNames} datesCard={["email", "phone_number"]} deleteValue={deletePersonal} values={valuesPersonal} dates={dates} showModalDelete={showModalDelete} deleteAprob={deleteAprob} closeModalDelete={closeModalDelete} showDelete={showDelete} indexDelete={indexDelete}></Table>
                }
            </div>
        </>
    )
}

export default ContentTeachers
