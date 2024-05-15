import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerOmisePaymentMethod } from './common';
import { getSetting } from '@woocommerce/settings';

const apms = [
    'omise_alipay_data',
    'omise_alipay_cn_data',
    'omise_alipay_hk_data',
    'omise_dana_data',
    'omise_gcash_data',
    'omise_kakaopay_data',
    'omise_promptpay_data',
    'omise_touch_n_go_data',
    'omise_billpayment_tesco_data',
    'omise_shopeepay_data',
    'omise_wechat_pay_data',
    'omise_grabpay_data',
    'omise_paynow_data',
    'omise_ocbc_data',
    'omise_fpx_data',
    'omise_maybank_qr_data',
    'omise_duitnow_qr_data',
    'omise_paypay_data',
    'omise_rabbit_linepay_data',
];

for (const apm of apms) {
    const settings = getSetting( apm, {} )

    if (settings.name) {
        const label = decodeEntities( settings.title ) || 'No title set'
        registerOmisePaymentMethod({settings, label})
    }
}
