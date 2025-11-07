import React, { useEffect, useState } from 'react'
import StudentsSeccion1 from '../../layouts/React/role/StudentsSeccion1';
import ContentWithSubnavbar from '../../components/React/ContentWithSubnavbar';
import StudentsSeccion2 from '../../layouts/React/role/StudentsSeccion2';

function ContentStudents({ opcionTab = [{ tab, value }], routePrimary }) {
    const [windowTab, setWindowTab] = useState("null")
    const [indexTab, setIndexTab] = useState(-1)
    const options = [<StudentsSeccion1></StudentsSeccion1>, <StudentsSeccion2></StudentsSeccion2>];

    useEffect(() => {
        const handlePopState = () => {
            const params = new URLSearchParams(window.location.search);
            const tabFromUrl = params.get('tab') || '';
            const index = opcionTab.findIndex(i => i.tab === tabFromUrl);
            setIndexTab(index);
            setWindowTab(tabFromUrl);
        };

        window.addEventListener('popstate', handlePopState);

        // Llamada inicial
        handlePopState();

        return () => {
            window.removeEventListener('popstate', handlePopState);
        };
    }, []);

    return (
        <ContentWithSubnavbar windowTab={windowTab} setWindowTab={setWindowTab} setParamIndex={setIndexTab} url={routePrimary} options={opcionTab} >
            {
                indexTab != -1 && options[indexTab]
            }
        </ContentWithSubnavbar>
    )
}

export default ContentStudents
