<?php

/**
 * Plugin Name:             WishList by WishList.com
 * Plugin URI:              https://www.wishlist.com/wishlist-dot-com-for-woocommerce
 * Description:             A WishList for your store that's also featured on WishList.com to increase your store's visibility, sales and loyalty. <a href="https://wishlist.com/wishlist-dot-com-for-woocommerce" target="_blank"><strong>More on WishList.com</strong></a>
 * Version:                 3.2.18
 * Author:                  WishList.com, Inc.
 * Author URI:              https://www.wishlist.com
 * Developer:               WishList.com, Inc.
 * Developer:               https://www.wishlist.com
 * License:                 GPL-3.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             wishlist-dot-com-for-woocommerce
 * WC requires at least:    8.3
 * WC tested up to:         9.3.3
 * Requires PHP:            7.3
 *
 * @package wishlist-dot-com\wishlist-dot-com-for-woocommerce
 * @version 3.2.18
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
// begin_variable_definitions
if ( ! defined( 'WLDCFWC_VERSION' ) ) {
	define('WLDCFWC_VERSION', '3.2.18');
}
if ( ! defined( 'WLDCFWC_URL' ) ) {
	define( 'WLDCFWC_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WLDCFWC_DIR' ) ) {
	define( 'WLDCFWC_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WLDCFWC_SLUG' ) ) {
	define( 'WLDCFWC_SLUG', 'wishlist-dot-com-for-woocommerce' );
}
if ( ! defined( 'WLDCFWC_WISHLIST_HOMEPAGE_TITLE' ) ) {
	define( 'WLDCFWC_WISHLIST_HOMEPAGE_TITLE', 'wldc-wishlist-home' );
}
if ( ! defined( 'WLDCFWC_WISHLIST_HOMEPAGE_CONTENT' ) ) {
	define( 'WLDCFWC_WISHLIST_HOMEPAGE_CONTENT', '<!-- wp:shortcode -->[wldcfwc_hp_shortcode]<!-- /wp:shortcode -->' );
}
if ( ! defined( 'WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION' ) ) {
	define( 'WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION', WLDCFWC_SLUG . '_wishlist_home_page_id' );
}
if ( ! defined( 'WLDCFWC_WISHLIST_TITLE' ) ) {
	define( 'WLDCFWC_WISHLIST_TITLE', esc_html__( 'Wishlist', 'wishlist-dot-com-for-woocommerce' ) );
}
if ( ! defined( 'WLDCFWC_ENV' ) ) {
	define('WLDCFWC_ENV', 'prod');
}
if ( ! defined( 'WLDCFWC_USE_MIN_CSS' ) ) {
	define( 'WLDCFWC_USE_MIN_CSS', true );
}
if ( ! defined( 'WLDCFWC_USE_MIN_JS' ) ) {
	define( 'WLDCFWC_USE_MIN_JS', true );
}

if ( ! defined( 'WLDCFWC_UPDATE_SOURCE' ) ) {
	define( 'WLDCFWC_UPDATE_SOURCE', 'wp' );
}
// end_variable_definitions

/**
 * The code that runs during plugin deactivation.
 *
 * @return void
 */
function wldcfwc_deactivate_plugin() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	// nonce not required since we're not processing the $_GET variable, and there's no security implications
	if ( ! empty( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

//Ensure functions like get_plugins, etc.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	// Woocommerce is required
	add_action( 'admin_notices', 'wldcfwc_woocommerce_admin_notice' );
	// deactivate the plugin
	add_action( 'admin_init', 'wldcfwc_deactivate_plugin' );
	return;
}

/**
 * Admin notice that WooCommerce is required
 *
 * @return void
 */
function wldcfwc_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php echo esc_html__( 'Please install WooCommerce. "WishList for WooCommerce" requires Woocommerce.', 'wishlist-dot-com-for-woocommerce' ); ?></p>
	</div>
	<?php
}

/**
 * Declare that this plugin is compatible with High-Performance Order Storage (HPOS).
 *
 * @return void
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * The code that runs during plugin activation.
 *  This action is documented in includes/class-wldcfwc-activator.php
 *
 * @return void
 */
function wldcfwc_activate_main() {
	include_once plugin_dir_path(__FILE__) . 'includes/class-wldcfwc-activator.php';
	WLDCFWC_Activator::wldcfwc_activate();

	set_transient( 'wldcfwc_get_started_notice_transient', true, 5 );
}

add_action( 'admin_notices', 'wldcfwc_get_started_notice' );

/**
 * Admin notice after activation
 *
 * @return void
 */
function wldcfwc_get_started_notice() {
	if ( get_transient( 'wldcfwc_get_started_notice_transient' ) ) {

		$settings_url = admin_url( 'admin.php?page=' . WLDCFWC_SLUG );

		?>
		<div class="notice notice-success is-dismissible">
			<div style="padding: 20px;">
				<div style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 5px;">
					<img style="width: 32px; height: auto; margin-right: 5px;" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) ); ?>/admin/images/WishListIcon-48.png"> <h1>WishList</h1>
				</div>
				<div style="font-weight: 600; font-size: medium; margin-bottom: 20px;">
					<?php echo esc_html__( 'Congratulations, "WishList for WooCommerce" is now activated.', 'wishlist-dot-com-for-woocommerce' ); ?>
				</div>
				<div>
					<a style="font-size: 14px; color: #fff; cursor: pointer; background-color: #0a558a !important; border: 1px solid #0a558a !important; border-radius: 0; height: 31px; line-height: 29px; box-shadow: none; text-shadow: none; vertical-align: baseline; padding: 0 10px; color: #fff; font-weight: 600; text-decoration: none; display: inline-block; white-space: nowrap; box-sizing: border-box;" href="<?php echo esc_url( $settings_url ); ?>"><?php echo esc_html__( 'Start setup', 'wishlist-dot-com-for-woocommerce' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		delete_transient( 'wldcfwc_get_started_notice_transient' );
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wldcfwc-deactivator.php
 */
function wldcfwc_deactivate_main() {
	include_once plugin_dir_path(__FILE__) . 'includes/class-wldcfwc-deactivator.php';
	WLDCFWC_Deactivator::wldcfwc_deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 * This action is documented in includes/class-wldcfwc-uninstaller.php
 */
function wldcfwc_uninstall_main() {
	include_once plugin_dir_path(__FILE__) . 'includes/class-wldcfwc-uninstaller.php';
	WLDCFWC_Uninstaller::wldcfwc_uninstall();
}
register_activation_hook( __FILE__, 'wldcfwc_activate_main' );
register_deactivation_hook( __FILE__, 'wldcfwc_deactivate_main' );
register_uninstall_hook( __FILE__, 'wldcfwc_uninstall_main' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wldcfwc-main.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function wldcfwc_run_main() {

	$plugin = new WLDCFWC_Main();
	$plugin->wldcfwc_run();
}
// Run after WooCommerce has been loaded. Other options include: plugins_loaded after_setup_theme woocommerce_init
add_action( 'woocommerce_init', 'wldcfwc_run_main' );




/**
 * Gets information about the store for use in api calls to WishList.com
 *
 * @param  $mode
 * @return array
 */
function wldcfwc_store_id( $params = array() ) {

	$store_url           = wc_get_page_permalink( 'shop' );
	$store_domain_a      = wldcfwc_url_to_domain_path( $store_url );
	$store_url_no_scheme = $store_domain_a['url_no_scheme'];
	$store_domain        = $store_domain_a['store_domain'];
	if ( ! empty( $params['use_api_call_domain'] ) ) {
		$use_api_call_domain = true;
	} else {
		$use_api_call_domain = false;
	}

	// set environment for api subdomain
	if ( 'dev' == WLDCFWC_ENV ) {
		$env = 'd';
	} elseif ( 'staging' == WLDCFWC_ENV ) {
		$env = 's';
	} else {
		$env = 'p';
	}

	if ( isset( $params['store_uuid'] ) ) {
		$store_uuid = $params['store_uuid'];
	} else {
		$store_uuid = get_option( WLDCFWC_SLUG . '_store_uuid' );
	}
	if ( isset( $params['store_api_subdomain'] ) ) {
		$store_api_subdomain = $params['store_api_subdomain'];
	} else {
		// store_api_subdomain is created via wldcfwc-admin.js from window message from wishlist.com
		$store_api_subdomain = get_option( WLDCFWC_SLUG . '_store_api_subdomain' );
	}
	if ( isset( $params['valid_api_key'] ) ) {
		$valid_api_key = $params['valid_api_key'];
	} else {
		$valid_api_key = get_option( WLDCFWC_SLUG . '_valid_api_key' );
	}
	if ( isset( $params['api_key'] ) ) {
		$api_key = $params['api_key'];
	} else {
		// api_key is created via initial api call to wishlist.com
		$api_key = get_option( WLDCFWC_SLUG . '_api_key' );
	}

	// sub-domain codes
	// env: d = development, s = staging, p = production;
	// user interface mode: u = normal, p = popwin
	// source: w = WishList for WooCommerce

	// $api_domain_codes = 'api[env][mode][source]-[storeid].wishlist.com';
	if ( 'yes_str' != $valid_api_key || $use_api_call_domain ) {
		//not connected
		$api_domain_codes = 'a[env][mode][source].wishlist.com';
	} elseif ( 'yes_str' == $valid_api_key ) {
		$api_domain_codes = '[store_api_subdomain]-[store_uuid]-a[env][mode][source].wishlist.com';
	}
	$api_domain = str_replace( array( '[store_api_subdomain]', '[env]', '[mode]', '[source]', '[store_uuid]' ), array( $store_api_subdomain, $env, 'u', 'w', $store_uuid ), $api_domain_codes );
	// [mode] is left to be set later as necessary
	$api_domain_mode = str_replace( array( '[store_api_subdomain]', '[env]', '[source]', '[store_uuid]' ), array( $store_api_subdomain, $env, 'w', $store_uuid ), $api_domain_codes );

	$return_a = array(
		'env'                 => $env,
		'api_domain'          => $api_domain,
		'api_domain_mode'     => $api_domain_mode,
		'store_uuid'          => $store_uuid,
		'store_url'           => $store_url,
		'store_url_no_scheme' => $store_url_no_scheme,
		'store_domain'        => $store_domain,
		'api_key'             => $api_key,
		'valid_api_key'       => $valid_api_key,
		'store_api_subdomain' => $store_api_subdomain,
	);

	return $return_a;
}
/**
 * Register API enpoint
 *
 * @param  $params
 * @return string[]
 */
function wldcfwc_register_api() {
	register_rest_route(
		'wldcfwc_wishlistdotcom/v1',
		'/wldcfwc_api/',
		array(
			'methods'             => WP_REST_Server::CREATABLE, // POST
			'callback'            => 'wldcfwc_api_endpoint',
			'permission_callback' => 'wldcfwc_api_permission_callback',
		)
	);
}
add_action( 'rest_api_init', 'wldcfwc_register_api' );
/**
 * Public API endpoint to receive request from public website and then create an API call to WishList.com to process data and state changes
 *
 * @param  $params
 * @return string[]
 */
function wldcfwc_api_endpoint( WP_REST_Request $request ) {

	$api_action = sanitize_text_field( $request->get_param( 'api_action' ) );

	if ( 'wldcfwc_get_res_prods' == $api_action ) {
		$name = 'wldcfwc_res_prods';
		if (isset($_COOKIE[ $name ])) {

			$wldcfwc_res_prods = wldcfwc_getcookie( $name );
			//$wldcfwc_res_prods = json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ), true );
			$wldcom_api_result = array( 'status' => 'success', 'wldcfwc_res_prods' => $wldcfwc_res_prods );
		} else {
			$wldcom_api_result = array( 'error' => 'cookie not present' );
		}
	} elseif ( 'save_theme_styles' == $api_action ) {
		// Handle the `theme_primary_colors` array sent from JavaScript
		if ( isset($request['theme_primary_colors']) && is_array($request['theme_primary_colors']) ) {
			$theme_primary_colors_sanitized = wldcfwc_sanitize_theme_styles($request['theme_primary_colors']);
			// Save sanitized data as JSON in the database
			update_option(WLDCFWC_SLUG . '_theme_primary_colors', wp_json_encode($theme_primary_colors_sanitized));

			//merge theme_primary_colors values into options
			$existing_options = get_option( WLDCFWC_SLUG );
			if ( ! empty( $existing_options ) ) {
				//merge
				$merged_options = array_merge( $existing_options, $theme_primary_colors_sanitized );

				//flag as completed so merge won't run again
				$merged_options[ 'js_theme_colors_merged_saved' ] = 'yes_str';

				//save local
				update_option( WLDCFWC_SLUG , $merged_options );
				//save to WishList.com
				wldcfwc_save_api_options_to_wlcom( $merged_options );
			}
			$wldcom_api_result = array( 'status' => 'success' );
		} else {
			$wldcom_api_result = array( 'error' => 'Invalid theme primary colors data' );
		}
	} else {
		$wldcom_api_result = array( 'error' => 'no_matching_action' );
	}
	return new WP_REST_Response( $wldcom_api_result, 200 );
}
/**
 * Permission callback function for the REST API endpoint
 *
 * @return bool True if the user has permission, false otherwise
 */
function wldcfwc_api_permission_callback( $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );

	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid nonce', 'wishlist-dot-com-for-woocommerce' ), array( 'status' => 403 ) );
	}

	return true;
}
/**
 * Do API calls to get a new API key
 *
 * @return void
 */
function wldcfwc_get_wlcom_api_key() {

	$store_id_a = wldcfwc_store_id();
	$store_uuid = $store_id_a['store_uuid'];
	$api_key = $store_id_a['api_key'];

	$wlcom_store_shop_url = $store_id_a[ 'store_url_no_scheme' ];

	$plugin_uuid = wldcfwc_get_create_plugin_uuid();
	$auth_request_token = wldcfwc_create_uuid();
	$store_wpadmin_request_token = wldcfwc_create_uuid();

	//do an api call to get a one-time auth token
	$params = [];
	$params['data']       = [ 'plugin_uuid' => $plugin_uuid, 'auth_request_token' => $auth_request_token, 'store_wpadmin_request_token' => $store_wpadmin_request_token, 'wlcom_store_shop_url' => $wlcom_store_shop_url ];
	$params['api_action'] = 'get_auth_token';
	// response is sanitized
	$response = wldcfwc_api_call( $params );

	if ( ! empty( $response ) && ! empty( $response[ 'data' ] ) && ! empty( $response[ 'data' ][ 'auth_token' ] ) ) {
		$auth_token = $response[ 'data' ][ 'auth_token' ];
	} else {
		$auth_token = '';
	}
	if ( ! empty( $response ) && ! empty( $response[ 'data' ] ) && ! empty( $response[ 'data' ][ 'store_wpadmin_token' ] ) ) {
		$store_wpadmin_token = $response[ 'data' ][ 'store_wpadmin_token' ];
		//save store_wpadmin_token for display on front-end used to confirm ownership and admin login
		update_option( WLDCFWC_SLUG . '_store_wpadmin_token', $store_wpadmin_token );
		update_option( WLDCFWC_SLUG . '_store_wpadmin_request_token', $store_wpadmin_request_token );
	}

	if ( empty( $auth_token ) ) {
		$wldcom_api_result = array( 'error' => $response['error'] );
	} else {
		//do api call with $auth_token to request an api_key
		//plugin_uuid, auth_token, auth_request_token, store_wpadmin_request_token, wlcom_store_shop_url, store_uuid, api_key;
		$params = [];
		$params['data'] = [ 'plugin_uuid' => $plugin_uuid, 'auth_token' => $auth_token, 'auth_request_token' => $auth_request_token, 'store_wpadmin_request_token' => $store_wpadmin_request_token, 'wlcom_store_shop_url' => $wlcom_store_shop_url, 'store_uuid' => $store_uuid, 'api_key' => $api_key ];
		$params['api_action'] = 'get_api_key';

		// response is sanitized
		$wldcom_api_result = wldcfwc_api_call( $params );
	}

	if ( isset( $wldcom_api_result['data'] ) && isset( $wldcom_api_result['data']['store_uuid'] ) && isset( $wldcom_api_result['data']['api_key'] ) ) {
		update_option( WLDCFWC_SLUG . '_store_uuid', $wldcom_api_result['data']['store_uuid'] );
		update_option( WLDCFWC_SLUG . '_api_key', $wldcom_api_result['data']['api_key'] );
		update_option( WLDCFWC_SLUG . '_valid_api_key', 'yes_str' );
		update_option( WLDCFWC_SLUG . '_api_key_deleted', 'no_str');
		update_option( WLDCFWC_SLUG . '_store_api_subdomain', $wldcom_api_result['data']['store_api_subdomain'] );
	}

	return new WP_REST_Response( $wldcom_api_result, 200 );
}
/**
 * Do API calls to delete the connection to WishList.com by deleting the api key
 *
 * @return void
 */
function wldcfwc_delete_wlcom_api_key() {

	$api_key = get_option( WLDCFWC_SLUG . '_api_key' );
	$store_uuid = get_option( WLDCFWC_SLUG . '_store_uuid' );

	//do api call to delete api key
	$params = [];
	$params['data'] = [ 'store_uuid' => $store_uuid, 'api_key' => $api_key ];
	$params['api_action'] = 'delete_api_key';

	// response is sanitized
	$response = wldcfwc_api_call( $params );

	if ( isset( $response['status'] ) && 'success' == $response['status'] ) {
		//mark valid_api_key as not valid
		update_option( WLDCFWC_SLUG . '_valid_api_key', 'no_str' );
		update_option( WLDCFWC_SLUG . '_api_key_deleted', 'yes_str');

		$wldcom_api_result = $response;
	} elseif ( isset( $response['error'] ) ) {
		$wldcom_api_result = array( 'status'=>'error','error' => $response['error'] );
	} else {
		$wldcom_api_result = array( 'status'=>'error','error' => 'error' );
	}

	return new WP_REST_Response( $wldcom_api_result, 200 );
}
/**
 * Do API calls to get a one-time auth_token_request
 *
 * @return void
 */
function wldcfwc_get_once_auth_token() {

	$api_key = get_option( WLDCFWC_SLUG . '_api_key' );
	$store_uuid = get_option( WLDCFWC_SLUG . '_store_uuid' );

	$once_auth_token = wldcfwc_create_uuid();

	//do api call to delete api key
	$params = [];
	//store_uuid, api_key;
	$params['data'] = [ 'store_uuid' => $store_uuid, 'api_key' => $api_key, 'once_auth_token' => $once_auth_token ];
	$params['api_action'] = 'get_once_auth_token';

	// response is sanitized
	$response = wldcfwc_api_call( $params );

	if ( isset( $response['status'] ) && 'success' == $response['status'] ) {
		$wldcom_api_result = $response;
	} elseif ( isset( $response['error'] ) ) {
		$wldcom_api_result = array( 'status'=>'error','error' => $response['error'] );
	} else {
		$wldcom_api_result = array( 'status'=>'error','error' => 'error' );
	}

	return new WP_REST_Response( $wldcom_api_result, 200 );
}
/**
 * Create plugin unique id and save it if necessary
 *
 * @return void
 */
function wldcfwc_get_create_plugin_uuid() {

	$plugin_uuid_option_name = WLDCFWC_SLUG . '_pluging_uuid';
	$plugin_uuid = get_option( $plugin_uuid_option_name );
	if ( empty( $plugin_uuid ) ) {
		$plugin_uuid = wldcfwc_create_uuid();
		update_option( $plugin_uuid_option_name, $plugin_uuid );
	}
	return $plugin_uuid;
}
/**
 * Create a one-time auth request token
 *
 * @return void
 */
function wldcfwc_create_uuid() {

	$unique_id = uniqid(bin2hex(random_bytes(4)), true);
	$unique_id = str_replace('.', '', $unique_id);
	$checksum = crc32($unique_id) % 10000;
	$unique_id_checksum = $unique_id . str_pad($checksum, 4, '0', STR_PAD_RIGHT);

	return $unique_id_checksum;
}
/**
 * Does an api call to WishList.com
 *
 * @param  $params
 * @return mixed|string[]
 */
function wldcfwc_api_call( $params ) {
	$data       = $params['data'];
	$api_action = $params['api_action'];

	$store_id_a = wldcfwc_store_id( [ 'use_api_call_domain' => true ] );

	if ( 'd' == $store_id_a['env'] ) {
		$sslverify = false;
	} else {
		$sslverify = true;
	}
	if ( in_array( $api_action, [ 'check_service_level_wlcom_for_woocom', 'get_auth_token' ] ) ) {
		$api_url = 'https://' . $store_id_a['api_domain'] . '/wooCommApi';
	} else {
		$api_url = 'https://' . $store_id_a['api_domain'] . '/wooComSecureApiCall';
	}

	// send store's logo, url, button and other preferences to wishlist.com for display on wishlist.com
	$api_key = get_option( WLDCFWC_SLUG . '_api_key' );

	if ( ! empty( $data['auth_request_token'] ) && empty( $data['auth_token'] ) ) {
		//get auth_token from api
		$credentials = base64_encode( 'auth_request_token:' . $data['auth_request_token'] );
	} elseif ( ! empty( $data['auth_request_token'] ) && ! empty( $data['auth_token'] ) ) {
		//using auth_token from api to do initial call to get api_key
		$credentials = base64_encode( 'auth_request_token:' . $data['auth_request_token'] . ':' . $data['auth_token'] );
	} elseif ( ! empty( $store_id_a['api_key'] ) && ! empty( $store_id_a['store_uuid'] ) ) {
		//using api_key as auth
		$credentials = base64_encode( $store_id_a['store_uuid'] . ':' . $store_id_a['api_key'] );
	}

	if ( ! empty( $credentials ) ) {
		$api_body = array(
			'store_uuid'  => $store_id_a['store_uuid'],
			'credentials' => $credentials,
			'data'        => wp_json_encode( $data ),
			'api_action'  => $api_action,
		);
		$args     = array(
			'headers'   => array(
				'Content-Type' => 'application/json',
			),
			'body'      => wp_json_encode( $api_body ),
			'sslverify' => $sslverify,
		);
		$response = wp_remote_post( $api_url, $args );

		if ( is_wp_error( $response ) ) {
			// Handle the error
			$error_message = $response->get_error_message();

			$return_a = array(
				'error' => $error_message,
			);

		} else {
			// Request was successful
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			// Parse the response body as needed (e.g., JSON or XML)
			$parsed_data = json_decode( $response_body, true ); // For JSON response

			if ( 200 === $response_code ) {
				// Process the parsed data
				$return_a = $parsed_data;
			} else {
				$return_a = array(
					'error' => $response_code,
				);
			}

		}
	} else {
		$return_a = array(
			'error' => 'no api_key',
		);
	}

	// sanitize response from WishList.com
	if ( ! empty( $return_a ) ) {
		array_walk_recursive(
			$return_a,
			function ( &$item, $key ) {
				if ( is_string( $item ) ) {
					$item = sanitize_text_field( $item );
				}
			}
		);
	}

	return $return_a;
}
/**
 * Sends options to WishList.com's API
 *
 * @param  $options
 * @return mixed|string[]
 */
function wldcfwc_save_api_options_to_wlcom( $options ) {

	//add necessary values
	$options['wlcom_currency']      = get_woocommerce_currency();
	$options['wlcom_slug']          = WLDCFWC_SLUG;
	$options['wlcom_version']       = WLDCFWC_VERSION;
	$options['wlcom_update_source'] = WLDCFWC_UPDATE_SOURCE;

	//api call
	$params['data']       = $options;
	$params['api_action'] = 'save_options';
	$response             = wldcfwc_api_call( $params );

	return $response;
}
/**
 * Sanitize theme data
 *
 * @param  $theme_primary_colors
 * @return mixed|string[]
 */
//begin_wordpress_reviewer_notice
/*
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * FOR WORDPRESS PLUGIN REVIEWERS:
 *
 * THIS DOES NOT VIOLATE: "Attempting to process custom CSS/JS/PHP / Allowing arbitrary script insertion."
 * THIS PLUGIN DOES NOT SUPPORT CUSTOM CSS OR CUSTOM JAVASCRIPT.
 * THIS CSS IS NOT USED BY WORDPRESS.
 * THIS CSS IS FOR THE WISHLIST.COM API SERVICE, USED ONLY ON WISHLIST.COM.
 * WISHLIST.COM SANITIZES ALL USER INPUT DATA, INCLUDING CSS.
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
//end_wordpress_reviewer_notice
function wldcfwc_sanitize_theme_styles($theme_primary_colors) {

	// Initialize the sanitized array
	$theme_primary_colors_sanitized = [];

	// Define button suffixes and style variables for mapping
	$button_suffix = ['share_button', 'primary_button', 'danger_button'];
	$style_vars = ['color', 'backgroundColor', 'borderColor'];
	$field_names = ['text_color', 'background_color', 'border_color'];

	// Loop through button styles and sanitize
	foreach ($button_suffix as $suffix) {
		foreach ($style_vars as $index => $style_var) {
			// Generate the regular and hover keys for each style
			$regular_key = 'wlcom_wishlist_button_' . $field_names[$index] . '__' . $suffix;
			$hover_key = 'wlcom_wishlist_button_' . $field_names[$index] . '_hover__' . $suffix;

			// Sanitize the regular style values if they exist
			$theme_primary_colors_sanitized[$regular_key] = isset($theme_primary_colors[$regular_key]) ? sanitize_text_field($theme_primary_colors[$regular_key]) : '';

			// Sanitize the hover style values if they exist
			$theme_primary_colors_sanitized[$hover_key] = isset($theme_primary_colors[$hover_key]) ? sanitize_text_field($theme_primary_colors[$hover_key]) : '';
		}
	}

	// Sanitize the icon source
	$theme_primary_colors_sanitized['wlcom_plgn_your_store_icon'] = isset($theme_primary_colors['wlcom_plgn_your_store_icon']) ? esc_url_raw($theme_primary_colors['wlcom_plgn_your_store_icon']) : '';

	// Sanitize the store name style
	$theme_primary_colors_sanitized['wlcom_template_store_name__text_color'] = isset($theme_primary_colors['wlcom_template_store_name__text_color']) ? sanitize_text_field($theme_primary_colors['wlcom_template_store_name__text_color']) : '';

	// Sanitize the menu item style
	$theme_primary_colors_sanitized['wlcom_header_template_menu_item__text_color'] = isset($theme_primary_colors['wlcom_header_template_menu_item__text_color']) ? sanitize_text_field($theme_primary_colors['wlcom_header_template_menu_item__text_color']) : '';
	$theme_primary_colors_sanitized['wlcom_header_template_menu_item__text_color_hover'] = isset($theme_primary_colors['wlcom_header_template_menu_item__text_color_hover']) ? sanitize_text_field($theme_primary_colors['wlcom_header_template_menu_item__text_color_hover']) : '';
	$theme_primary_colors_sanitized['wlcom_header_template_menu_item__text_font_size'] = isset($theme_primary_colors['wlcom_header_template_menu_item__text_font_size']) ? sanitize_text_field($theme_primary_colors['wlcom_header_template_menu_item__text_font_size']) : '';
	$theme_primary_colors_sanitized['wlcom_header_template_menu_item__text_decoration'] = isset($theme_primary_colors['wlcom_header_template_menu_item__text_decoration']) ? sanitize_text_field($theme_primary_colors['wlcom_header_template_menu_item__text_decoration']) : '';

	// Sanitize the body font style
	$theme_primary_colors_sanitized['wlcom_text_color'] = isset($theme_primary_colors['wlcom_text_color']) ? sanitize_text_field($theme_primary_colors['wlcom_text_color']) : '';

	return $theme_primary_colors_sanitized;
}
/**
 * Get a cookie.
 *
 * @param string $name Cookie name.
 *
 * @return mixed
 * @since 1.0.0
 */
function wldcfwc_getcookie($name) {
	if ( isset( $_COOKIE[ $name ] ) ) {
		return json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ), true );
	}
	return array();
}
/**
 * Set a cookie.
 *
 * @param string $name Cookie name.
 * @param mixed $value Cookie value.
 * @param int $expiration Cookie exp time.
 * @param bool $secure Available to secured connection only.
 * @param bool $httponly No js.
 *
 * @return bool
 * @since 1.0.0
 */
function wldcfwc_setcookie( $name, $value = array(), $expiration = null, $secure = false, $httponly = false ) {
	$value = wp_json_encode( stripslashes_deep( $value ) );
	$_COOKIE[ $name ] = $value;
	wc_setcookie( $name, $value, $expiration, $secure, $httponly );
	return true;
}
