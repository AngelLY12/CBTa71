import React, { useEffect, useState } from 'react'
import { urlGlobal } from '../../../data/global';
import { userStore } from '../../../data/userStore';
import api from '../../../components/React/api';

const StudentRating = () => {
    const [valuesGrades, setValueGrades] = useState([]);
    const heads = [
        "Id",
        "Matricula",
        "Nombre",
        "Apellidos",
        "Carrera",
        "Semestre",
        "Grupo",
        "Estatus",
        "Promedio General",
    ];

    const getValueGrades = async () => {
        try {
            const response = await api.get(`${urlGlobal}/grades/gradeStudent`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            })
            setValueGrades(response.data.data);
        }
        catch (error) {
            console.log(error)
        }
    }

    useEffect(() => {
        getValueGrades()
    }, [])

    return (
        valuesGrades.student
            ?
            < div className="border px-2 py-2 mb-6 overflow-auto" >
                <div className="w-full flex flex-col min-w-max">
                    <table
                        className="relative w-full table-auto border-collapse border border-gray-400"
                    >
                        <thead>
                            <tr>
                                {
                                    heads.map((head, i) => (
                                        <th className="p-0.5 border border-gray-400">{head}</th>
                                    ))
                                }
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                {
                                    valuesGrades?.student
                                    && Object.entries(valuesGrades.student).map(([key, value]) => (
                                        <td className={`border border-gray-400 p-1.5 ${key == "carrer" ? "max-w-16" : "max-w-max"}`}>
                                            <div>
                                                <p className="w-full text-center overflow-hidden overflow-ellipsis">
                                                    {value}
                                                </p>
                                            </div>
                                        </td>
                                    ))
                                }
                            </tr>
                        </tbody>
                    </table>

                    {Array.isArray(valuesGrades.grades) &&
                        valuesGrades.grades.map((value) => (
                            <>
                                <h3 className="mt-2 font-bold text-center border border-gray-400">
                                    {value.period_code}
                                </h3>

                                <table
                                    className="mt-2 w-full border border-collapse table-auto border-gray-400"
                                >
                                    <thead>
                                        <tr>
                                            <th className="border border-gray-400">ID</th>
                                            <th className="border border-gray-400">Materia</th>
                                            <th className="border border-gray-400">Maestro</th>
                                            <th className="border border-gray-400">Horas</th>
                                            <th className="border border-gray-400">
                                                <div className="min-w-max flex flex-col">
                                                    <div><p>Calificación</p></div>
                                                    <div className="flex pr-2">
                                                        <div className="w-full flex justify-center">
                                                            <p>1er</p>
                                                        </div>
                                                        <div className="w-full flex justify-center">
                                                            <p>2do</p>
                                                        </div>
                                                        <div className="w-full flex justify-center">
                                                            <p>3ro</p>
                                                        </div>
                                                        <div className="w-full flex justify-center">
                                                            <p>Estatus</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th className="border border-gray-400">Promedio</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {
                                            value.subjects.map((subject, i) => (
                                                <tr key={i}>
                                                    <td className="border border-gray-400 w-20">
                                                        <p className="text-center overflow-hidden overflow-ellipsis break-words">
                                                            {subject.subject_code}
                                                        </p>
                                                    </td>
                                                    <td className="border border-gray-400 max-w-12">
                                                        <p className="w-full text-center overflow-hidden overflow-ellipsis px-2 break-words">
                                                            {subject.subject_name}
                                                        </p>
                                                    </td>
                                                    <td className="border border-gray-400 max-w-28">
                                                        <p className="w-full text-center overflow-hidden overflow-ellipsis break-words">
                                                            {subject.teacher_name}
                                                        </p>
                                                    </td>
                                                    <td className="border border-gray-400 max-w-8">
                                                        <p className="w-full text-center overflow-hidden overflow-ellipsis break-words">
                                                            {subject.hours_imparted}/{subject.hours_per_partial}
                                                        </p>
                                                    </td>
                                                    <td className="border border-gray-400">
                                                        <div className="flex">
                                                            {Array.from({ length: 3 }).map((_, index) => {
                                                                const grade = subject.grades[index]; // accedemos al índice
                                                                return (
                                                                    <p
                                                                        key={index}
                                                                        className="w-full text-center"
                                                                    >
                                                                        {grade ? grade.score : ""} {/* si no existe, mostramos vacío */}
                                                                    </p>
                                                                );
                                                            })}
                                                            <p className={`md:w-full w-16  text-center wrap-break-word ${subject.status == "Aprobado" ? "text-green-600" : "text-red-700"}`}>
                                                                {subject.status}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p className="text-center">{subject.average_score}</p>
                                                    </td>
                                                </tr>
                                            ))
                                        }
                                        <tr>
                                            <td className="border-b border-gray-400" colSpan={6}>
                                                <div className="w-full flex">
                                                    <div className="w-full"></div>
                                                    <div className="w-full"></div>
                                                    <div
                                                        className="border-collapse border-x border-gray-400"
                                                    >
                                                        <p className="font-semibold w-24 text-center">
                                                            Promedio Semestral
                                                        </p>
                                                    </div>
                                                    <div
                                                        className="-mt-[0.5px] flex items-center justify-center w-full border-t border-gray-400"
                                                    >
                                                        <p className="text-center">{value.period_average}</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td className="border-l border-b border-white" colSpan={6}>
                                                <div className="w-full flex">
                                                    <div className="w-full"></div>
                                                    <div className="w-full"></div>
                                                    <div
                                                        className="border-collapse border-x border-b border-gray-400"
                                                    >
                                                        <p className="font-semibold w-24 text-center">
                                                            Promedio General
                                                        </p>
                                                    </div>
                                                    <div
                                                        className="-mt-[0.5px] flex items-center justify-center w-full border-b border-gray-400"
                                                    >
                                                        <p className="text-center">{value.general_average_until_now}</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </>
                        ))
                    }
                </div>
            </div>
            :
            <div className='text-gray-400 animate-spin'>
                <div className='mt-5 flex justify-center items-center'>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-8">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </div>
            </div>
    )
}

export default StudentRating
