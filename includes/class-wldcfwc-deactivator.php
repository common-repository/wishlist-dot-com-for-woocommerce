<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */
class WLDCFWC_Deactivator {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function wldcfwc_deactivate() {
		wldcfwc_delete_wlcom_api_key();
	}
}
