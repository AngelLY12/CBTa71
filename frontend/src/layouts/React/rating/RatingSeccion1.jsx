import React, { useState } from 'react'
import InputSearch from '../../../components/React/InputSearch'
import Button from '../../../components/React/Button';
import Modal from '../../../components/React/Modal';
import CardsListInfo from '../../../components/React/CardsListInfo';
import SelectInputOption from '../../../components/React/SelectInputOption';
import SelectInput from '../../../components/React/SelectInput';
import InputTitleUp from '../../../components/React/InputTitleUp';

const RatingSeccion1 = () => {
    const [selectCarrerFiltre, setSelectCarrerFiltre] = useState("")
    const [selectSemesterFiltre, setSelectSemesterFiltre] = useState(-1);

    const [matricule, setMatricule] = useState("")
    const [name, setName] = useState("");
    const [lastName, setLastName] = useState("")
    const [selectCarrer, setSelectCarrer] = useState("Ofimatica")
    const [selectSemest, setSelectSemest] = useState("")
    const [selectGroup, setSelectGroup] = useState("")
    const [selectPeriod, setSelectPeriod] = useState(-1)
    const [selectTeacher, setSelectTeacher] = useState("");
    const [selectMater, setSelectMater] = useState("");
    const [selectPartial, setSelectPartial] = useState(-1)
    const [hour, setHour] = useState(0)
    const [selectStatus, setSelectStatus] = useState("")
    const [rang, setRang] = useState(0);
    const [showModalRegist, setShowModalRegist] = useState(false);

    const [optionsCarrer, setOptionsCarrer] = useState(["Ofimatica", "Administración de Emprendimiento", " Administración de Recursos Humanos", "Agropecuario"]);
    const [optionsSemester, setOptionsSemester] = useState([1, 2, 3, 4, 5, 6]);
    const [optionsGroup, setOptionsGroup] = useState(["A", "B", "C", "D"]);
    const [optionsPeriod, setOptionsPerior] = useState(["ENERO/MAYO 2025", "AGO/DIC 2025"]);
    const [optionsTeacher, setOptionsTeacher] = useState(["Juan Antonio", "Sanchez Perez"]);
    const [optionsMater, setOptionsMater] = useState(["Quimica", "Matematicas", "Español"]);
    const [optionsParcial, setOptionsParcial] = useState([1, 2, 3, 4]);
    const [optionsStatus, setOptionsStatus] = useState(["Aprobado", "Reprobado"]);

    const heads = ["Id", "Matricula", "Nombre", "Apellidos", "Carrera", "Semestre", "Grupo", "Estatus", "Promedio General"];
    const dates = ["id", "matricula", "nombre", "apellidos", "carrera", "semestre", "grupo", "estatus", "promedio_general"]
    const [valueStudents, setValueStudents] = useState(
        [
            { id: 1, matricula: "21900123", nombre: "Juanito", apellidos: "Esparra", carrera: "Ofimatica", semestre: 5, grupo: "A", estatus: "Aprobado", promedio_general: 7.5 },
            { id: 2, matricula: "21900124", nombre: "Jhoana", apellidos: "Villanueva Yañez", carrera: "Ofimatica", semestre: 5, grupo: "A", estatus: "Aprobado", promedio_general: 8.5 }
        ]
    );
    const [showModalRang, setShowModalRang] = useState(false);
    const [showModalEdit, setShowModalEdit] = useState(false);

    const [searchStudent, setSearchStudent] = useState("")
    const [studentSelect, setStudentSelect] = useState({ id: -1, matricula: "", nombre: "", apelldios: "", carrera: "", semestre: 0, grupo: "", estatus: "", promedio_general: 0 });
    const [infoStudentEdit, setInfoStudentEdit] = useState("");

    const closeModalRang = () => {
        setShowModalRang(false);
    }

    const getStudent = async () => {
        // try {
        //     const response = await fetch(`/api/personal?search=${searchTeacher}`);
        //     if (!response.ok) {
        //         throw new Error(`HTTP error! status: ${response.status}`);
        //     }
        //     const data = await response.json();
        //     setPersonalResponse(data);
        // } catch (error) {
        //     console.error("Error fetching personal data:", error);
        // }
    }

    const onClickSelectStudent = (student) => {
        setStudentSelect(student);
        setShowModalRang(true);
    }

    const onClickEditStudent = async (student) => {
        // try {
        //     const response = await fetch(`/api/personal?search=${student.id}`);
        //     if (!response.ok) {
        //         throw new Error(`HTTP error! status: ${response.status}`);
        //     }
        //     const data = await response.json();
        //     setPersonalResponse(data);
        // } catch (error) {
        //     console.error("Error fetching personal data:", error);
        // }
        setShowModalEdit(true);
    }

    return (
        <div className='mt-5'>
            <div className='flex h-10 justify-between items-center'>
                <div className='h-full md:w-4/12'>
                    <InputSearch options={valueStudents} getOptions={getStudent} valueSearch={["nombre", "apellidos"]} title={"Buscar"} value={searchStudent} setValue={setSearchStudent} />
                </div>
                <div className='flex items-center h-full gap-2'>
                    <p className='font-semibold w-auto text-sm hidden lg:visible lg:block md:text-base'>Filtrar por</p>
                    <SelectInput upperCase={true} className={"md:h-full md:w-60 md:z-20"} options={optionsCarrer} setOption={() => { }} setValue={setSelectCarrerFiltre} title='Carrera' >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </SelectInput>
                    <SelectInput className={"md:h-full md:z-20"} options={optionsSemester} setOption={() => { }} setValue={setSelectSemesterFiltre} title='Semestre' >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                        </svg>
                    </SelectInput>
                </div>
            </div>

            <div className='mt-2 pb-12 overflow-auto'>
                <div className='pt-2 md:pl-2 md:pr-5 gap-4 mt-4 hidden md:visible md:flex md:flex-col'>
                    {valueStudents.map((student) => (
                        <div className='relative min-w-max overflow-visible'>
                            <Button onClick={() => onClickEditStudent(student)} className={"w-9 h-9 ring-2 absolute -top-5 -right-4 bg-white rounded-full z-10"}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </Button>
                            <table className='relative w-full table-auto border-collapse border border-gray-400'>
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
                                            <td key={`data-${i}`} className='max-w-12 border border-gray-400 p-1.5'>
                                                <div>

                                                    {i != 1 ?
                                                        <p className='w-full text-center overflow-hidden overflow-ellipsis'>{student[data]}</p>
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

                <CardsListInfo onClickCard={onClickSelectStudent} datesCard={["matricula", "estatus"]} className={"md:hidden"} items={valueStudents}></CardsListInfo>
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
                                                    <p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>{studentSelect[data]}</p>
                                                    :
                                                    <p className='w-full text-indigo-600 text-center'>{studentSelect[data]}</p>
                                                }
                                            </div>
                                        </td>
                                    ))}
                                </tr>
                            </tbody>
                        </table>

                        <div className='w-full mt-4 pb-4'>
                            <h3 className='font-bold text-center border border-gray-400'>Agosto/Diciembre 2025</h3>
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
                                        Array.from({ length: 5 }, () => 0).map((_, i) => (
                                            <tr>
                                                <td className='border border-gray-400 max-w-5'><p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>{i + 1}</p></td>
                                                <td className='border border-gray-400 max-w-12'><p className='w-full text-center overflow-hidden overflow-ellipsis px-2 break-words'>Quimica</p></td>
                                                <td className='border border-gray-400 max-w-28'><p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>Susan Perez</p></td>
                                                <td className='border border-gray-400 max-w-8'><p className='w-full text-center overflow-hidden overflow-ellipsis break-words'>96/96</p></td>
                                                <td className='border border-gray-400'>
                                                    <div className='flex pr-2'>
                                                        <p className='w-full text-center'>7.5</p>
                                                        <p className='w-full text-center'>8.2</p>
                                                        <p className='w-full text-center'>8.5</p>
                                                        <p className='w-full text-center min-w-20 overflow-hidden overflow-ellipsis break-words'>Aprobado</p>
                                                    </div>
                                                </td>
                                                <td><p className='text-center'>8.0</p></td>
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
                                                    <p className='text-center'>8.3</p>
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
                                                    <p className='text-center'>8.3</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div >

            </Modal>

            <Modal show={showModalEdit} fullScreen={true} onDisable={() => setShowModalEdit(false)} aceptModal={false} onClickAccept={false}>
                <div className='w-full max-h-96 my-2 pt-4 px-4'>
                    <h3 className='text-center w-full font-semibold text-lg md:text-2xl'>Editar</h3>
                    <div className='lg:max-w-6xl lg:mx-auto mt-2 border rounded-3xl px-2 pt-2 pb-4'>
                        <div className='flex items-center justify-between flex-col lg:flex-row'>
                            <InputTitleUp value={matricule} setValue={setMatricule} className={"lg:w-3/12"} title={"Matricula"} />
                            <InputTitleUp value={name} setValue={setName} className={"lg:w-3/12"} title={"Nombre"} />
                            <InputTitleUp value={lastName} setValue={setLastName} className={"lg:w-3/12"} title={"Apellidos"} />
                        </div>

                        <div className='w-full flex items-center justify-between flex-col lg:flex-row gap-2 lg:gap-12 mt-2'>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectCarrer} setValue={setSelectCarrer} options={optionsCarrer} titleSelector={"Selecciona la carrera"} title={"Carrera"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectSemest} setValue={setSelectSemest} options={optionsSemester} titleSelector={"Selecciona el semestre"} title={"Semestre"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectGroup} setValue={setSelectGroup} options={optionsGroup} titleSelector={"Selecciona el grupo"} title={"Grupo"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectPeriod} setValue={setSelectPeriod} options={optionsPeriod} titleSelector={"Selecciona el periodo"} title={"Periodo"} />
                            </div>
                        </div>

                        <div className='w-full flex items-center justify-between flex-col lg:flex-row gap-2 lg:gap-12 mt-2'>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectTeacher} setValue={setSelectTeacher} options={optionsTeacher} titleSelector={"Selecciona el maestro"} title={"Maestro"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectMater} setValue={setSelectMater} options={optionsMater} titleSelector={"Selecciona la materia"} title={"Materia"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectPartial} setValue={setSelectPartial} options={optionsParcial} titleSelector={"Selecciona el parcial"} title={"Parcial"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <InputTitleUp value={hour} setValue={setHour} type='number' title={"Horas"} />
                            </div>
                        </div>

                        <div className='w-full flex items-center justify-between flex-col lg:flex-row gap-2 lg:gap-12 mt-2'>
                            <div className={"w-full lg:w-3/12"}>
                                <SelectInputOption value={selectStatus} setValue={setSelectStatus} options={optionsStatus} titleSelector={"Selecciona el estatus"} title={"Estatus"} />
                            </div>
                            <div className={"w-full lg:w-3/12"}>
                                <InputTitleUp value={rang} setValue={setRang} type='number' title={"Calificacion"} />
                            </div>
                            <div className={"hidden lg:visible lg:block lg:w-3/12"}></div>
                            <div className={"hidden lg:visible lg:block lg:w-3/12"}></div>
                        </div>

                        <div className=' mt-4 items-center flex justify-end gap-2'>
                            <Button className={"w-22 ring-1 ring-black rounded hover:bg-green-400 hover:text-white hover:ring-3 active:text-white active:bg-green-400 active:ring-3"}>Cancelar</Button>
                            <Button className={"w-22 rounded bg-neutral-700 ring-1 ring-neutral-700 text-white hover:bg-neutral-500 hover:ring-3 active:ring-3 active:bg-neutral-700"}>Guardar</Button>
                        </div>
                    </div>
                </div>
            </Modal>
        </div >
    )
}

export default RatingSeccion1
