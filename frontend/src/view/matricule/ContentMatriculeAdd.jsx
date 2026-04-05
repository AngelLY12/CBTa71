import React, { use, useEffect, useState } from 'react'
import InputTitleUp from '../../components/React/InputTitleUp'
import Button from '../../components/React/Button'
import SelectInputOption from '../../components/React/SelectInputOption'
import { routes } from '../../data/routes'
import { Controller, useForm } from 'react-hook-form'
import { urlGlobal } from '../../data/global'
import { userStore } from '../../data/userStore'
import api from '../../components/React/api'

const ContentMatriculeAdd = () => {
    const [id, setId] = useState(-1);
    const [mode, setMode] = useState("");
    const defaultValues = {
        name: "",
        last_name: "",
        n_control: "",
        career_name: "",
        carrer_id: 0,
        semestre: -1,
        group_name: "",
        workshop_id: null,
        workshop_name: "",
        email: ""
    };
    const { control, handleSubmit, reset, formState: { isDirty, errors } } = useForm({ defaultValues });

    const [optionsCarrer, setCarrerOptions] = useState([]);
    const [optionsGroup, setGroupOptions] = useState([]);
    const [optionsSemester, setSemesterOptions] = useState([]);
    const [optionsWorkshop, setWorkshopOptions] = useState([]);

    const clickCancel = () => {
        window.location.href = routes.matricule.url;
    }

    const getInfoStudent = async (id) => {
        try {
            const response = await api.get(`${urlGlobal}/admin-actions/get-student/${id}`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            console.log(response.data.data)
            reset(response.data.data.student_details);
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getCarrerOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/careers`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            setCarrerOptions(response.data.data.careers);
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getGroupOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/groups`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            setGroupOptions(response.data.data.groups)
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getSmtOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/class-schedules/semester`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            setSemesterOptions(response.data.data.semesters)
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getWorkshop = async () => {
        try {
            const response = await api.get(`${urlGlobal}/workshop`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            setWorkshopOptions(response.data.data.semesters)
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getData = async () => {
        await getGroupOptions();
        await getSmtOptions();
        await getCarrerOptions();
        await getWorkshop();
    }

    const getMode = async (id) => {
        await getData();
        if (id) {
            await getInfoStudent(id);
            setMode("update")
        } else {
            setMode("add")
        }
    }

    const updateInfo = async (data) => {
        try {
            const response = await api.patch(`${urlGlobal}/admin-actions/update-matricule/${data.user_id}`,
                {
                    name: data.name,
                    last_name: data.last_name,
                    n_control: data.n_control,
                    semestre: data.semestre.semester ? data.semestre.semester : data.semestre,
                    email: data.email,
                    career_id: data.career_name.id ?? data.career_id,
                    group_id: data.group_name.id ?? data.group_id,
                    workshop_id: data.workshop_name ? data.workshop_name.id : data.workshop_id
                }, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            alert("Usuario actualizado")
            reset(response.data.data.student);
            clickCancel();
        } catch (error) {
            console.error(error);
        }
    }

    const createNewStudent = async (data) => {
        console.log(data)
        try {
            const response = await api.patch(`${urlGlobal}/admin-actions/register-student`,
                {
                    name: data.name,
                    last_name: data.last_name,
                    n_control: data.n_control,
                    semestre: data.semestre.semester,
                    email: data.email,
                    career_id: data.career_name.id ?? data.career_id,
                    group_id: data.group_name.id ?? data.group_id,
                    workshop_id: data.workshop_name ? data.workshop_name.id ?? null : data.workshop_id
                }, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            console.log(response.data.data)
            alert("Usuario creado")
            clickCancel();
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const onSubmit = (handleSubmit((data) => {
        if (mode == "update") {
            if (isDirty) {
                updateInfo(data);
            }
        } else {
            createNewStudent(data);
        }
    }));


    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get("id");
        setId(id);
        getMode(id);
    }, [])


    return (
        mode &&
        <div className='border rounded-2xl pt-5 pb-9 px-4 mt-6'>
            <form onSubmit={onSubmit} className='flex flex-col gap-6'>
                <div className='flex-col md:flex-row flex justify-between gap-4'>
                    <Controller
                        name="email"
                        control={control}
                        rules={{ required: "El usuario es obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <InputTitleUp
                                    {...field}
                                    className={"md:w-3/12"}
                                    title={"Usuario"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="n_control"
                        control={control}
                        rules={{ required: "La matricula es obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <InputTitleUp
                                    {...field}
                                    className={"md:w-3/12"}
                                    title={"Matricula"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="name"
                        control={control}
                        rules={{ required: "La matricula es obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <InputTitleUp
                                    {...field}
                                    className={"md:w-3/12"}
                                    title={"Nombre"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="last_name"
                        control={control}
                        rules={{ required: "Los apelldios son obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <InputTitleUp
                                    {...field}
                                    className={"md:w-3/12"}
                                    title={"Apellidos"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                </div>

                <div className='flex-col md:flex-row flex justify-between gap-4 '>
                    <Controller
                        name="career_name"
                        control={control}
                        rules={{ required: "El usuario es obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <SelectInputOption
                                    value={field.value}
                                    setValue={field.onChange}
                                    options={optionsCarrer}
                                    valueOption='career_name'
                                    valueSet='career_name'
                                    title={"Carrera"}
                                    titleSelector={"Selecciona una carrera"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="semestre"
                        control={control}
                        rules={{ required: "El semestre es obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <SelectInputOption
                                    value={field.value}
                                    setValue={field.onChange}
                                    options={optionsSemester}
                                    valueOption='semester'
                                    valueSet='semester'
                                    title={"Semestre"}
                                    titleSelector={"Selecciona el semestre"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="group_name"
                        control={control}
                        rules={{ required: "El grupo es obligatorio" }}
                        render={({ field, fieldState }) => (
                            <>
                                <SelectInputOption
                                    value={field.value}
                                    setValue={field.onChange}
                                    options={optionsGroup}
                                    valueOption='group_name'
                                    valueSet='group_name'
                                    title={"Grupo"}
                                    titleSelector={"Selecciona el grupo"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />

                    <Controller
                        name="workshop_name"
                        control={control}
                        render={({ field, fieldState }) => (
                            <>
                                <SelectInputOption
                                    value={field.value}
                                    setValue={field.onChange}
                                    options={optionsWorkshop}
                                    title={"Taller"}
                                    titleSelector={"Selecciona el taller"}
                                />
                                {fieldState.error && <span>{fieldState.error.message}</span>}
                            </>
                        )}
                    />
                </div>
                <div className='flex justify-end md:justify-start md:mt-5 gap-4'>
                    <Button type="button" onClick={clickCancel} className={"md:w-32 ring-1 rounded hover:bg-green-400 hover:text-white hover:ring-2 hover:ring-black active:bg-green-400 active:text-white active:ring-2 active:ring-black"}>Cancelar</Button>
                    <Button className={"md:w-32 px-4 py-1 ring-1 ring-neutral-700 rounded bg-neutral-700 text-white hover:ring-3 hover:ring-neutral-500 hover:bg-neutral-500 active:ring-3 active:ring-neutral-500 active:bg-neutral-500"}>Guardar</Button>
                </div>
            </form>
        </div >
    )
}

export default ContentMatriculeAdd
