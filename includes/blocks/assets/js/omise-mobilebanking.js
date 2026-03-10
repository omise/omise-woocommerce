import {useEffect, useState} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_mobilebanking_data', {} )
const label = decodeEntities( settings.title ) || 'No title set'
const Label = ( props ) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={ label } />
}

const MobileBankingPaymentMethod = (props) => {
    const {eventRegistration, emitResponse} = props;
    const {onPaymentSetup} = eventRegistration;
    const description = decodeEntities( settings.description || '' )
    const data = settings.data || {};
    const backends = data.backends || [];
    const isUpaEnabled = !!data.is_upa_enabled;
    const noPaymentMethods = __( 'There are no payment methods available.', 'omise' )
    const [selectedBank, setSelectedBank] = useState(null);


    const onMobileBankSelected = (e) => {
        setSelectedBank(e.target.value)
    }

    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            if (isUpaEnabled) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: { "omise-offsite": "mobile_banking" }
                    }
                };
            }

            if (!selectedBank) {
                return {type: emitResponse.responseTypes.ERROR, message: __( 'Select a bank', 'omise' )}
            }

            try {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: { "omise-offsite": selectedBank }
                    }
                };
            } catch (error) {
                return {type: emitResponse.responseTypes.ERROR, message: error.message}
            }
        });
        return () => unsubscribe();
    }, [
        emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentSetup,
        selectedBank,
        isUpaEnabled
    ]);

    return (<>
        {description && <p>{description}</p>}
        {
            !isUpaEnabled && (
                backends.length == 0
                    ? <p>{noPaymentMethods}</p>
                    : (
                        <fieldset key={"omise-form-mobilebanking" + backends.length} id="omise-form-mobilebanking">
                            <ul className="omise-banks-list">
                            {
                                backends.map((backend, i) => (
                                    <li key={backend['name'] + i} className="item mobile-banking">
                                        <div>
                                            <input id={backend['name']} type="radio" name="omise-offsite" value={backend['name']} onChange={onMobileBankSelected}/>
                                            <label htmlFor={backend['name']}>
                                                <div className={`mobile-banking-logo ${backend['provider_logo']}`}></div>
                                                <div className="mobile-banking-label">
                                                    <span className="title">{backend['provider_name']}</span><br/>
                                                </div>
                                            </label>
                                        </div>
                                    </li>
                                ))
                            }
                            </ul>
                        </fieldset>
                    )
            )
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name || "",
    label: <Label />,
    content: <MobileBankingPaymentMethod />,
    edit: <MobileBankingPaymentMethod />,
    canMakePayment: () => settings.is_active,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
