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
		'omise_promptpay': '/includes/blocks/assets/js/omise-promptpay.js',
		'omise_alipay': '/includes/blocks/assets/js/omise-alipay.js',
		'omise_alipay_hk': '/includes/blocks/assets/js/omise-alipay-hk.js',
		'omise_alipay_cn': '/includes/blocks/assets/js/omise-alipay-cn.js',
		'omise_dana': '/includes/blocks/assets/js/omise-dana.js',
		'omise_kakaopay': '/includes/blocks/assets/js/omise-kakaopay.js',
		'omise_gcash': '/includes/blocks/assets/js/omise-gcash.js',
		// 'omise_touch_n_go': '/includes/blocks/assets/js/omise-touch-n-go.js',
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
