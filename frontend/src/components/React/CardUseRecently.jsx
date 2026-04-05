import React from 'react'

const CardUseRecently = ({item}) => {
    return (
        <a
            href={item.url}
            className="flex flex-col w-[45%] h-36 md:w-[29%] md:h-48 lg:w-[23%] lg:h-52 gap-2 pt-2 pb-4 px-3 border-2 rounded-md transition duration-100 ease-out group hover:shadow-xl hover:border-green-500 hover:border-[0.2rem]"
        >
            <img
                className="w-full h-[70%] object-cover object-center rounded"
                src={item.img}
                alt={item.alt}
            />
            <p
                className="w-full mt-2 text-sm lg:text-xl font-semibold text-center group-hover:text-green-800 group-hover:font-normal"
            >
                {item.title}
            </p>
        </a>
    )
}

export default CardUseRecently
