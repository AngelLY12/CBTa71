import { useRef, useState } from "react"
import Modal from "./Modal"
import SelectInput from "./SelectInput"
import Button from "./Button"

const TableShedule = ({ options = { smt: [], grp: [], tutor: [], period: [], carrer: [] }, edit = false, updateTable = false, tableTeacher = false, saveTable, closeTable, setBottonEdit, newTable = false, turnSelect = 0, setValueCell, valueCell = [], headValue = [], setHeadValue }) => {
    const [showNewMater, setShowNewMater] = useState(false)
    const [showNewClassroom, setShowNewClassroom] = useState(false)
    const [showMessageSave, setMessageSave] = useState(false);

    const [valueSmtSelect, setValueSmtSelect] = useState();
    const [valueGrouptSelect, setValueGroupSelect] = useState();
    const [valueTutorSelect, setValueTutorSelect] = useState();
    const [valuePeriodSelect, setValuePeriodSelect] = useState();
    const [valueCareerSelect, setValueCareerSelect] = useState();
    const [valueClassroom, setValueClassroom] = useState("");
    const [valueID, setValueID] = useState();
    const [valueMater, setValueMater] = useState();
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

    const [optionID, setOptionID] = useState(["1", "2"])
    const [optionMatter, setOptionMatter] = useState(["Fisica", "Matematicas", "Español"])

    const days = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"]
    const hours =
        [
            [{ hourIn: "7:00", hourOut: "7:50" }, { hourIn: "8:00", hourOut: "8:50" }, { hourIn: "9:00", hourOut: "9:50" }, { hourIn: "10:00", hourOut: "10:50" }, { hourIn: "11:00", hourOut: "11:50" }],
            [{ hourIn: "2:00", hourOut: "2:50" }, { hourIn: "3:00", hourOut: "3:50" }]
        ];

    const [headValueNew, setHeadValueNew] = useState({ semester: 1, group: "D", tutor: "Juan Carlos", period: "AGO/DIC 2025", career: "OFIMATICA" });
    const [infoCellNew, setInfoCellNew] = useState(
        [
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
        ]
    );

    const disableNewMater = () => {
        setShowNewMater(false);
    }

    const onClickcloseTable = () => {
        closeTable();
    }

    const onClicksaveTable = async () => {
        if (!isValueCellSet && newTable) {
            setError("No ha llenado ninguna celda de la tabla con materia y aula")
            errorFocus.current?.focus()
            return;
        } else {
            setMessageSave(true);
            await delay(1000);
            setMessageSave(false);
            saveTable()
            setInfoCellNew(prev => {
                const copy = [...prev];    // copia del array principal
                copy[cellRowSelect] = ""; // copia de la fila
                return copy;
            });
        }

        if (newTable && isValueCellSet) {
            setValueCell(infoCellNew);
            setHeadValue({ semester: valueSmtSelect, group: valueGrouptSelect, tutor: valueTutorSelect, period: valuePeriodSelect, career: valueCareerSelect });
        }
    }

    const clickSaveMater = () => {
        if (newTable) {
            setInfoCellNew(prev => {
                const copy = [...prev];              // copia del array principal
                copy[cellRowSelect] = [...copy[cellRowSelect]]; // copia de la fila
                copy[cellRowSelect][cellColSelect].matter.teacher = `${valueID}`;  // actualiza la celda
                copy[cellRowSelect][cellColSelect].matter.name = `${valueMater}`;  // actualiza la celda
                return copy;
            });
        } else if (updateTable) {
            setValueCell(prev => {
                const copy = [...prev];              // copia del array principal
                copy[cellRowSelect] = [...copy[cellRowSelect]]; // copia de la fila
                copy[cellRowSelect][cellColSelect].matter.teacher = `${valueID}`;  // actualiza la celda
                copy[cellRowSelect][cellColSelect].matter.name = `${valueMater}`;  // actualiza la celda
                return copy;
            });
        }
        if (valueID != null && valueMater != null && !isValueCellSet) {
            setIsValueCellSet(true);
        }
        disableNewMater();
    }

    const clickSaverClassRoom = () => {
        if (newTable) {
            setInfoCellNew(prev => {
                const copy = [...prev];              // copia del array principal
                copy[cellRowSelect] = [...copy[cellRowSelect]]; // copia de la fila
                copy[cellRowSelect][cellColSelect].classroom = valueClassroom;  // actualiza la celda
                return copy;
            });
        } else if (updateTable) {
            setValueCell(prev => {
                const copy = [...prev];              // copia del array principal
                copy[cellRowSelect] = [...copy[cellRowSelect]]; // copia de la fila
                copy[cellRowSelect][cellColSelect].classroom = valueClassroom;  // actualiza la celda
                return copy;
            });
        }
        if (valueID != null && valueMater != null && !isValueCellSet) {
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

    return (
        <>
            <div className={`relative mt-2 rounded-4xl px-2  ${!(newTable || edit) && "border-2 "}`}>
                {headValue.semester != 0 && !edit &&
                    <div className="absolute -top-4 -end-4">
                        <Button title={"Editar horario"} onClick={setBottonEdit} className={"p-0 ring-1 ring-green-400 bg-white rounded-full hover:text-white hover:ring-3 hover:bg-green-400 active:ring-3 active:text-white active:bg-green-400"}>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-5 md:size-7">
                                <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </Button>
                    </div>
                }
                <div className={`w-full overflow-auto mb-3 px-2 my-1 md:px-4 md:mb-0 h-full ${!(newTable || edit) && "py-8 md:mt-6 mt-0"}`}>
                    <input type="text" ref={errorFocus} className="text-center w-full text-red-700 font-semibold outline-0 focus:outline-0" readOnly value={error} />
                    {headValue.semester == 0 && !newTable && !updateTable
                        ?
                        <p className="font-semibold text-sm md:text-lg text-center md:mb-12">No hay elementos aun</p>
                        :
                        <div className='flex flex-col min-w-max md:mb-6 md:mx-auto md:max-w-5xl text-xs md:text-base'>
                            <table className='border-collapse border border-gray-400 w-full table-auto'>
                                <thead className='w-full'>
                                    <tr>
                                        <td colSpan={2} className='border border-gray-300 px-1 md:p-4 text-center break-words'>CBTA NO.71 TLALNEPANTLA, MOR</td>
                                        {!tableTeacher
                                            ?
                                            <>
                                                <td className='border border-gray-300'>
                                                    <div className='flex flex-col'>
                                                        {newTable
                                                            ?
                                                            <div className='flex h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar semestre"} title="Semestre" titleEnter={true} filtre={false} setValue={setValueSmtSelect} options={optionSmt}></SelectInput></div>
                                                            :
                                                            <span className='h-full p-1 md:p-2'>Semestre: &nbsp;<b>{headValue.semester}</b></span>
                                                        }

                                                        {newTable
                                                            ?
                                                            <div className='flex w-full h-full p-1 md:p-2'><SelectInput className={"w-full"} topTitle={true} titleMovil={"Seleccionar grupo"} title="Grupo" titleEnter={true} filtre={false} setValue={setValueGroupSelect} options={optionGroup}></SelectInput></div>
                                                            :
                                                            <span className='flex justify-center items-center h-full p-1 md:p-2'>Grupo: &nbsp;<b>{headValue.group}</b></span>
                                                        }
                                                    </div>
                                                </td>
                                                <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                                    {newTable
                                                        ?
                                                        <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar tutor"} title="Tutor" titleEnter={true} filtre={false} setValue={setValueTutorSelect} options={optionTutor}></SelectInput></div>
                                                        :
                                                        <span className='flex items-center h-full p-1 md:p-2'>Tutor: &nbsp;<b className="">{headValue.tutor}</b></span>
                                                    }
                                                </td>
                                            </>
                                            :
                                            newTable &&
                                            <>
                                                <td className='border border-gray-300'>
                                                    <div className='flex flex-col'>
                                                        <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar semestre"} title="Semestre" titleEnter={true} filtre={false} setValue={setValueSmtSelect} options={optionSmt}></SelectInput></div>
                                                        <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar grupo"} title="Grupo" titleEnter={true} filtre={false} setValue={setValueGroupSelect} options={optionGroup}></SelectInput></div>
                                                    </div>
                                                </td>
                                                <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                                    <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar tutor"} title="Tutor" titleEnter={true} filtre={false} setValue={setValueTutorSelect} options={optionTutor}></SelectInput></div>
                                                </td>
                                            </>
                                        }
                                        <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                            {newTable
                                                ?
                                                <div className='flex w-full h-full p-1 md:p-2'><SelectInput topTitle={true} titleMovil={"Seleccionar periodo"} title="Periodo" titleEnter={true} filtre={false} setValue={setValuePeriodSelect} options={optionPeriod}></SelectInput></div>
                                                :
                                                <span className='flex justify-center items-center h-full p-1 md:p-2 fo'>{headValue.period}</span>
                                            }
                                        </td>
                                        <td className='border border-gray-300 p-2 md:p-4 text-center'>
                                            {newTable
                                                ?
                                                <div className='flex w-full h-full p-1 md:p-2'><SelectInput widthText={"max-w-32"} upperCase={true} topTitle={true} titleMovil={"Seleccionar carrera"} title="Carrera" titleEnter={true} filtre={false} setValue={setValueCareerSelect} options={optionCareer}></SelectInput></div>
                                                :
                                                <span className='flex items-center justify-center h-full p-1 md:p-2 uppercase'>{headValue.career}</span>
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
                                    {hours[turnSelect].map((tur, i) => (
                                        <tr key={i}>
                                            <td className='border border-gray-300'>
                                                <div className='flex flex-col items-center justify-center'>
                                                    <span className='w-full p-2 md:p-2 border-b border-gray-300 text-center'>{tur.hourIn}</span>
                                                    <span className='w-full p-2 md:p-2 text-center'>{tur.hourOut}</span>
                                                </div>
                                            </td>
                                            {newTable ?
                                                infoCellNew[i].map((info, index) => (
                                                    <td key={index} className="border border-gray-300 h-20 break-words">
                                                        <div className="flex h-full min-w-36">
                                                            <div onClick={() => clickMater(i, index)} className={`flex flex-col justify-center items-center p-1 md:p-2 w-full h-full border-r border-gray-300 hover:bg-gray-300 active:bg-gray-300 cursor-pointer`}>
                                                                <p>{info.matter.name}</p>
                                                                <p>{info.matter.teacher}</p>
                                                            </div>
                                                            <div onClick={() => clickClassroom(i, index)} className={`flex justify-center items-center p-1 md:p-2 w-full h-full text-center hover:bg-gray-300 active:bg-gray-300 cursor-pointer`}>
                                                                {info.classroom}
                                                            </div>
                                                        </div>
                                                    </td>
                                                )) :
                                                valueCell[i].map((info, index) => (
                                                    <td key={index} className="border border-gray-300 h-20 break-words">
                                                        <div className="flex h-full min-w-36">
                                                            <div onClick={() => clickMater(i, index)} className={`flex flex-col justify-center items-center p-1 md:p-2 w-full h-full border-r border-gray-300 ${edit && "hover:bg-gray-300 active:bg-gray-300 cursor-pointer"}`}>
                                                                <p>{info.matter.name}</p>
                                                                <p>{info.matter.teacher}</p>
                                                                {tableTeacher && info.matter.name != "" &&
                                                                    <>
                                                                        <p className="text-center">Semestre: {headValue.semester}</p>
                                                                        <p>Grupo: {headValue.group}</p>
                                                                    </>
                                                                }
                                                            </div>
                                                            <div onClick={() => clickClassroom(i, index)} className={`flex justify-center items-center p-1 md:p-2 w-full h-full text-center ${edit && "hover:bg-gray-300 active:bg-gray-300 cursor-pointer"}`}>
                                                                {info.classroom}
                                                            </div>
                                                        </div>
                                                    </td>
                                                ))}
                                        </tr>
                                    ))
                                    }
                                </tbody>
                            </table>

                            {!newTable &&
                                <table className='border-collapse border border-gray-400 w-full mt-2 table-auto'>
                                    <thead>
                                        <tr>
                                            <th className='border border-gray-300 p-1 md:p-2'>Profesor</th>
                                            <th className='border border-gray-300 p-1 md:p-2'>Materia</th>
                                            <th className='border border-gray-300 p-1 md:p-2'>Horas</th>
                                        </tr>
                                    </thead>
                                    <tbody className='text-center'>
                                        <tr>
                                            <td className='border border-gray-300 p-1 md:p-2'>Susan Garcia</td>
                                            <td className='border border-gray-300 p-1 md:p-2'>Quimica</td>
                                            <td className='border border-gray-300 p-1 md:p-2'>4</td>
                                        </tr>
                                        <tr>
                                            <td className='border border-gray-300 p-2'>Juan Ibarra</td>
                                            <td className='border border-gray-300 p-2'>Fisica</td>
                                            <td className='border border-gray-300 p-2'>3</td>
                                        </tr>
                                        <tr>
                                            <td className='border border-gray-300 p-2'>Jesus Corona</td>
                                            <td className='border border-gray-300 p-2'>Ciencias naturales</td>
                                            <td className='border border-gray-300 p-2'>3</td>
                                        </tr>
                                        <tr>
                                            <td className='border border-gray-300 p-2'>Salama Mendez</td>
                                            <td className='border border-gray-300 p-2'>Pensamiento Critico</td>
                                            <td className='border border-gray-300 p-2'>4</td>
                                        </tr>
                                    </tbody>
                                </table>
                            }
                        </div>
                    }
                </div>
            </div >

            {edit &&
                <div className='mt-2 flex gap-4 px-4 justify-end'>
                    <Button onClick={onClickcloseTable} className={"w-30 rounded ring-1 ring-green-300 hover:ring-3 active:bg-green-300 hover:bg-green-300 text-sm md:text-base"} >Cancelar</Button>
                    <Button onClick={onClicksaveTable} className={"w-30 text-white rounded bg-gray-700 text-sm md:text-base ring-1 ring-gray-700 hover:ring-3"}>Guardar</Button>
                </div>
            }
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
                            <p className="font-semibold mt-2 text-md md:text-lg">¡Horario creado correctamente!</p>
                        </div>
                    </Modal>

                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showNewMater} onDisable={disableNewMater} aceptModal={false}>
                        <div className="px-4 pt-4 pb-2">
                            <h3 className='text-center text-md md:text-xl font-semibold mb-3'>Registrar nueva materia</h3>
                            <div className="flex flex-col">
                                <SelectInput filtre={false} titleMovil={"Selecionar ID"} titleEnter={false} setValue={setValueID} options={optionID} title="ID" topTitle={true}></SelectInput>
                                <SelectInput filtre={false} titleMovil={"Selecionar Materia"} titleEnter={false} setValue={setValueMater} options={optionMatter} title="Materia" topTitle={true}></SelectInput>
                            </div>
                            <div className="flex justify-center mt-4">
                                <Button onClick={clickSaveMater} className={"rounded bg-neutral-600 px-3 ring-1 ring-neutral-600 hover:ring-3 active:ring-3 text-white"}>Guardar</Button>
                            </div>
                        </div>
                    </Modal>

                    <Modal overlap={true} className={"h-56 overflow-visible"} show={showNewClassroom} onDisable={disableNewClassroom} aceptModal={false}>
                        <div className="px-4 pt-4 pb-2">
                            <h3 className='text-center text-md md:text-xl font-semibold mb-3'>Registrar nueva aula</h3>
                            <div className="">
                                <label className="flex flex-col font-semibold">
                                    <p>Aula</p>
                                    <input required value={valueClassroom} onChange={(e) => setValueClassroom(e.target.value)} className="py-0.5 px-2 font-normal outline-1 focus:outline-green-700 focus:outline-2" type="text" />
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
