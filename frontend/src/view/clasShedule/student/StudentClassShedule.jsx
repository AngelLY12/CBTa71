import React, { useEffect, useState } from 'react'
import SelectInput from '../../../components/React/SelectInput'
import TableShedule from '../../../components/React/TableShedule';
import { urlGlobal } from '../../../data/global';
import { userStore } from '../../../data/userStore';
import api from '../../../components/React/api';

const StudentClassShedule = () => {
    const [periodSelect, setPeriodSelect] = useState({});
    const [periodsOptions, setPeriodsOptions] = useState([]);
    const [error, setError] = useState("");

    const [headValue, setHeadValue] = useState([]);
    const [footerValue, setFooterValue] = useState([]);
    const [infoCell, setInfoCell] = useState([]);

    const getClassShedule = async () => {
        try {
            const response = await api.get(`${urlGlobal}/class-schedules/period/${periodSelect.id}`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            })
            setHeadValue(response.data.data.class);
            setInfoCell(response.data.data.time_blocks);
            setFooterValue(response.data.data.teacher_sumarry);
        }
        catch (error) {
            setError(error.message);
        }
    }

    const getPeriodsOptions = async () => {
        try {
            const response = await api.get(`${urlGlobal}/periods`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            })
            setPeriodsOptions(response.data.data.periods)
        }
        catch (error) {
            console.log(error)
        }
    }

    useEffect(() => {
        getPeriodsOptions();
    }, [])

    useEffect(() => {
        if (periodSelect.id) {
            setError("")
            getClassShedule();
        }
    }, [periodSelect])

    return (
        <div className='mt-6 border-2 px-2 py-2 mb-4'>
            <h2 className='text-center font-semibold text-lg md:text-xl mt-4'>Horario de grupo</h2>
            {
                (infoCell.length <= 0) &&
                <div className='flex w-full'>
                    <SelectInput valueOption="period_code" notSelectDefault={true} className={"w-auto"} titleMovil={"Seleciona el periodo"} setValue={setPeriodSelect} options={periodsOptions} setOption={() => { }} topTitle={true} title='Periodo' titleEnter={false}></SelectInput>
                </div>
            }

            {
                (error) &&
                <p className='w-full block text-center text-red-700'>
                    {error}
                </p>
            }

            {
                (periodSelect.id && infoCell.length > 0) &&
                <TableShedule className='border-none md:mt-0' valueCell={infoCell} headValue={headValue} footerValue={footerValue} turnSelect={0} />
            }
        </div>
    )
}

export default StudentClassShedule
