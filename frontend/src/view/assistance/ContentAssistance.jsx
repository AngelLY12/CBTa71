import React, { useState } from 'react'
import StudentAssistance from './student/StudentAssistance';
import StaffAssistance from './staff/StaffAssistance';
import { userStore } from '../../data/userStore';

const ContentAssistance = () => {
    const [rol, setRol] = useState(userStore.user?.roles);
    const renderPag = { student: <StudentAssistance />, staff: <StaffAssistance/> }

    return (
        <div>
            {rol && renderPag[rol]}
        </div>
    )
}

export default ContentAssistance
