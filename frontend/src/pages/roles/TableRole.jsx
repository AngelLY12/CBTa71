import React, { useEffect, useState } from 'react'
import InputSearch from '../../components/React/InputSearch'

function TableRole() {
    const [searchPersonal, setSearchPersonal] = useState("")
    const [personalResponse, setPersonalResponse] = useState([])
    const optionsPersonal = ["A","A","A"]

    const getPersonal = async () => {
        try {
            const response = await fetch(`/api/personal?search=${searchPersonal}`);
            const data = await response.json();
            setPersonalResponse(data);
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    useEffect(() => {
        getPersonal;
    }, [searchPersonal])
    return (
        <>
            <div className="flex mt-2">
                <InputSearch options={optionsPersonal} value={searchPersonal} setValue={setSearchPersonal} title="Buscar personal" />
            </div>
            <div className='mt-2'>
                <p>add</p>
            </div>
        </>
    )
}

export default TableRole
