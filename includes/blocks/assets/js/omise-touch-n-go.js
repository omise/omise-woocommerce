import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { registerOmisePaymentMethod } from './common';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'omise_touch_n_go_data', {} )
const defaultLabel = __( 'Touch N Go', 'omise' );
const label = decodeEntities( settings.title ) || defaultLabel;
registerOmisePaymentMethod({settings, label})
