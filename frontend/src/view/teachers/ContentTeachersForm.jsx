import React, { useEffect, useState } from 'react'
import InputTitleUp from '../../components/React/InputTitleUp'
import ButtonSecondary from '../../components/React/ButtonSecondary'
import ButtonPrimary from '../../components/React/ButtonPrimary'
import { routes } from '../../data/routes'
import { urlGlobal } from '../../data/global'
import { userStore } from '../../data/userStore'
import api from '../../components/React/api'
import { Controller, useForm } from 'react-hook-form'

const ContentTeachersForm = () => {
    const [loading, setIsLoading] = useState(false);
    const [idUpdate, setIdUpdate] = useState(-1)

    const defaultValues = {
        user_id: -1,
        last_name: "",
        name: "",
        email: "",
        phone_number: ""
    };

    const { control, handleSubmit, reset, formState: { isDirty, errors, touchedFields } } = useForm({ defaultValues });

    const getDatasTeacher = async (id) => {
        try {
            const response = await api.get(`${urlGlobal}/teachers/${id}`, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                }
            });
            reset(response.data.data.teacher)
            setIsLoading(true)
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    const updateTeacher = async (data) => {
        try {
            const response = await api.patch(`${urlGlobal}/teachers/${idUpdate}`, data, {
                headers: {
                    Authorization: `Bearer ${userStore.tokens?.access_token}`,
                    "Content-Type": "application/json",
                },
            });
            reset(response.data.data.teacher);
            alert("Profesor actualizado correctamente")
            clickCancell();
        } catch (error) {
            console.error("Error fetching personal data:", error);
        }
    }

    const onSubmit = (handleSubmit((data) => {
        if (isDirty) {
            updateTeacher(data);
        }
    }));

    const clickCancell = () => {
        window.location.href = routes.teachers.url;
    }

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get("id");
        getDatasTeacher(id);
        setIdUpdate(id);
    }, [])


    return (
        loading &&
        <form onSubmit={onSubmit} className='mt-4 border-2 px-4 py-6'>
            <div className="w-full flex flex-col md:flex-row md:gap-4 ">
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <Controller
                            name="name"
                            control={control}
                            rules={{ required: "El nombre es obligatorio" }}
                            render={({ field }) => (
                                <InputTitleUp {...field} title={"Nombre"} />
                            )}
                        />

                        <Controller
                            name="email"
                            control={control}
                            rules={{ required: "El correo es obligatorio" }}
                            render={({ field }) => (
                                <InputTitleUp  {...field} type="email" title={"Correo"} />
                            )}
                        />
                    </div>
                </div>
                <div className='w-full'>
                    <div className='w-full flex flex-col md:w-9/12'>
                        <Controller
                            name="last_name"
                            control={control}
                            rules={{ required: "El apellido es obligatorio" }}
                            render={({ field }) => (
                                <InputTitleUp {...field} title={"Apellidos"} />
                            )}
                        />

                        <Controller
                            name="phone_number"
                            control={control}
                            rules={{ required: "El telefono es obligatorio" }}
                            render={({ field }) => (
                                <InputTitleUp {...field} type='tel' title={"Telefono"} />
                            )}
                        />
                    </div>
                </div>
            </div>
            <div className='mt-2 w-1/2 flex gap-2'>
                <ButtonSecondary showText={true} title={"Guardar"} />
                <ButtonPrimary showText={true} title={"Cancelar"} onClick={clickCancell} />
            </div>
        </form>
    )
}

export default ContentTeachersForm
