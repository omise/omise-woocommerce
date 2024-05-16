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
    const description = decodeEntities( settings.description || '' )
    const backends = settings.data.backends;
    const noPaymentMethods = __( 'There are no payment methods available.', 'omise' )

    return (<>
        <p>{description}</p>
        {
            backends.length == 0
                ? <p>{noPaymentMethods}</p>
                : (
                    <fieldset key={"omise-form-mobilebanking" + backends.length} id="omise-form-mobilebanking">
                        <ul className="omise-banks-list"></ul>
                        {
                            backends.map((backend, i) => (
                                <li key={backend['_id'] + i} className="item mobile-banking">
                                    <div>
                                        <input id={backend['_id']} type="radio" name="omise-offsite" value={backend['_id']} />
                                        <label htmlFor={backend['_id']}>
                                            <div className={`mobile-banking-logo ${backend['provider_logo']}`}></div>
                                            <div className="mobile-banking-label">
                                                <span className="title">{backend['provider_name']}</span><br/>
                                            </div>
                                        </label>
                                    </div>
                                </li>
                            ))
                        }

                    </fieldset>
                )
        }
    </>)
}

registerPaymentMethod( {
    name: settings.name,
    label: <Label />,
    content: <MobileBankingPaymentMethod />,
    edit: <MobileBankingPaymentMethod />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
} )
