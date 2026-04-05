import React, { useState } from 'react'
import { userStore } from '../../data/userStore';
import HomeStudent from './student/HomeStudent';
import HomeStaff from './staff/HomeStaff';
import HomeAspirant from './aspirant/HomeAspirant';

const HomeContent = () => {
    const [rol, setRol] = useState(userStore.user?.roles);
    const renderPag = { student: <HomeStudent />, staff: <HomeStaff />, aspirant: <HomeAspirant /> }

    return (
        <div>
            {rol && renderPag[rol]}
        </div>
    )
}

export default HomeContent
