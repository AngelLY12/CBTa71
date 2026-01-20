import React from 'react'
import StructureWithTab from '../../components/React/ContentWithTab';
import RatingSeccion1 from '../../layouts/React/rating/RatingSeccion1';
import RatingSeccion2 from '../../layouts/React/rating/RatingSeccion2';

function ContentRating({ opcionTab = [{ tab, value }], routePrimary }) {
    const options = [<RatingSeccion1></RatingSeccion1>, <RatingSeccion2></RatingSeccion2>];
    return (
        <StructureWithTab routePrimary={routePrimary} opcionTab={opcionTab} options={options}></StructureWithTab>
    )
}

export default ContentRating
