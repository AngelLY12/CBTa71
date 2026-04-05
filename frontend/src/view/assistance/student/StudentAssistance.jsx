import React, { useEffect, useState } from 'react'
import SelectInput from '../../../components/React/SelectInput'
import { urlGlobal } from '../../../data/global';
import { userStore } from '../../../data/userStore';
import api from '../../../components/React/api';

const StudentAssistance = () => {
    const [matterSelect, setMatterSelect] = useState("");
    const [optionsMatter, setOptionMatter] = useState([]);
    const [datesAssit, setDatesAssit] = useState([]);

    const getSelectMatter = () => {
        if (matterSelect) {
            getAttendance();
        }
    }

    const getAttendance = async () => {
        try {
            const response = await api.get(`${urlGlobal}/attendances/by-subject/${matterSelect.subject_offering_id}`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            })
            setDatesAssit(response.data.data);
        }
        catch (error) {
            console.log(error.message);
        }
    }

    const getSubjects = async () => {
        try {
            const response = await api.get(`${urlGlobal}/subjects/by-student`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            })
            setOptionMatter(response.data.data.subjects);
        }
        catch (error) {
            setError(error.message);
        }
    }

    useEffect(() => {
        getSubjects();
    }, [])

    return (
        <div className='border rounded p-2 md:p-4'>
            <div className='flex w-2/12'>
                <SelectInput valueOption='name' notSelectDefault={true} titleMovil={"Seleccionar materia"} setOption={getSelectMatter} setValue={setMatterSelect} options={optionsMatter} className={"w-full"} title='Materia' topTitle={true} titleEnter={false}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </SelectInput>
            </div>

            {
                matterSelect == ""
                    ?
                    <div className='flex justify-center items-center h-56'>
                        <p>Seleccione una materia para poder ver sus asistencias</p>
                    </div>
                    :
                    datesAssit &&
                    <div className='mt-4 overflow-hidden overflow-x-auto pb-4'>
                        <table className='table-auto border-collapse w-full border-gray-300'>
                            <thead>
                                {
                                    datesAssit.subject_info &&
                                    <tr>
                                        <th className='border border-gray-300'>Nombre del alumno: <span className='font-normal'>{datesAssit.subject_info.student}</span></th>
                                        <th className='border border-gray-300'>Materia: <span className='font-normal'>{datesAssit.subject_info.subject}</span></th>
                                        <th className='border border-gray-300'>Profesor: <span className='font-normal'>{datesAssit.subject_info.teacher}</span></th>
                                        <th className='border border-gray-300'>Semestre: <span className='font-normal'>{datesAssit.subject_info.semester}</span></th>
                                        <th className='border border-gray-300'>Grupo: <span className='font-normal'>{datesAssit.subject_info.group}</span></th>
                                        <th className='border border-gray-300'>Carrera: <span className='font-normal'>{datesAssit.subject_info.career}</span></th>
                                    </tr>
                                }
                            </thead>

                            <tbody>
                                {
                                    Array.isArray(datesAssit.attendances)
                                    &&
                                    <tr>
                                        <td className='border border-gray-300'>
                                            <p className='text-center'>Mes</p>
                                        </td>
                                        <td colSpan={5} className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <div className='flex justify-between'>
                                                    <div className='flex flex-col w-full overflow-hidden'>
                                                        <p className='text-center'>DÍA</p>
                                                        <div className='flex'>
                                                            {
                                                                [...Array(32)].map((_, i) => (
                                                                    <div key={"day" + i} className={`w-full border-t border-l border-gray-300 ${i + 1 == 1 && "border-l-0"}`}>
                                                                        <p className='text-center'>{i + 1}</p>
                                                                    </div>
                                                                ))
                                                            }
                                                        </div>
                                                    </div>
                                                    <div className='flex flex-col w-12'>
                                                        <p className='border-l border-gray-300 text-center'>TOTAL</p>
                                                        <div className='flex'>
                                                            <div className='border-x border-t border-gray-300 w-full flex justify-center'>A</div>
                                                            <div className='border-t border-gray-300 w-full flex justify-center'>F</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                }
                                {
                                    Array.isArray(datesAssit.attendances) &&
                                    datesAssit.attendances.map((attendance) => (
                                        <tr key={attendance.month}>
                                            <td className='border border-gray-300'>
                                                <p className='uppercase py-2 text-center'>{attendance.month}</p>
                                            </td>
                                            <td colSpan={5} className='border border-gray-300'>
                                                <div className='flex w-full justify-between h-10'>
                                                    <div className='flex w-full h-full'>
                                                        {
                                                            [...Array(32)].map((_, i) => (
                                                                <div key={attendance.month + "Day" + i} className={`flex justify-center items-center h-full w-full border-l border-gray-300 ${i + 1 == 1 && "border-l-0"}`}>
                                                                    <p>{attendance.attendances[i] ? attendance.attendances[i].status[0] : ''}</p>
                                                                </div>
                                                            ))
                                                        }
                                                    </div>
                                                    <div className='flex w-12'>
                                                        <div className='w-full flex justify-center items-center border-x border-gray-300'>{attendance.total_present}</div>
                                                        <div className=' w-full flex justify-center items-center border-gray-300'>{attendance.total_absent}</div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                }

                            </tbody>
                        </table>

                    </div>
            }
        </div>
    )
}

export default StudentAssistance
