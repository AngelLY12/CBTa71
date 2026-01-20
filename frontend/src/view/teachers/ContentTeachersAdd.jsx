import React, { useState } from 'react'
import InputTitleUp from '../../components/React/InputTitleUp'
import ButtonSecondary from '../../components/React/ButtonSecondary'
import ButtonPrimary from '../../components/React/ButtonPrimary'

const ContentTeachersAdd = () => {
    const [name, setName] = useState("")
    const [apellidos, setApellidos] = useState("")
    const [correo, setCorreo] = useState("")
    const [telefono, setTelefono] = useState("")
    const clickCancell = () => {
        window.history.back();
    }

    return (
        <div className='mt-4 border-2 px-4 py-6'>
            <div className="w-full flex flex-col md:flex-row md:gap-4 ">
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <InputTitleUp title={"Nombre"} value={name} setValue={setName} />
                        <InputTitleUp type="email" title={"Correo"} value={correo} setValue={setCorreo} />
                    </div>
                </div>
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <InputTitleUp title={"Apellidos"} value={apellidos} setValue={setApellidos} />
                        <InputTitleUp type='tel' title={"Telefono"} value={telefono} setValue={setTelefono} />
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
