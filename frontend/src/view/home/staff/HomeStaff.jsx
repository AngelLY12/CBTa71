import React, { useEffect, useState } from 'react'
import CardUseRecently from '../../../components/React/CardUseRecently';
import { routes } from '../../../data/routes';

const HomeStaff = () => {
    const [recentItems, setRecentItems] = useState([]);

    const items = [
        {
            title: "Alumnos",
            url: routes.students.url,
            img: "/img/bg-2.png",
            alt: "Imagen demo",
        },
        {
            title: "Docentes",
            url: routes.teachers.url,
            img: "/img/bg-1.png",
            alt: "Imagen demo",
        },
        {
            title: "Personal",
            url: routes.roles.url,
            img: "/img/bg-2.png",
            alt: "Imagen demo",
        },
          {
            title: "Materias",
            url: routes.matter.url,
            img: "/img/bg-1.png",
            alt: "Imagen demo",
        }
    ];

    useEffect(() => {
        // Leer historial guardado en localStorage
        const history = JSON.parse(localStorage.getItem("recentPaths") || "[]");

        // Mapear las rutas a los items definidos
        const mappedItems = history
            .map(path => items.find(item => item.url === path))
            .filter(Boolean); // eliminar nulls si no hay coincidencia

        setRecentItems(mappedItems);
        console.log(mappedItems)
    }, []);

    return (
        <div>
            <p className="text-lg font-semibold">Usados recientes</p>
            <div className="flex flex-wrap justify-evenly gap-x-1 gap-y-2 mt-2">
                {recentItems.map((item) => (
                    <CardUseRecently key={item.id} item={item} />
                ))}
            </div>
        </div>
    )
}

export default HomeStaff
