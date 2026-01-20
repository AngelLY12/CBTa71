import React, { useState } from 'react'
import InputTitleUp from '../../components/React/InputTitleUp'
import Button from '../../components/React/Button'
import SelectInputOption from '../../components/React/SelectInputOption'

const ContentMatriculeAdd = () => {
    const optionsCarrer = ["Ofimatica", "Administración de Emprendimiento", " Administración de Recursos Humanos", "Agropecuario"];
    const optionsSemester = [1, 2, 3, 4, 5, 6];
    const optionsGroup = ["A", "B", "C", "D"];
    const optionsWorkshop = ["Computo", "Ddasd", "C", "D"];

    const [carrerSelect, setCarrerSelect] = useState("");
    const [matriculeSelect, setMatriculeSelect] = useState("");
    const [groupSelect, setGroupSelect] = useState("");
    const [workshopSelect, setWorkshopSelect] = useState("");


    const clickCancel = () => {
        window.history.back();
    }
    return (
        <div className='border rounded-2xl pt-5 pb-9 px-4 mt-6'>
            <div className='flex flex-col gap-6'>
                <div className='flex-col md:flex-row flex justify-between gap-4'>
                    <InputTitleUp className={"md:w-3/12"} title={"Matricula"} />
                    <InputTitleUp className={"md:w-3/12"} title={"Nombre"} />
                    <InputTitleUp className={"md:w-3/12"} title={"Apellidos"} />
                </div>

                <div className='flex-col md:flex-row flex justify-between gap-4'>
                    <SelectInputOption setValue={setCarrerSelect} options={optionsCarrer} title={"Carrera"} titleSelector={"Selecciona una carrera"} />
                    <SelectInputOption setValue={setMatriculeSelect} options={optionsSemester} title={"Semestre"} titleSelector={"Selecciona el semestre"} />
                    <SelectInputOption setValue={setGroupSelect} options={optionsGroup} title={"Grupo"} titleSelector={"Selecciona el grupo"} />
                    <SelectInputOption setValue={setWorkshopSelect} options={optionsWorkshop} title={"Taller"} titleSelector={"Selecciona un taller"} />
                </div>

                <div className='flex-col md:flex-row flex justify-between gap-4'>
                    <InputTitleUp className={"md:w-3/12"} title={"Usuario"} />
                    <InputTitleUp type='password' className={"md:w-3/12"} title={"Contraseña"} />
                    <div className='hidden md:visible md:block md:w-3/12'></div>
                </div>

                <div className='flex justify-end md:justify-start md:mt-5 gap-4'>
                    <Button onClick={clickCancel} className={"md:w-32 ring-1 rounded hover:bg-green-400 hover:text-white hover:ring-2 hover:ring-black active:bg-green-400 active:text-white active:ring-2 active:ring-black"}>Cancelar</Button>
                    <Button className={"md:w-32 px-4 py-1 ring-1 ring-neutral-700 rounded bg-neutral-700 text-white hover:ring-3 hover:ring-neutral-500 hover:bg-neutral-500 active:ring-3 active:ring-neutral-500 active:bg-neutral-500"}>Guardar</Button>
                </div>
            </div>
        </div >
    )
}

export default ContentMatriculeAdd
