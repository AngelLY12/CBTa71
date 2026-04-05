import React, { useEffect, useState } from 'react'
import InputTitleUp from '../../components/React/InputTitleUp'
import SelectInputOption from '../../components/React/SelectInputOption'
import RadioButtonTitle from '../../components/React/RadioButtonTitle'
import ButtonPrimary from '../../components/React/ButtonPrimary'
import ButtonSecondary from '../../components/React/ButtonSecondary'
import { Controller, useForm } from 'react-hook-form'
import { urlGlobal } from '../../data/global'
import { userStore } from '../../data/userStore'
import { routes } from '../../data/routes'
import ChecksButtonTitle from '../../components/React/ChecksButtonTitle'
import api from '../../components/React/api'

const ContentAddRole = () => {
    const [mode, setMode] = useState("");
    const [idUpdate, setidUpdate] = useState(-1);
    const { control, reset, setValue, watch, handleSubmit, formState: { isDirty, dirtyFields } } = useForm({
        defaultValues: {
            name: "",
            last_name: "",
            email: "",
            roles: [],
            password: "",
            repeat_password: "",
            status: "",
            permission: []
        }
    });
    const optionsRol = [{ id: 0, value: "Administrador", valueReal: "admin" }, { id: 2, value: "Maestro", valueReal: "supervisor" }, { id: 3, value: "Personal financiero", valueReal: "financial-staff" }, { id: 4, value: "Supervisor", valueReal: "supervisor" }]
    const optionsStatus = ["activo", "baja", "baja-temporal", "eliminado"]
    const optionPermiss = ["Editar", "Visualizar", "Borrar"]
    const optionsViewAccept = ["Todas", "Roles", "Horarios", "Docentes", "Alumnos", "Matriculado", "Calificaciones", "Materias", "Pago"]

    const clickCancel = () => {
        window.location.href = routes.roles.url;
    }

    const onSubmit = (handleSubmit((data) => {
        if (mode != "update") {
            submit(data);
        } else {
            if (isDirty) {
                updateUser(data);
            }
        }
    }));

    const submit = async (data) => {
        try {
            const response = await api.post(`${urlGlobal}/admin-actions/register`, data, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            submitRol(response.data.data.user.id, data)
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const submitRol = async (id, data) => {
        try {
            const response = await api.post(`${urlGlobal}/admin-actions/updated-roles/${id}`, { 'rolesToAdd': [data.roles[0].valueReal], 'rolesToRemove': ["unverified"] }, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            if (mode != "update") {
                alert("Usuario registrado con exíto");
                reset();
            } else {
                alert("Campos del usuario actualizados con exíto");
                reset();
                clickCancel();
            }
        } catch (error) {
            console.error(error);
        }
    }

    const updateUser = async (data) => {
        try {
            const response = await api.patch(`${urlGlobal}/admin-actions/update-user/${idUpdate}`, data, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            if (dirtyFields.roles) {
                submitRol(idUpdate, data);
            }
        } catch (error) {
            console.error(error.response?.data);
        }
    }

    const getInfoStuden = async (id) => {
        try {
            const response = await api.get(`${urlGlobal}/admin-actions/show-users/${id}`, {
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${userStore.tokens?.access_token}`
                },
            });
            console.log(response.data.data.user)
            reset(response.data.data.user);
        } catch (error) {
            console.error(error);
        }
    }

    const getMode = async (id) => {
        if (id) {
            await getInfoStuden(id);
            setMode("update")
        } else {
            setMode("add")
        }
    }

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get("id");
        setidUpdate(id);
        getMode(id);
    }, [])

    return (
        mode &&
        <form onSubmit={onSubmit} className='mt-4 border-2 px-4 py-6'>
            <div className="w-full flex flex-col md:flex-row md:gap-4 ">
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <Controller
                            name="name"
                            control={control}
                            rules={{ required: "El nombre es obligatorio" }}
                            render={({ field, fieldState }) => (
                                <>
                                    <InputTitleUp
                                        {...field}
                                        title={"Nombre"}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />
                        <Controller
                            name="email"
                            control={control}
                            rules={{ required: "El correo es obligatorio" }}
                            render={({ field, fieldState }) => (
                                <>
                                    <InputTitleUp
                                        {...field}
                                        title={"Correo"}
                                        type="email"
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />
                        <Controller
                            name="roles.0"
                            control={control}
                            rules={{ required: "El rol es obligatorio" }}
                            render={({ field, fieldState }) => (
                                <>
                                    <SelectInputOption
                                        title={"Rol"}
                                        titleSelector={"Seleccionar rol"}
                                        setValue={field.onChange}
                                        value={field.value}
                                        valueSet='valueReal'
                                        valueOption='value'
                                        options={optionsRol}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />
                        <Controller
                            name="permission"
                            control={control}
                            rules={{
                                validate: (value) =>
                                    idUpdate ? true : "Los permisos son obligatorios",
                            }}
                            render={({ field, fieldState }) => (
                                <>
                                    <ChecksButtonTitle
                                        setValue={field.onChange}
                                        value={field.value}
                                        sizeW={"w-auto"}
                                        title={"Permisos"}
                                        options={optionPermiss}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />
                    </div>
                </div>
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <Controller
                            name="last_name"
                            control={control}
                            rules={{ required: "Los apellidos son obligatorios" }}
                            render={({ field, fieldState }) => (
                                <>
                                    <InputTitleUp
                                        {...field}
                                        title={"Apellidos"}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />
                        <Controller
                            name="password"
                            control={control}
                            rules={{
                                validate: (value) =>
                                    idUpdate ? true : "La contraseña es obligatoria",
                            }}
                            render={({ field, fieldState }) => (
                                <>
                                    <InputTitleUp
                                        {...field}
                                        watch={field.value}
                                        type='password'
                                        required={false}
                                        title={idUpdate ? "Nueva contraseña" : "Contraseña"}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />

                        <Controller
                            name="repeat_password"
                            control={control}
                            rules={{
                                validate: {
                                    requiredIfTyped: () =>
                                        !watch('password') ? true : "Repite la contraseña es obligatoria",
                                    matchPassword: (value) =>
                                        value === watch("password") || "Las contraseñas no coinciden",
                                },
                            }}
                            render={({ field, fieldState }) => (
                                <>
                                    <InputTitleUp
                                        {...field}
                                        watch={field.value}
                                        required={watch("password") == "" ? true : false}
                                        type="password"
                                        title="Repetir contraseña"
                                    />
                                    {fieldState.error && (
                                        <span className="text-red-600">{fieldState.error.message}</span>
                                    )}
                                </>
                            )}
                        />

                        <Controller
                            name="status"
                            control={control}
                            rules={{ required: "El estatus es obligatorio" }}
                            render={({ field, fieldState }) => (
                                <>
                                    <SelectInputOption
                                        title={"Estatus"}
                                        titleSelector={"Seleccionar estatus"}
                                        setValue={field.onChange}
                                        value={field.value}
                                        options={optionsStatus}
                                    />
                                    {fieldState.error && <span className='text-red-600'>{fieldState.error.message}</span>}
                                </>
                            )}
                        />

                        <Controller
                            name="viewPermission"
                            control={control}
                            rules={{
                                validate: () =>
                                    idUpdate ? true : "Los permisos son obligatorios"
                            }}
                            render={({ field, fieldState }) => (
                                <>
                                    <RadioButtonTitle
                                        setValue={field.onChange}
                                        value={field.value}
                                        title={"Vistar permitidas"}
                                        options={optionsViewAccept}
                                    />
                                    {fieldState.error && <span className='text-red-600' >{fieldState.error.message}</span>}
                                </>
                            )}
                        />
                    </div>
                </div>
            </div>
            <div className='mt-2 w-1/2 flex gap-2'>
                <ButtonSecondary showText={true} title={"Guardar"} />
                <ButtonPrimary type='button' showText={true} title={"Cancelar"} onClick={clickCancel} />
            </div>
        </form>
    )
}

export default ContentAddRole
