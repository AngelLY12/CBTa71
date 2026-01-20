import React, { useState } from 'react'
import TableShedule from '../../../components/React/TableShedule'
import Button from '../../../components/React/Button'
import SelectInput from '../../../components/React/SelectInput'
import Modal from '../../../components/React/Modal'

const ClassSheduleSeccion2 = () => {
    const [optionsEnable, setOptionsEnable] = useState(["Profesor", "Alumnos"]);
    const [optionsPeriod, setOptionsPerior] = useState(["ENERO/MAYO 2025", "AGO/DIC 2025"]);
    const [optionsSemester, setOptionsSemester] = useState([1, 2, 3, 4, 5, 6]);
    const [optionsCarrer, setOptionsCarrer] = useState(["Ofimatica", "Administración de Emprendimiento", " Administración de Recursos Humanos", "Agropecuario"]);

    const [selectOptionPeriod, setSelectOptionPeriod] = useState("");
    const [selectOptionSemester, setSelectOptionSemester] = useState(0);
    const [selectOptionCarrer, setSelectOptionCarrer] = useState("");
    const [selectOptionEnable, setSelectOptionEnable] = useState("");

    const [showTableNew, setShowTableNew] = useState(false);
    const [tableAccion, setTableAccion] = useState({ update: false, new: false })

    const [headValue, setHeadValue] = useState({ semester: 1, group: "D", tutor: "Juan Carlos", period: "AGO/DIC 2025", career: "OFIMATICA" });
    const [infoCell, setInfoCell] = useState(
        [
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
            [{ matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }, { matter: { name: "", teacher: "" }, classroom: "" }],
        ]
    );

    const closeModalTable = () => {
        setShowTableNew(false);
    }

    const saveTable = () => {
        closeModalTable();
    }

    const getOptionPeriodo = async () => {
        // try {
        //     const response = await fetch(`/api/personal?category=${filtreSelect == "" ? optionsSelect[0] : filtreSelect}`);
        //     if (!response.ok) {
        //         throw new Error(`HTTP error! status: ${response.status}`);
        //     }
        //     const data = await response.json();
        //     setPersonalResponse(data);
        // } catch (error) {
        //    console.error("Error fetching personal data:", error);
        // }
    }

    const openModalTable = () => {
        setShowTableNew(true);
        setTableAccion({ new: true, update: false });
    }

    const openModalTableEdit = () => {
        setShowTableNew(true);
        setTableAccion({ new: false, update: true });
    }

    return (
        <div className='w-full pb-4'>
            <Button onClick={openModalTable} className={"z-10 w-12 h-12 md:w-16 md:h-16 fixed bottom-12 right-4 bg-gray-400 rounded-full text-white ring-1 ring-gray-400 hover:ring-4 hover:bg-gray-500 hover:ring-gray-500 active:bg-gray-500 active:ring-gray-500"}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" className="size-8 md:size-10">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </Button>

            <div className='w-full flex items-center justify-start min-[321px]:justify-end mt-2 gap-1'>
                <div className='min-[321px]:hidden flex gap-1 items-center'>
                    <SelectInput className={"min-[321px]:hidden md:w-3/12 md:h-9"} setOption={getOptionPeriodo} setValue={setSelectOptionPeriod} options={optionsPeriod} title='Periodo' titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </SelectInput>
                    <SelectInput className={"min-[321px]:hidden md:w-3/12 md:h-9"} setOption={getOptionPeriodo} setValue={setSelectOptionCarrer} options={optionsCarrer} title='Carrera' titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                        </svg>
                    </SelectInput>
                    <SelectInput className={"min-[321px]:hidden md:w-3/12 md:h-9"} setOption={getOptionPeriodo} setValue={setSelectOptionSemester} options={optionsSemester} title='Semestre' titleEnter={false}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                        </svg>
                    </SelectInput>
                </div>
                <div className='w-full flex min-[321px]:w-auto min-[321px]:inline-block justify-end'>
                    <Button className={"min-[321px]:hidden w-auto px-1.5 py-2 text-sm md:text-base md:w-24 ring-1 ring-cyan-600 bg-cyan-600 text-white rounded md:px-4 hover:ring-3"}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <p className='hidden md:visible md:block'>Actualizar</p>
                    </Button>

                    <Button className={'pr-0 text-sm md:text-base'}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span className='hidden min-[375px]:visible min-[375px]:block'>Importar</span>
                    </Button>
                </div>
            </div>

            <div className='hidden min-[370px]:visible min-[370px]:flex items-center justify-start gap-1 md:gap-0 md:justify-between mt-2'>
                <SelectInput className={"md:w-3/12 md:h-9"} titleMovil={"Seleccionar periodo"} setOption={getOptionPeriodo} setValue={setSelectOptionPeriod} options={optionsPeriod} title='Periodo' titleEnter={false}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </SelectInput>
                <SelectInput className={"md:w-3/12 md:h-9"} titleMovil={"Seleccionar carrera"} setOption={getOptionPeriodo} setValue={setSelectOptionCarrer} options={optionsCarrer} title='Carrera' titleEnter={false}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </SelectInput>
                <SelectInput className={"md:w-3/12 md:h-9"} titleMovil={"Seleccionar semestre"} setOption={getOptionPeriodo} setValue={setSelectOptionSemester} options={optionsSemester} title='Semestre' titleEnter={false}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002h.375a1.125 1.125 0 0 1 0 2.25H2.99" />
                    </svg>
                </SelectInput>
                <div className='flex min-[425px]:justify-end min-[425px]:w-full md:block md:w-auto'>
                    <Button className={"w-auto px-1.5 py-2 text-sm md:text-base md:w-24 ring-1 ring-cyan-600 bg-cyan-600 text-white rounded md:px-4 hover:ring-3"}>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <p className='hidden md:visible md:block'>Actualizar</p>
                    </Button>
                </div>
            </div>

            <div className='py-2 flex'>
                <label className='flex items-center'><input className='size-4 mr-2' type="radio" />Mostrar todo</label>
            </div>

            <TableShedule tableTeacher={true} setBottonEdit={openModalTableEdit} headValue={headValue} valueCell={infoCell}></TableShedule>

            <Modal show={showTableNew} onDisable={closeModalTable} aceptModal={false} fullScreen={true}>
                <div className='w-full px-2 py-4'>
                    <h3 className='text-center text-md md:text-xl font-semibold mb-3'>{tableAccion.new ? "Nuevo horario" : "Editar tabla"}</h3>
                    <TableShedule tableTeacher={true} options={{ carrer: optionsCarrer, smt: optionsSemester, period: optionsPeriod }} setHeadValue={setHeadValue} closeTable={closeModalTable} saveTable={saveTable} edit={true} updateTable={tableAccion.update} newTable={tableAccion.new} headValue={headValue} valueCell={infoCell} setValueCell={setInfoCell}></TableShedule>
                </div>
            </Modal>
        </div>
    )
}

export default ClassSheduleSeccion2
