import React, { useState } from 'react'
import SelectInput from '../../../components/React/SelectInput'
import TableShedule from '../../../components/React/TableShedule';

const StudentClassShedule = () => {
    const [periodSelect, setPeriodSelect] = useState("");
    const periodsOptions = [1, 2, 3, 4, 5, 6];

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

    const getClassShedule = () => {

    }

    return (
        <div className='mt-6 border-2 px-2 py-2 mb-4'>
            <h2 className='text-center font-semibold text-lg md:text-xl mt-4'>Horario de grupo</h2>

            {
                !periodSelect &&
                <div className='flex w-full'>
                    <SelectInput notSelectDefault={true} className={"w-auto"} titleMovil={"Seleciona el periodo"} setValue={setPeriodSelect} options={periodsOptions} setOption={getClassShedule} topTitle={true} title='Periodo' titleEnter={false}></SelectInput>
                </div>
            }
            {
                periodSelect &&
                <TableShedule className='border-none md:mt-0 ' valueCell={infoCell} headValue={headValue} turnSelect={0} />
            }
        </div>
    )
}

export default StudentClassShedule
