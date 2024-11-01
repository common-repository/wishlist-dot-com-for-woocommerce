<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */
class WLDCFWC_Activator {


	/**
	 * Runs at activation
	 *
	 * @since 1.0.0
	 */
	public static function wldcfwc_activate() {
		//activation logic
		//reset flags so api_key is checked again
		update_option( WLDCFWC_SLUG . '_initial_get_api_key_attempted', 'no_str' );
		update_option( WLDCFWC_SLUG . '_valid_api_key', 'no_str' );
		update_option( WLDCFWC_SLUG . '_api_key_deleted', 'no_str' );
	}
}
