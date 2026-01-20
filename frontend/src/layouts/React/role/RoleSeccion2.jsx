import React, { useState } from 'react'

function RoleSeccion2() {
  const [enableAspirant, setEnableAspirant] = useState(false);
  const [enableMaterial, setEnableMaterial] = useState(false);
  const [enableSectionPay, setEnableSectionPay] = useState(false);

  const activeAspirant = (e) => {
    setEnableAspirant(e.target.checked);
  }

  const activeMaterial = (e) => {
    setEnableMaterial(e.target.checked)
  }

  const activeSeccionPay = (e) => {
    setEnableSectionPay(e.target.checked)
  }

  return (
    <div className='border-2 mt-4 p-4 rounded-md'>
      <p className='text-lg md:text-2xl font-medium'>Permisos</p>
      <div className='flex flex-col gap-2 mt-2'>
        <label className="w-full inline-flex items-center justify-between mr-5 cursor-pointer">
          <span className={`select-none mr-3 text-sm text-gray-900 ${enableAspirant && "font-bold"}`} >Habilitar la pestaña de Aspirantes en Login</span>
          <input onChange={activeAspirant} type="checkbox" value="" className="sr-only peer" defaultChecked={enableAspirant} />
          <div className="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-green-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
        </label>

        <label className="w-full inline-flex items-center justify-between mr-5 cursor-pointer">
          <span className={`select-none mr-3 text-sm text-gray-900 ${enableMaterial && "font-bold"}`} >Habilitar la sección de Materias para alumnos</span>
          <input onChange={activeMaterial} type="checkbox" value="" className="sr-only peer" defaultChecked={enableMaterial} />
          <div className="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-green-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
        </label>

        <label className="w-full inline-flex items-center justify-between mr-5 cursor-pointer">
          <span className={`select-none mr-3 text-sm text-gray-900 ${enableSectionPay && "font-bold"}`} >Habilitar sección de Pagos para alumnos</span>
          <input onChange={activeSeccionPay} type="checkbox" value="" className="sr-only peer" defaultChecked={enableSectionPay} />
          <div className="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-green-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
        </label>
      </div>
    </div>
  )
}

export default RoleSeccion2
