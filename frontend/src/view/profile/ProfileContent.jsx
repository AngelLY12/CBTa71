import React, { useState } from 'react'
import Input from '../../components/React/Input'
import SelectInputOption from '../../components/React/SelectInputOption'
import InputTitleUp from '../../components/React/InputTitleUp';

const ProfileContent = () => {
    const [selectGenr, setSelectGenr] = useState("");
    const optionsGenr = ["Masculino", "Femenino"];

    return (
        <div className='mb-8 mt-6'>
            <h3 className='text-center font-semibold text-lg md:text-3xl'>Datos generales</h3>

            <div className='py-0.5 flex flex-col min-h-max lg:flex-row mt-4 h-auto md:border md:border-gray-300'>
                <div className='flex justify-center h-full lg:h-48 lg:block lg:w-2/12'>
                    <div className='h-full flex flex-col'>
                        <div className='flex h-full'>
                            <img className='rounded-full lg:rounded-none w-32 h-32 border lg:w-full lg:h-full' src="" alt="" />
                        </div>
                        <div className='md:border md:border-gray-300 h-auto'>
                            <p className='w-full text-center'>Foto</p>
                        </div>
                    </div>
                </div>

                <div className='mt-2 w-full lg:w-10/12 lg:ml-4 flex flex-col max-h-max'>
                    <table className='hidden md:visible md:table table-auto w-full border-collapse border border-gray-300'>
                        <tbody>
                            <tr>
                                <td className='border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Nombre</p>
                                        </div>

                                        <div className='h-12 p-1'>
                                            <p className='w-full text-center'>Jose Sanchez</p>
                                        </div>
                                    </div>
                                </td>
                                <td className='border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Apellidos</p>
                                        </div>

                                        <div className='h-12 p-1'>
                                            <p className='w-full text-center'>Jose Sanchez</p>
                                        </div>
                                    </div>
                                </td>
                                <td className='border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Edad</p>
                                        </div>

                                        <div className='h-12 p-1'>
                                            <p className='w-full text-center'>15</p>
                                        </div>
                                    </div>
                                </td>
                                <td className='border border-gray-300'>
                                    <div className='flex flex-col w-full h-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Fecha de nacimiento</p>
                                        </div>

                                        <div className='flex items-end h-12 '>
                                            <Input className={"h-full"} type="date"></Input>
                                        </div>
                                    </div>
                                </td>
                                <td className='border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>No.Control</p>
                                        </div>

                                        <div className='h-12 p-1'>
                                            <p className=' w-full text-center'>15680016</p>
                                        </div>
                                    </div>
                                </td>

                                <td className='border border-gray-300 w-24'>
                                    <div className='flex flex-col'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Sexo</p>
                                        </div>

                                        <div className='fle h-12'>
                                            <SelectInputOption setValue={setSelectGenr} className={"w-full"} titleSelector={"Selecciona el sexo"} options={optionsGenr} />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table className='hidden md:visible md:table mt-2 table-auto w-full border-collapse border border-gray-300'>
                        <tbody>
                            <tr>
                                <td className='max-w-32 lg:max-w-max border border-gray-300'>
                                    <div className='flex flex-col w-full max-h-max'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center truncate'>Correo</p>
                                        </div>

                                        <div className='min-h-28 lg:min-h-12 flex items-center p-1'>
                                            <p className='w-full text-center'>jose@gmail.com</p>
                                        </div>
                                    </div>
                                </td>
                                <td className='max-w-32 xl:max-w-max border border-gray-300'>
                                    <div className='flex flex-col w-full h-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center truncate'>Dirección (calle, número y colonia)</p>
                                        </div>
                                        <div className='min-h-28 lg:min-h-12 lg:max-h-12 flex items-center p-1'>
                                            <p className='w-full text-center lg:truncate'>Felipe Berreozabal #8a Col. Benito Jaurez</p>
                                        </div>
                                    </div>
                                </td>
                                <td colSpan={2} className='border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Curp</p>
                                        </div>

                                        <div className='flex items-center min-h-28 lg:min-h-12 p-1'>
                                            <p className='w-full text-center'>CHES100816HMSLVDA4</p>
                                        </div>
                                    </div>
                                </td>
                                <td className='border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Teléfono</p>
                                        </div>

                                        <div className='flex items-center min-h-28 lg:min-h-12 lg:max-h-12'>
                                            <p className='w-full text-center'>7358910912</p>
                                        </div>
                                    </div>
                                </td>

                                <td className='w-12 border border-gray-300'>
                                    <div className='flex flex-col w-full'>
                                        <div className='h-8 p-1 border-b border-gray-300'>
                                            <p className='w-full text-center'>Entidad</p>
                                        </div>

                                        <div className='flex items-center min-h-28 lg:min-h-12 lg:max-h-12'>
                                            <SelectInputOption titleSelector={"Selecciona la entidad"} type="date"></SelectInputOption>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div className='visible md:hidden w-full flex flex-col'>
                        <InputTitleUp className={"w-full"} title={"Nombre"}></InputTitleUp>
                        <InputTitleUp className={"w-full"} title={"Apellidos"}></InputTitleUp>
                        <InputTitleUp className={"w-full"} title={"Edad"}></InputTitleUp>
                        <InputTitleUp type='date' className={"w-full"} title={"Fecha de nacimiento"}></InputTitleUp>
                        <InputTitleUp className={"w-full"} title={"No. Control"}></InputTitleUp>
                        <SelectInputOption options={["Masculino", "Femenino"]} titleSelector={"Selecciona el sexo"} title={"Sexo"}></SelectInputOption>
                    </div>

                    <div className='visible md:hidden w-full flex flex-col'>
                        <InputTitleUp type='email' className={"w-full"} title={"Correo"}></InputTitleUp>
                        <InputTitleUp className={"w-full"} title={"Dirección"}></InputTitleUp>
                        <InputTitleUp className={"w-full"} title={"Curp"}></InputTitleUp>
                        <InputTitleUp type='tel' className={"w-full"} title={"Teléfono"}></InputTitleUp>
                        <SelectInputOption options={["Masculino", "Femenino"]} titleSelector={"Selecciona la entidad"} title={"Entidad"}></SelectInputOption>
                    </div>
                </div>
            </div>

            <div className='mt-6'>
                <h3 className='font-semibold text-center text-lg md:text-3xl mb-2 md:mb-0'>Datos domiciliarios</h3>
                <table className='hidden md:visible md:table table-auto mt-6 text-auto border-collapse w-full border border-gray-300'>
                    <thead>
                        <tr>
                            <th colSpan={4}>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Calle (Numero interior y/o exterior)</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Estado</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Municipio</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Código postal</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Colonia</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td colSpan={2} className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Correo electronico</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td colSpan={2} className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Teléfono</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div className='md:hidden flex flex-col gap-2'>
                    <InputTitleUp title={"Calle (Numero interior y/o exterior)"}></InputTitleUp>
                    <InputTitleUp title={"Estado"}></InputTitleUp>
                    <InputTitleUp title={"Municipio"}></InputTitleUp>
                    <InputTitleUp type='number' title={"Código postal"}></InputTitleUp>
                    <InputTitleUp title={"Colonia"}></InputTitleUp>
                    <InputTitleUp type='email' title={"Correo (Personal)"}></InputTitleUp>
                    <InputTitleUp type='tel' title={"Teléfono"}></InputTitleUp>
                </div>
            </div>

            <div className='mt-6'>
                <h3 className='font-semibold text-center text-lg md:text-3xl'>Datos academicos</h3>
                <table className='hidden md:visible md:table table-auto mt-6 text-auto border-collapse w-full border border-gray-300'>
                    <thead>
                        <tr>
                            <th colSpan={4}>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Carrera</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Matricula</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Semestre</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Grupo</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Taller</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td colSpan={2} className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Materias aprobadas</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>

                            <td colSpan={2} className='border border-gray-300'>
                                <div className='flex flex-col'>
                                    <h3 className='font-semibold text-center'>Materias reprobadas</h3>
                                    <div className='border-t-1 border-gray-300 '>
                                        <Input />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div className='md:hidden flex flex-col gap-2'>
                    <InputTitleUp title={"Carrera"}></InputTitleUp>
                    <InputTitleUp title={"Matricula"}></InputTitleUp>
                    <InputTitleUp title={"Semestre"}></InputTitleUp>
                    <InputTitleUp type='number' title={"Grupo"}></InputTitleUp>
                    <InputTitleUp title={"Taller"}></InputTitleUp>
                    <InputTitleUp title={"Materias aprobadas"}></InputTitleUp>
                    <InputTitleUp title={"Materias reprobadas"}></InputTitleUp>
                </div>
            </div>
        </div>
    )
}

export default ProfileContent
