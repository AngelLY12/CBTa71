import React, { useState } from 'react'
import SelectInput from '../../../components/React/SelectInput'

const StaffAssistance = () => {
    const [assistDate, setAssistDate] = useState([]);
    const [matterSelect, setMatterSelect] = useState("");

    const [matterOptions, setMatterOptions] = useState(["Algebra", "Matematicas", "Español"]);
    const [optionsCarrer, setOptionsCarrer] = useState(["Ofimatica", "Administración de Emprendimiento", " Administración de Recursos Humanos", "Agropecuario"]);
    const [optionsSemester, setOptionsSemester] = useState([1, 2, 3, 4, 5, 6]);
    const [optionsGroup, setOptionsGroup] = useState(["A", "B", "C", "D"]);
    const [optionsMouth, setOptionsMouth] = useState(["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio"]);

    const getOptionMatter = () => {
        if (matterSelect != "") {
            setAssistDate([1])
        }
    }

    return (
        <div className='border-2 pb-12 rounded overflow-hidden'>
            <div className='flex items-center mt-4 mx-4 md:justify-start gap-1 justify-between md:gap-4 lg:gap-10 md:overflow-visible overflow-x-auto'>
                <div className='flex md:w-30'>
                    <SelectInput notSelectDefault={true} titleMovil={"Seleccionar materia"} setOption={getOptionMatter} setValue={setMatterSelect} options={matterOptions} className={"w-full"} title='Materia' topTitle={true} titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </SelectInput>
                </div>

                <div className='flex md:w-50'>
                    <SelectInput notSelectDefault={true} titleMovil={"Seleccionar carrera"} setOption={getOptionMatter} setValue={setMatterSelect} options={optionsCarrer} className={"w-full"} title='Carrera' topTitle={true} titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" className="size-6 bi bi-mortarboard" viewBox="0 0 16 16">
                            <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917zM8 8.46 1.758 5.965 8 3.052l6.242 2.913z" />
                            <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46z" />
                        </svg>
                    </SelectInput>
                </div>

                <div className='flex md:w-30'>
                    <SelectInput notSelectDefault={true} titleMovil={"Seleccionar semestre"} setOption={getOptionMatter} setValue={setMatterSelect} options={optionsSemester} className={"w-full"} title='Semestre' topTitle={true} titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                        </svg>
                    </SelectInput>
                </div>

                <div className='flex md:w-30'>
                    <SelectInput notSelectDefault={true} titleMovil={"Seleccionar grupo"} setOption={getOptionMatter} setValue={setMatterSelect} options={optionsGroup} className={"w-full"} title='Grupo' topTitle={true} titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" className="size-6 bi bi-people" viewBox="0 0 16 16">
                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4" />
                        </svg>
                    </SelectInput>
                </div>

                <div className='flex md:w-30'>
                    <SelectInput notSelectDefault={true} titleMovil={"Seleccionar mes"} setOption={getOptionMatter} setValue={setMatterSelect} options={optionsMouth} className={"w-full"} title='Mes' topTitle={true} titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                        </svg>
                    </SelectInput>
                </div>
            </div>

            <div className='mt-8 px-4'>
                {assistDate.length > 0
                    ?
                    <table className='table-auto border-collapse border border-gray-300 w-full'>
                        <thead>
                            <tr>
                                <th className='border border-gray-300'>No.</th>
                                <th className='border border-gray-300'>Apellidos</th>
                                <th className='border border-gray-300'>Nombre(s)</th>
                                <th className='border border-gray-300'>
                                    <div className='flex flex-col min-w-max'>
                                        <div>Día</div>
                                        <div className='flex border-collapse'>
                                            {
                                                [...Array(31)].map((_, i) => (
                                                    <div key={i} className={`w-full border-gray-300 border-t border-l ${i + 1 == 1 && "border-l-0"}`}>
                                                        <p className='text-center'>{i + 1}</p>
                                                    </div>
                                                ))
                                            }
                                        </div>
                                    </div>
                                </th>
                                <th className='border border-gray-300'>
                                    <div className='flex flex-col min-w-max'>
                                        <div>
                                            <p>Total</p>
                                        </div>
                                        <div className='flex'>
                                            <div className='w-full border-r border-t border-gray-300'>A</div>
                                            <div className='w-full border-t border-gray-300'>F</div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {
                                [...Array(20)].map((_, i) => (
                                    <tr>
                                        <td className='border border-gray-300'>
                                            <p className='text-center'></p>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <p className='text-center'></p>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <p className='text-center'></p>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex border-collapse'>
                                                {
                                                    [...Array(31)].map((_, i) => (
                                                        <div key={i} className={`w-full border-gray-300 border-t border-l ${i + 1 == 1 && "border-l-0"}`}>
                                                            <p className='text-center'>A</p>
                                                        </div>
                                                    ))
                                                }
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex min-w-max'>
                                                <div className='w-full flex justify-center border-r border-gray-300'>31</div>
                                                <div className='w-full flex justify-center'></div>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            }
                        </tbody>
                    </table>
                    :
                    <div className='flex items-center justify-center rounded min-h-80 border-2 mx-auto max-w-5xl'>
                        <p className='px-2 md:text-lg'>Seleccione las opciones para ver la lista de asistencias </p>
                    </div>
                }
            </div>
        </div>
    )
}

export default StaffAssistance
