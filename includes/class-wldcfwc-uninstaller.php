<?php

/**
 * Fired during plugin uninstallation.
 *
 * This class defines all code necessary to run during the plugin's uninstallation.
 *
 * @since      1.0.0
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */
class WLDCFWC_Uninstaller {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function wldcfwc_uninstall() {

		// delete pages
		$wishlist_hp_page_id = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		if ( $wishlist_hp_page_id ) {
			wp_delete_post( $wishlist_hp_page_id, true );
		}

		// Get all keys
		global $wpdb; // WordPress database access

		// SQL query to select all option keys related to the plugin
		$option_name_like = '%' . $wpdb->esc_like( WLDCFWC_SLUG ) . '%';

		// Get the options to delete
		$options = $wpdb->get_results( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", $option_name_like ), ARRAY_A );

		// Delete options.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $option_name_like ) );

		// Clear cache for deleted options
		if ( ! empty( $options ) ) {
			foreach ( $options as $option ) {
				wp_cache_delete( $option['option_name'], 'options' );
			}
		}
	}
}
