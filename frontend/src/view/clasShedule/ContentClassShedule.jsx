import StructureWithTab from '../../components/React/ContentWithTab';
import ClassSheduleSeccion1 from '../../layouts/React/classShedule/ClassSheduleSeccion1';
import ClassSheduleSeccion2 from '../../layouts/React/classShedule/ClassSheduleSeccion2';

const ContentClassShedule = ({ opcionTab = [{ tab, value }], routePrimary, }) => {
    const options = [<ClassSheduleSeccion1></ClassSheduleSeccion1>, <ClassSheduleSeccion2></ClassSheduleSeccion2>];

    return (
        <StructureWithTab routePrimary={routePrimary} opcionTab={opcionTab} options={options}></StructureWithTab>
    )
}

export default ContentClassShedule
