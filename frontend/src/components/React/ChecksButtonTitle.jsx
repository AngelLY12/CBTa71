function ChecksButtonTitle({ title, options = [], sizeW = false, setValue, value = "" }) {

    const handleChange = (e) => {
        const val = e.target.value;
        let newSelected;

        if (e.target.checked) {
            newSelected = [...value, val];
        } else {
            newSelected = value.filter((item) => item !== val);
        }

        setValue(newSelected); // RHF actualiza el valor
    };

    return (
        <div className='mt-2 md:mt-1'>
            <label className='text-md md:text-lg font-medium block'>{title}</label>
            <div className={`flex flex-wrap justify-between md:justify-start md:gap-x-4`}>
                {options.map((option, i) => (
                    <div key={i} className={!sizeW ? "w-5/12 md:w-[7.4rem]" : sizeW}>
                        <label
                            className='inline-flex gap-2 items-center'
                        >
                            <input
                                type="checkbox"
                                className='size-4'
                                name={"option-" + title}
                                value={option}
                                checked={value.includes(option)}
                                onChange={handleChange}
                            />
                            <span>{option}</span>
                        </label>
                    </div>
                ))}
            </div>
        </div>
    )
}

export default ChecksButtonTitle
