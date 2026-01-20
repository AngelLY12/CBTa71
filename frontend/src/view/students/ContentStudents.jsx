import StudentsSeccion1 from '../../layouts/React/students/StudentsSeccion1';
import StudentsSeccion2 from '../../layouts/React/students/StudentsSeccion2';
import StructureWithTab from '../../components/React/ContentWithTab';

const ContentStudents = ({ opcionTab = [{ tab, value }], routePrimary }) => {
    const options = [<StudentsSeccion1></StudentsSeccion1>, <StudentsSeccion2></StudentsSeccion2>];
    return (
        <StructureWithTab routePrimary={routePrimary} opcionTab={opcionTab} options={options}></StructureWithTab>
    )
}

export default ContentStudents
