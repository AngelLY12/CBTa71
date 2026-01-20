import RoleSeccion1 from '../../layouts/React/role/RoleSeccion1';
import RoleSeccion2 from '../../layouts/React/role/RoleSeccion2';
import StructureWithTab from '../../components/React/ContentWithTab';

const ContentRole = ({ opcionTab = [{ tab, value }], routePrimary }) => {
    const options = [<RoleSeccion1></RoleSeccion1>, <RoleSeccion2></RoleSeccion2>]

    return (
        <StructureWithTab routePrimary={routePrimary} opcionTab={opcionTab} options={options}></StructureWithTab>
    )
}

export default ContentRole
