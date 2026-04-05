import React, { use, useEffect, useState } from 'react'
import InputSearch from '../../../components/React/InputSearch';
import Table from '../../../components/React/Table';
import { urlGlobal } from '../../../data/global';
import { userStore } from '../../../data/userStore';
import api from '../../../components/React/api';
import Modal from '../../../components/React/Modal';
import Button from '../../../components/React/Button';
import { Controller, useForm } from 'react-hook-form';
import Input from '../../../components/React/Input';
import ButtonPrimary from '../../../components/React/ButtonPrimary';
import SelectInputOption from '../../../components/React/SelectInputOption';

function StudentsSeccion1() {
  const [searchStudent, setSearchStudent] = useState("")
  const [valuesStudents, setValuesStudents] = useState([])
  const [indexDelete, setIndexDelete] = useState(-1);
  const [deleteAprob, setDeleteAprob] = useState(false)
  const [showDelete, setShowDelete] = useState(false)
  const headNames = ["Nombre", "Apellidos", "Edad", "Fecha de nacimiento", "Correo", "Telefono", "Sexo", "CURP", "Dirección", "Entidad", "Editar / Eliminar"]
  const dates = ["name", "last_name", "age", "birthdate", "email", "phone_number", "gender", "curp", "address", "address.1"]
  const optionsSelect = ["Nombre", "ID", "Administrador"]
  const genderOption = ["Hombre", "Mujer"]
  const [infoSelectAspirant, setInfoSelectAspirant] = useState({});
  const [showEditStudent, setShowEditStudent] = useState(false)

  const { control, reset, watch, handleSubmit, formState: { isDirty, dirtyFields } } = useForm({
    defaultValues: {
      name: "",
      last_name: "",
      email: "",
      address: [],
      phone_number: "",
      curp: "",
      gender: "",
    }
  });

  const closeModalDelete = () => {
    setShowDelete(false)
  }

  const showModalDelete = (i) => {
    setIndexDelete(i)
    setShowDelete(true)
  }


  const getStudents = async () => {
    try {
      const response = await api.get(`${urlGlobal}/admin-actions`, {
        headers: {
          Authorization: `Bearer ${userStore.tokens?.access_token}`,
          "Content-Type": "application/json",
        },
      });
      setValuesStudents(response.data.data.students);
    } catch (error) {
      console.error("Error fetching personal data:", error);
    }
  }

  const getSearchStudent = async () => {
    if (searchStudent == "") {
      getStudents();
    }
    try {
      const response = await api.get(`${urlGlobal}/admin-actions/search-student`, {
        headers: {
          Authorization: `Bearer ${userStore.tokens?.access_token}`,
          "Content-Type": "application/json",
        },
        params: {
          search: searchStudent
        }
      });
      setValuesStudents(response.data.data.students);
    } catch (error) {
      console.error("Error fetching personal data:", error);
    }
  }

  const deletePersonal = async () => {
    setDeleteAprob(true)
    closeModalDelete()
    setTimeout(() => {
      setValuesStudents(prev => prev.filter(item => item.id !== indexDelete));
      setIndexDelete(-1)
      setDeleteAprob(false);
      try {
        const response = api.post(`${urlGlobal}/admin-actions/delete-users`, { ids: [indexDelete] }, {
          headers: {
            Authorization: `Bearer ${userStore.tokens?.access_token}`,
            "Content-Type": "application/json",
          },
        });
        console.log(response)
      } catch (error) {
        console.error("Error fetching personal data:", error);
      }
    }, 300)
  }

  const updateStudent = async (data) => {
    try {
      const response = await api.patch(`${urlGlobal}/admin-actions/update-student/${infoSelectAspirant.id}`, data, {
        headers: {
          Authorization: `Bearer ${userStore.tokens?.access_token}`,
          "Content-Type": "application/json",
        },
      });
      const updatedStudent = response.data.data.student;

      // Actualizar el array local de estudiantes
      setValuesStudents((prevStudents) =>
        prevStudents.map((student) =>
          student.id === updatedStudent.id ? updatedStudent : student
        )
      );
      setShowEditStudent(false);
    } catch (error) {
      console.error("Error fetching personal data:", error);
    }
  }

  const onSubmit = (handleSubmit((data) => {
    if (isDirty) {
      updateStudent(data);
    }
  }));


  // Función para calcular edad
  const calculateAge = (birthDate) => {
    if (!birthDate) return null;
    const today = new Date();
    const birth = new Date(birthDate);

    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();

    // Ajustar si aún no ha cumplido años este año
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
      age--;
    }

    return age;
  };

  const clickEditStudent = (info) => {
    setInfoSelectAspirant(info)
    reset(info)
    setShowEditStudent(true)
  }

  useEffect(() => {
    getStudents();
  }, [])

  return (
    <>
      <div>
        <div className="w-full flex justify-between mt-4">
          <div className="flex md:gap-2 justify-start gap-0.5 w-5/12">
            <InputSearch valueSearch={"full_name"} className={"md:w-full md:h-11"} getOptions={getSearchStudent} options={valuesStudents} value={searchStudent} setValue={setSearchStudent} title="Buscar alumno" />
          </div>
        </div>
        {
          valuesStudents.length > 0 &&
          < Table Heads={headNames} datesCard={["age", "email"]} values={valuesStudents} dates={dates} clickEdit={clickEditStudent} showModalDelete={showModalDelete} indexDelete={indexDelete} deleteAprob={deleteAprob} deleteValue={deletePersonal} closeModalDelete={closeModalDelete} showDelete={showDelete} />
        }

        {infoSelectAspirant.name &&
          <Modal show={showEditStudent} onDisable={() => setShowEditStudent(false)} fullScreen={true} aceptModal={false}>
            <form onSubmit={onSubmit} className='w-full h-full pr-1 pl-4'>
              <div className='py-4 bg-white'>
                <h3 className='text-center text-md md:text-xl font-bold'>Datos del aspirante</h3>
              </div>
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
                        <td className='border border-gray-300'>
                          <Controller
                            name="name"
                            control={control}
                            rules={{ required: "El nombre es obligatorio" }}
                            render={({ field, fieldState }) => (
                              <>
                                <Input
                                  {...field}
                                  className=' text-center'
                                />
                                {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                              </>
                            )}
                          />
                        </td>
                        <td colSpan={2} className='border border-gray-300'>
                          <Controller
                            name="last_name"
                            control={control}
                            rules={{ required: "El apellido es obligatorio" }}
                            render={({ field, fieldState }) => (
                              <>
                                <Input
                                  {...field}
                                  className=' text-center'
                                />
                                {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                              </>
                            )}
                          />
                        </td>
                        <td className='border border-gray-300'>
                          <Controller
                            name="phone_number"
                            control={control}
                            rules={{ required: "El telefono es obligatorio" }}
                            render={({ field, fieldState }) => (
                              <>
                                <Input
                                  {...field}
                                  className=' text-center'
                                />
                                {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                              </>
                            )}
                          />
                        </td>
                        <td className='border border-gray-300'>
                          <p className='text-center w-full'>{watch("address")?.join(" ")}</p>
                        </td>
                      </tr>
                      <tr>
                        <td className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <div className='h-10 flex items-center justify-center'>
                              <h3 className='font-semibold text-center'>Edad</h3>
                            </div>
                            <div className='flex items-center border-t-1 border-gray-300 h-13'>
                              <p className='w-full text-center'>{calculateAge(watch("birthdate"))}</p>
                            </div>
                          </div>
                        </td>
                        <td className='w-40 border border-gray-300'>
                          <div className='flex flex-col'>
                            <div className='h-10 flex items-center justify-center'>
                              <h3 className='font-semibold text-center'>Fecha de nacimiento</h3>
                            </div>
                            <div className='border-t-1 border-gray-300 h-13'>
                              <Controller
                                name="birthdate"
                                control={control}
                                rules={{ required: "La fecha de nacimiento es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      type='date'
                                      className='text-center h-full'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>
                        <td className='w-40 border border-gray-300'>
                          <div className='flex flex-col'>
                            <div className='h-10 flex items-center justify-center'>
                              <h3 className='font-semibold text-center'>Correo</h3>
                            </div>
                            <div className='flex items-center border-t-1 border-gray-300 h-13'>
                              <Controller
                                name="email"
                                control={control}
                                rules={{ required: "El correo es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center h-full'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>
                        <td className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <div className='h-10 flex items-center justify-center'>
                              <h3 className='font-semibold text-center'>Sexo</h3>
                            </div>
                            <div className='flex border-t-1 border-gray-300'>
                              <Controller
                                name="gender"
                                control={control}
                                rules={{ required: "El genero es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <SelectInputOption
                                      className='h-full'
                                      value={field.value}
                                      setValue={field.onChange}
                                      options={genderOption}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>
                        <td className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <div className='h-10 flex items-center justify-center'>
                              <h3 className='font-semibold text-center'>Curp</h3>
                            </div>
                            <div className='border-t-1 border-gray-300 h-13'>
                              <Controller
                                name="curp"
                                control={control}
                                rules={{ required: "La curp es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center h-full'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
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
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="address.0"
                                control={control}
                                rules={{ required: "La calle es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
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
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="address.1"
                                control={control}
                                rules={{ required: "La calle es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>

                        <td className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <h3 className='font-semibold text-center'>Municipio</h3>
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="address.2"
                                control={control}
                                rules={{ required: "El municipio es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>

                        <td className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <h3 className='font-semibold text-center'>Código postal</h3>
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="address.3"
                                control={control}
                                rules={{ required: "El codigo postal es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>

                        <td className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <h3 className='font-semibold text-center'>Colonia</h3>
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="address.4"
                                control={control}
                                rules={{ required: "La colonia es obligatorio" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>
                      </tr>

                      <tr>
                        <td colSpan={2} className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <h3 className='font-semibold text-center'>Correo electronico</h3>
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="email"
                                control={control}
                                rules={{ required: "El correo" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      type='email'
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>

                        <td colSpan={2} className='border border-gray-300'>
                          <div className='flex flex-col'>
                            <h3 className='font-semibold text-center'>Teléfono</h3>
                            <div className='border-t-1 border-gray-300 min-h-4'>
                              <Controller
                                name="phone_number"
                                control={control}
                                rules={{ required: "El correo" }}
                                render={({ field, fieldState }) => (
                                  <>
                                    <Input
                                      {...field}
                                      className='text-center'
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                  </>
                                )}
                              />
                            </div>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div className='flex justify-end gap-2 mt-2 mb-2'>
                <ButtonPrimary
                  title={"Cancelar"}
                  type="button"
                  onClick={() => setShowEditStudent(false)}
                >
                </ButtonPrimary>
                <Button
                  title={"Actualizar información"}
                  className={"text-white hover:bg-green-700 active:bg-green-700 bg-green-500 rounded-lg justify-center "}
                >
                  Actualizar
                </Button>
              </div>
            </form>
          </Modal>
        }
      </div>
    </>
  )
}

export default StudentsSeccion1
