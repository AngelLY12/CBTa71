import React, { useEffect, useState } from 'react'
import Button from '../../components/React/Button'
import InputSearch from '../../components/React/InputSearch'
import SelectInput from '../../components/React/SelectInput';
import Table from '../../components/React/Table';
import Modal from '../../components/React/Modal';
import InputTitleUp from '../../components/React/InputTitleUp';
import SelectInputOption from '../../components/React/SelectInputOption';
import { urlGlobal } from '../../data/global';
import { userStore } from '../../data/userStore';
import api from '../../components/React/api';
import { Controller, useForm } from 'react-hook-form';

const ContentMatter = () => {
    const [matterSearch, setMatterSearch] = useState("");
    const [matterOptions, setOptionsMater] = useState("");
    const [matters, setMatters] = useState([]);
    const [carrerOptions, setCarrerOptions] = useState([]);
    const [teacherOptions, setTeacherOptions] = useState([]);
    const [smtOptions, setSmtOptions] = useState([])
    const [loading, setLoading] = useState(true);
    const [mode, setMode] = useState("");
    const [showSelectMatter, setShowSelectMatter] = useState(false);
    const [selectMatterFiltre, setSelectMatterFiltre] = useState("")
    const [indexDelete, setIndexDelete] = useState(-1);
    const [deleteAprob, setDeleteAprob] = useState(false)
    const [showDelete, setShowDelete] = useState(false)
    const [showModalMatter, setShowModalMatter] = useState(false);
    const heads = ["ID", "Materia", "Carrera", "Semestre", "Grupo", "Aula", "Maestro", "Horario", "Editar/Eliminar"];
    const dates = ["id", "subject.subject_name", "career.career_name", "semester", "group_name", "classrooms", "teacher.full_name", "schedule_times"];

    const defaultValues = {
        subject_name: "",
        subject: { id: null, subject_name: "", subject_code: "" },
        career: { id: -1, career_name: "" },
        classrooms: "",
        teacher: { id: -1, name: "", last_name: "", full_name: "" },
        semester: -1,
        group_name: ""
    };

    const { control, register, handleSubmit, reset, watch, formState: { isDirty, dirtyFields, errors } } = useForm({ defaultValues });
    const getMatterSearch = async () => {
        if (matterSearch == "") {
            getMatterFiltre();
            return;
        }
        try {
            const response = await api.get(`${urlGlobal}/subject-offerings/search`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
                params: {
                    search: matterSearch
                }
            });
            setMatters(response.data.data.offerings)
        } catch (error) {
            console.error(error);
        }
    }

    const getMatterFiltre = async (matterOptions = []) => {
        if (selectMatterFiltre != "" || matterOptions.length > 0) {
            try {
                const response = await api.get(`${urlGlobal}/subject-offerings/by-subject/${selectMatterFiltre?.id ?? matterOptions[0]?.id}`, {
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                });
                setMatters(response.data.data.offerings)
            } catch (error) {
                console.error(error);
            }
        }
    }

    const getMatterOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/subjects`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            setOptionsMater(response.data.data.subjects);
            getMatterFiltre(response.data.data.subjects);
        } catch (error) {
            console.error(error);
        }
    }

    const getCareerOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/careers`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            setCarrerOptions(response.data.data.careers);
        } catch (error) {
            console.error(error);
        }
    }

    const getTeacherOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/teachers`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            setTeacherOptions(response.data.data.teachers);
        } catch (error) {
            console.error(error);
        }
    }

    const getSemesterOption = async () => {
        try {
            const response = await api.get(`${urlGlobal}/class-schedules/semester`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            setSmtOptions(response.data.data.semesters);
        } catch (error) {
            console.error(error);
        }
    }

    const deleteMatter = async () => {
        setDeleteAprob(true)
        closeModalDelete()
        setTimeout(() => {
            setMatters(prev => prev.filter(item => item.id !== indexDelete));
            try {
                const response = api.delete(`${urlGlobal}/subject-offerings/${indexDelete}`, {
                    headers: {
                        "Content-Type": "application/json",
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                });
                console.log(response.data);
            } catch (error) {
                console.error(error);
            }
            setIndexDelete(-1)
            setDeleteAprob(false);
        }, 300)
    }

    const setNewSubject = async (data) => {
        try {
            const response = await api.post(`${urlGlobal}/subject-offerings`, data, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            alert("Materia creada correctamente");
            setShowModalMatter(false);
            const updatedOffering = response.data.data.offering;
            
            // Verificar si la materia existe en optionsMater, si no agregarla
            const exists = optionsMater.some(
                (m) => m.subject_name.toLowerCase() === updatedOffering.subject_name.toLowerCase()
            );
            if (!exists) {
                setOptionsMater((prev) => [...prev, updatedOffering.subject]);
            }
        } catch (error) {
            if (error?.response) {
                alert(error?.response?.data?.message);
            }
        }
    }

    const setUpdateSubject = async (data) => {
        try {
            const response = await api.patch(`${urlGlobal}/subject-offerings/${data.id}`, data, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            alert("Materia actualizada correctamente");
            setShowModalMatter(false);

            const updatedOffering = response.data.data.offering;

            // Actualizar la lista de matters reemplazando el que tenga el mismo id
            setMatters((prev) =>
                prev.map((m) => (m.id === updatedOffering.id ? updatedOffering : m))
            );

            // Verificar si la materia existe en optionsMater, si no agregarla
            const exists = optionsMater.some(
                (m) => m.subject_name.toLowerCase() === updatedOffering.subject_name.toLowerCase()
            );
            if (!exists) {
                setOptionsMater((prev) => [...prev, updatedOffering.subject]);
            }
        } catch (error) {
            if (error?.response) {
                alert(error?.response?.data?.message);
            }
        }
    }

    const closeModalDelete = () => {
        setShowDelete(false)
    }

    const showModalDelete = (i) => {
        setIndexDelete(i)
        setShowDelete(true)
    }

    const onClickeditMatter = (matter) => {
        reset(matter);
        setShowModalMatter(true)
        setMode("update");
    }

    const onSubmit = (handleSubmit((data) => {
        if (isDirty) {
            if (mode == "new") {
                setNewSubject(data);
            }
            if (mode == "update") {
                setUpdateSubject(data);
            }
        }
    }));

    const getData = async () => {
        await getMatterOptions();
        await getCareerOptions();
        await getTeacherOptions();
        await getSemesterOption();
        setLoading(false)
    }

    const clickNew = () => {
        setMode("new")
        setShowSelectMatter(false);
        setShowModalMatter(true);
        reset(defaultValues);
    }

    useEffect(() => {
        getData();
    }, [])

    return (
        !loading &&
        <div className='w-full mt-4'>
            <div className='flex md:gap-2 justify-between'>
                <InputSearch className={"md:w-5/12"} valueSearch={"subject_name"} getOptions={getMatterSearch} options={matters} value={matterSearch} setValue={setMatterSearch} title={"Buscar materia"}>
                </InputSearch>
                <Button onClick={clickNew} className={"rounded text-white bg-green-800 ring-green-800 ring-1 hover:font-semibold hover:ring-3 active:ring-3 active:font-semibold"}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Agregar
                </Button>
            </div>

            <div className='flex gap-1 justify-start md:justify-between mt-4'>
                {matterOptions.length > 0 &&
                    <SelectInput valueOption='subject_name' className={"md:w-3/12"} setOption={getMatterFiltre} setValue={setSelectMatterFiltre} options={matterOptions} titleEnter={true} title='Materia'>
                    </SelectInput>
                }
                <div className='w-full flex justify-end md:w-auto md:block'>
                    <Button className={"bg-cyan-600 text-white rounded"}>
                        Actualizar
                    </Button>
                </div>
            </div>

            <Table datesCard={["teacher.full_name", "subject_name", "career.career_name"]} dates={dates} clickEdit={onClickeditMatter} showModalDelete={showModalDelete} showDelete={showDelete} indexDelete={indexDelete} deleteValue={deleteMatter} deleteAprob={deleteAprob} closeModalDelete={closeModalDelete} Heads={heads} values={matters} />

            <Modal onDisable={() => setShowModalMatter(false)} show={showModalMatter} fullScreen={true} onClickAccept={false} aceptModal={false} >
                <form onSubmit={onSubmit} className='w-full pt-4 px-2 lg:px-4'>
                    <h3 className='text-center font-semibold text-lg md:text-2xl'>Agregar nueva materia</h3>
                    <div className='pb-2 lg:pb-0'>
                        <div className='flex flex-col lg:flex-row items-center justify-between gap-2 mt-6'>
                            <button onClick={() => setShowSelectMatter(!showSelectMatter)} title='Seleccionar materia' type='button' className='h-auto rounded-full border p-1 hover:bg-green-300 active:bg-green-300'>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                            </button>
                            <div className='w-full flex gap-2 lg:block'>
                                <Controller
                                    name="subject"
                                    control={control}
                                    rules={{
                                        validate: value => {
                                            if (showSelectMatter) {
                                                return value ? true : "La materia es obligatoria";
                                            }
                                            return true; // no validar si no está activo
                                        }
                                    }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            {showSelectMatter && (
                                                <SelectInputOption
                                                    value={field.value.subject_name}
                                                    setValue={field.onChange}
                                                    valueOption="subject_name"
                                                    options={matterOptions}
                                                    titleSelector="Selecciona una materia"
                                                    title="Seleccionar materia"
                                                />
                                            )}
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />

                                <Controller
                                    name="subject_name"
                                    control={control}
                                    rules={{
                                        validate: value => {
                                            if (!showSelectMatter) {
                                                return value ? true : "La materia es obligatoria";
                                            }
                                            return true; // no validar si no está activo
                                        }
                                    }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            {!showSelectMatter && (
                                                <InputTitleUp {...field} title="Materia" />
                                            )}
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                            </div>
                            <div className='w-full lg:w-48'>
                                <Controller
                                    name="career"
                                    control={control}
                                    rules={{ required: "La carrera es obligatorio" }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <SelectInputOption
                                                value={field.value.career_name}
                                                setValue={field.onChange}
                                                valueOption='career_name'
                                                options={carrerOptions}
                                                titleSelector={"Selecciona una carrera"}
                                                title="Carrera">
                                            </SelectInputOption>
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                            </div>
                        </div>

                        <div className='flex flex-col lg:flex-row justify-between gap-2 mt-4'>
                            <Controller
                                name="teacher"
                                control={control}
                                rules={{ required: "El maestro es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            value={field.value.full_name}
                                            setValue={field.onChange}
                                            valueOption='full_name'
                                            options={teacherOptions}
                                            titleSelector={"Selecciona un maestro"}
                                            title="Maestro">
                                        </SelectInputOption>
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="semester"
                                control={control}
                                rules={{ required: "El semestre es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            value={field.value}
                                            setValue={field.onChange}
                                            valueOption='semester'
                                            options={smtOptions}
                                            titleSelector={"Selecciona un semestre"}
                                            title="Semestre">
                                        </SelectInputOption>
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                        </div>

                        <div className='flex flex-col lg:flex-row  justify-between gap-2 mt-4'>
                            <Controller
                                name="classrooms"
                                control={control}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            required={false}
                                            title="Aula"
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
                                        <InputTitleUp
                                            {...field}
                                            title="Grupo"
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                        </div>

                        <div className='mt-3 mb-4 flex justify-end gap-2'>
                            <Button type="button" onClick={() => setShowModalMatter(false)} className={"w-24 rounded ring-1 ring-green-300 hover:ring-3 hover:bg-green-300 active:ring-3 active:bg-green-300"}>Cancelar</Button>
                            <Button className={"w-24 rounded text-white ring-neutral-600 bg-neutral-600 hover:ring-2 active:ring-2"}>Guardar</Button>
                        </div>
                    </div>
                </form>
            </Modal>
        </div>
    )
}

export default ContentMatter
