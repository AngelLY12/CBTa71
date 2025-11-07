import React, { useState } from 'react'
import InputTitleUp from '../../components/React/InputTitleUp'
import ButtonSecondary from '../../components/React/ButtonSecondary'
import ButtonPrimary from '../../components/React/ButtonPrimary'

function ContentTeachersAdd() {
    const [name, setName] = useState("")
    const [apellidos, setApellidos] = useState("")
    const [optionRol, setOptionRol] = useState("")
    const optionsRol = ["Administrador", "Personal"]
    const optionsStatus = ["Activo", "Inactivo"]
    const optionPermiss = ["Editar", "Visualizar", "Borrar"]
    const optionsViewAccept = ["Todas", "Roles", "Horarios", "Docentes", "Alumnos", "Matriculado", "Calificaciones", "Materias", "Pago"]

    const clickCancell = () => {
        window.history.back();
    }

    return (
        <div className='mt-4 border-2 px-4 py-6'>
            <div className="w-full flex flex-col md:flex-row md:gap-4 ">
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <InputTitleUp title={"Nombre"} value={name} setValue={setName} />
                        <InputTitleUp title={"Correo"} value={name} setValue={setName} />
                    </div>
                </div>
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <InputTitleUp title={"Apellidos"} value={apellidos} setValue={setApellidos} />
                        <InputTitleUp title={"Telefono"} value={apellidos} setValue={setApellidos} />
                    </div>
                </div>
            </div>
            <div className='mt-2 w-1/2 flex gap-2'>
                <ButtonSecondary showText={true} title={"Guardar"} />
                <ButtonPrimary showText={true} title={"Cancelar"} onClick={clickCancell} />
            </div>
        </div>
    )
}

export default ContentTeachersAdd
