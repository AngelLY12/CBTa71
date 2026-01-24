import React, { useState } from 'react'
import Button from '../../components/React/Button'
import InputSearch from '../../components/React/InputSearch'
import SelectInput from '../../components/React/SelectInput';
import Table from '../../components/React/Table';
import Modal from '../../components/React/Modal';
import InputTitleUp from '../../components/React/InputTitleUp';
import SelectInputOption from '../../components/React/SelectInputOption';

const ContentMatter = () => {
    const [matterSearch, setMatterSearch] = useState("");
    const [selectMatterFiltre, setSelectMatterFiltre] = useState("")
    const [indexDelete, setIndexDelete] = useState(-1);
    const [infoEditMatter, setInfoEditMatter] = useState({});
    const [deleteAprob, setDeleteAprob] = useState(false)
    const [showDelete, setShowDelete] = useState(false)
    const [showModalMatter, setShowModalMatter] = useState(false);
    const heads = ["ID", "Materia", "Carrera", "Semestre", "Grupo", "Aula", "Maestro", "Horario", "Editar/Eliminar"];
    const dates = ["id", "nombre", "carrera", "semestre", "grupo", "aula", "maestro", "horario"];

    const [matters, setMatters] = useState([
        { id: 1, nombre: "Quimica", carrera: "Ofimatica", semestre: 1, grupo: "A", aula: 2, maestro: "Jose Perez Sanchez", horario: "12am - 1pm" }
    ]);

    const getMatterSearch = () => {

    }

    const getMatterFiltre = () => {

    }

    const closeModalDelete = () => {
        setShowDelete(false)
    }

    const showModalDelete = (i) => {
        setIndexDelete(i)
        setShowDelete(true)
    }

    const onClickeditMatter = (matter) => {
        setInfoEditMatter(matter);
        console.log(`La materia a editar es ${matter.nombre}`)
    }

    const deletePersonal = async () => {
        setDeleteAprob(true)
        closeModalDelete()
        setTimeout(() => {
            setMatters(prev => prev.filter(item => item.id !== indexDelete));
            setIndexDelete(-1)
            setDeleteAprob(false);
            // try {
            //     const response = await fetch(`/api/personal/${indexDelete}`, {
            //         method: 'DELETE',
            //     });
            //     if (!response.ok) {
            //         throw new Error(`HTTP error! status: ${response.status}`);
            //     }
            //     const data = await response.json();
            //     console.log("Deleted:", data);
            // } catch (error) {
            //     console.error("Error deleting personal data:", error);
            // }
        }, 300)
    }

    return (
        <div className='w-full mt-4'>
            <div className='flex md:gap-2 justify-between'>
                <InputSearch className={"md:w-5/12"} valueSearch={"name"} getOptions={getMatterSearch} options={matters} value={matterSearch} setValue={setMatterSearch} title={"Buscar materia"}>
                </InputSearch>
                <Button className={"rounded text-white bg-green-800 ring-green-800 ring-1 hover:font-semibold hover:ring-3 active:ring-3 active:font-semibold"}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Agregar
                </Button>
            </div>

            <div className='flex gap-1 justify-start md:justify-between mt-4'>
                <SelectInput className={"md:w-3/12"} setOption={getMatterFiltre} setValue={setSelectMatterFiltre} titleEnter={true} title='Materia'>
                </SelectInput>
                <SelectInput className={"md:w-3/12"} setOption={getMatterFiltre} setValue={setSelectMatterFiltre} titleEnter={true} title='Carrera'>
                </SelectInput>
                <div className='w-full flex justify-end md:w-auto md:block'>
                    <Button className={"bg-cyan-600 text-white rounded"}>
                        Actualizar
                    </Button>
                </div>
            </div>

            <Table clickEdit={onClickeditMatter} showModalDelete={showModalDelete} showDelete={showDelete} indexDelete={indexDelete} deleteValue={deletePersonal} deleteAprob={deleteAprob} closeModalDelete={closeModalDelete} Heads={heads} values={matters} />

            <Modal onDisable={() => setShowModalMatter(false)} show={showModalMatter} fullScreen={true} onClickAccept={false} aceptModal={false} >
                <div className='w-full pt-4 px-2 lg:px-4'>
                    <h3 className='text-center font-semibold text-lg md:text-2xl'>Agregar nueva materia</h3>
                    <div className='pb-2 lg:pb-0'>
                        <div className='flex flex-col lg:flex-row justify-between gap-2 mt-6'>
                            <SelectInputOption titleSelector={"Selecciona un id"} title="ID"></SelectInputOption>
                            <SelectInputOption titleSelector={"Selecciona una materia"} title="Materia"></SelectInputOption>
                            <SelectInputOption titleSelector={"Selecciona una carrera"} title="Carrera"></SelectInputOption>
                        </div>

                        <div className='flex flex-col lg:flex-row justify-between gap-2 mt-4'>
                            <SelectInputOption titleSelector={"Selecciona un maestro"} title="Maestro"></SelectInputOption>
                            <SelectInputOption titleSelector={"Selecciona un semestre"} title="Semestre"></SelectInputOption>
                            <SelectInputOption titleSelector={"Selecciona un horario"} title="Horario"></SelectInputOption>
                        </div>

                        <div className='flex flex-col lg:flex-row  justify-between gap-2 mt-4'>
                            <InputTitleUp title="Aula"></InputTitleUp>
                            <InputTitleUp title="Grupo"></InputTitleUp>
                            <div className='w-full hidden lg:visible lg:block'>
                            </div>
                        </div>

                        <div className='mt-3 mb-4 flex justify-end gap-2'>
                            <Button className={"w-24 rounded ring-1 ring-green-300 hover:ring-3 hover:bg-green-300 active:ring-3 active:bg-green-300"}>Cancelar</Button>
                            <Button className={"w-24 rounded text-white ring-neutral-600 bg-neutral-600 hover:ring-2 active:ring-2"}>Guardar</Button>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
    )
}

export default ContentMatter
