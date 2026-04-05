import React, { useState } from 'react'
import StaffClassShedule from './staff/StaffClassShedule';
import StudentClassShedule from './student/StudentClassShedule';
import { routes } from '../../data/routes';
import { userStore } from '../../data/userStore';

const ClassSheduleContent = () => {
    const linksSubNavbar = [
        { tab: "", value: "Alumnos" },
        { tab: "classShedule-teacher", value: "Maestros" },
    ];
    const [rol, setRol] = useState(userStore.user?.roles);
    const renderPag = { student: <StudentClassShedule />, staff: <StaffClassShedule routePrimary={routes.classSchedule.url} opcionTab={linksSubNavbar}/> }

    return (
        <div>
            {rol && renderPag[rol]}
        </div>
    )
}

export default ClassSheduleContent
