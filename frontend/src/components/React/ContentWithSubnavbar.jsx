import React from 'react'
import SubNavbar from './SubNavbar'

function ContentWithSubnavbar({url, options, setWindowTab, windowTab, setParamIndex, children}) {
  return (
    <>
       <SubNavbar url={url} options={options} windowTab={windowTab} setWindowTab={setWindowTab} setParamIndex={setParamIndex} />
        {children}
    </>
  )
}

export default ContentWithSubnavbar
