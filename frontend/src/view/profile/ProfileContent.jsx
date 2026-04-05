import React, { useEffect, useRef, useState } from 'react'
import Input from '../../components/React/Input'
import SelectInputOption from '../../components/React/SelectInputOption'
import InputTitleUp from '../../components/React/InputTitleUp';
import { urlGlobal, urlPrimary } from '../../data/global';
import { userStore } from '../../data/userStore';
import Button from '../../components/React/Button';
import { Controller, useForm } from 'react-hook-form';
import api from '../../components/React/api';

const ProfileContent = () => {
    const optionsGenr = ["Mujer", "Hombre"];
    const fileInputRef = useRef(null);
    const [loading, setLoading] = useState(true);
    const [academicInfo, setAcademicInfo] = useState(null);
    const defaultValues = {
        address: [
            "",
            "",
            "",
            1111, // número, no string
            ""
        ],
        age: 0,
        birthdate: "",
        curp: "",
        email: "",
        gender: "",
        last_name: "",
        name: "",
        phone_number: ""
    };

    const { control, register, handleSubmit, reset, watch, formState: { isDirty, errors } } = useForm({ defaultValues });
    const [preview, setPreview] = useState('');
    const [imageDirty, setImageDirty] = useState(false);
    const [selectedFile, setSelectedFile] = useState(null);


    const getInfoStudent = async () => {
        try {
            const response = await api.get(`${urlGlobal}/users/student-details`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                },
            });
            const detailsStudent = response.data.data.student_details.details;
            const academicInfo = response.data.data.student_details.academic;
            reset(detailsStudent);
            setAcademicInfo(academicInfo);
            setPreview(`${urlPrimary}/img/student-profile/${academicInfo.n_control}.png`)
            setLoading(false);
        } catch (error) {
            console.error("Error:", error.message);
        }
    }

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

    const setDate = async (data) => {
        if (imageDirty) {
            setProfileImg(selectedFile);
            setImageDirty(false);
        }
        if (isDirty) {
            try {
                const response = await api.patch(`${urlGlobal}/users/update`, data, {
                    headers: {
                        Authorization: `Bearer ${userStore.tokens?.access_token}`,
                        "Content-Type": "application/json",
                    },
                });
                const detailsStudent = response.data.data.user;
                reset(detailsStudent);
            } catch (error) {
                console.error(error.response?.data);
            }
        }
    }

    const handleButtonClick = () => {
        fileInputRef.current.click(); // dispara el input
    };

    const handleFileChange = (event) => {
        const file = event.target.files[0];
        if (file) {
            // Crear una URL temporal en memoria para mostrar la imagen seleccionada
            const localPreview = URL.createObjectURL(file);
            setPreview(localPreview);

            // Guardar el archivo para luego mandarlo al backend
            const formData = new FormData();
            formData.append("profile_image", file);
            setSelectedFile(formData);
            setImageDirty(true);
        }
    };

    const setProfileImg = async (img) => {
        try {
            const response = await api.post(`${urlGlobal}/users/updateProfileImg`, img, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                },
            });
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const onSubmit = (handleSubmit((data) => {
        setDate(data)
    }));

    useEffect(() => {
        getInfoStudent();
    }, [])

    return (
        (loading)
            ?
            <div className=' text-gray-400 mt-5 flex justify-center items-center'>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="size-8 animate-spin">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            :
            <div className='mb-8 mt-6'>
                {/* Zona de computadora */}
                <form onSubmit={onSubmit} >
                    < h3 className='text-center font-semibold text-lg md:text-3xl'>Datos generales</h3 >
                    <div className='py-0.5 flex flex-col min-h-max lg:flex-row mt-4 h-auto md:border md:border-gray-300'>
                        <div className='flex justify-center h-full lg:h-48 lg:block lg:w-2/12'>
                            <div className='h-full flex flex-col'>
                                <div className='flex h-full relative'>
                                    <img className='object-fill lg:rounded-none w-48 h-40 border lg:w-full lg:h-full' src={preview} alt="" />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        className="hidden"
                                        id="profileImageInput"
                                        ref={fileInputRef}
                                        onChange={handleFileChange}
                                    />
                                    <Button
                                        onClick={handleButtonClick}
                                        type='button'
                                        className={'opacity-0 hover:opacity-100 hover:bg-white/50 absolute inset-0'}
                                    >
                                        Actualizar
                                    </Button>
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
                                        <td className='border border-gray-300 w-48'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Nombre</p>
                                                </div>

                                                <div>
                                                    <Controller
                                                        name="name"
                                                        control={control}
                                                        rules={{ required: "El nombre es obligatorio" }}
                                                        render={({ field }) => (
                                                            <Input {...field} value={field.value} className="text-center text-[1.1rem]" />
                                                        )}
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300 w-64'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Apellidos</p>
                                                </div>
                                                <div>
                                                    <Controller
                                                        name="last_name"
                                                        control={control}
                                                        rules={{ required: "El apellido es obligatorio" }}
                                                        render={({ field }) => (
                                                            <Input {...field} value={field.value || ""} className="text-center text-[1.1rem]" />
                                                        )}
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Edad</p>
                                                </div>

                                                <div className='flex items-center justify-center h-12 p-1'>
                                                    <p className='text-[1.1rem]'>{calculateAge(watch("birthdate"))}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300 2xl:max-w-28'>
                                            <div className='flex flex-col h-full'>
                                                <div className='w-full h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Fecha de nacimiento</p>
                                                </div>

                                                <div className='w-full flex items-end h-12 '>
                                                    <Controller
                                                        name="birthdate"
                                                        control={control}
                                                        rules={{ required: "La fecha de nacimiento es obligatoria" }}
                                                        render={({ field, fieldState }) => (
                                                            <>
                                                                <Input
                                                                    {...field} // incluye value y onChange
                                                                    type="date"
                                                                    className="text-[1.1rem]"
                                                                />
                                                                {fieldState.error && <span>{fieldState.error.message}</span>}
                                                            </>
                                                        )}
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>No.Control</p>
                                                </div>

                                                <div className='flex justify-center items-center h-12 p-1'>
                                                    <p className='text-[1.1rem] text-lg'>{academicInfo.n_control}</p>
                                                </div>
                                            </div>
                                        </td>

                                        <td className='border border-gray-300 w-24'>
                                            <div className='flex flex-col'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Sexo</p>
                                                </div>

                                                <div className='fle h-12'>
                                                    <Controller
                                                        name="gender"
                                                        control={control}
                                                        rules={{ required: "El sexo es obligatorio" }}
                                                        render={({ field, fieldState }) => (
                                                            <>
                                                                <SelectInputOption
                                                                    value={field.value}          // valor actual
                                                                    setValue={field.onChange}    // actualiza el valor
                                                                    className="w-full"
                                                                    titleSelector="Selecciona el sexo"
                                                                    options={optionsGenr}
                                                                />
                                                                {fieldState.error && <span>{fieldState.error.message}</span>}
                                                            </>
                                                        )}
                                                    />
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
                                                    <p className='w-full text-center'>{watch('email')}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className='max-w-32 xl:max-w-max border border-gray-300'>
                                            <div className='flex flex-col w-full h-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center truncate'>Dirección (calle, número y colonia)</p>
                                                </div>
                                                <div className='min-h-28 overflow-hidden lg:min-h-12 lg:max-h-12 flex justify-center items-center p-1'>
                                                    <p className='truncate'>{watch('address').join(' ')}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td colSpan={2} className='border border-gray-300 max-w-28'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Curp</p>
                                                </div>

                                                <div className='flex items-center min-h-28 lg:min-h-12 p-1'>
                                                    <p className='w-full text-center break-words'>{watch('curp')}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className='border border-gray-300'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Teléfono</p>
                                                </div>

                                                <div className='flex items-center min-h-28 lg:min-h-12 lg:max-h-12'>
                                                    <p className='w-full text-center'>{watch('phone_number')}</p>
                                                </div>
                                            </div>
                                        </td>

                                        <td className='w-12 border border-gray-300'>
                                            <div className='flex flex-col w-full'>
                                                <div className='h-8 p-1 border-b border-gray-300'>
                                                    <p className='w-full text-center'>Entidad</p>
                                                </div>
                                                <div className='flex items-center min-h-28 lg:min-h-12 lg:max-h-12'>
                                                    <Controller
                                                        name="gender"
                                                        control={control}
                                                        rules={{ required: "El sexo es obligatorio" }}
                                                        render={({ field, fieldState }) => (
                                                            <>
                                                                <SelectInputOption
                                                                    value={field.value}          // valor actual
                                                                    setValue={field.onChange}    // actualiza el valor
                                                                    titleSelector={"Selecciona la entidad"}
                                                                    type="date"
                                                                />
                                                                {fieldState.error && <span>{fieldState.error.message}</span>}
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

                    <div className='hidden md:visible md:block mt-6'>
                        <h3 className='font-semibold text-center text-lg md:text-3xl mb-2 md:mb-0'>Datos domiciliarios</h3>
                        <table className='md:table table-auto mt-6 text-auto border-collapse w-full border border-gray-300'>
                            <thead>
                                <tr>
                                    <th colSpan={4}>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Calle (Numero interior y/o exterior)</h3>
                                            <div className='border-t-1 border-gray-300 '>
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
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
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
                                            <div className='border-t-1 border-gray-300 '>
                                                <Controller
                                                    name="address.1"
                                                    control={control}
                                                    rules={{ required: "El estado es obligatorio" }}
                                                    render={({ field, fieldState }) => (
                                                        <>
                                                            <Input
                                                                {...field}
                                                                className='text-center'
                                                            />
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                                        </>
                                                    )}
                                                />
                                            </div>
                                        </div>
                                    </td>

                                    <td className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Municipio</h3>
                                            <div className='border-t-1 border-gray-300 '>
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
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                                        </>
                                                    )}
                                                />
                                            </div>
                                        </div>
                                    </td>

                                    <td className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Código postal</h3>
                                            <div className='border-t-1 border-gray-300 '>
                                                <Controller
                                                    name="address.3"
                                                    control={control}
                                                    rules={{ required: "El codigo postal es obligatorio" }}
                                                    render={({ field, fieldState }) => (
                                                        <>
                                                            <Input
                                                                {...field}
                                                                className='text-center'
                                                                type='number'
                                                                value={field.value ?? ""}
                                                                onChange={(e) => field.onChange(e.target.valueAsNumber)}
                                                            />
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                                        </>
                                                    )}
                                                />
                                            </div>
                                        </div>
                                    </td>

                                    <td className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Colonia</h3>
                                            <div className='border-t-1 border-gray-300 '>
                                                <Controller
                                                    name="address.4"
                                                    control={control}
                                                    rules={{ required: "La colonia es obligatoria" }}
                                                    render={({ field, fieldState }) => (
                                                        <>
                                                            <Input
                                                                {...field}
                                                                className='text-center'
                                                            />
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
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
                                            <div className='border-t-1 border-gray-300 '>
                                                <Input
                                                    readOnly
                                                    {...register("email", {
                                                        required: "El email es obligatorio",
                                                    })}
                                                    className='text-center'
                                                />
                                            </div>
                                        </div>
                                    </td>

                                    <td colSpan={2} className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Teléfono</h3>
                                            <div className='border-t-1 border-gray-300 '>
                                                <Controller
                                                    name="phone_number"
                                                    control={control}
                                                    rules={{ required: "El teléfono esobligatorio" }}
                                                    render={({ field, fieldState }) => (
                                                        <>
                                                            <Input
                                                                {...field}
                                                                className='text-center'
                                                                type='tel'
                                                            />
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
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

                    <div className='hidden md:visible md:block mt-6'>
                        <h3 className='font-semibold text-center text-lg md:text-3xl'>Datos academicos</h3>
                        <table className='md:table table-auto mt-6 text-auto border-collapse w-full border border-gray-300'>
                            <thead>
                                <tr>
                                    <th colSpan={4}>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Carrera</h3>
                                            <div className='border-t-1 border-gray-300 bg-gray-50'>
                                                <p className='text-center font-normal'>{academicInfo.career}</p>
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
                                                <p className='min-h-6 text-center font-normal'>{academicInfo.enrollment}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Semestre</h3>
                                            <div className='border-t-1 border-gray-300 '>
                                                <p className='min-h-6 text-center font-normal'>{academicInfo.semester}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Grupo</h3>
                                            <div className='border-t-1 border-gray-300 '>
                                                <p className='min-h-6 text-center font-normal'>{academicInfo.group}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Taller</h3>
                                            <div className='border-t-1 border-gray-300 '>
                                                <p className='min-h-6 text-center font-normal'>{academicInfo.workshop}</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colSpan={2} className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Materias aprobadas</h3>
                                            <div className='flex justify-center items-center border-t-1 min-h-10 border-gray-300 '>
                                                <p className=' text-center font-normal'>{academicInfo.subjects_passed}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td colSpan={2} className='border border-gray-300'>
                                        <div className='flex flex-col'>
                                            <h3 className='font-semibold text-center'>Materias reprobadas</h3>
                                            <div className='flex justify-center items-center border-t-1 min-h-10 border-gray-300 '>
                                                <p className='text-center font-normal'>{academicInfo.subjects_failed}</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        className={`fixed right-0 bottom-0 w-auto h-auto pr-2 pb-2 transform transition-transform duration-500 
                        ${isDirty || imageDirty ? "translate-x-0" : "translate-x-full"}`}
                    >
                        <Button
                            title={"Guardar cambios"}
                            className={"hidden md:visible md:block text-white hover:bg-green-700 active:bg-green-700 bg-green-500 rounded-lg justify-center "}
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth="1.5"
                                stroke="currentColor"
                                className="size-7"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"
                                />
                            </svg>
                        </Button>
                    </div>
                </form>

                <form className='visible md:hidden' onSubmit={onSubmit}>
                    {/* Zona de celular */}
                    <div className='flex flex-col gap-6'>
                        <div className='visible md:hidden w-full flex flex-col'>
                            <Controller
                                name="name"
                                control={control}
                                rules={{ required: "El nombre es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            className={"w-full"}
                                            title={"Nombre"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="last_name"
                                control={control}
                                rules={{ required: "La colonia es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            className={"w-full"}
                                            title={"Apellidos"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Edad</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{calculateAge(watch('birthdate'))}</p>
                            </div>
                            <Controller
                                name="birthdate"
                                control={control}
                                rules={{ required: "La colonia es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            type='date'
                                            {...field}
                                            className={"w-full"}
                                            title={"Fecha de nacimiento"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Numero de control</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.n_control}</p>
                            </div>
                            <Controller
                                name="gender"
                                control={control}
                                rules={{ required: "La colonia es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            nameValue={'gender'}
                                            setValue={field.onChange}
                                            value={field.value}
                                            options={optionsGenr}
                                            titleSelector={"Selecciona el sexo"}
                                            title={"Sexo"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                        </div>
                        <div className='visible md:hidden w-full flex flex-col'>
                            <h3 className='font-semibold text-center text-lg md:text-3xl mb-2 md:mb-0'>Datos domiciliarios</h3>
                            <Controller
                                name="email"
                                control={control}
                                rules={{ required: "La colonia es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            readOnly
                                            type='email'
                                            {...field}
                                            className={"w-full"}
                                            title={"Correo"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Dirección</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{watch('address').join(' ')}</p>
                            </div>
                            <Controller
                                name="curp"
                                control={control}
                                rules={{ required: "La curp es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            readOnly
                                            type='email'
                                            {...field}
                                            className={"w-full"}
                                            title={"Curp"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="email"
                                control={control}
                                rules={{ required: "La colonia es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            className={"w-full"}
                                            title={"Teléfono"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />

                            <Controller
                                name="address.0"
                                control={control}
                                rules={{ required: "La entidad es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            options={["Masculino", "Femenino"]}
                                            titleSelector={"Selecciona la entidad"}
                                            title={"Entidad"}
                                            value={field.value}
                                            setValue={field.onChange}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                        </div>
                        <div className='md:hidden flex flex-col gap-2'>
                            <Controller
                                name="address.0"
                                control={control}
                                rules={{ required: "La calle es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Calle (Numero interior y/o exterior)"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="address.1"
                                control={control}
                                rules={{ required: "El estado es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Estado"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="address.2"
                                control={control}
                                rules={{ required: "El municipio es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Municipio"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="address.3"
                                control={control}
                                rules={{ required: "El codigo postal es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            type='number'
                                            title={"Código postal"}
                                            value={field.value ?? ""}
                                            onChange={(e) => field.onChange(e.target.valueAsNumber)}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="address.4"
                                control={control}
                                rules={{ required: "La colonia es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Colonia"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <InputTitleUp
                                readOnly
                                type='email'
                                {...register("email", {
                                    required: "El email es obligatorio",
                                })}
                                title={"Correo (Personal)"}
                            />
                            <Controller
                                name="phone_number"
                                control={control}
                                rules={{ required: "El telefono es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Telefono"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                        </div>
                        <div className='md:hidden flex flex-col gap-2'>
                            <h3 className='font-semibold text-center text-lg md:text-3xl'>Datos academicos</h3>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Carrera</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.career}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Matricula</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.enrollment}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Semestre</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.semester}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Grupo</p>
                                <p className='min-h-9 mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.group}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Taller</p>
                                <p className='min-h-9 mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.workshop}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Carrera</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.career}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Materias aprobadas</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.subjects_passed}</p>
                            </div>
                            <div className='flex flex-col'>
                                <p className='font-semibold w-full'>Materias aprobadas</p>
                                <p className='mt-2 p-2 rounded outline-1 outline-gray-300 bg-gray-100 w-full'>{academicInfo.subjects_failed}</p>
                            </div>
                        </div>
                    </div>
                    <div
                        className={`fixed right-0 bottom-0 w-auto h-auto pr-2 pb-2 transform transition-transform duration-500 
                        ${isDirty || imageDirty ? "translate-x-0" : "translate-x-full"}`}
                    >
                        <Button
                            title={"Guardar cambios"}
                            className={"md:hidden visible block text-white bg-green-500 rounded-lg justify-center"}
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth="1.5"
                                stroke="currentColor"
                                className="size-7"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"
                                />
                            </svg>
                        </Button>
                    </div>
                </form>
            </div >

    )
}

export default ProfileContent
