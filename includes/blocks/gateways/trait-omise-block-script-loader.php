<?php

defined( 'ABSPATH' ) || exit;

/**
 * Shared helper for loading Webpack-generated *.asset.php files with caching and safe fallbacks.
 */
trait Omise_Block_Script_Loader {
    /**
     * Returns the script asset metadata or a safe fallback when build artifacts are missing.
     *
     * @param string $asset_path Path to generated *.asset.php file.
     * @return array
     */
    private function load_script_asset( $asset_path ) {
        static $asset_cache = [];

        $defaults = [
            'dependencies' => [],
            'version'      => defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) ? OMISE_WOOCOMMERCE_PLUGIN_VERSION : null,
        ];

        if ( isset( $asset_cache[ $asset_path ] ) ) {
            return $asset_cache[ $asset_path ];
        }

        if ( ! file_exists( $asset_path ) ) {
            $asset_cache[ $asset_path ] = $defaults;
            return $asset_cache[ $asset_path ];
        }

        $asset = require $asset_path; // NOSONAR: asset files return arrays; require_once can return true on repeat include.
        if ( ! is_array( $asset ) ) {
            $asset_cache[ $asset_path ] = $defaults;
            return $asset_cache[ $asset_path ];
        }

        $asset_cache[ $asset_path ] = array_merge( $defaults, $asset );
        return $asset_cache[ $asset_path ];
    }
}
