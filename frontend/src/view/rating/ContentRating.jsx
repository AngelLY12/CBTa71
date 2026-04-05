import React, { useState } from 'react'
import StudentRating from './student/StudentRating';
import StaffRating from './staff/StaffRating';
import { userStore } from '../../data/userStore';
import { routes } from '../../data/routes';

const ContentRating = () => {
    const [rol, setRol] = useState(userStore.user?.roles);

    const linksSubNavbar = [
        { tab: "", value: "Buscar" },
        { tab: "rating-capture", value: "Capturar" },
    ];

    const renderPag = { student: <StudentRating />, staff: <StaffRating opcionTab={linksSubNavbar} routePrimary={routes.ratings.url} /> }

    return (
        <div>
            {rol && renderPag[rol]}
        </div>
    )
}

export default ContentRating
