import Input from './Input';

function InputTitleUp({ title, type = "text", className = "", watch = "", borderT = false, required = true, ...props }) {
    return (
        <label className={`w-full inline-flex flex-col ${className}`}>
            <span className='mb-1 font-medium text-md md:text-lg'>{title}</span>
            <Input className={`h-full min-h-12 ${borderT && "border-x-transparent border-b-transparent border-t"}`} required={required} type={type} {...props} watch={watch} />
        </label>
    )
}

export default InputTitleUp
