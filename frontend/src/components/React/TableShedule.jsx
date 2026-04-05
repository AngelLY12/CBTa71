import { useEffect, useRef, useState } from "react"
import Modal from "./Modal"
import SelectInput from "./SelectInput"
import Button from "./Button"
import api from "./api"
import { urlGlobal } from "../../data/global"
import { userStore } from "../../data/userStore"
import InputTitleUp from "./InputTitleUp"

const TableShedule = ({ className = "", setShowModal = null, options = { smt: [], grp: [], tutor: [], period: [], carrer: [] }, edit = false, updateTable = false, tabletutor_name = false, saveTable, closeTable, setBottonEdit, newTable = false, setValueCell, valueCell = [], headValue = [], setHeadValue, footerValue = [], setFooterValue }) => {
    const [showNewMater, setShowNewMater] = useState(false)
    const [showNewClassroom, setShowNewClassroom] = useState(false)
    const [showMessageSave, setMessageSave] = useState(false);
    const [showTable, setShowTable] = useState(newTable ? false : true)
    const [showNewDatas, setShowNewDatas] = useState(newTable ? true : false)
    const [showNewTime, setShowNewTime] = useState(false);

    const [valueSmtSelect, setValueSmtSelect] = useState("");
    const [valueGrouptSelect, setValueGroupSelect] = useState("");
    const [valueTurntSelect, setValueTurnSelect] = useState("");
    const [valueTutorSelect, setValueTutorSelect] = useState("");
    const [valuePeriodSelect, setValuePeriodSelect] = useState("");
    const [valueCareerSelect, setValueCareerSelect] = useState("");
    const [valueClassroom, setValueClassroom] = useState("");
    const [valueTeacherSelect, setValueTeacherSelect] = useState("");
    const [valueMaterSelect, setValueMaterSelect] = useState("");
    const [valueTimeStart, setTimeStart] = useState("");
    const [valueTimeEnd, setTimeEnd] = useState("");
    const [isValueCellSet, setIsValueCellSet] = useState();
    const [error, setError] = useState("");
    const errorFocus = useRef(null);
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    const [cellRowSelect, setValueCellRowSelect] = useState();
    const [cellColSelect, setCellColSelect] = useState();

    const [optionSmt, setOptionSmt] = useState(options.smt ?? [1, 2, 3, 4])
    const [optionGroup, setOptionGroup] = useState(options.grp ?? ["A", "B", "C", "D"])
    const [optionTutor, setOptionTutor] = useState(options.tutor ?? ["Juan Albert", "Jose Sanchez", "Mario Perez"])
    const [optionPeriod, setOptionPeriod] = useState(options.period ?? ["ENERO/MAYO 2025", "AGO/DIC 2025"])
    const [optionCareer, setOptionCareer] = useState(options.carrer ?? ["Ofimatica", "Administración de Emprendimiento", " Administración de Recursos Humanos", "Agropecuario"])
    const [optionTurn, setOptionTurn] = useState(["Matutino", "Vespertino"])
    const [optionTeacherByMatter, setOptionTeacherByMatter] = useState([])
    const [optionMatter, setOptionMatter] = useState([])

    const days = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"]

    const [headValueNew, setHeadValueNew] = useState();
    const [infoCellNew, setInfoCellNew] = useState();

    const disableNewMater = () => {
        setShowNewMater(false);
    }

    const disableNewTime = () => {
        setShowNewTime(false);
    }

    const disableDatasNew = () => {
        setShowNewDatas(false);
        closeTable();
    }

    const onClickcloseTable = () => {
        closeTable();
    }

    const onSubmitNewTime = (e) => {
        e.preventDefault();

        setInfoCellNew(prev => {
            const lastId = prev.length > 0 ? prev[prev.length - 1].time_block.id : 0;

            const start = valueTimeStart; // valor del input
            const end = valueTimeEnd;     // valor del input

            const newItem = {
                time_block: {
                    id: lastId + 1,
                    label: `Bloque ${lastId + 1} (${start} - ${end})`,
                    start_time: start,
                    end_time: end,
                    sort_order: 1
                },
                slots: Array(5).fill({})
            };

            // Agregar y luego ordenar por start_time
            const updated = [...prev, newItem].sort((a, b) => {
                return a.time_block.start_time.localeCompare(b.time_block.start_time);
            });

            return updated;
        });
        setTimeStart("");
        setTimeEnd("");
        disableNewTime();
    };

    const onClicksaveTable = () => {
        if (!isValueCellSet && newTable) {
            setError("No ha llenado ninguna celda de la tabla con materia y aula")
            errorFocus.current?.focus()
            return;
        } else {
            if (updateTable) {
                setUpdateTable();
            }
            if (newTable) {
                setSaveNewTable();
            }
        }
        if (newTable && isValueCellSet) {
            setValueCell(infoCellNew);
            setHeadValue(headValueNew);
        }
    }

    const setSaveNewTable = async () => {
        const payload = {
            period_id: headValueNew.period.id,
            career_id: headValueNew.career.id,
            semester: headValueNew.semester,
            group_id: headValueNew.group.id,
            tutor_teacher_user_id: headValueNew.tutor.id,
            status: "active",
            time_blocks: infoCellNew
        };
        console.log(payload)
        try {
            const response = await api.post(`${urlGlobal}/class-schedules/store-full`,
                payload,
                {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                });
            setHeadValue(response.data.data.class);
            setValueCell(response.data.data.time_blocks);
            setFooterValue(response.data.data.teacher_summary);
            setMessageSave(true);
            await delay(1000);
            setMessageSave(false);
            closeTable();
        } catch (error) {
            alert(error.response?.data.message);
        }
    }

    const setUpdateTable = async () => {
        const payload = {
            period_id: headValue.period.id ?? valuePeriodSelect.id,
            career_id: headValue.career.id ?? headValueNew.career.id,
            semester: headValue.semester ?? headValueNew.semester,
            group_id: headValue.group.id ?? headValueNew.group.id,
            tutor_teacher_user_id: headValue.tutor.id ?? headValueNew.tutor.id,
            status: "active",
            time_blocks: valueCell
        };
        try {
            const response = await api.patch(`${urlGlobal}/class-schedules/${headValue.id_classShedule}`,
                payload,
                {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                });
            setHeadValue(response.data.data.class);
            setValueCell(response.data.data.time_blocks);
            setFooterValue(response.data.data.teacher_summary);
            setMessageSave(true);
            await delay(1000);
            setMessageSave(false);
            closeTable();
        } catch (error) {
            console.error(error);
        }
    }

    const clickSaveDatasNew = () => {
        setHeadValueNew({ career: valueCareerSelect, group: valueGrouptSelect, period: valuePeriodSelect, tutor: valueTutorSelect, semester: valueSmtSelect.semester })
        getTimeBlocks();
        getMatter();
    }

    const getTimeBlocks = async () => {
        try {
            const response = await api.get(`${urlGlobal}/time-blocks/show-by-turn-create`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
                params: {
                    turn: valueTurntSelect
                }
            });
            setInfoCellNew(response.data.data.timeBlocks);
            setShowNewDatas(false);
            setShowTable(true);
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getMatter = async () => {
        try {
            const response = await api.get(`${urlGlobal}/subjects/by-datas`,
                {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                    params: {
                        career_id: valueCareerSelect.id ?? headValue.career.id,
                        period_id: valuePeriodSelect.id ?? headValue.period.id,
                        semester: valueSmtSelect.semester ?? headValue.semester,
                        group_id: valueGrouptSelect.id ?? headValue.group.id
                    }
                });
            setOptionMatter(response.data.data.subjects)
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getTeacherByMatter = async () => {
        try {
            const response = await api.get(`${urlGlobal}/teachers/by-datas`,
                {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    },
                    params: {
                        subject_id: valueMaterSelect.id,
                        career_id: headValue.career.id ?? valueCareerSelect.id,
                        period_id: headValue.period.id ?? valuePeriodSelect.id,
                        semester: headValue.semester ?? valueSmtSelect.semester,
                        group_id: headValue.group.id ?? valueGrouptSelect.id
                    }
                });
            setOptionTeacherByMatter(response.data.data.teachers)
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const addSlotToBlock = (aulaUpdate = false) => {
        setInfoCellNew(prev => {
            const copy = [...prev]; // copia del array principal

            // copia del bloque seleccionado
            const blockCopy = {
                ...copy[cellRowSelect],
                slots: [...copy[cellRowSelect].slots]
            };

            // slot actual en la columna seleccionada
            const currentSlot = blockCopy.slots[cellColSelect] || {};

            // si ya tiene classroom, lo dejamos; si no, usamos el nuevo
            const classroomValue = valueClassroom ? valueClassroom : currentSlot.classroom;
            // si ya tiene classroom, lo dejamos; si no, usamos el nuevo
            const idValue = currentSlot.id ? currentSlot.id : null;

            if (aulaUpdate) {
                // reemplazar el slot en la columna seleccionada
                blockCopy.slots[cellColSelect] = {
                    ...currentSlot, // conserva lo que ya tenía
                    classroom: classroomValue,
                };
            } else {
                // reemplazar el slot en la columna seleccionada
                blockCopy.slots[cellColSelect] = {
                    ...currentSlot, // conserva lo que ya tenía
                    id: idValue,
                    classroom: currentSlot.classroom,
                    day_of_week: days[cellColSelect].slice(0, 2),
                    subject: valueMaterSelect.subject_name,
                    subject_code: valueMaterSelect.subject_code,
                    subject_offering_id: valueTeacherSelect.subject_ofering_id,
                    teacher: valueTeacherSelect.full_name
                };
            }

            // actualizar el bloque en la copia principal
            copy[cellRowSelect] = blockCopy;
            return copy;
        });
    };

    const updateSlot = (aulaUpdate = false) => {
        setValueCell(prev => {
            const copy = [...prev]; // copia del array principal

            // copia del bloque seleccionado
            const blockCopy = {
                ...copy[cellRowSelect],
                slots: [...copy[cellRowSelect].slots]
            };

            // slot actual en la columna seleccionada
            const currentSlot = blockCopy.slots[cellColSelect] || {};

            // si ya tiene classroom, lo dejamos; si no, usamos el nuevo
            const classroomValue = valueClassroom ? valueClassroom : currentSlot.classroom;
            // si ya tiene classroom, lo dejamos; si no, usamos el nuevo
            const idValue = currentSlot.id ? currentSlot.id : null;

            if (aulaUpdate) {
                // reemplazar el slot en la columna seleccionada
                blockCopy.slots[cellColSelect] = {
                    ...currentSlot, // conserva lo que ya tenía
                    classroom: classroomValue,
                };
            } else {
                // reemplazar el slot en la columna seleccionada
                blockCopy.slots[cellColSelect] = {
                    ...currentSlot, // conserva lo que ya tenía
                    id: idValue,
                    classroom: currentSlot.classroom,
                    day_of_week: days[cellColSelect].slice(0, 2),
                    subject: valueMaterSelect.subject_name,
                    subject_code: valueMaterSelect.subject_code,
                    subject_offering_id: valueTeacherSelect.subject_ofering_id,
                    teacher: valueTeacherSelect.full_name
                };
            }

            // actualizar el bloque en la copia principal
            copy[cellRowSelect] = blockCopy;
            return copy;
        });
    }

    const clickSaveMater = () => {
        if (newTable) {
            addSlotToBlock()
        }
        if (updateTable) {
            updateSlot()
        }
        if (valueTeacherSelect != null && valueMaterSelect != null && !isValueCellSet) {
            setIsValueCellSet(true);
        }

        setValueMaterSelect("")
        setOptionTeacherByMatter([]);

        disableNewMater();
    }


    const clickSaverClassRoom = () => {
        if (newTable) {
            addSlotToBlock(true)
        }
        if (updateTable) {
            updateSlot(true)
        }
        if (valueTeacherSelect != null && valueMaterSelect != null && !isValueCellSet) {
            setIsValueCellSet(true);
        }
        disableNewClassroom();
    }

    const disableNewClassroom = () => {
        setShowNewClassroom(false);
        setValueClassroom("");
    }

    const clickClassroom = (row, col) => {
        if (!edit) return;
        setShowNewClassroom(true);
        setValueCellRowSelect(row);
        setCellColSelect(col);
    }

    const clickMater = (row, col) => {
        if (!edit) return;
        setShowNewMater(true);
        setValueCellRowSelect(row);
        setCellColSelect(col);
    }

    useEffect(() => {
        if (setShowModal) {
            setShowModal(showTable);
        }
    }, [showTable])

    useEffect(() => {
        if (updateTable) {
            getMatter();
        }
    }, [])


    return (
        <>
            {showTable &&
                <div className={`relative mt-2 rounded-4xl px-2 pb-6 ${!(newTable || edit) && "border-2 pb-0"} ${className}`}>
                    {setBottonEdit &&
                        <div className="absolute -top-4 -end-4">
                            <Button title={"Editar horario"} onClick={setBottonEdit} className={"p-0 ring-1 ring-green-400 bg-white rounded-full hover:text-white hover:ring-3 hover:bg-green-400 active:ring-3 active:text-white active:bg-green-400"}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-5 md:size-7">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </Button>
                        </div>
                    }
                    <div className={`w-full overflow-auto mb-3 px-2 my-1 md:px-4 md:mb-0 h-full ${!(newTable || edit) && "py-8 md:mt-6 mt-0"}`}>
                        <input type="text" ref={errorFocus} className={`text-center w-full text-red-700 font-semibold outline-0 focus:outline-0 ${error ? "" : "hidden"}`} readOnly value={error} />
                        {headValue.semester == 0 && !newTable && !updateTable
                            ?
                            <p className="font-semibold text-sm md:text-lg text-center md:mb-12">No hay elementos aun</p>
                            :
                            <div className='flex flex-col min-w-max md:mb-6 md:mx-auto md:max-w-5xl text-xs md:text-base'>
                                <table className='border-collapse border border-gray-400 w-full table-auto'>
                                    <thead className='w-full'>
                                        <tr>
                                            <td colSpan={2} className='border border-gray-300 px-1 md:p-4 text-center break-words'>CBTA NO.71 TLALNEPANTLA, MOR</td>
                                            {!tabletutor_name
                                                ?
                                                <>
                                                    <td className='border border-gray-300'>
                                                        <div className='flex flex-col'>
                                                            {updateTable
                                                                ?
                                                                <div className='flex h-full p-1 w-full justify-center md:max-w-44 md:p-2'><SelectInput valueOption="semester" className="w-full" topTitle={true} titleMovil={"Seleccionar semestre"} title="Semestre" titleEnter={true} filtre={false} setValue={setValueSmtSelect} options={optionSmt}></SelectInput></div>
                                                                :
                                                                <span className='h-full p-1 md:p-2'>Semestre: &nbsp;<b>{!newTable ? headValue.semester : headValueNew.semester}</b></span>
                                                            }
                                                            {updateTable
                                                                ?
                                                                <div className='flex w-full h-full justify-center p-1 md:max-w-44 md:p-2'><SelectInput valueOption="group_name" className={"w-full"} topTitle={true} titleMovil={"Seleccionar grupo"} title="Grupo" titleEnter={true} filtre={false} setValue={setValueGroupSelect} options={optionGroup}></SelectInput></div>
                                                                :
                                                                <span className='flex justify-center items-center h-full p-1 md:p-2'>Grupo: &nbsp;<b>{!newTable ? headValue.group.group_name : headValueNew.group.group_name}</b></span>
                                                            }
                                                        </div>
                                                    </td>
                                                    <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                                        {updateTable
                                                            ?
                                                            <div className='flex md:max-w-44 h-full p-1 md:p-2'><SelectInput valueOption="full_name" className="w-full" topTitle={true} titleMovil={"Seleccionar tutor"} title="Tutor" titleEnter={true} filtre={false} setValue={setValueTutorSelect} options={optionTutor}></SelectInput></div>
                                                            :
                                                            <span className='flex items-center h-full p-1 md:p-2'>Tutor: &nbsp;<b className="">{!newTable ? headValue.tutor.full_name : headValueNew.tutor.full_name}</b></span>
                                                        }
                                                    </td>
                                                </>
                                                :
                                                updateTable &&
                                                <>
                                                    <td className='border border-gray-300'>
                                                        <div className='flex flex-col'>
                                                            <div className='flex w-full h-full p-1 md:p-2'><SelectInput valueOption="semester" topTitle={true} titleMovil={"Seleccionar semestre"} title="Semestre" titleEnter={true} filtre={false} setValue={setValueSmtSelect} options={optionSmt}></SelectInput></div>
                                                            <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar grupo"} title="Grupo" titleEnter={true} filtre={false} setValue={setValueGroupSelect} options={optionGroup}></SelectInput></div>
                                                        </div>
                                                    </td>
                                                    <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                                        <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar tutor"} title="Tutor" titleEnter={true} filtre={false} setValue={setValueTutorSelect} options={optionTutor}></SelectInput></div>
                                                    </td>
                                                </>
                                            }
                                            <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                                {updateTable
                                                    ?
                                                    <div className='flex w-full h-full p-1  md:w-44 md:p-2'><SelectInput valueOption="period_code" className="w-full" topTitle={true} titleMovil={"Seleccionar periodo"} title="Periodo" titleEnter={true} filtre={false} setValue={setValuePeriodSelect} options={optionPeriod}></SelectInput></div>
                                                    :
                                                    <span className='flex justify-center items-center h-full p-1 md:p-2 fo'>{!newTable ? headValue.period.period_code : headValueNew.period.period_code}</span>
                                                }
                                            </td>
                                            <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                                {updateTable
                                                    ?
                                                    <div className='flex w-full h-full p-1 md:p-2 md:w-40'><SelectInput valueOption="career_name" className="w-full" widthText={"max-w-32"} topTitle={true} titleMovil={"Seleccionar carrera"} title="Carrera" titleEnter={true} filtre={false} setValue={setValueCareerSelect} options={optionCareer}></SelectInput></div>
                                                    :
                                                    <span className='flex items-center justify-center h-full p-1 md:p-2'>{!newTable ? headValue.career.career_name : headValueNew.career.career_name}</span>
                                                }
                                            </td>
                                        </tr>
                                    </thead>
                                </table>

                                <table className='-mt-[0.5px] border-collapse border border-gray-400 w-full table-auto'>
                                    <thead >
                                        <tr>
                                            <td className='border border-gray-300 md:p-4 text-center'>Hora</td>
                                            {days.map((day, i) => (
                                                <td key={`day-${i}`} className='border border-gray-300'>
                                                    <div className='flex flex-col'>
                                                        <div className='flex justify-center py-2 px-1 md:p-2 border-b border-gray-300 overflow-hidden'>
                                                            <span>{day}</span>
                                                        </div>
                                                        <div className='flex px-1 py-2 md:p-2 overflow-hidden'>
                                                            <span className='w-full block visible md:hidden text-center'>Mt.</span>
                                                            <span className='w-full hidden md:visible md:block text-center'>Materia</span>
                                                            <span className='w-full block visible md:hidden text-center'>Au.</span>
                                                            <span className='w-full hidden md:visible md:block text-center'>Aula</span>
                                                            <div></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            ))}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {
                                            newTable
                                                ?
                                                Array.isArray(infoCellNew) &&
                                                infoCellNew.map((tur, i) => (
                                                    <tr key={i}>
                                                        <td className='border border-gray-300'>
                                                            <div className='flex flex-col items-center justify-center'>
                                                                <span className='w-full p-2 md:p-2 border-b border-gray-300 text-center'>{tur.time_block.start_time.slice(0, 5)}                                                       </span>
                                                                <span className='w-full p-2 md:p-2 text-center'>{tur.time_block.end_time.slice(0, 5)}</span>
                                                            </div>
                                                        </td>

                                                        {Array.isArray(tur.slots) &&
                                                            tur.slots.map((info, index) => (
                                                                <td key={index} className="border border-gray-300 h-20">
                                                                    <div className="flex h-full min-w-36">
                                                                        <div onClick={() => clickMater(i, index)} className={`flex flex-col justify-center items-start p-1 md:p-2 w-7/12 h-full border-r border-gray-300 ${edit && "hover:bg-gray-300 active:bg-gray-300 cursor-pointer"}`}>
                                                                            <p className="pl-2 text-sm font-bold break-all">{info.subject_code}</p>
                                                                            <p className="pl-2 md:text-xl break-all">{info.subject}</p>
                                                                            <p className="pl-2 text-xs break-all">{info.teacher}</p>
                                                                            {tabletutor_name && info.matter.name != "" &&
                                                                                <>
                                                                                    <p className="text-center">Semestre: {headValue.semester}</p>
                                                                                    <p>Grupo: {headValue.group}</p>
                                                                                </>
                                                                            }
                                                                        </div>
                                                                        <div onClick={() => clickClassroom(i, index)} className={`flex justify-center items-center p-1 md:p-2 w-6/12 h-full text-center ${edit && "hover:bg-gray-300 active:bg-gray-300 cursor-pointer"}`}>
                                                                            {info.classroom}
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            ))
                                                        }
                                                    </tr>
                                                ))
                                                :
                                                Array.isArray(valueCell) &&
                                                valueCell.map((tur, i) => (
                                                    <tr key={i}>
                                                        <td className='border border-gray-300'>
                                                            <div className='flex flex-col items-center justify-center'>
                                                                <span className='w-full p-2 md:p-2 border-b border-gray-300 text-center'>{tur.time_block.start_time.slice(0, 5)}</span>
                                                                <span className='w-full p-2 md:p-2 text-center'>{tur.time_block.end_time.slice(0, 5)}</span>
                                                            </div>
                                                        </td>

                                                        {Array.isArray(tur.slots) &&
                                                            tur.slots.map((info, index) => (
                                                                <td key={index} className="border border-gray-300 h-20">
                                                                    <div className="flex h-full min-w-36">
                                                                        <div onClick={() => clickMater(i, index)} className={`flex flex-col justify-center items-start p-1 md:p-2 w-7/12 h-full border-r border-gray-300 ${edit && "hover:bg-gray-300 active:bg-gray-300 cursor-pointer"}`}>
                                                                            <p className="pl-2 text-sm font-bold break-all">{info.subject_code ?? ""}</p>
                                                                            <p className="pl-2 md:text-xl break-all">{info.subject ?? ""}</p>
                                                                            <p className="pl-2 text-xs break-all">{info.teacher ?? ""}</p>
                                                                            {tabletutor_name && info.matter.name != "" &&
                                                                                <>
                                                                                    <p className="text-center">Semestre: {headValue.semester ?? ""}</p>
                                                                                    <p>Grupo: {headValue.group ?? ""}</p>
                                                                                </>
                                                                            }
                                                                        </div>
                                                                        <div onClick={() => clickClassroom(i, index)} className={`flex justify-center items-center p-1 md:p-2 w-6/12 h-full text-center ${edit && "hover:bg-gray-300 active:bg-gray-300 cursor-pointer"}`}>
                                                                            {info.classroom ?? ""}
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            ))
                                                        }
                                                    </tr>
                                                ))
                                        }
                                    </tbody>
                                </table>

                                {!edit &&
                                    <table className='border-collapse border border-gray-400 w-full mt-2 table-auto'>
                                        <thead>
                                            <tr>
                                                <th className='border border-gray-300 p-1 md:p-2'>Profesor</th>
                                                <th className='border border-gray-300 p-1 md:p-2'>Materia</th>
                                                <th className='border border-gray-300 p-1 md:p-2'>Horas</th>
                                            </tr>
                                        </thead>
                                        <tbody className='text-center'>
                                            {Array.isArray(footerValue) &&
                                                footerValue.flatMap((teacher, i) =>
                                                    teacher.subjects.map((subject, j) => (
                                                        <tr key={`${i}-${j}`}>
                                                            <td className="border border-gray-300 p-1 md:p-2">{teacher.teacher}</td>
                                                            <td className="border border-gray-300 p-1 md:p-2">{subject.subject}</td>
                                                            <td className="border border-gray-300 p-1 md:p-2">{subject.total_hours}</td>
                                                        </tr>
                                                    ))
                                                )
                                            }
                                        </tbody>
                                    </table>
                                }
                            </div>
                        }
                    </div>

                    {
                        edit &&
                        <button onClick={() => setShowNewTime(true)} title="Agregar nueva hora" type="button" className="cursor-pointer mt-8 md:mt-2 flex items-center w-full relative h-3 group">
                            <div className="border rounded h-full w-full group-hover:border-2"></div>
                            <div className="z-10 absolute w-auto -bottom-3/4 left-1/2">
                                <div className="flex items-center justify-center bg-white rounded-full w-9 h-9 border border-indigo-500 group-hover:border-2 group-hover:border-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-4">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>
                            </div>
                        </button>
                    }


                    {edit &&
                        <div className='mt-12 md:mt-5 flex gap-4 px-4 justify-end'>
                            <Button onClick={onClickcloseTable} className={"w-30 rounded ring-1 ring-green-300 hover:ring-3 active:bg-green-300 hover:bg-green-300 text-sm md:text-base"} >Cancelar</Button>
                            <Button onClick={onClicksaveTable} className={"w-30 text-white rounded bg-gray-700 text-sm md:text-base ring-1 ring-gray-700 hover:ring-3"}>{updateTable ? "Actualizar" : "Guardar"}</Button>
                        </div>
                    }
                </div >}

            {
                edit &&
                <>
                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showMessageSave} onDisable={() => setMessageSave(false)} aceptModal={false}>
                        <div className="flex flex-col items-center gap-2 px-4 pt-4 pb-2">
                            <div className="size-32 md:p-0 md:size-48 flex justify-center items-center text-white bg-green-600 rounded-full border-2 border-black">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-28 md:size-32">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <p className="font-semibold mt-2 text-md md:text-lg">{updateTable ? "¡Horario actualizado correctamente!" : "¡Horario creado correctamente!"}</p>
                        </div>
                    </Modal>


                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showNewTime} onDisable={disableNewTime} aceptModal={false}>
                        <div className="px-4 pt-4 pb-2">
                            <form onSubmit={onSubmitNewTime}>
                                <h3 className='text-center text-md md:text-xl font-semibold mb-3'>Registrar nueva hora</h3>
                                <div className="flex flex-col">
                                    <div className="mt-2 flex w-full">
                                        <InputTitleUp value={valueTimeStart} onChange={(e) => setTimeStart(e.target.value)} title="Inicial" type="time" />
                                    </div>
                                    <div className="mt-2 flex w-full">
                                        <InputTitleUp value={valueTimeEnd} onChange={(e) => setTimeEnd(e.target.value)} title="Final" type="time" />
                                    </div>
                                </div>
                                <div className="flex justify-center mt-4">
                                    <Button className={"rounded bg-neutral-600 px-3 ring-1 ring-neutral-600 hover:ring-3 active:ring-3 text-white"}>Guardar</Button>
                                </div>
                            </form>
                        </div>
                    </Modal>

                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showNewMater} onDisable={disableNewMater} aceptModal={false}>
                        <div className="px-4 pt-4 pb-2">
                            <h3 className='text-center text-md md:text-xl font-semibold mb-3'>Registrar nueva materia</h3>
                            <div className="flex flex-col">
                                <div className="mt-2 flex w-full">
                                    <SelectInput notSelectDefault={true} setOption={getTeacherByMatter} valueOption="subject_name" classNameMovil="w-full" className="w-full" filtre={false} titleMovil={"Selecionar Materia"} titleEnter={false} setValue={setValueMaterSelect} options={optionMatter} title="Materia" topTitle={true} />
                                </div>
                                {
                                    (valueMaterSelect && optionTeacherByMatter.length > 0) &&
                                    <div className="mt-2 flex w-full">
                                        <SelectInput valueOption="full_name" classNameMovil="w-full" className="w-full" filtre={false} titleMovil={"Selecionar profesor"} titleEnter={false} setValue={setValueTeacherSelect} options={optionTeacherByMatter} title="Profesor" topTitle={true} />
                                    </div>
                                }
                            </div>
                            <div className="flex justify-center mt-4">
                                <Button onClick={clickSaveMater} className={"rounded bg-neutral-600 px-3 ring-1 ring-neutral-600 hover:ring-3 active:ring-3 text-white"}>Guardar</Button>
                            </div>
                        </div>
                    </Modal>

                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showNewDatas} onDisable={disableDatasNew} aceptModal={false}>
                        <div className="px-4 pt-4 pb-2">
                            <h3 className='text-center text-md md:text-xl font-semibold mb-3'>Selección de datos</h3>
                            <div className="flex flex-col gap-2">
                                <div className="flex items-center gap-2">
                                    <div className="mt-2 flex w-full">
                                        <SelectInput valueOption='career_name' classNameMovil="w-full" className="w-full" topTitle={true} titleEnter={false} titleMovil={"Seleccionar carrera"} title="Carrera" filtre={false} setValue={setValueCareerSelect} options={optionCareer}></SelectInput>
                                    </div>
                                    <div className="mt-2 flex w-20">
                                        <SelectInput valueOption='semester' classNameMovil="w-full" className="w-full" topTitle={true} titleMovil={"Selecciona semestre"} title="Semestre" titleEnter={false} filtre={false} setValue={setValueSmtSelect} options={optionSmt}></SelectInput>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <div className="mt-2 flex w-full">
                                        <SelectInput valueOption="period_code" classNameMovil="w-full" className="w-full" topTitle={true} titleMovil={"Seleccionar periodo"} title="Periodo" titleEnter={false} filtre={false} setValue={setValuePeriodSelect} options={optionPeriod}></SelectInput>
                                    </div>

                                    <div className="mt-2 flex w-20">
                                        <SelectInput valueOption="group_name" classNameMovil="w-full" className="w-full" topTitle={true} titleMovil={"Seleccionar grupo"} title="Grupo" titleEnter={false} filtre={false} setValue={setValueGroupSelect} options={optionGroup}></SelectInput>
                                    </div>
                                </div>
                                <div className="flex w-full gap-2 items-center">
                                    <div className="mt-2 flex w-full">
                                        <SelectInput valueOption="full_name" classNameMovil="w-full" className="w-full" topTitle={true} titleMovil={"Seleccionar tutor"} title="Tutor" titleEnter={false} filtre={false} setValue={setValueTutorSelect} options={optionTutor}></SelectInput>
                                    </div>
                                    <div className="mt-2 flex w-20">
                                        <SelectInput className="w-full" classNameMovil="w-full" topTitle={true} titleMovil={"Seleccionar turno"} title="Turno" titleEnter={false} filtre={false} setValue={setValueTurnSelect} options={optionTurn}></SelectInput>
                                    </div>
                                </div>
                            </div>
                            <div className="flex justify-center mt-4">
                                <Button onClick={clickSaveDatasNew} className={"rounded bg-neutral-600 px-3 ring-1 ring-neutral-600 hover:ring-3 active:ring-3 text-white"}>Guardar</Button>
                            </div>
                        </div>
                    </Modal>

                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showNewClassroom} onDisable={disableNewClassroom} aceptModal={false}>
                        <div className="px-4 pt-4 pb-2">
                            <h3 className='text-center text-md md:text-xl font-semibold mb-3'>Registrar aula</h3>
                            <div className="">
                                <label className="flex flex-col font-semibold">
                                    <InputTitleUp title={"Aula"} value={valueClassroom} onChange={(e) => setValueClassroom(e.target.value)} className="font-normal" />
                                </label>
                            </div>
                            <div className="flex justify-center mt-4">
                                <Button onClick={clickSaverClassRoom} className={"rounded bg-neutral-600 px-3 ring-1 ring-neutral-600 hover:ring-3 active:ring-3 text-white"}>Guardar</Button>
                            </div>
                        </div>
                    </Modal>
                </>
            }
        </>
    )
}

export default TableShedule
