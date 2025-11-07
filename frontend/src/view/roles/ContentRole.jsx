import React, { useEffect, useState } from 'react'
import { routes } from "../../data/routes";
import RoleSeccion1 from '../../layouts/React/role/RoleSeccion1';
import RoleSeccion2 from '../../layouts/React/role/RoleSeccion2';
import ContentWithSubnavbar from '../../components/React/ContentWithSubnavbar';

function ContentRole({ opcionTab = [{ tab, value }], routePrimary }) {
    const [windowTab, setWindowTab] = useState("null")
    const [indexTab, setIndexTab] = useState(-1)
    const options = [<RoleSeccion1></RoleSeccion1>, <RoleSeccion2></RoleSeccion2>];

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

export default ContentRole
