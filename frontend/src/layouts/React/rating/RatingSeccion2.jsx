import React, { use, useState } from 'react'
import InputTitleUp from '../../../components/React/InputTitleUp'
import SelectInputOption from '../../../components/React/SelectInputOption'
import Button from '../../../components/React/Button'
import { routes } from '../../../data/routes'
import Modal from '../../../components/React/Modal'

const RatingSeccion2 = () => {
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

    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    const clickCancel = () => {
        window.location.href = routes.ratings.url;
    }

    const clickSave = async () => {
        await delay(400);
        setShowModalRegist(true);
        await delay(20);
        window.location.href = routes.ratings.url;
    }

    return (
        <div className='border rounded-3xl my-2 pt-4 pb-12 px-4 md:px-6'>
            <div className='md:max-w-6xl md:mx-auto mt-2'>
                <div className='flex items-center justify-between flex-col md:flex-row'>
                    <InputTitleUp value={matricule} setValue={setMatricule} className={"md:w-3/12"} title={"Matricula"} />
                    <InputTitleUp value={name} setValue={setName} className={"md:w-3/12"} title={"Nombre"} />
                    <InputTitleUp value={lastName} setValue={setLastName} className={"md:w-3/12"} title={"Apellidos"} />
                </div>

                <div className='w-full flex items-center justify-between flex-col md:flex-row gap-2 md:gap-12 mt-2'>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectCarrer} options={optionsCarrer} titleSelector={"Selecciona la carrera"} title={"Carrera"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectSemest} options={optionsSemester} titleSelector={"Selecciona el semestre"} title={"Semestre"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectGroup} options={optionsGroup} titleSelector={"Selecciona el grupo"} title={"Grupo"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectPeriod} options={optionsPeriod} titleSelector={"Selecciona el periodo"} title={"Periodo"} />
                    </div>
                </div>

                <div className='w-full flex items-center justify-between flex-col md:flex-row gap-2 md:gap-12 mt-2'>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectTeacher} options={optionsTeacher} titleSelector={"Selecciona el maestro"} title={"Maestro"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectMater} options={optionsMater} titleSelector={"Selecciona la materia"} title={"Materia"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectPartial} options={optionsParcial} titleSelector={"Selecciona el parcial"} title={"Parcial"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <InputTitleUp value={hour} setValue={setHour} type='number' title={"Horas"} />
                    </div>
                </div>

                <div className='w-full flex items-center justify-between flex-col md:flex-row gap-2 md:gap-12 mt-2'>
                    <div className={"w-full md:w-3/12"}>
                        <SelectInputOption setValue={setSelectStatus} options={optionsStatus} titleSelector={"Selecciona el estatus"} title={"Estatus"} />
                    </div>
                    <div className={"w-full md:w-3/12"}>
                        <InputTitleUp value={rang} setValue={setRang} type='number' title={"Calificacion"} />
                    </div>
                    <div className={"hidden  md:visible md:block md:w-3/12"}></div>
                    <div className={"hidden md:visible md:block md:w-3/12"}></div>
                </div>

                <div className='mt-4 items-center flex justify-end md:justify-start gap-2'>
                    <Button onClick={clickCancel} className={"w-22 ring-1 ring-black rounded hover:bg-green-400 hover:text-white hover:ring-3 active:text-white active:bg-green-400 active:ring-3"}>Cancelar</Button>
                    <Button onClick={clickSave} className={"w-22 rounded bg-neutral-700 ring-1 ring-neutral-700 text-white hover:bg-neutral-500 hover:ring-3 active:ring-3 active:bg-neutral-700"}>Guardar</Button>
                </div>
            </div>

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
