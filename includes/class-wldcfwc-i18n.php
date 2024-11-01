<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */
class WLDCFWC_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_load_plugin_textdomain() {

		load_plugin_textdomain(
			WLDCFWC_SLUG,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
