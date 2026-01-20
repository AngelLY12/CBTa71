import React, { useEffect, useState } from 'react'
import Modal from './Modal'
import CardInfoMovil from './CardInfoMovil';

const CardsListInfo = ({ className, onClickCard, items = [], datesCard = ["correo", "status"], id = "id" }) => {
    const [showitem, setShowitem] = useState(false);

    const clickCard = (item) => {
        onClickCard(item);
    }

    const editClick = (item) => {
        onClickCard(item);
    }

    // Añadir o remover la clase 'no-scroll' del body cuando openMovilSearch cambie
    useEffect(() => {
        if (showitem) {
            document.body.classList.add('overflow-y-hidden');
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    }, [showitem]);

    return (
        <>
            <div className={'flex flex-col gap-2 ' + className}>
                {items.map((item, i) => (
                    <CardInfoMovil onClickEdit={editClick} onClickCard={clickCard} cardClick={true} deleteItem={false} info={[datesCard[0], datesCard[1]]} key={item[id]} item={item} index={item[id]} />
                ))}
            </div>
        </>
    )
}

export default CardsListInfo
