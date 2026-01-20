import React, { useEffect, useState } from 'react'
import ContentWithSubnavbar from './ContentWithSubnavbar';

const StructureWithTab = ({ opcionTab = [{ tab, value }], routePrimary, options = [] }) => {
    const [windowTab, setWindowTab] = useState("null")
    const [indexTab, setIndexTab] = useState(-1)

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
        <ContentWithSubnavbar windowTab={windowTab} setWindowTab={setWindowTab} setParamIndex={setIndexTab} url={routePrimary} options={opcionTab}>
            {
                indexTab != -1 && options[indexTab]
            }
        </ContentWithSubnavbar>
    )
}

export default StructureWithTab
