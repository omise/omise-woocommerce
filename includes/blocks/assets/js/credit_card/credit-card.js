import {useEffect, useRef} from '@wordpress/element';

export const PaymentMethod = ({getData, content, ...props}) => {
    const Content = content;
    const desc = getData('description');
    const el = useRef(null);
    useEffect(() => {
        if (el.current && el.current.childNodes.length == 0) {
            el.current.classList.add('no-content');
        }
    });
    return (
        <>
            {desc && <Description desc={desc} payment_method={getData('name')}/>}
            <div ref={el} className='wc-stripe-blocks-payment-method-content'>
                <Content {...{...props, getData}}/>
            </div>
        </>);
}

const Description = ({desc, payment_method}) => {
    return (
        <div className={`wc-stripe-blocks-payment-method__desc ${payment_method}`}>
            <p>{desc}</p>
        </div>
    )
}