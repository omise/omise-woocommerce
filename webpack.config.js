const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require('path');

const wcDepMap = {
	'@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
	'@woocommerce/settings'       : ['wc', 'wcSettings']
};

const wcHandleMap = {
	'@woocommerce/blocks-registry': 'wc-blocks-registry',
	'@woocommerce/settings'       : 'wc-settings'
};

const requestToExternal = (request) => {
	if (wcDepMap[request]) {
		return wcDepMap[request];
	}
};

const requestToHandle = (request) => {
	if (wcHandleMap[request]) {
		return wcHandleMap[request];
	}
};

// Export configuration.
module.exports = {
	...defaultConfig,
	entry: {
		'credit_card': '/includes/blocks/assets/js/omise-credit-card.js',
		'omise-one-click-apms': '/includes/blocks/assets/js/omise-one-click-apms.js',
		'omise_mobilebanking': '/includes/blocks/assets/js/omise-mobilebanking.js',
		'omise_installment': '/includes/blocks/assets/js/omise-installment.js',
		'omise_fpx': '/includes/blocks/assets/js/omise-fpx.js',
		'omise_atome': '/includes/blocks/assets/js/omise-atome.js',
		'omise_truemoney': '/includes/blocks/assets/js/omise-truemoney.js',
		'omise_googlepay': '/includes/blocks/assets/js/omise-googlepay.js',
		'omise_internetbanking': '/includes/blocks/assets/js/omise-internetbanking.js',
		'omise_duitnow_obw': '/includes/blocks/assets/js/omise-duitnow-obw.js',
		'omise_konbini': '/includes/blocks/assets/js/omise-konbini.js',
	},
	output: {
		path: path.resolve( __dirname, 'includes/blocks/assets/js/build' ),
		filename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin({
			requestToExternal,
			requestToHandle
		}),
	]
};
