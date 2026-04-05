import React, { useEffect, useState } from 'react'
import Button from '../../../components/React/Button';
import InputTitleUp from '../../../components/React/InputTitleUp';
import { Controller, useForm } from 'react-hook-form';
import SelectInputOption from '../../../components/React/SelectInputOption';
import { urlGlobal } from '../../../data/global';;
import axios from 'axios';
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

const HomeAspirant = () => {
    const [partForm, setPartFomr] = useState(0);
    const [optionsCareer, setOptionCareer] = useState([])
    const [aspirantInfo, setAspirantInfo] = useState([]);
    const [maxDate, setMaxDate] = useState("");

    const defaultValues = {
        name: "",
        last_name: "",
        phone_number: "",
        address: ["", "", "", 0, "", ""],
        birthdate: "",
        email: "",
        gender: "",
        curp: "",
        schoolData: { secondary_school_name: "", state_origin: "", municipality_origin: "", graduation_date: "", general_average: 0 },
        career_preferences: [
            {},
            {},
            {}
        ]
    };

    const handleViewPDF = () => {
        const doc = new jsPDF();

        doc.text("Información del Aspirante", 14, 15);

        autoTable(doc, {
            head: [["Número de solicitud", "Número del aspirante", "Carrera preferida"]],
            body: [[
                aspirantInfo?.application_number || "",
                aspirantInfo?.id || "",
                aspirantInfo?.career_preferences?.[0].career_name
            ]],
            startY: 25,
            headStyles: {
                fillColor: [34, 197, 94], // Tailwind green-400
                textColor: [255, 255, 255],       // Texto negro
                halign: "center",
                fontStyle: "bold"
            },
            bodyStyles: {
                halign: "center",
            }
        });

        window.open(doc.output("bloburl"), "_blank");
    };

    const { control, register, handleSubmit, reset, watch, formState: { isDirty, errors } } = useForm({ defaultValues });
    const optionsGender = ["Hombre", "Mujer"]

    const career1 = watch("career_preferences.0");
    const career2 = watch("career_preferences.1");
    const career3 = watch("career_preferences.2");

    const getOptionCareer = async () => {
        try {
            const response = await axios.get(`${urlGlobal}/careersIndex`, {
                headers: {
                    "Content-Type": "application/json",
                },
            })
            setOptionCareer(response.data.data.careers);
        }
        catch (error) {
            console.log(error.message);
        }
    }

    const setDataAspirant = async (data) => {
        console.log(data)
        try {
            const response = await axios.post(`${urlGlobal}/setAspirant`, data, {
                headers: {
                    "Content-Type": "application/json",
                },
            })
            console.log(response.data)
            setAspirantInfo(response.data);
            setPartFomr(partForm + 1);
        }
        catch (error) {
            console.log(error?.response?.data);
        }
    }

    const onClickSig = (data) => {
        if (partForm == 1) {
            setDataAspirant(data)
        }
        if (partForm < 1) {
            setPartFomr(partForm + 1);
        }
    }



    const onSubmit = (handleSubmit((data) => {
        onClickSig(data);
    }));


    useEffect(() => {
        const today = new Date();
        today.setFullYear(today.getFullYear() - 15); // restar 15 años
        const formattedDate = today.toISOString().split("T")[0]; // formato YYYY-MM-DD
        setMaxDate(formattedDate);
        getOptionCareer();
    }, [])

    return (
        <div className='pb-12 md:pb-5'>
            <h2 className='text-center font-semibold text-lg md:text-5xl'>Nuevo Aspirante</h2>
            <h3 className='mt-4 text-center text-base md:text-4xl'>Datos del usuario</h3>
            <div className='w-full h-full mt-3 mb-4 overflow-hidden'>
                {partForm == 0 &&
                    <div>
                        <form className='hidden md:visible md:block' onSubmit={onSubmit}>
                            <table className='border-collapse border border-gray-400 w-full table-auto mt-2'>
                                <thead className=''>
                                    <tr>
                                        <th className='border border-gray-300 font-semibold'>
                                            <Controller
                                                name="name"
                                                control={control}
                                                rules={{ required: "El nombre es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Nombre"}
                                                            className='text-center h-full'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </th>
                                        <th colSpan={2} className='border border-gray-300'>
                                            <Controller
                                                name="last_name"
                                                control={control}
                                                rules={{ required: "Los apellidos son obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Apellidos"}
                                                            className='text-center h-full'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </th>
                                        <th className='border border-gray-300'>
                                            <Controller
                                                name="phone_number"
                                                control={control}
                                                rules={{ required: "El telefono son obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            type='tel'
                                                            title={"Telefono"}
                                                            className='text-center h-full'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </th>
                                        <th className='border border-gray-300'>
                                            <Controller
                                                name="address.5"
                                                control={control}
                                                rules={{ required: "La entidad son obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            type='tel'
                                                            title={"Entidad"}
                                                            className='text-center h-full'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td className='border border-gray-300'>
                                            <div className='flex items-center'>
                                                <Controller
                                                    name="birthdate"
                                                    control={control}
                                                    rules={{ required: "La fecha de nacimiento es obligatoria" }}
                                                    render={({ field, fieldState }) => (
                                                        <>
                                                            <InputTitleUp
                                                                {...field}
                                                                max={maxDate} // aquí se aplica la restricción
                                                                borderT={true}
                                                                title={"Fecha de nacimiento"}
                                                                className='text-center'
                                                                type={"date"}
                                                            />
                                                            {fieldState.error && <span>{fieldState.error.message}</span>}
                                                        </>
                                                    )}
                                                />
                                            </div>
                                        </td>
                                        <td colSpan={2} className='border border-gray-300'>
                                            <Controller
                                                name="email"
                                                control={control}
                                                rules={{ required: "El correo es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Correo"}
                                                            type="email"
                                                            className='text-center h-full'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="gender"
                                                control={control}
                                                rules={{ required: "La fecha de nacimiento es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <SelectInputOption
                                                            setValue={field.onChange}
                                                            value={field.value}
                                                            title='Sexo'
                                                            titleSelector='Selecciona una opción'
                                                            options={optionsGender}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="curp"
                                                control={control}
                                                rules={{ required: "La curp es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Curp"}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td className='border border-gray-300' colSpan={5}><p className='font-semibold text-center text-base md:text-lg py-2'>Carrera preferida</p></td>
                                    </tr>

                                    <tr>
                                        <td colSpan={2} className='border border-gray-300 max-w-20'>
                                            <Controller
                                                name="career_preferences.0"
                                                control={control}
                                                rules={{
                                                    required: "La primera carrera es obligatoria",
                                                    validate: (value) =>
                                                        value !== career2 && value !== career3 || "No puede repetir carrera"
                                                }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <SelectInputOption
                                                            setValue={field.onChange}
                                                            value={field.value}
                                                            valueOption='career_name'
                                                            title='Opción 1'
                                                            titleSelector='Selecciona una carrera'
                                                            options={optionsCareer}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                        <td colSpan={2} className='border border-gray-300 max-w-20'>
                                            <Controller
                                                name="career_preferences.1"
                                                control={control}
                                                rules={{
                                                    required: "La segunda carrera es obligatoria",
                                                    validate: (value) =>
                                                        value !== career1 && value !== career3 || "No puede repetir carrera"
                                                }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <SelectInputOption
                                                            setValue={field.onChange}
                                                            value={field.value}
                                                            valueOption='career_name'
                                                            title='Opción 2'
                                                            titleSelector='Selecciona una carrera'
                                                            options={optionsCareer}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                        <td className='border border-gray-300 max-w-20'>
                                            <Controller
                                                name="career_preferences.2"
                                                control={control}
                                                rules={{
                                                    required: "La tercera carrera es obligatoria",
                                                    validate: (value) =>
                                                        value !== career1 && value !== career2 || "No puede repetir carrera"
                                                }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <SelectInputOption
                                                            setValue={field.onChange}
                                                            value={field.value}
                                                            valueOption='career_name'
                                                            title='Opción 3'
                                                            titleSelector='Selecciona una carrera'
                                                            options={optionsCareer}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td className='border border-gray-300 py-2' colSpan={5}><h3 className='font-semibold text-center text-base md:text-lg'>Secundaria de procedencia</h3></td>
                                    </tr>

                                    <tr>
                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="schoolData.state_origin"
                                                control={control}
                                                rules={{ required: "La entidad es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Entidad de procedencia"}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="schoolData.municipality_origin"
                                                control={control}
                                                rules={{ required: "La entidad es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Municipio de procedencia"}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td colSpan={3} className='border border-gray-300 h-16'>
                                            <Controller
                                                name="schoolData.secondary_school_name"
                                                control={control}
                                                rules={{ required: "La escuela de procedencia es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            title={"Escuela de procedencia"}
                                                            className='text-center h-full'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colSpan={3} className='border border-gray-300 h-20'>
                                            <Controller
                                                name="schoolData.graduation_date"
                                                control={control}
                                                rules={{ required: "La escuela de procedencia es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            type='date'
                                                            title={"Fecha de egreso de la escuela"}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td colSpan={2} className='border border-gray-300'>
                                            <Controller
                                                name="schoolData.general_average"
                                                control={control}
                                                rules={{ required: "El promedio es obligatoria" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            type='number'
                                                            title={"Promedio general (6 a 10)"}
                                                            className='text-center'
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div className='mt-4 mb-5 flex gap-3 justify-center'>
                                <Button className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                                    Guardar
                                </Button>
                                <Button className={"ring-green-600 rounded w-24 ring hover:bg-green-600 hover:text-white hover:ring-3 active:ring-3 active:bg-green-600 active:text-white"}>
                                    Siguiente
                                </Button>
                            </div>
                        </form>
                        <form onSubmit={onSubmit} className='md:hidden flex flex-col gap-2'>
                            <Controller
                                name="name"
                                control={control}
                                rules={{ required: "El nombre es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Nombre"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />

                            <Controller
                                name="last_name"
                                control={control}
                                rules={{ required: "El apellido es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Apellidos"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="phone_number"
                                control={control}
                                rules={{ required: "El telefono es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Teléfono"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="address.5"
                                control={control}
                                rules={{ required: "La entidad es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Entidad"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="birthdate"
                                control={control}
                                rules={{ required: "La fecha de nacimiento es obligatoria" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            max={maxDate} // aquí se aplica la restricción
                                            type='date'
                                            title={"Fecha de nacimiento"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
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
                                            type='email'
                                            title={"Correo"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="gender"
                                control={control}
                                rules={{ required: "El correo es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            value={field.value}
                                            setValue={field.onChange}
                                            options={optionsGender}
                                            title='Sexo'
                                            titleSelector='Selecciona una opción'
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="curp"
                                control={control}
                                rules={{ required: "El curp es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Curp"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <p className='pl-2 mt-2 py-3 border-y-2 font-semibold text-lg border-gray-300'>Carrera preferida</p>
                            <Controller
                                name="career_preferences.0"
                                control={control}
                                rules={{
                                    required: "La opción 1 es obligatoria",
                                    validate: (value) =>
                                        value !== career2 && value !== career3 || "No puede repetir carrera"
                                }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            value={field.value}
                                            setValue={field.onChange}
                                            options={optionsCareer}
                                            title='Opción 1'
                                            valueOption='career_name'
                                            titleSelector='Selecciona una carrera'
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="career_preferences.1"
                                control={control}
                                rules={{
                                    required: "La segunda carrera es obligatoria",
                                    validate: (value) =>
                                        value !== career1 && value !== career3 || "No puede repetir carrera"
                                }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            setValue={field.onChange}
                                            value={field.value}
                                            valueOption='career_name'
                                            title='Opción 2'
                                            titleSelector='Selecciona una carrera'
                                            options={optionsCareer}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="career_preferences.2"
                                control={control}
                                rules={{
                                    required: "La tercera carrera es obligatoria",
                                    validate: (value) =>
                                        value !== career1 && value !== career2 || "No puede repetir carrera"
                                }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <SelectInputOption
                                            setValue={field.onChange}
                                            value={field.value}
                                            valueOption='career_name'
                                            title='Opción 3'
                                            titleSelector='Selecciona una carrera'
                                            options={optionsCareer}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <p className='pl-2 mt-2 py-3 border-y-2 font-semibold text-lg border-gray-300'>Escuela de procedencia</p>
                            <Controller
                                name="schoolData.state_origin"
                                control={control}
                                rules={{ required: "La entidad de procedencia es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            className='mt-2'
                                            title={"Entidad de procedencia"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="schoolData.municipality_origin"
                                control={control}
                                rules={{ required: "El municipio de procedencia es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Municipio de procedencia"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="schoolData.secondary_school_name"
                                control={control}
                                rules={{ required: "La escuela de procedenica es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Escuela de procedencia"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="schoolData.graduation_date"
                                control={control}
                                rules={{ required: "La fecha de ingreso es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            type='date'
                                            title={"Fecha de egreso"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="schoolData.general_average"
                                control={control}
                                rules={{ required: "El promedio general es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            type='number'
                                            title={"Promedio general (6 a 10)"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <div className='mt-4 mb-5 flex gap-3 justify-center'>
                                <Button className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                                    Guardar
                                </Button>
                                <Button className={"ring-green-600 rounded w-24 ring hover:bg-green-600 hover:text-white hover:ring-3 active:ring-3 active:bg-green-600 active:text-white"}>
                                    Siguiente
                                </Button>
                            </div>
                        </form>
                    </div>
                }

                {partForm == 1 &&
                    <>
                        <form onSubmit={onSubmit} className='hidden md:visible md:block'>
                            <table className='table-auto mt-6 text-auto border-collapse w-full border border-gray-300'>
                                <thead>
                                    <tr>
                                        <th colSpan={4}>
                                            <Controller
                                                name="address.0"
                                                control={control}
                                                rules={{ required: "La calle es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            className='mt-2'
                                                            title={"Calle (Numero interior y/o exterior)"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="address.1"
                                                control={control}
                                                rules={{ required: "El estado es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            className='text-center'
                                                            title={"Estado"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="address.2"
                                                control={control}
                                                rules={{ required: "El municipio es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            className='text-center'
                                                            title={"Municipio"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="address.3"
                                                control={control}
                                                rules={{ required: "El código postal es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            type='number'
                                                            borderT={true}
                                                            className='text-center'
                                                            title={"Código postal"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td className='border border-gray-300'>
                                            <Controller
                                                name="address.4"
                                                control={control}
                                                rules={{ required: "La colonia es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            className='text-center'
                                                            title={"Colonia"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colSpan={2} className='border border-gray-300'>
                                            <Controller
                                                name="email"
                                                control={control}
                                                rules={{ required: "El correo es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            borderT={true}
                                                            className='text-center'
                                                            title={"Correo (Personal)"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>

                                        <td colSpan={2} className='border border-gray-300'>
                                            <Controller
                                                name="phone_number"
                                                control={control}
                                                rules={{ required: "El telefono es obligatorio" }}
                                                render={({ field, fieldState }) => (
                                                    <>
                                                        <InputTitleUp
                                                            {...field}
                                                            type='tel'
                                                            borderT={true}
                                                            className='text-center'
                                                            title={"Teléfono"}
                                                        />
                                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                                    </>
                                                )}
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            < div className='flex my-2 gap-3 justify-center'>
                                <Button className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                                    Finalizar
                                </Button>
                            </div>
                        </form>
                        <form onSubmit={onSubmit} className='md:hidden flex flex-col gap-2'>
                            <Controller
                                name="address.0"
                                control={control}
                                rules={{ required: "La calle es obligatoria" }}
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
                                rules={{ required: "El estado es obligatoria" }}
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
                                rules={{ required: "El municipio es obligatoria" }}
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
                                rules={{ required: "El código postal es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            type='number'
                                            title={"Código postal"}
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
                            <Controller
                                name="email"
                                control={control}
                                rules={{ required: "El correo es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Correo (Personal)"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            <Controller
                                name="phone_number"
                                control={control}
                                rules={{ required: "El telefono es obligatorio" }}
                                render={({ field, fieldState }) => (
                                    <>
                                        <InputTitleUp
                                            {...field}
                                            title={"Teléfono"}
                                        />
                                        {fieldState.error && <span>{fieldState.error.message}</span>}
                                    </>
                                )}
                            />
                            < div className='flex my-2 gap-3 justify-center'>
                                <Button className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                                    Finalizar
                                </Button>
                            </div>
                        </form>
                    </>
                }
            </div>

            {partForm == 2 &&
                <div>
                    <table className='w-full border-collapse border-gray-400 border table-auto'>
                        <thead>
                            <tr>
                                <th>Numero de solicitud</th>
                                <th>Numero del aspirante</th>
                                <th>Carrera preferida</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td className='border border-gray-400 min-h-12'>
                                    <div className='flex items-center justify-center min-h-12'>{aspirantInfo?.application_number}</div>
                                </td>
                                <td className='border border-gray-400 min-h-12'>
                                    <div className='flex items-center justify-center min-h-12'>{aspirantInfo?.id}</div>
                                </td>
                                <td className='border border-gray-400 min-h-12'>
                                    <div className='flex items-center justify-center min-h-12 line-clamp-1'>
                                        <p className='p-1'>{aspirantInfo?.career_preferences?.[0]?.career_name}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    < div className='my-2 flex gap-3 justify-center'>
                        <Button onClick={handleViewPDF} className={"bg-neutral-600 rounded w-24 text-white ring ring-neutral-600 hover:ring-3 active:ring-3"}>
                            Imprimir
                        </Button>
                    </div>
                </div>
            }
        </div>
    )
}

export default HomeAspirant
