import React, { useEffect, useState } from 'react'
import InputSearch from '../../../components/React/InputSearch'
import Button from '../../../components/React/Button';
import Modal from '../../../components/React/Modal';
import CardsListInfo from '../../../components/React/CardsListInfo';
import SelectInputOption from '../../../components/React/SelectInputOption';
import SelectInput from '../../../components/React/SelectInput';
import InputTitleUp from '../../../components/React/InputTitleUp';
import api from '../../../components/React/api';
import { urlGlobal } from '../../../data/global';
import { userStore } from '../../../data/userStore';
import { Controller, useForm } from 'react-hook-form';

const RatingSeccion1 = () => {
    const [selectCarrerFiltre, setSelectCarrerFiltre] = useState("")
    const [selectSemesterFiltre, setSelectSemesterFiltre] = useState(-1);
    const [loading, setLoading] = useState(true);
    const defaultValues = {
        id: "",
        matricule: "",
        name: "",
        last_name: "",
        full_name: "",
        career: {},
        semestre: -1,
        group: {},
        partial: -1,
        period: {},
        teacher: {},
        subject: {},
        score_general: 0,
        grade: { id: -1, score: "", status: "" },
        subjects_with_grades: {}
    };
    const { control, handleSubmit, setValue, reset, watch, formState: { isDirty, dirtyFields, errors } } = useForm({ defaultValues });
    const partial = [1, 2, 3];
    const [optionsCarrer, setOptionsCarrer] = useState([]);
    const [optionsSemester, setOptionsSemester] = useState([]);
    const [optionsGroup, setOptionsGroup] = useState([]);
    const [optionsPeriod, setOptionsPerior] = useState([]);
    const [optionsMater, setOptionsMater] = useState(["Quimica", "Matematicas", "Español"]);
    const [optionsParcial, setOptionsParcial] = useState([]);
    const [optionsStatus, setOptionsStatus] = useState(["Aprobado", "Reprobado"]);
    const heads = ["Id", "Matricula", "Nombre", "Apellidos", "Carrera", "Semestre", "Grupo", "Estatus", "Promedio General"];
    const dates = ["id", "matricule", "name", "last_name", "career.career_name", "semester", "group.group_name", "status", "score_general"]
    const [valueStudents, setValueStudents] = useState([]);
    const [showModalRang, setShowModalRang] = useState(false);
    const [showModalEdit, setShowModalEdit] = useState(false);

    const [searchStudent, setSearchStudent] = useState("")
    const [studentSelect, setStudentSelect] = useState({});

    const getStudent = async () => {
        if ((selectCarrerFiltre && selectSemesterFiltre) || (optionsCarrer[0].id && optionsSemester[0].semester)) {
            try {
                const response = await api.get(`${urlGlobal}/grades/by-career-smt`, {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                        "Content-Type": "application/json",
                    },
                    params: {
                        career_id: selectCarrerFiltre.id ?? optionsCarrer[0].id,
                        semestre: selectSemesterFiltre.semester ?? optionsSemester[0].semester
                    }
                });
                setValueStudents(response.data.data.students)
            } catch (error) {
                console.error("Error fetching personal data:", error);
            }
        }
    }

    const getSearchStudent = async () => {
        if (searchStudent == "") {
            getStudent();
            return;
        }
        try {
            const response = await api.get(`${urlGlobal}/grades/search`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
                params: {
                    search: searchStudent
                }
            });
            setValueStudents(response.data.data.students);
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getGroupOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/groups`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                },
            });
            setOptionsGroup(response.data.data.groups);
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    const getOptionSemester = async () => {
        try {
            const response = await api.get(`${urlGlobal}/class-schedules/semester`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            setOptionsSemester(response.data.data.semesters);
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getOptionCareer = async () => {
        try {
            const response = await api.get(`${urlGlobal}/careers`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "x-refresh-token": `${userStore.tokens?.refresh_token}`
                },
            });
            setOptionsCarrer(response.data.data.careers);
        } catch (error) {
            console.error(error);
        }
    }

    const getPeriodsOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/periods`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                },
            });
            setOptionsPerior(response.data.data.periods);
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    const updateGrade = async (data) => {
        try {
            const response = await api.patch(`${urlGlobal}/grades/${data.grade.id}`, { score: data.grade.score, status: data?.grade?.status }, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
            alert("Calificación actualizada")
            closeModalEdit();
        } catch (error) {
            console.error(error?.response?.data);
        }
    }

    const getGradeByTeacher = async () => {
        try {
            const response = await api.get(`${urlGlobal}/grades/by-teacher-student`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
                params: {
                    teacher_id: watch("teacher.teacher_id"),
                    student_id: watch("id")
                }
            });
            setOptionsMater(response.data.data.subjects)
        } catch (error) {
            console.error(error?.response?.data);
        }
    }

    const onClickSelectStudent = (student) => {
        setStudentSelect(student);
        setShowModalRang(true);
    }

    const onClickEditStudent = async (student) => {
        reset(student)
        setShowModalEdit(true);
    }

    const closeModalRang = () => {
        setShowModalRang(false);
    }

    const closeModalEdit = () => {
        setShowModalEdit(false);
        reset(defaultValues);
        setOptionsMater([]);
        setOptionsMater([]);
        setOptionsParcial([]);
    }

    const onSubmit = (handleSubmit((data) => {
        updateGrade(data);
    }));

    const getDatas = async () => {
        await getOptionSemester();
        await getOptionCareer();
        await getGroupOptions();
        await getPeriodsOptions();
        setLoading(false);
    }
    useEffect(() => {
        getDatas()
    }, [])

    useEffect(() => {
        if (watch("teacher")) {
            getGradeByTeacher();
        }
    }, [watch("teacher")])

    useEffect(() => {
        if (watch("subject")) {
            setOptionsParcial(partial)
        }
    }, [watch("subject")])

    useEffect(() => {
        setValue("grade", defaultValues.grade);
        if (watch("partial")) {
            const partialIndex = parseInt(watch("partial"), 10);
            const grades = watch("subject.grades") || [];
            if (grades[partialIndex - 1]) {
                setValue("grade", grades[partialIndex - 1]);
            }
        }
    }, [watch("partial")])

    return (

        loading
            ?
            <div className='mt-4 text-gray-300 flex justify-center items-center'>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-8 animate-spin">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            :
            <div className='mt-5'>
                <div className='flex h-10 justify-between items-center'>
                    <div className='h-full md:w-4/12'>
                        <InputSearch options={valueStudents} getOptions={getSearchStudent} valueSearch={["name", "last_name"]} title={"Buscar"} value={searchStudent} setValue={setSearchStudent} />
                    </div>
                    <div className='flex items-center h-full gap-2'>
                        <p className='font-semibold w-auto text-sm hidden lg:visible lg:block md:text-base'>Filtrar por</p>
                        <SelectInput valueOption='career_name' className={"md:h-full md:w-60 md:z-20"} options={optionsCarrer} setOption={getStudent} setValue={setSelectCarrerFiltre} title='Carrera' >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                        </SelectInput>
                        <SelectInput valueOption='semester' className={"md:h-full md:z-20"} options={optionsSemester} setOption={getStudent} setValue={setSelectSemesterFiltre} title='Semestre' >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                            </svg>
                        </SelectInput>
                    </div>
                </div>

                <div className='mt-2 pb-12 overflow-auto'>
                    <div className='pt-2 md:pl-2 md:pr-5 gap-4 mt-4 hidden md:visible md:flex md:flex-col'>
                        {valueStudents.map((student) => (
                            <div key={student.id} className='relative min-w-max overflow-visible'>
                                <Button onClick={() => onClickEditStudent(student)} className={"w-9 h-9 ring-2 absolute -top-5 -right-4 bg-white rounded-full z-10"}>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                    </svg>
                                </Button>
                                <table className='relative w-full table-auto border-collapse border border-gray-400'>
                                    <thead>
                                        <tr>
                                            {heads.map((head, i) => (
                                                <th className='border border-gray-400' key={`${student.id}-head-${i}`}>{head}</th>
                                            ))}
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            {dates.map((data, i) => (
                                                <td key={`data-${student.id}-${i}`} className='max-w-12 border border-gray-400 p-1.5'>
                                                    <div>
                                                        {i != 1 ?
                                                            <p className='w-full text-center overflow-hidden overflow-ellipsis'>
                                                                {
                                                                    !data.includes(".")
                                                                        ?
                                                                        student[data]
                                                                        :
                                                                        data.split(".").reduce((acc, key) => acc?.[key], student)
                                                                }
                                                            </p>
                                                            :
                                                            <Button onClick={() => onClickSelectStudent(student)} className='w-full text-indigo-600 hover:underline'>{student[data]}</Button>
                                                        }
                                                    </div>
                                                </td>
                                            ))}
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        ))}
                    </div>

                    <CardsListInfo onClickCard={onClickSelectStudent} datesCard={["", "matricule"]} className={"md:hidden"} items={valueStudents}></CardsListInfo>
                </div>

                <Modal show={showModalRang} onDisable={closeModalRang} fullScreen={false} aceptModal={false} onClickAccept={false}>
                    <div className='w-full md:px-4 py-4 md:w-3xl md:min-w-xl'>
                        <h3 className='md:text-xl font-semibold text-center'>Calificaciones</h3>
                        <div className='flex flex-col min-w-max px-3'>
                            <table className='mt-3 relative w-full table-auto border-collapse border border-gray-400'>
                                <thead>
                                    <tr>
                                        {heads.map((head, i) => (
                                            <th className='border border-gray-400' key={i}>{head}</th>
                                        ))}
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        {dates.map((data, i) => (
                                            <td key={`data-${i}`} className='min-w-auto max-w-24 border border-gray-400 p-1.5'>
                                                <div>
                                                    {i != 1 ?
                                                        <p className='line-clamp-3 w-full text-center overflow-hidden overflow-ellipsis break-words'>
                                                            {
                                                                !data.includes(".")
                                                                    ?
                                                                    studentSelect[data]
                                                                    :
                                                                    data.split(".").reduce((acc, key) => acc?.[key], studentSelect)
                                                            }
                                                        </p>
                                                        :
                                                        <p className='w-full text-green-400 text-center'>{studentSelect[data]}</p>
                                                    }
                                                </div>
                                            </td>
                                        ))}
                                    </tr>
                                </tbody>
                            </table>

                            {
                                studentSelect?.grades?.length > 0 ?
                                    studentSelect.grades.map((value, i) => (
                                        <div className='w-full mt-4 pb-4'>
                                            <h3 className='font-bold text-center border border-gray-400'>{value.period_code}</h3>
                                            <table className='mt-2 w-full border border-collapse table-auto border-gray-400'>
                                                <thead>
                                                    <tr>
                                                        <th className='border border-gray-400'>ID</th>
                                                        <th className='border border-gray-400'>Materia</th>
                                                        <th className='border border-gray-400'>Maestro</th>
                                                        <th className='border border-gray-400'>Horas</th>
                                                        <th className='border border-gray-400'>
                                                            <div className='min-w-max flex flex-col'>
                                                                <div><p>Calificación</p></div>
                                                                <div className='flex pr-2'>
                                                                    <div className='w-full flex justify-center'>
                                                                        <p>1er</p>
                                                                    </div>
                                                                    <div className='w-full flex justify-center'>
                                                                        <p>2do</p>
                                                                    </div>
                                                                    <div className='w-full flex justify-center'>
                                                                        <p>3ro</p>
                                                                    </div>
                                                                    <div className='w-full flex justify-center'>
                                                                        <p>Estatus</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </th>
                                                        <th className='border border-gray-400'>Promedio</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    {
                                                        value.subjects.map((subject, i) => (
                                                            <tr key={subject.id}>
                                                                <td className='border border-gray-400 max-w-5'><p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>{i + 1}</p></td>
                                                                <td className='border border-gray-400 max-w-12'><p className='w-full text-center overflow-hidden overflow-ellipsis px-2 break-words'>{subject.subject_name}</p></td>
                                                                <td className='border border-gray-400 max-w-28'><p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>{subject.teacher_name}</p></td>
                                                                <td className='border border-gray-400 max-w-8'><p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>{subject.hours_imparted}/{subject.hours_per_partial}</p></td>
                                                                <td className='border border-gray-400'>
                                                                    <div className='flex pr-2'>
                                                                        {Array.from({ length: 3 }).map((_, i) => (
                                                                            <p className='w-full text-center'>{subject.grades?.[i]?.score ?? ""}</p>
                                                                        ))}
                                                                        <p className='line-clamp-1 w-full text-center min-w-20 overflow-hidden overflow-ellipsis break-words'>{subject.status}</p>
                                                                    </div>
                                                                </td>
                                                                <td><p className='text-center'>{subject.average_score}</p></td>
                                                            </tr>
                                                        ))
                                                    }
                                                    <tr>
                                                        <td className='border-b border-gray-400' colSpan={6}>
                                                            <div className='w-full flex'>
                                                                <div className='w-full'></div>
                                                                <div className='w-full'></div>
                                                                <div className='border-collapse border-x border-gray-400'>
                                                                    <p className='font-semibold w-24 text-center'>Promedio Semestral</p>
                                                                </div>
                                                                <div className='-mt-[0.5px] flex items-center justify-center w-full border-t border-gray-400'>
                                                                    <p className='text-center'>{value.period_average}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td className='border-l border-b border-white' colSpan={6}>
                                                            <div className='w-full flex'>
                                                                <div className='w-full'></div>
                                                                <div className='w-full'></div>
                                                                <div className='border-collapse border-x border-b border-gray-400'>
                                                                    <p className='font-semibold w-24 text-center'>Promedio General</p>
                                                                </div>
                                                                <div className='-mt-[0.5px] flex items-center justify-center w-full border-b border-gray-400'>
                                                                    <p className='text-center'>{value.general_average_until_now}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    ))
                                    :
                                    <div className='mt-3 w-full flex justify-center items-center'>
                                        <p className='text-base md:text-lg font-semibold'>Sin calificaciones aun</p>
                                    </div>
                            }
                        </div>
                    </div >
                </Modal>

                <Modal className={"mb-4"} show={showModalEdit} fullScreen={true} onDisable={closeModalEdit} aceptModal={false} onClickAccept={false}>
                    <form onSubmit={onSubmit} className='w-full max-h-96 mt-2 pt-4 px-4'>
                        <h3 className='text-center w-full font-semibold text-lg md:text-2xl'>Editar</h3>
                        <div className='lg:max-w-6xl lg:mx-auto mt-4 border rounded-3xl p-2'>
                            <div className='flex items-center justify-between flex-col lg:flex-row'>
                                <Controller
                                    name="matricule"
                                    control={control}
                                    rules={{ required: "La matricula es obligatoria" }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <InputTitleUp
                                                {...field}
                                                className={"lg:w-3/12"}
                                                title={"Matricula"}
                                            />
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                                <Controller
                                    name="name"
                                    control={control}
                                    rules={{ required: "El name es obligatoria" }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <InputTitleUp
                                                {...field}
                                                className={"lg:w-3/12"}
                                                title={"Nombre"}
                                            />
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
                                                className={"lg:w-3/12"}
                                                title={"Apellidos"}
                                            />
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                            </div>

                            <div className='w-full flex items-center justify-between flex-col lg:flex-row gap-2 lg:gap-12 mt-2'>
                                <Controller
                                    name="career"
                                    control={control}
                                    rules={{ required: "La carrera es obligatoria" }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                <SelectInputOption
                                                    value={field.value.career_name}
                                                    setValue={field.onChange}
                                                    options={optionsCarrer}
                                                    valueOption='career_name'
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
                                            <div className={"w-full lg:w-3/12"}>
                                                <SelectInputOption
                                                    value={field.value}
                                                    setValue={field.onChange}
                                                    options={optionsSemester}
                                                    valueOption='semester'
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
                                    rules={{ required: "El grupo es obligatorio" }}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                <SelectInputOption
                                                    value={field.value.group_name}
                                                    setValue={field.onChange}
                                                    valueOption='group_name'
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
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                <SelectInputOption
                                                    value={field?.value?.period_code}
                                                    setValue={field.onChange}
                                                    valueOption='period_code'
                                                    options={optionsPeriod}
                                                    titleSelector={"Selecciona el periodo"}
                                                    title={"Periodo"} />
                                            </div>
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                            </div>

                            <div className='w-full flex items-center justify-between flex-col lg:flex-row gap-2 lg:gap-12 mt-2'>
                                <Controller
                                    name="teacher"
                                    control={control}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                <SelectInputOption
                                                    value={field?.value}
                                                    setValue={field.onChange}
                                                    valueOption='teacher_name'
                                                    options={watch("subjects_with_grades")}
                                                    titleSelector={"Selecciona el profesor"}
                                                    title={"Profesor"}
                                                />
                                            </div>
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                                <Controller
                                    name="subject"
                                    control={control}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                <SelectInputOption
                                                    value={field?.value}
                                                    setValue={field.onChange}
                                                    valueOption='subject_name'
                                                    options={optionsMater}
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
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
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
                                            <div className={"w-full lg:w-3/12"}>
                                                <InputTitleUp
                                                    {...field}
                                                    type='number'
                                                    required={false}
                                                    title={"Horas"}
                                                />
                                            </div>
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                            </div>

                            <div className='w-full flex items-center justify-between flex-col lg:flex-row gap-2 lg:gap-12 mt-2'>
                                <Controller
                                    name="grade.status"
                                    control={control}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                {
                                                    !field?.value
                                                        ?
                                                        <div>
                                                            <SelectInputOption
                                                                titleSelector={"Selecciona el estatus"}
                                                                title={"Estatus"}
                                                            />
                                                        </div>
                                                        :
                                                        <SelectInputOption
                                                            value={field?.value}
                                                            setValue={field.onChange}
                                                            options={optionsStatus}
                                                            titleSelector={"Selecciona el estatus"}
                                                            title={"Estatus"}
                                                        />
                                                }
                                            </div>
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                                <Controller
                                    name="grade.score"
                                    control={control}
                                    render={({ field, fieldState }) => (
                                        <>
                                            <div className={"w-full lg:w-3/12"}>
                                                <InputTitleUp
                                                    {...field}
                                                    required={false}
                                                    type='number'
                                                    title={"Calificación"}
                                                />
                                            </div>
                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                        </>
                                    )}
                                />
                                <div className={"hidden lg:visible lg:block lg:w-7/12"}></div>
                            </div>
                            <div className='mt-4 items-center flex justify-end gap-2'>
                                <Button type="button" onClick={closeModalEdit} className={"w-22 ring-1 ring-black rounded hover:bg-green-400 hover:text-white hover:ring-3 active:text-white active:bg-green-400 active:ring-3"}>Cancelar</Button>
                                <Button className={"w-22 rounded bg-neutral-700 ring-1 ring-neutral-700 text-white hover:bg-neutral-500 hover:ring-3 active:ring-3 active:bg-neutral-700"}>Guardar</Button>
                            </div>
                        </div>
                    </form>
                </Modal>
            </div >
    )
}

export default RatingSeccion1
