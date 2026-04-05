import React, { useEffect, useState } from 'react'
import Modal from '../../../components/React/Modal';
import CardPersonal from '../../../components/React/CardInfoMovil';
import Table from '../../../components/React/Table';
import Button from '../../../components/React/Button';
import api from '../../../components/React/api';
import { urlGlobal } from '../../../data/global';
import { userStore } from '../../../data/userStore';

function StudentsSeccion2() {
  const [valuesApplication, setValuesApplication] = useState([])
  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [loading, setLoading] = useState(true);

  const [showDelete, setShowDelete] = useState(false)
  const [showInfoAspirant, setShowInfoAspirant] = useState(false);

  const [infoSelectAspirant, setInfoSelectAspirant] = useState({ name: "", career_preferences: [{ id: 0, career_name: "" }], last_name: "" });

  const closeModalDelete = () => {
    setShowDelete(false)
  }

  const showClickModalInfo = (aspirant) => {
    setInfoSelectAspirant(aspirant);
    setShowInfoAspirant(true);
  }

  const showModalDelete = (i) => {
    setIndexDelete(i)
    setShowDelete(true)
  }

  const getApplicants = async () => {
    try {
      const response = await api.get(`${urlGlobal}/admin-actions/applicants`, {
        headers: {
          Authorization: `Bearer ${userStore.tokens?.access_token}`,
          "Content-Type": "application/json",
        },
      });
      setValuesApplication(response.data.data.applicants);
      setLoading(false);
    } catch (error) {
      console.error("Error fetching personal data:", error);
    }
  }

  const accepApplicant = async (id) => {
    try {
      const response = await api.post(
        `${urlGlobal}/admin-actions/register-student/${id}`,
        {}, // body vacío si no mandas datos
        {
          headers: {
            Authorization: `Bearer ${userStore.tokens?.access_token}`,
            "Content-Type": "application/json",
          },
        }
      );
      console.log(response.data.data.applicant);
      // Si todo salió bien, quitamos al applicant de la lista
      setValuesApplication(prevApplicants =>
        prevApplicants.filter(applicant => applicant.id !== id)
      );
      alert(`Estudiante aceptado correctamente`)
    } catch (error) {
      console.error("Error registrando estudiante:", error);
    }
  };

  const deleteValueApplication = async () => {
    setDeleteAprob(true)
    closeModalDelete()
    setTimeout(() => {
      setValuesApplication(prev => prev.filter(item => item.id !== indexDelete));
      try {
        const response = api.post(
          `${urlGlobal}/admin-actions/reject-applicant/${indexDelete}`,
          {}, // body vacío si no mandas datos
          {
            headers: {
              Authorization: `Bearer ${userStore.tokens?.access_token}`,
              "Content-Type": "application/json",
            },
          }
        );
        console.log(response)
        setDeleteAprob(false);
      } catch (error) {
        console.error("Error registrando estudiante:", error);
      }
    }, 300)
    setIndexDelete(-1)
  }

  useEffect(() => {
    getApplicants();
  }, [])

  return (
    <>
      <div>
        <div className='w-full mt-6 md:overflow-hidden'>
          {
            !loading ?
              valuesApplication.length > 0
                ?
                <>
                  <table className='hidden md:visible md:table text-sm md:text-md w-full border-separate border-spacing-1.5 border rounded-md border-gray-600 table-auto'>
                    <thead>
                      <tr>
                        <th>Numero de solicitud</th>
                        <th>Nombre del aspirantes</th>
                        <th>Carrera preferida</th>
                        <th>Aceptar / Rechazar</th>
                      </tr>
                    </thead>
                    <tbody>
                      {valuesApplication.map((aspirant) => (
                        <tr className={`text-center transition-opacity duration-300 ease-out ${(aspirant.id == indexDelete && deleteAprob) && 'opacity-0'}`} key={aspirant.id}>
                          <td>{aspirant.application_number}</td>
                          <td><div className='flex justify-center'><Button onClick={() => showClickModalInfo(aspirant)} href="1" className='w-auto text-center text-indigo-600 hover:underline active:underline'>{`${aspirant.name} ${aspirant.last_name}`}</Button></div></td>
                          <td><p>{aspirant.career_preferences[0].career_name}</p></td>
                          <td>
                            <button onClick={() => accepApplicant(aspirant.id)} className='mr-2 cursor-pointer group hover:text-green-400' title='Editar elemento'>
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 group-hover:hidden">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                              </svg>
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6 hidden group-hover:visible group-hover:block">
                                <path fillRule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clipRule="evenodd" />
                              </svg>
                            </button>
                            <button onClick={() => showModalDelete(aspirant.id)} title='Eliminar elemento' className='ml-2 cursor-pointer group hover:text-red-500'>
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6 group-hover:hidden">
                                <path strokeLinecap="round" strokeLinejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                              </svg>
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="size-6 hidden group-hover:visible group-hover:block">
                                <path fillRule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clipRule="evenodd" />
                              </svg>
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>

                  <div className='flex flex-col visible md:hidden'>
                    {
                      valuesApplication.map((option) => (
                        <CardPersonal onClickCard={() => showClickModalInfo(option)} cardClick={true} textFuction={["Aceptar", "Rechazar"]} key={"card-" + option.id} info={["application_number", "status"]} item={option} index={option.numeroSolicitud} onClickDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} />
                      ))
                    }
                  </div>
                </>
                :
                <div>
                  <p className='font-bold text-center text-md md:text-xl'>No se encuentran datos aun</p>
                </div>
              :
              <div>
                <p className='flex justify-center items-center animate-spin text-gray-300'>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-6">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                  </svg>
                </p>
              </div>
          }
          <Modal onClickAccept={deleteValueApplication} show={showDelete} onDisable={closeModalDelete} text={"¿ Esta seguro de querer rechazar a este estudiante ?"} />

          {infoSelectAspirant.name &&
            <Modal show={showInfoAspirant} onDisable={() => setShowInfoAspirant(false)} fullScreen={true} aceptModal={false}>
              <div className='w-full h-full pr-1 pl-4'>
                <h3 className='py-4 text-center text-sm md:text-xl font-bold'>Datos del aspirante</h3>
                <div className='overflow-auto'>
                  <div className='flex flex-col min-w-max pb-4'>
                    <table className='border-collapse border border-gray-400 w-full table-auto mt-2'>
                      <thead>
                        <tr>
                          <th className='border border-gray-300 p-2 font-semibold'>Nombre</th>
                          <th colSpan={2} className='border border-gray-300 p-2 font-semibold'>Apellidos</th>
                          <th className='border border-gray-300 p-2 font-semibold'>Teléfono</th>
                          <th className='border border-gray-300 p-2 font-semibold'>Entidad</th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr>
                          <td className='border border-gray-300 px-2 py-4'>
                            <p className='text-center w-full'>{infoSelectAspirant.name}</p>
                          </td>
                          <td colSpan={2} className='border border-gray-300 px-2 py-4'>
                            <p className='text-center w-full'>{infoSelectAspirant.last_name}</p>
                          </td>
                          <td className='border border-gray-300 px-2 py-4'>
                            <p className='text-center w-full'>{infoSelectAspirant.phone_number}</p>
                          </td>
                          <td className='w-10 border border-gray-300 px-2 py-4'>
                            <p className='text-center w-full'>{infoSelectAspirant?.address?.[5]}</p>
                          </td>
                        </tr>
                        <tr>
                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Edad</h3>
                              <div className='flex items-center border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.age}</p>
                              </div>
                            </div>
                          </td>
                          <td className='w-40 border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='w-full font-semibold text-center'>Fecha de nacimiento</h3>
                              <div className='flex items-center border-t-1 border-gray-300 h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.birthdate}</p>
                              </div>
                            </div>
                          </td>
                          <td className='w-40 border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Correo</h3>
                              <div className='flex items-center border-t-1 border-gray-300 h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.email}</p>
                              </div>
                            </div>
                          </td>
                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Sexo</h3>
                              <div className='flex items-center border-t-1 border-gray-300 h-4 px-2 py-4'>
                                <p className='w-full text-center capitalize'>{infoSelectAspirant.gender}</p>
                              </div>
                            </div>
                          </td>
                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Curp</h3>
                              <div className='flex items-center border-t-1 border-gray-300 h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.curp}</p>
                              </div>
                            </div>
                          </td>
                        </tr>

                        <tr>
                          <td className='border border-gray-300' colSpan={5}><h3 className='font-semibold text-center'>Carrera preferida</h3></td>
                        </tr>

                        <tr>
                          <td colSpan={2} className='max-w-32 border border-gray-300'>
                            <div className='w-full flex flex-col'>
                              <div className='flex justify-center items-center h-9'>
                                <h3 className='font-semibold text-center'>Opción 1</h3>
                              </div>
                              <div className='border-t-1 border-gray-300 h-20 px-2 py-4'>
                                <p className='w-full text-center line-clamp-2'>{infoSelectAspirant?.career_preferences?.[0]?.career_name}</p>
                              </div>
                            </div>
                          </td>
                          <td className='min-w-48 border border-gray-300'>
                            <div className='w-full flex flex-col'>
                              <div className='flex justify-center items-center h-9'>
                                <h3 className='font-semibold text-center'>Opción 2</h3>
                              </div>
                              <div className='border-t-1 border-gray-300 h-20 px-2 py-4'>
                                <p className='w-full text-center line-clamp-2'>{infoSelectAspirant?.career_preferences?.[1]?.career_name}</p>
                              </div>
                            </div>
                          </td>
                          <td colSpan={2} className='max-w-24 border border-gray-300'>
                            <div className='flex flex-col'>
                              <div className='flex justify-center items-center h-9'>
                                <h3 className='font-semibold text-center'>Opción 3</h3>
                              </div>
                              <div className='border-t-1 border-gray-300 h-20 px-2 py-4'>
                                <p className='w-full text-center line-clamp-2'>{infoSelectAspirant?.career_preferences?.[2]?.career_name }</p>
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
                              <div className='h-12 flex items-center'>
                                <h3 className='font-semibold text-center px-2'>Entidad de procedencia</h3>
                              </div>
                              <div className='flex items-center border-t-1 border-gray-300 h-8 px-2'>
                                <p className='w-full text-center'>{infoSelectAspirant.secondary_school_data.state_origin}</p>
                              </div>
                            </div>
                          </td>

                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center px-2'>Municipio de procedencia</h3>
                              <div className='flex items-center border-t-1 border-gray-300 h-8 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.secondary_school_data.municipality_origin}</p>
                              </div>
                            </div>
                          </td>

                          <td colSpan={3} className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <div className='h-12 flex items-center justify-center'>
                                <h3 className='font-semibold text-center'>Escuela de procedencia</h3>
                              </div>
                              <div className='flex items-center border-t-1 border-gray-300 h-8 px-2 '>
                                <p className='w-full text-center'>{infoSelectAspirant.secondary_school_data.secondary_school_name}</p>
                              </div>
                            </div>
                          </td>
                        </tr>

                        <tr>
                          <td colSpan={2} className='border border-gray-300 h-20'>
                            <div className='flex flex-col'>
                              <div className='flex items-center justify-center h-15 md:h-auto'>
                                <h3 className='font-semibold text-center'>Fecha de egreso de la escuela</h3>
                              </div>
                              <div className='flex items-center border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.secondary_school_data.graduation_date}</p>
                              </div>
                            </div>
                          </td>

                          <td colSpan={3} className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <div className='flex justify-center items-center h-15 md:h-auto'>
                                <h3 className='font-semibold text-center'>Promedio general <br className='md:hidden' />(6 a 10)</h3>
                              </div>
                              <div className='flex items-center border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.secondary_school_data.general_average}</p>
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <table className='table-auto mt-6 text-auto border-collapse w-full border border-gray-300'>
                      <thead>
                        <tr>
                          <th colSpan={4}>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Calle (Numero interior y/o exterior)</h3>
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.address[0]}</p>
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
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.address[1]}</p>
                              </div>
                            </div>
                          </td>

                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Municipio</h3>
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.address[2]}</p>
                              </div>
                            </div>
                          </td>

                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Código postal</h3>
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.address[3]}</p>
                              </div>
                            </div>
                          </td>

                          <td className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Colonia</h3>
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.address[4]}</p>
                              </div>
                            </div>
                          </td>
                        </tr>

                        <tr>
                          <td colSpan={2} className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Correo electronico</h3>
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.email}</p>
                              </div>
                            </div>
                          </td>

                          <td colSpan={2} className='border border-gray-300'>
                            <div className='flex flex-col'>
                              <h3 className='font-semibold text-center'>Teléfono</h3>
                              <div className='border-t-1 border-gray-300 min-h-4 px-2 py-4'>
                                <p className='w-full text-center'>{infoSelectAspirant.phone_number}</p>
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </Modal>
          }
        </div>
      </div>
    </>
  )
}

export default StudentsSeccion2
