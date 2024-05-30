<?php

class Omise_Block {
    public static function init() {
        if ( self::is_active() ) {
            self::container()->get( Omise_Block_Config::class );
        }
    }

    /**
     * Loads the Blocks integration if WooCommerce Blocks is installed as a feature plugin.
     */
    private static function is_active() {
        if ( \class_exists( '\Automattic\WooCommerce\Blocks\Package' ) ) {
            if ( self::is_core_plugin_build() ) {
                return true;
            }

            if ( \method_exists( '\Automattic\WooCommerce\Blocks\Package', 'feature' ) ) {
                $feature = \Automattic\WooCommerce\Blocks\Package::feature();
                if ( \method_exists( $feature, 'is_feature_plugin_build' ) ) {
                    if ( $feature->is_feature_plugin_build() ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private static function is_core_plugin_build() {
        return \function_exists( 'WC' ) && \version_compare( '6.9.0', WC()->version, '<=' );
    }

    /**
     * @return \Automattic\WooCommerce\Blocks\Registry\Container
     */
    public static function container() {
        static $container;
        if ( ! $container ) {
            $container = \Automattic\WooCommerce\Blocks\Package::container();
            $container->register( Omise_Block_Config::class, function ( $container ) {
                return new Omise_Block_Config( $container );
            } );
        }

        return $container;
    }
}
