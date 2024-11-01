<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */
class WLDCFWC_Main {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @var    wldcfwc_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @var    string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @var    string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = WLDCFWC_VERSION;
		$this->plugin_name = WLDCFWC_SLUG;

		$this->wldcfwc_load_dependencies();
		$this->wldcfwc_set_locale();
		if ( is_admin() ) {
			$this->wldcfwc_define_admin_hooks();
		}
		$this->wldcfwc_define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - wldcfwc_Loader. Orchestrates the hooks of the plugin.
	 * - wldcfwc_i18n. Defines internationalization functionality.
	 * - wldcfwc_Admin. Defines all hooks for the admin area.
	 * - wldcfwc_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 */
	private function wldcfwc_load_dependencies() {

		/**
		 * Trait with shared logic
		 */
		include_once plugin_dir_path( __DIR__ ) . 'includes/class-wldcfwc-trait.php';

		/**
		 * The defining all functions
		 */
		include_once plugin_dir_path( __DIR__ ) . 'includes/class-wldcfwc-functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( __DIR__ ) . 'includes/class-wldcfwc-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( __DIR__ ) . 'includes/class-wldcfwc-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		if ( is_admin() ) {
			include_once plugin_dir_path( __DIR__ ) . 'admin/class-wldcfwc-admin.php';
		}

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( __DIR__ ) . 'public/class-wldcfwc-public.php';

		/**
		 * Exopite Simple Options Framework
		 *
		 * @link   https://github.com/JoeSz/Exopite-Simple-Options-Framework
		 */
		include_once plugin_dir_path( __DIR__ ) . 'admin/exopite-simple-options/exopite-simple-options-framework-class.php';

		$this->loader = new WLDCFWC_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the wldcfwc_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 */
	private function wldcfwc_set_locale() {

		$plugin_i18n = new WLDCFWC_I18n();

		$this->loader->wldcfwc_add_action( 'init', $plugin_i18n, 'wldcfwc_load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 */
	private function wldcfwc_define_admin_hooks() {

		$plugin_admin = new WLDCFWC_Admin( $this->wldcfwc_get_plugin_name(), $this->wldcfwc_get_version() );

		$this->loader->wldcfwc_add_action( 'admin_enqueue_scripts', $plugin_admin, 'wldcfwc_enqueue_styles' );
		$this->loader->wldcfwc_add_action( 'admin_enqueue_scripts', $plugin_admin, 'wldcfwc_enqueue_scripts' );
		$this->loader->wldcfwc_add_action( 'init', $plugin_admin, 'wldcfwc_create_menu', 999 );

		// filter to adjust items prior to saving
		$this->loader->wldcfwc_add_filter( 'exopite_sof_save_menu_options', $plugin_admin, 'wldcfwc_adjust_options_prior_to_saving', 999 );

		$this->loader->wldcfwc_add_action( 'wp_ajax_wldcfwc_post_options_to_wlcom_rec_ajax', $plugin_admin, 'wldcfwc_post_options_to_wlcom_rec_ajax', 999 );
		$this->loader->wldcfwc_add_action( 'wp_ajax_wldcfwc_get_wlcom_api_key_rec_ajax', $plugin_admin, 'wldcfwc_get_wlcom_api_key_rec_ajax', 999 );
		$this->loader->wldcfwc_add_action( 'wp_ajax_wldcfwc_get_once_auth_token_rec_ajax', $plugin_admin, 'wldcfwc_get_once_auth_token_rec_ajax', 999 );
		$this->loader->wldcfwc_add_action( 'wp_ajax_wldcfwc_delete_wlcom_api_key_rec_ajax', $plugin_admin, 'wldcfwc_delete_wlcom_api_key_rec_ajax', 999 );
		$this->loader->wldcfwc_add_action( 'wp_ajax_wldcfwc_get_product_droppown_rec_ajax', $plugin_admin, 'wldcfwc_get_product_droppown_rec_ajax', 999 );
		$this->loader->wldcfwc_add_action( 'wp_ajax_wldcfwc_get_theme_primary_colors_rec_ajax', $plugin_admin, 'wldcfwc_get_theme_primary_colors_rec_ajax', 999 );

		// product added or updated
		$this->loader->wldcfwc_add_action( 'woocommerce_new_product', $plugin_admin, 'wldcfwc_add_productid_to_wlcom_queue', 31 );
		$this->loader->wldcfwc_add_action( 'woocommerce_update_product', $plugin_admin, 'wldcfwc_add_productid_to_wlcom_queue', 31 );
		$this->loader->wldcfwc_add_action( 'woocommerce_new_product_variation', $plugin_admin, 'wldcfwc_add_productid_to_wlcom_queue', 31 );
		$this->loader->wldcfwc_add_action( 'woocommerce_update_product_variation', $plugin_admin, 'wldcfwc_add_productid_to_wlcom_queue', 31 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 */
	private function wldcfwc_define_public_hooks() {

		$plugin_public = new WLDCFWC_Public( $this->wldcfwc_get_plugin_name(), $this->wldcfwc_get_version() );

		$options    = $plugin_public->wldcfwc_return_options();
		$store_uuid = get_option( WLDCFWC_SLUG . '_store_uuid' );

		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include_once ABSPATH . 'wp-includes/pluggable.php';
		}

		// plugin status. don't display if not live, admin user
		// nonce not required since we're not processing $_GET variable, and there no security implications
		if ( ! isset( $_GET['wldcomshowwishlist'] )
			&& ! (
			// must have store_uuid
			! empty( $store_uuid )
			// admin panal's plugin status must be live or testing
			&& ( str_contains( $options['wlcom_plgn_plugin_display_status'], 'status_live' )
			|| ( 'status_testing' == $options['wlcom_plgn_plugin_display_status'] && current_user_can( 'edit_pages' ) ) ) )
		) {
			// the WishList isn't live, so remove it from the top menu
			// adjust default top menu that's generated from pages. wp_page_menu, wp_page_menu_args
			$this->loader->wldcfwc_add_filter( 'wp_page_menu_args', $plugin_public, 'wldcfwc_remove_all_wishlist_pages_from_menu', 999 );

			// stop here, since it's not live or admin login
			return 1;
		}

		// position on product page. note priority
		$positions__product_page = array(
			'wishlist_button_after_add_to_cart_button' => array(
				// can't use woocommerce_after_add_to_cart_button since it doesn't work if there's no add to cart button
				'hook'     => 'woocommerce_single_product_summary',
				'priority' => 31,
				'callback' => 'wldcfwc_add2wishlist_button__product_page',
			),
			'wishlist_button_after_product_info'       => array(
				'hook'     => 'woocommerce_single_product_summary',
				'priority' => 31,
				'callback' => 'wldcfwc_add2wishlist_button__product_page',
			),
			'wishlist_button_after_thumbnails'         => array(
				'hook'     => 'woocommerce_before_single_product_summary',
				'priority' => 21,
				'callback' => 'wldcfwc_add2wishlist_button__product_page',
			),
			'wishlist_button_after_summary'            => array(
				'hook'     => 'woocommerce_after_single_product_summary',
				'priority' => 11,
				'callback' => 'wldcfwc_add2wishlist_button__product_page',
			),
			'wishlist_button_use_javascript'           => array(
				'hook'     => 'woocommerce_after_single_product_summary',
				'priority' => 31,
				'callback' => 'wldcfwc_add2wishlist_button__product_page_js_pos',
			),
			'wishlist_button_use_shortcode'            => array(
				'shortcode' => 'wldcfwc_button_product_page',
				'callback'  => 'wldcfwc_product_page_button_shortcode',
			),
		);
		if ( ! empty( $options['wishlist_button_position__product_page'] ) && ! empty( $positions__product_page[ $options['wishlist_button_position__product_page'] ] ) ) {
			if ( isset( $positions__product_page[ $options['wishlist_button_position__product_page'] ]['hook'] ) ) {
				$this->loader->wldcfwc_add_action( $positions__product_page[ $options['wishlist_button_position__product_page'] ]['hook'], $plugin_public, $positions__product_page[ $options['wishlist_button_position__product_page'] ]['callback'], $positions__product_page[ $options['wishlist_button_position__product_page'] ]['priority'] );
			} elseif ( isset( $positions__product_page[ $options['wishlist_button_position__product_page'] ]['shortcode'] ) ) {
				$this->loader->wldcfwc_add_shortcode( $positions__product_page[ $options['wishlist_button_position__product_page'] ]['shortcode'], $plugin_public, $positions__product_page[ $options['wishlist_button_position__product_page'] ]['callback'] );
			}
		}

		// position on product loop page, note priority
		$positions__product_loop = array(
			'wishlist_button_before_add_to_cart_button' => array(
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 7,
				'callback' => 'wldcfwc_add2wishlist_button__product_loop',
			),
			'wishlist_button_after_add_to_cart_button'  => array(
				'hook'     => 'woocommerce_after_shop_loop_item',
				'priority' => 15,
				'callback' => 'wldcfwc_add2wishlist_button__product_loop',
			),
			// woocommerce_shop_loop_item_title is likely to be used by the theme
			'wishlist_button_use_javascript'            => array(
				'hook'     => 'woocommerce_shop_loop_item_title',
				'priority' => 31,
				// output hidden span that can be moved and displayed via js
				'callback' => 'wldcfwc_add2wishlist_button__product_loop_js_pos',
			),
			'wishlist_button_use_shortcode'             => array(
				'shortcode' => 'wldcfwc_button_product_loop',
				'callback'  => 'wldcfwc_poduct_loop_button_shortcode',
			),
		);
		if ( ! empty( $options['wishlist_button_position__product_loop'] ) && ! empty( $positions__product_loop[ $options['wishlist_button_position__product_loop'] ] ) ) {
			if ( isset( $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['hook'] ) ) {
				$this->loader->wldcfwc_add_action( $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['hook'], $plugin_public, $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['callback'], $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['priority'] );
			} elseif ( isset( $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['shortcode'] ) ) {
				$this->loader->wldcfwc_add_shortcode( $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['shortcode'], $plugin_public, $positions__product_loop[ $options['wishlist_button_position__product_loop'] ]['callback'] );
			}
		}

		// position on cart page, note priority
		$positions__cart = array(
			'wishlist_button_above_right'    => array(
				'hook'     => 'woocommerce_before_cart_table',
				'priority' => 7,
				'callback' => 'wldcfwc_add2wishlist_button__cart',
			),
			'wishlist_button_above_left'     => array(
				'hook'     => 'woocommerce_before_cart_table',
				'priority' => 7,
				'callback' => 'wldcfwc_add2wishlist_button__cart',
			),
			'wishlist_button_above_centered' => array(
				'hook'     => 'woocommerce_before_cart_table',
				'priority' => 7,
				'callback' => 'wldcfwc_add2wishlist_button__cart',
			),
			'wishlist_button_below_right'    => array(
				'hook'     => 'woocommerce_after_cart_table',
				'priority' => 15,
				'callback' => 'wldcfwc_add2wishlist_button__cart',
			),
			'wishlist_button_below_left'     => array(
				'hook'     => 'woocommerce_after_cart_table',
				'priority' => 15,
				'callback' => 'wldcfwc_add2wishlist_button__cart',
			),
			'wishlist_button_below_centered' => array(
				'hook'     => 'woocommerce_after_cart_table',
				'priority' => 15,
				'callback' => 'wldcfwc_add2wishlist_button__cart',
			),
			'wishlist_button_use_javascript' => array(
				'hook'     => 'woocommerce_after_cart_table',
				'priority' => 31,
				'callback' => 'wldcfwc_add2wishlist_button__cart_js_pos',
			),
			'wishlist_button_use_shortcode'  => array(
				'shortcode' => 'wldcfwc_button_cart',
				'callback'  => 'wldcfwc_cart_button_shortcode',
			),
		);
		if ( ! empty( $options['wishlist_button_position__cart'] ) && ! empty( $positions__cart[ $options['wishlist_button_position__cart'] ] ) ) {
			if ( isset( $positions__cart[ $options['wishlist_button_position__cart'] ]['hook'] ) ) {
				$this->loader->wldcfwc_add_action( $positions__cart[ $options['wishlist_button_position__cart'] ]['hook'], $plugin_public, $positions__cart[ $options['wishlist_button_position__cart'] ]['callback'], $positions__cart[ $options['wishlist_button_position__cart'] ]['priority'] );
			} elseif ( isset( $positions__cart[ $options['wishlist_button_position__cart'] ]['shortcode'] ) ) {
				$this->loader->wldcfwc_add_shortcode( $positions__cart[ $options['wishlist_button_position__cart'] ]['shortcode'], $plugin_public, $positions__cart[ $options['wishlist_button_position__cart'] ]['callback'] );
			}
		}

		// store id, js var, modal html.
		// wp_body_open is not called by all themes. use these instead
		$this->loader->wldcfwc_add_action( 'wp_head', $plugin_public, 'wldcfwc_global_js_var', 31 );

		// add to WishList.com javascript
		$this->loader->wldcfwc_add_action( 'wp_enqueue_scripts', $plugin_public, 'wldcfwc_enqueue_scripts' );
		$this->loader->wldcfwc_add_action( 'wp_enqueue_scripts', $plugin_public, 'wldcfwc_enqueue_styles' );

		// hidden wish data on cart page with each item in the cart
		$this->loader->wldcfwc_add_action( 'woocommerce_after_cart_item_name', $plugin_public, 'wldcfwc_add2WishList_wish_data_cart', 31 );

		// hidden notice on shopping cart page
		$this->loader->wldcfwc_add_action( 'woocommerce_before_cart', $plugin_public, 'wldcfwc_cart_shipping_address_message_html', 31 );

		// hidden wish data on checkout page
		$this->loader->wldcfwc_add_action( 'woocommerce_before_checkout_form', $plugin_public, 'wldcfwc_add2WishList_wish_data_checkout', 31 );

		// hidden notice on checkout form
		$this->loader->wldcfwc_add_action( 'woocommerce_before_checkout_shipping_form', $plugin_public, 'wldcfwc_checkout_shipping_address_message_html', 31 );

		// shortcode for wishlist full homepage
		$this->loader->wldcfwc_add_shortcode( 'wldcfwc_hp_shortcode', $plugin_public, 'wldcfwc_hp_shortcode' );

		// check user's wldcom login and redirect to mywishlists as necessary
		$this->loader->wldcfwc_add_action( 'template_redirect', $plugin_public, 'wldcfwc_redirect_wishlist_hp_to_mywishlist', 31 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_run() {
		$this->loader->wldcfwc_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string    The name of the plugin.
	 */
	public function wldcfwc_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return wldcfwc_Loader    Orchestrates the hooks of the plugin.
	 */
	public function wldcfwc_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string    The version number of the plugin.
	 */
	public function wldcfwc_get_version() {
		return $this->version;
	}
}
