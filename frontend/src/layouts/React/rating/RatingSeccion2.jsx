import React, { use, useEffect, useState } from 'react'
import InputTitleUp from '../../../components/React/InputTitleUp'
import SelectInputOption from '../../../components/React/SelectInputOption'
import Button from '../../../components/React/Button'
import { routes } from '../../../data/routes'
import Modal from '../../../components/React/Modal'
import { Controller, useForm } from 'react-hook-form'
import api from '../../../components/React/api'
import { urlGlobal } from '../../../data/global'
import { userStore } from '../../../data/userStore'
import InputSearch from '../../../components/React/InputSearch'

const RatingSeccion2 = () => {
    const [matricule, setMatricule] = useState("")
    const [optionsStudent, setOptionStudent] = useState([])
    const [showModalRegist, setShowModalRegist] = useState(false);

    const defaultValues = {
        id: "",
        matricule: "",
        name: "",
        last_name: "",
        career: {},
        semester: -1,
        group: {},
        partial: -1,
        period: {},
        teacher: {},
        subject: {},
        score_general: 0,
        grade: { id: -1, score: "", status: "" },
        teachers: {}
    };
    const { control, handleSubmit, setValue, reset, watch, formState: { isDirty, dirtyFields, errors } } = useForm({ defaultValues });

    const [optionsCarrer, setOptionsCarrer] = useState(["Ofimatica", "Administración de Emprendimiento", " Administración de Recursos Humanos", "Agropecuario"]);
    const [optionsSemester, setOptionsSemester] = useState([1, 2, 3, 4, 5, 6]);
    const [optionsGroup, setOptionsGroup] = useState(["A", "B", "C", "D"]);
    const [optionsPeriod, setOptionsPerior] = useState(["ENERO/MAYO 2025", "AGO/DIC 2025"]);
    const [optionsTeacher, setOptionsTeacher] = useState();
    const [optionsMater, setOptionsMater] = useState();
    const [optionsParcial, setOptionsParcial] = useState([]);
    const [optionsStatus, setOptionsStatus] = useState(["Aprobado", "Reprobado"]);

    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    const getSearchMatricule = async () => {
        if (matricule == "") {
            setOptionStudent([]);
            reset(defaultValues)
            return;
        }
        try {
            const response = await api.get(`${urlGlobal}/grades/search-by-matricule`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
                params: {
                    search: matricule
                }
            });
            const data = response.data.data.students
            setOptionStudent(data)
            if (data?.length == 1) {
                reset(data[0])
                setOptionsTeacher(data[0].teachers)
            }
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const onSubmit = (handleSubmit((data) => {
        newGrade(data);
    }));

    const clickCancel = () => {
        window.location.href = routes.ratings.url;
    }

    const clickSave = async () => {
        await delay(400);
        setShowModalRegist(true);
        await delay(20);
        window.location.href = routes.ratings.url;
    }

    const newGrade = async (data) => {
        try {
            const response = await api.post(`${urlGlobal}/grades`,
                {
                    score: data.grade.score,
                    status: data.grade.status.toLowerCase(),
                    subject_id: data.subject.id,
                    teacher_user_id: data.teacher.teacher_id,
                    partial: data.partial,
                    group_id: data.group.id,
                    student_user_id: data.id,
                }
                , {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                    params: {
                        career_id: watch("career.id"),
                        semester: watch("semester"),
                        group_id: watch("group.id"),
                        teacher_user_id: watch("teacher.teacher_id")
                    }
                });
            clickSave();
        } catch (error) {
            if (error?.response?.data) {
                alert(error?.response?.data?.message);
            }
        }
    }

    const getMatter = async () => {
        try {
            const response = await api.get(`${urlGlobal}/subjects/by-datas`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
                params: {
                    career_id: watch("career.id"),
                    semester: watch("semester"),
                    group_id: watch("group.id"),
                    teacher_user_id: watch("teacher.teacher_id")
                }
            });
            const data = response.data.data.subjects
            setOptionsMater(data);
            setOptionsParcial([1, 2, 3]);
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    useEffect(() => {
        if (dirtyFields.teacher) {
            getMatter();
        }

    }, [watch("teacher")])

    return (
        <div className='border rounded-3xl my-2 pt-4 pb-12 px-4 md:px-6'>
            <form onSubmit={onSubmit} className='md:max-w-6xl md:mx-auto mt-2'>
                <div className='flex items-end justify-between flex-col md:flex-row'>
                    <div className=''>
                        <div className='mb-1'>
                            <label htmlFor='searh-Buscar' className='font-semibold text-lg'>Matricula</label>
                        </div>
                        <InputSearch
                            valueSearch={"matricule"}
                            type='number'
                            value={matricule}
                            setValue={setMatricule}
                            getOptions={getSearchMatricule}
                            options={optionsStudent}
                            className={"h-full"}
                        />
                    </div>
                    <Controller
                        name="name"
                        control={control}
                        rules={{ required: "El nombre de nacimiento es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <InputTitleUp
                                    {...field}
                                    className={"md:w-3/12"}
                                    title={"Nombre"} />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <Controller
                        name="last_name"
                        control={control}
                        rules={{ required: "El apellido es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <InputTitleUp
                                    {...field}
                                    className={"md:w-3/12"}
                                    title={"Apellidos"} />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                </div>

                <div className='w-full flex items-center justify-between flex-col md:flex-row gap-2 md:gap-12 mt-2'>
                    <Controller
                        name="career"
                        control={control}
                        rules={{ required: "La carrera es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value?.career_name}
                                        setValue={field.onChange}
                                        options={optionsCarrer}
                                        titleSelector={"Selecciona la carrera"}
                                        title={"Carrera"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="semester"
                        control={control}
                        rules={{ required: "El semestre es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value}
                                        setValue={field.onChange}
                                        options={optionsSemester}
                                        titleSelector={"Selecciona el semestre"}
                                        title={"Semestre"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="group"
                        control={control}
                        rules={{ required: "El grupo es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value}
                                        setValue={field.onChange}
                                        options={optionsGroup}
                                        titleSelector={"Selecciona el grupo"}
                                        title={"Grupo"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <Controller
                        name="period"
                        control={control}
                        rules={{ required: "El periodo es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value}
                                        setValue={field.onChange}
                                        options={optionsPeriod}
                                        titleSelector={"Selecciona el periodo"}
                                        title={"Periodo"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                </div>

                <div className='w-full flex items-center justify-between flex-col md:flex-row gap-2 md:gap-12 mt-2'>
                    <Controller
                        name="teacher"
                        control={control}
                        rules={{ required: "El profesor es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field.value}
                                        setValue={field.onChange}
                                        valueOption='full_name'
                                        options={optionsTeacher}
                                        titleSelector={"Selecciona el maestro"}
                                        title={"Maestro"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <Controller
                        name="subject"
                        control={control}
                        rules={{ required: "La materia es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value}
                                        setValue={field.onChange}
                                        options={optionsMater}
                                        valueOption='subject_name'
                                        titleSelector={"Selecciona la materia"}
                                        title={"Materia"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <Controller
                        name="partial"
                        control={control}
                        rules={{ required: "El parcial es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value}
                                        setValue={field.onChange}
                                        options={optionsParcial}
                                        titleSelector={"Selecciona el parcial"}
                                        title={"Parcial"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <Controller
                        name="hours"
                        control={control}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <InputTitleUp
                                        {...field}
                                        type='number'
                                        title={"Horas"}
                                        required={false}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                </div>
                <div className='w-full flex items-center justify-between flex-col md:flex-row gap-2 md:gap-12 mt-2'>
                    <Controller
                        name="grade.status"
                        control={control}
                        rules={{ required: "El status es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <SelectInputOption
                                        value={field?.value}
                                        setValue={field.onChange}
                                        options={optionsStatus}
                                        titleSelector={"Selecciona el estatus"}
                                        title={"Estatus"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <Controller
                        name="grade.score"
                        control={control}
                        rules={{ required: "La calificación es obligatoria" }}
                        render={({ field, fieldState }) => (
                            <>
                                <div className={"w-full md:w-3/12"}>
                                    <InputTitleUp
                                        {...field}
                                        type='number'
                                        title={"Calificacion"}
                                    />
                                </div>
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                    <div className={"hidden md:visible md:block md:w-7/12"}></div>
                </div>

                <div className='mt-4 items-center flex justify-end md:justify-start gap-2'>
                    <Button onClick={clickCancel} type="button" className={"w-22 ring-1 ring-black rounded hover:bg-green-400 hover:text-white hover:ring-3 active:text-white active:bg-green-400 active:ring-3"}>Cancelar</Button>
                    <Button className={"w-22 rounded bg-neutral-700 ring-1 ring-neutral-700 text-white hover:bg-neutral-500 hover:ring-3 active:ring-3 active:bg-neutral-700"}>Guardar</Button>
                </div>
            </form>

            <Modal show={showModalRegist} onDisable={() => setShowModalRegist(false)} onClickAccept={false} aceptModal={false}>
                <div className='px-4 py-4'>
                    <div className='flex flex-col justify-center items-center'>
                        <div className=' w-32 h-32 md:w-40 md:h-40 flex justify-center items-center text-white bg-green-600 rounded-full'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" className="size-14">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                        <p className='text-lg md:text-xl font-semibold w-full text-center mt-3'>¡Calificación registrada!</p>
                    </div>
                </div>
            </Modal>
        </div>
    )
}

export default RatingSeccion2
