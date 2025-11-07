import React, { useEffect, useState } from 'react'

function SubNavbar({ url, options = [{ tab, value }], windowTab, setWindowTab, setParamIndex }) {

    const clickOption = (tab, i) => {
        setWindowTab(tab)
        setParamIndex(i)
        if (tab != "") {
            window.history.pushState({}, '', `${url}?tab=${tab}`)
        } else {
            window.history.pushState({}, '', `${url}`)
        }
    }
    
    return (
        <div className='flex gap-2 overflow-auto'>
            {options.map((option, i) => (
                <button key={i} className={`cursor-pointer hover:underline ${option.tab == windowTab && 'text-blue-600 underline'}`} onClick={() => clickOption(option.tab, i)}>
                    {option.value}
                </button>
            ))}
        </div>
    )
}

export default SubNavbar
