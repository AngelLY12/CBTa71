import React, { useState } from 'react'
import Input from '../../../components/React/Input';
import Button from '../../../components/React/Button';
import InputTitleUp from '../../../components/React/InputTitleUp';

const AspirantHomeContent = () => {
    const [infoSelectAspirant, setInfoSelectAspirant] = useState({ nombre: "", carrerPrefed: "", lasName: "" });
    const [partForm, setPartFomr] = useState(0);

    const onClickSig = () => {
        if (partForm < 2) {
            setPartFomr(partForm + 1);
        }
    }

    return (
        <div className='pb-12 md:pb-5'>
            <h2 className='text-center font-semibold text-lg md:text-5xl'>Nuevo Aspirante</h2>
            <h3 className='mt-4 text-center text-base md:text-4xl'>Datos del usuario</h3>
            <div className='w-full h-full mt-3 mb-4 overflow-auto'>
                <div className='flex flex-col min-w-max'>
                    {partForm == 0 &&
                        <>
                            <table className='hidden md:visible md:table border-collapse border border-gray-400 w-full table-auto mt-2'>
                                <thead className=''>
                                    <tr>
                                        <th className='border border-gray-300 p-2 font-semibold'>Nombre</th>
                                        <th colSpan={2} className='border border-gray-300 p-2 font-semibold'>Apellidos</th>
                                        <th className='border border-gray-300 p-2 font-semibold'>Teléfono</th>
                                        <th className='border border-gray-300 p-2 font-semibold'>Entidad</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td className='border border-gray-300'>
                                            <Input />
                                        </td>
                                        <td colSpan={2} className='border border-gray-300'>
                                            <Input />
                                        </td>
                                        <td className='border border-gray-300'>
                                            <Input type={"number"} className={"text-center"} />
                                        </td>
                                        <td className='border border-gray-300 max-w-24'>
                                            <Input />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Edad</h3>
                                                <div className='border-t-1 border-gray-300 min-h-4'>
                                                    <Input type={"number"} />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Fecha de nacimiento</h3>
                                                <div className='flex justify-center border-t-1 border-gray-300 min-h-4'>
                                                    <Input type={"date"} />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Correo</h3>
                                                <div className='border-t-1 border-gray-300 min-h-4'>
                                                    <Input type="email" />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Sexo</h3>
                                                <div className='border-t-1 border-gray-300 min-h-4'>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Curp</h3>
                                                <div className='border-t-1 border-gray-300 min-h-4'>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td className='border border-gray-300' colSpan={5}><p className='font-semibold text-center'>Carrera preferida</p></td>
                                    </tr>

                                    <tr>
                                        <td colSpan={2} className='border border-gray-300'>
                                            <div className='w-full h-full flex flex-col'>
                                                <div className='flex justify-center items-center h-2/12'>
                                                    <label htmlFor='opcion1' className='w-full font-semibold text-center'>Opción 1</label>
                                                </div>
                                                <div className='border-t-1 border-gray-300 h-full'>
                                                    <Input id="opcion1" />
                                                </div>
                                            </div>
                                        </td>
                                        <td colSpan={2} className='border border-gray-300'>
                                            <div className='w-full h-full flex flex-col'>
                                                <div className='flex justify-center items-center h-2/12'>
                                                    <h3 className='font-semibold text-center'>Opción 2</h3>
                                                </div>
                                                <div className='border-t-1 border-gray-300 h-full'>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <div className='flex justify-center items-center h-2/12'>
                                                    <h3 className='font-semibold text-center'>Opción 3</h3>
                                                </div>
                                                <div className='border-t-1 border-gray-300 h-full'>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td className='border border-gray-300' colSpan={5}><h3 className='font-semibold text-center'>Secundaria de procedencia</h3></td>
                                    </tr>

                                    <tr>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Entidad de procedencia</h3>
                                                <div className='border-t-1 border-gray-300'>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>

                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Municipio de procedencia</h3>
                                                <div className='border-t-1 border-gray-300'>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>

                                        <td colSpan={3} className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <h3 className='font-semibold text-center'>Escuela de procedencia</h3>
                                                <div className='border-t-1 border-gray-300 '>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colSpan={3} className='border border-gray-300 h-20'>
                                            <div className='flex flex-col'>
                                                <div className='flex items-center justify-center h-10'>
                                                    <h3 className='font-semibold text-center'>Fecha de egreso de la escuela</h3>
                                                </div>
                                                <div className='border-t-1 border-gray-300 '>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>

                                        <td colSpan={2} className='border border-gray-300'>
                                            <div className='flex flex-col'>
                                                <div className='flex justify-center items-center h-10'>
                                                    <h3 className='font-semibold text-center'>Promedio general <br className='md:hidden' />(6 a 10)</h3>
                                                </div>
                                                <div className='border-t-1 border-gray-300 '>
                                                    <Input />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div className='md:hidden flex flex-col gap-2'>
                                <InputTitleUp title={"Nombre"}></InputTitleUp>
                                <InputTitleUp title={"Apellidos"}></InputTitleUp>
                                <InputTitleUp type='tel' title={"Teléfono"}></InputTitleUp>
                                <InputTitleUp title={"Entidad"}></InputTitleUp>
                                <InputTitleUp type='number' title={"Edad"}></InputTitleUp>
                                <InputTitleUp type='date' title={"Fecha de nacimiento"}></InputTitleUp>
                                <InputTitleUp type='email' title={"Correo"}></InputTitleUp>
                                <InputTitleUp title={"Sexo"}></InputTitleUp>
                                <InputTitleUp title={"Curp"}></InputTitleUp>
                                <p className='pl-2 mt-2 py-3 border-y-2 font-semibold text-lg border-gray-300'>Carrera preferida</p>
                                <InputTitleUp className={"mt-2"} title={"Opción 1"}></InputTitleUp>
                                <InputTitleUp title={"Opción 2"}></InputTitleUp>
                                <InputTitleUp title={"Opción 3"}></InputTitleUp>
                                <p className='pl-2 mt-2 py-3 border-y-2 font-semibold text-lg border-gray-300'>Escuela de procedencia</p>
                                <InputTitleUp className={"mt-2"} title={"Entidad de procedencia"}></InputTitleUp>
                                <InputTitleUp title={"Municipio de procedencia"}></InputTitleUp>
                                <InputTitleUp title={"Escuela de procedencia"}></InputTitleUp>
                                <InputTitleUp type='date' title={"Fecha de egreso"}></InputTitleUp>
                                <InputTitleUp type='number' title={"Promedio general (6 a 10)"}></InputTitleUp>
                            </div>
                        </>
                    }

                    {partForm == 1 &&
                        <>
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
                        </>
                    }

                    {partForm == 2 &&
                        <table className='w-full border-collapse border-gray-400 border table-auto'>
                            <thead>
                                <tr>
                                    <th>Numero de solicitud</th>
                                    <th>Numero del aspirante</th>
                                    <th>Carrera preferida</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td className='border border-gray-400'> <Input /></td>
                                    <td className='border border-gray-400'> <Input /></td>
                                    <td className='border border-gray-400'> <Input /></td>
                                </tr>
                            </tbody>
                        </table>
                    }
                </div>
            </div>
            {partForm == 0 &&
                <div className='flex gap-3 justify-center'>
                    <Button className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                        Guardar
                    </Button>
                    <Button onClick={onClickSig} className={"ring-green-600 rounded w-24 ring hover:bg-green-600 hover:text-white hover:ring-3 active:ring-3 active:bg-green-600 active:text-white"}>
                        Siguiente
                    </Button>
                </div>
            }
            {partForm == 1 &&
                < div className='flex gap-3 justify-center'>
                    <Button onClick={onClickSig} className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                        Finalizar
                    </Button>
                </div>
            }
            {partForm == 2 &&
                < div className='flex gap-3 justify-center'>
                    <Button className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                        Imprimir
                    </Button>
                </div>
            }
        </div >
    )
}

export default AspirantHomeContent
