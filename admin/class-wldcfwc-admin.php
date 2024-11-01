<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/admin
 */

class WLDCFWC_Admin {


	/**
	 * Common functionality used by wldcfwc_Public and wldcfwc_Admin
	 */
	use Wldcfwc_Trait;

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The options that are saved within the admin panel
	 *
	 * @since  1.0.0
	 * @var    string    $options
	 */
	private $options;
	private $options_all;

	private $featured_products_count;
	private $save_default_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->featured_products_count = 8;

		if ( ! isset( $this->options_all ) ) {
			//used to instantiate admin page
			$this->options_all = $this->wldcfwc_get_options( 'all' );
		}

		// add a post display state for special WC pages.
		add_filter( 'display_post_states', array( $this, 'wldcfwc_add_display_post_states' ), 10, 2 );
	}

	/**
	 * Initial connection to WishList.com and saving of default options
	 */
	public function wldcfwc_initial_connection_wishlistdotcom() {
		/**
		 * Initial Connection to WishList.com.
		 */

		$initial_get_api_key_attempted	= get_option( WLDCFWC_SLUG . '_initial_get_api_key_attempted' );
		$valid_api_key					= get_option( WLDCFWC_SLUG . '_valid_api_key' );
		$api_key_deleted				= get_option( WLDCFWC_SLUG . '_api_key_deleted' );
		$do_initial_post_options_to_wlcom 		= false;
		if ( 'yes_str' != $initial_get_api_key_attempted && 'yes_str' != $valid_api_key && 'yes_str' != $api_key_deleted ) {
			update_option( WLDCFWC_SLUG . '_initial_get_api_key_attempted', 'yes_str');
			wldcfwc_get_wlcom_api_key();
			$this->options_all = $this->wldcfwc_get_options( 'all' );
			$do_initial_post_options_to_wlcom = true;
		}
		$options = get_option( WLDCFWC_SLUG );
		$this->save_default_options = false;
		if ( empty( $options ) ) {
			$this->save_default_options = true;
			//create data array to save
			$save_options = $this->wldcfwc_get_options( 'all', false );
			$final_save_options = $this->wldcfwc_adjust_options_prior_to_saving( $save_options );
			update_option( WLDCFWC_SLUG , $final_save_options );
		}

		if ( $do_initial_post_options_to_wlcom ) {
			$this->wldcfwc_post_options_to_wlcom();
		}

	}

	/**
	 * Add a post display state for wldcfwc pages in the list of pages.
	 *
	 * @param array   $post_states An array of post display states.
	 * @param WP_Post $post        The current post object.
	 */
	public function wldcfwc_add_display_post_states( $post_states, $post ) {
		if ( (int) get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION ) === $post->ID ) {
			$post_states['wldcfwc_hp'] = 'WishList Homepage';
		}
		return $post_states;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_enqueue_styles() {

		if ( WLDCFWC_USE_MIN_CSS ) {
			$ext = '.min.css';
		} else {
			$ext = '.css';
		}
		// load after exopite-simple-options-framework (styles.css) which is used on our admin page only
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wldcfwc-admin' . $ext, array( 'exopite-simple-options-framework' ), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_enqueue_scripts() {

		// load scripts for our admin pages only
		$current_screen = get_current_screen();
		if ( $current_screen && str_contains( $current_screen->id, WLDCFWC_SLUG ) ) {
			if ( WLDCFWC_USE_MIN_JS ) {
				$ext = '.min.js';
			} else {
				$ext = '.js';
			}
			$script_handle = $this->plugin_name;
			wp_enqueue_script( $script_handle, plugin_dir_url( __FILE__ ) . 'js/wldcfwc-admin' . $ext, array( 'jquery' ), $this->version, false );

			wp_localize_script(
				$script_handle,
				'wldcfwc_object_admin',
				array(
					'nonce' => array(
						'wldcfwc_post_options_to_wlcom_rec_ajax' => wp_create_nonce( 'wldcfwc_post_options_to_wlcom_rec_ajax' ),
						'wldcfwc_get_wlcom_api_key_rec_ajax' => wp_create_nonce( 'wldcfwc_get_wlcom_api_key_rec_ajax' ),
						'wldcfwc_get_once_auth_token_rec_ajax' => wp_create_nonce( 'wldcfwc_get_once_auth_token_rec_ajax' ),
						'wldcfwc_delete_wlcom_api_key_rec_ajax' => wp_create_nonce( 'wldcfwc_delete_wlcom_api_key_rec_ajax' ),
						'wldcfwc_get_product_droppown_rec_ajax' => wp_create_nonce( 'wldcfwc_get_product_droppown_rec_ajax' ),
						'wldcfwc_get_theme_primary_colors_rec_ajax' => wp_create_nonce( 'wldcfwc_get_theme_primary_colors_rec_ajax' ),
					),
				)
			);
		}
	}

	/**
	 * Begin options using Exopite-Simple-Options-Framework
	 */
	public function wldcfwc_create_menu() {

		$this->wldcfwc_initial_connection_wishlistdotcom();

		/**
		 * Configure admin page
		 */
		$initial_get_api_key_attempted	= get_option( WLDCFWC_SLUG . '_initial_get_api_key_attempted' );
		$valid_api_key					= get_option( WLDCFWC_SLUG . '_valid_api_key' );
		$api_key_deleted				= get_option( WLDCFWC_SLUG . '_api_key_deleted' );
		$default_values					= $this->wldcfwc_get_field_defaults__options( 'all' );
		$store_id_a						= wldcfwc_store_id();
		$api_domain						= $store_id_a['api_domain'];
		$wlcom_your_store_url			= $store_id_a['store_url'];
		$wlcom_store_shop_url			= $store_id_a['store_url_no_scheme'];
		$store_domain					= $store_id_a['store_domain'];
		if ( 'dev' == WLDCFWC_ENV ) {
			$wldcom_domain = 'dev.wishlist.com';
		} elseif ( 'staging' == WLDCFWC_ENV ) {
			$wldcom_domain = 'staging.wishlist.com';
		} else {
			$wldcom_domain = 'www.wishlist.com';
		}
		$wlcom_store_listing_preview_url = 'https://' . $wldcom_domain . '/wooCommApi?api_action=store_listing&store_domain=' . $store_domain;
		$wlcom_support_url = 'https://' . $wldcom_domain . '/wooCommApi?api_action=support&store_domain=' . $store_domain;

		$support_button_html = '
			<div class="wldcfwc-preview-email-template">
				<button type="button" class="wldcfwc-button-md wldcfwc-license-save-button wldcfwc-popwin-button-js" data-button-mode="get_support">' . esc_html__( 'Get support', 'wishlist-dot-com-for-woocommerce' ) . '</button>
			</div>
	        ';

		$wl_hp_page_id = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		$wl_hp_url     = get_permalink( $wl_hp_page_id );
		$section_tab_css = '';

		$button_shortcode__product_page = '[wldcfwc_button_product_page]';
		$button_shortcode__product_loop = '[wldcfwc_button_product_loop]';
		$button_shortcode__cart         = '[wldcfwc_button_cart]';
		$button_style_options           = array(
			'theme_button'            => esc_html__( 'Button with theme style', 'wishlist-dot-com-for-woocommerce' ),
			'custom_button'           => esc_html__( 'Button with custom style', 'wishlist-dot-com-for-woocommerce' ),
			'custom_button_using_css' => esc_html__( 'Button using custom css', 'wishlist-dot-com-for-woocommerce' ),
			'text_link'               => esc_html__( 'Text link', 'wishlist-dot-com-for-woocommerce' ),
		);
		$button_style_options_default   = $button_style_options;

		$button_position_options               = array(
			'wishlist_button_after_add_to_cart_button' => esc_html__( 'After "Add to cart" button', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_after_product_info'       => esc_html__( 'After product information', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_after_thumbnails'         => esc_html__( 'After thumbnails', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_after_summary'            => esc_html__( 'After summary', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_use_shortcode'            => esc_html__( 'Use shortcode', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_use_javascript'           => esc_html__( 'Use Javascript', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_hide'                     => esc_html__( 'Hidden', 'wishlist-dot-com-for-woocommerce' ),
		);
		$button_position_options__product_page = array();
		foreach ( $button_position_options as $key => $val ) {
			$button_position_options__product_page[ $key ] = $val;
		}
		$button_position_options               = array(
			'wishlist_button_before_add_to_cart_button' => esc_html__( 'Before "Add to cart" button', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_after_add_to_cart_button'  => esc_html__( 'After "Add to cart" button', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_use_shortcode'             => esc_html__( 'Use shortcode', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_use_javascript'            => esc_html__( 'Use Javascript', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_hide'                      => esc_html__( 'Hidden', 'wishlist-dot-com-for-woocommerce' ),
		);
		$button_position_options__product_loop = array();
		foreach ( $button_position_options as $key => $val ) {
			$button_position_options__product_loop[ $key ] = $val;
		}
		$button_position_options       = array(
			'wishlist_button_above_right'    => esc_html__( 'Above cart, on right', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_above_left'     => esc_html__( 'Above cart, on left', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_above_centered' => esc_html__( 'Above cart, centered', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_below_right'    => esc_html__( 'Below cart, on right', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_below_left'     => esc_html__( 'Below cart, on left', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_below_centered' => esc_html__( 'Below cart, centered', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_use_shortcode'  => esc_html__( 'Use shortcode', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_use_javascript' => esc_html__( 'Use Javascript', 'wishlist-dot-com-for-woocommerce' ),
			'wishlist_button_hide'           => esc_html__( 'Hidden', 'wishlist-dot-com-for-woocommerce' ),
		);
		$button_position_options__cart = array();
		foreach ( $button_position_options as $key => $val ) {
			$button_position_options__cart[ $key ] = $val;
		}

		// get the add2wishlist button icons
		$svg_icons = $this->wldcfwc_add2wishlist_icons_drop();
		foreach ( $svg_icons as $icon ) {
			if ( ! empty( $icon['source'] ) ) {
				$svg_icons_options[ $icon['value'] ] = array(
					'value'     => $icon['name'],
					'attribute' => 'data-type="' . esc_attr( $icon['type'] ) . '" data-source="' . esc_attr( $icon['source'] ) . '"',
				);
			} else {
				$svg_icons_options[ $icon['value'] ] = array(
					'value'     => $icon['name'],
					'attribute' => 'data-type="' . esc_attr( $icon['type'] ) . '"',
				);
			}
		}

		$wlcom_featured_product_skus_options = array();
		foreach ( $this->options_all['wlcom_featured_product_skus'] as $item ) {
			// concactenate sku^text so it's stored together so we don't have to do a query to display later
			if ( ! empty( $item ) ) {
				$item_a = explode( '^', $item );
				if ( isset( $item_a[1] ) ) {
					$item_name = $item_a[1];
				} else {
					$item_name = $item_a[0];
				}
				$wlcom_featured_product_skus_options[ $item ] = $item_name;
			}
		}

		$font_size_dropdown        = $this->wldcfwc_get_front_size_dropdown();
		$font_size_dropdown_footer = $this->wldcfwc_get_front_size_dropdown( 'text' );

		$store_id_a                       = wldcfwc_store_id();
		$store_url                        = $store_id_a['store_url'];
		$wlcom_plgn_plugin_display_status = $this->options_all['wlcom_plgn_plugin_display_status'];

		$service_level_key = WLDCFWC_SLUG . '_service_level';
		$service_level     = get_option( $service_level_key );

		$display_status_options = array(
			'status_offline' => esc_html__( 'Offline. Customers can\'t see your store\'s WishList.', 'wishlist-dot-com-for-woocommerce' ),
			'status_testing' => esc_html__( 'Testing - Only administrators, and those who can edit pages, can see your store\'s WishList.', 'wishlist-dot-com-for-woocommerce' ),
			'status_live'    => esc_html__( 'Live - Customers can see your store\'s WishList', 'wishlist-dot-com-for-woocommerce' ),
		);

		/*
		* Create a submenu page under Plugins.
		*/
		$config_submenu = array(
			'type'            => 'menu',
			'id'              => $this->plugin_name,
			'menu_title'      => 'WishList',//left side menu
			'title'           => 'WishList',
			'capability'      => 'manage_options',
			'plugin_basename' => plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' ),
			'tabbed'          => true,
			'icon'            => plugin_dir_url( __FILE__ ) . 'images/wishlist_bubble_white.svg',
			'search_box'      => false,
			'multilang'       => false,
			'settings_link'   => admin_url( 'admin.php?page=' . WLDCFWC_SLUG ),
		);

		/**
		 * General settings
		 */
		$permalink_structure = get_option( 'permalink_structure' );
		$store_url_test      = wc_get_page_permalink( 'wldc-wishlist' );
		if ( str_contains( $wlcom_store_shop_url, '?' ) ) {
			$change_store_url_prompt = '
                <div class="wldcfwc-connect-store-prompt notice notice-error">'
				. esc_html__( 'Consider changing your store\'s URL to not use "?".', 'wishlist-dot-com-for-woocommerce' ) .
				'<br>'
				. esc_html__( 'See WordPress Settings / Permalinks, then Permalink structure: "Post name", and Product permalinks: "Shop base".', 'wishlist-dot-com-for-woocommerce' ) .
				'<br>'
				. esc_html__( 'After changing your store\'s permalink URLs, connect your store to WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '
                </div>
	            ';
		} else {
			$change_store_url_prompt = '';
		}

		$connect_store_description = '
            <div class="wldcfwc-title-subsection wldcfwc-connect-store-section">
                <div class="wldcfwc-connect-store wldcfwc-hide">
                    ' . $change_store_url_prompt . '
                    <div class="wldcfwc-connect-store-prompt notice notice-info">
                        <p>
                            ' . esc_html__( 'Connect to WishList.com to configure your WishList.', 'wishlist-dot-com-for-woocommerce' ) . '
                        </p>
                        <p>
                            ' . esc_html__( 'The required plugin settings are transmitted to WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '
                        </p>
                    </div>
                    <button id="wldcfwc-connect-to-wldcom-js" type="button" class="wldcfwc-button-margin-top wldcfwc-button-md wldcfwc-connect-to-wldcom-button">' . esc_html__( 'Connect', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                </div>
   				<div id="wldcfwc-connect-store-error" class="wldcfwc-hide wldcfwc-connect-store-error wldcfwc-connect-store-prompt notice notice-error">
					' . esc_html__( 'Error connecting to WishList.com. Please refresh this page.', 'wishlist-dot-com-for-woocommerce' ) . '
				</div>
                <div class="wldcfwc-connected-store wldcfwc-hide">
                    ' . $change_store_url_prompt . '
                    <div class="wldcfwc-connect-store-prompt notice notice-success">
                        <p>
                        	' . esc_html__( 'Connected to WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '
                      	</p>
                        <p>
                            ' . esc_html__( 'The required plugin settings are transmitted to WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '
                        </p>
                    </div>
                    <div class="wldcfwc-margin-top-10">
                        <a id="wldcfwc-delete-connection-to-wldcom-js" class="wldcfwc-danger-light-text" href="#">' . esc_html__( 'Delete connection', 'wishlist-dot-com-for-woocommerce' ) . '</a>
                    </div>
                </div>
                <div id="wldcfwc-delete-connection-confirmation" class="wldcfwc-delete-connection-confirmation wldcfwc-hide">
                    <div class="wldcfwc-connect-store-prompt notice notice-error">
                        <p>
                        	' . esc_html__( 'Are you sure you want to delete this store\'s connection to WishList.com?', 'wishlist-dot-com-for-woocommerce' ) . '
                      	</p>
                        <p>
                        	' . esc_html__( 'It will no longer be connected to WishList.com or listed on WishList.com', 'wishlist-dot-com-for-woocommerce' ) . '
                      	</p>
                        <p>
                        	<a id="wldcfwc-confirm-delete-connection-to-wldcom-js" class="wldcfwc-danger-light-text" href="#">' . esc_html__( 'Yes, delete connection', 'wishlist-dot-com-for-woocommerce' ) . '</a>
                        	&nbsp;&nbsp;&nbsp;
                        	<a id="wldcfwc-cancel-delete-connection-to-wldcom-js" class="wldcfwc-success" href="#">' . esc_html__( 'Cancel', 'wishlist-dot-com-for-woocommerce' ) . '</a>
                      	</p>
                    </div>
                </div>
            </div>
        ';

		$fields[] = array(
			'name'            => 'wldcfwc_general_settings_start',
			'title'           => esc_html__( 'Start', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'Display status &amp; connect to WishList.com', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Getting started', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'These settings control the visibility of your store\'s WishList and its connection to WishList.com.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'name' => 'connect_description',
					'type' => 'html',
					'html' => $support_button_html,
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Connection to WishList.com', 'wishlist-dot-com-for-woocommerce' ),
					'id'                => 'connect_title',
					'description'       => '',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description-licensing',
				),

				array(
					'name' => 'connect_description',
					'type' => 'html',
					'html' => $connect_store_description,
				),

				array(
					'id'          => 'wlcom_plgn_plugin_display_status',
					'type'        => 'radio',
					'title'       => esc_html__( 'Set the visibility of this plugin', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( '"Testing" allows you to test the plugin prior to going live. Don\'t forget to click "Save Settings" after making changes.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => $display_status_options,
					'default'     => $default_values['wlcom_plgn_plugin_display_status'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom',
				),
				array(
					'id'         => 'wlcom_admin_email',
					'type'       => 'text',
					'title'      => esc_html__( 'Your admin email', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This email address is used by WishList.com for support and administrative purposes.', 'wishlist-dot-com-for-woocommerce' ),
					'before'     => esc_html__( 'For administrative purposes only', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_admin_email'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom',
				),
			),
		);

		$section_admin_fields_for_insert = array();

		// add commone buttons using get_admin_fields__button
		$params                            = array();
		$params['section_title']           = esc_html__( 'Product page "Add to WishList" button', 'wishlist-dot-com-for-woocommerce' );
		$params['option_suffix']           = 'product_page';
		$params['position_title']          = esc_html__( 'Position of "Add to WishList" on the product page', 'wishlist-dot-com-for-woocommerce' );
		$params['button_position_options'] = $button_position_options__product_page;
		$params['button_shortcode']        = $button_shortcode__product_page;
		$params['button_style_options']    = $button_style_options;
		$params['svg_icons_options']       = $svg_icons_options;
		$params['default_values']          = $default_values;
		$section_admin_fields_for_insert['product_page'] = $this->wldcfwc_get_admin_fields__button( $params );
		$insert_fields_after_name['product_page']        = 'begin_product_page_div';

		$params                            = array();
		$params['section_title']           = esc_html__( 'Search &amp; category page "Add to WishList" buttons.', 'wishlist-dot-com-for-woocommerce' );
		$params['option_suffix']           = 'product_loop';
		$params['position_title']          = esc_html__( 'Position of "Add to WishList" on the search and category page', 'wishlist-dot-com-for-woocommerce' );
		$params['button_position_options'] = $button_position_options__product_loop;
		$params['button_shortcode']        = $button_shortcode__product_loop;
		$params['button_style_options']    = $button_style_options;
		$params['svg_icons_options']       = $svg_icons_options;
		$params['default_values']          = $default_values;
		$section_admin_fields_for_insert['product_loop'] = $this->wldcfwc_get_admin_fields__button( $params );
		$insert_fields_after_name['product_loop']        = 'begin_product_loop_div';

		$params                                  = array();
		$params['section_title']                 = esc_html__( 'Shopping cart "Add to WishList" button.', 'wishlist-dot-com-for-woocommerce' );
		$params['option_suffix']                 = 'cart';
		$params['position_title']                = esc_html__( 'Position of "Add to WishList" on the shopping cart page', 'wishlist-dot-com-for-woocommerce' );
		$params['button_position_options']       = $button_position_options__cart;
		$params['button_shortcode']              = $button_shortcode__cart;
		$params['button_style_options']          = $button_style_options;
		$params['svg_icons_options']             = $svg_icons_options;
		$params['default_values']                = $default_values;
		$params['omit_fields']                   = array( 'browse_wishlist_text__', 'item_alread_added_text__', 'item_added_text__' );
		$section_admin_fields_for_insert['cart'] = $this->wldcfwc_get_admin_fields__button( $params );
		$insert_fields_after_name['cart']        = 'begin_cart_div';

		/**
		 * Add to WishList
		 */
		$fields[] = array(
			'name'            => 'wldcfwc_add_to_wishlist',
			'title'           => esc_html__( 'Add to WishList', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'Add to WishList buttons and screens', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'These settings control the "Add to WishList" buttons on your store\'s shopping pages. These settings also control the "Add to WishList" screens', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				// sub tabs
				array(
					'type'              => 'content',
					'content'           => '
                        <div class="wldcfwc-sub-nav-button-group">
                            <button id="wldcfwc_sub_nav__product_page" data-content-id="wldcfwc_section__product_page" type="button" class="wldcfwc-sub-nav active exopite-sof-sub-nav-list-item">' . esc_html__( 'Product page', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__product_loop" data-content-id="wldcfwc_section__product_loop" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Search &amp; Category', 'wishlist-dot-com-for-woocommerce' ) . ' </button>
                            <button id="wldcfwc_sub_nav__cart"  data-content-id="wldcfwc_section__cart" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Shopping Cart', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                        </div>
                        ',
					'description'       => '',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top wldcfwc-sub-nav-button-group-wrapper',
					'description_class' => '',
				),

				// begin product page div
				array(
					'name' => 'begin_product_page_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__product_page" class="wldcfwc-show_hide_section__product_page">',
				),
				// end product page div
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin product loop div
				array(
					'name' => 'begin_product_loop_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__product_loop" class="wldcfwc-hide wldcfwc-show_hide_section__product_loop">',
				),
				// end product loop div
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin cart div
				array(
					'name' => 'begin_cart_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__cart" class="wldcfwc-hide wldcfwc-show_hide_section__cart">',
				),
				// end cart div
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// pop window/main window
				array(
					'type'        => 'content',
					'content'     => esc_html__( '"Add to WishList" screens', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Your customers see these screens after clicking an Add to WishList button on your store.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'  => 'wldcfwc-content-border-top exopite-sof-fieldgroup-title-h4 no-border-bottom',
				),
				array(
					'id'          => 'add2wishlist_window',
					'type'        => 'radio',
					'title'       => esc_html__( 'Choose what happens when Add to WishList buttons are clicked', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'How the Add to WishList screens are presented to your customers after clicking the Add to WishList buttons', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'main_window' => esc_html__( 'Navigate to the Add to WishList screens in the main window. The customer is returned to the original page after adding their Wish.', 'wishlist-dot-com-for-woocommerce' ),
						'popwindow'   => esc_html__( 'Open a smaller pop-window for the Add to WishList screens. This pop-window is closed after adding their Wish.', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['add2wishlist_window'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),
				array(
					'id'          => 'show_add2wishlist_button_spinner',
					'type'        => 'radio',
					'title'       => esc_html__( 'Show a spinner when the Add to WishList button is clicked', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'A standard circular spinner that lets customers know their Wish is being saved.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'YES, show a spinner', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'NO, don\'t show a spinner', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['show_add2wishlist_button_spinner'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),
				array(
					'id'         => 'add2wishlist_button_spinner_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Add to WishList button spinner color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['add2wishlist_button_spinner_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__button_spinner',
				),
				array(
					'id'         => 'add2wishlist_button_spinner_height',
					'type'       => 'text',
					'before'     => esc_html__( 'Height of the Add to WishList button spinner', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['add2wishlist_button_spinner_height'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__button_spinner',
				),
			),
		);
		/**
		 * WishList homepage
		 */
		$fields[] = array(
			'name'            => 'wldcfwc_wishlist_homepage',
			'title'           => esc_html__( 'WishList Homepage', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'WishList homepage', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(
				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'These settings control your store\'s WishList homepage. Your "WishList" top menu links to this WishList homepage. The WishList homepage has 3 buttons &amp; provides customers a quick way to 1) create WishLists, 2) find WishLists, or 3) manage their own WishLists.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'name' => 'wl_hp_url',
					'type' => 'html',
					'html' => '
                                <div class="exopite-sof-field exopite-sof-field-text no-border-bottom ">
                                    <h4 class="exopite-sof-title">
                                        ' . esc_html__( 'URL to your store\'s WishList homepage', 'wishlist-dot-com-for-woocommerce' ) . '
                                        <p class="exopite-sof-description">
                                            ' . esc_html__( 'If you have a custom menu for your store, use this url to add the WishList Homepage to it.', 'wishlist-dot-com-for-woocommerce' ) . '
                                        </p>
                                    </h4>
                                    <div class="exopite-sof-fieldset">
                                        <span class="wldcfwc-code-display">
                                            ' . esc_html( $wl_hp_url ) . '
                                        </span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                ',
				),

				array(
					'id'          => 'wishlist_hp_title',
					'type'        => 'text',
					'title'       => esc_html__( 'WishList homepage title', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This is the title of your WishList homepage. This is also the title of the top menu item that leads to your WishList homepage', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wishlist_hp_title'],
					'class'       => 'wldcfwc-form-control',
					'wrap_class'  => 'no-border-bottom ',
				),

				// store logo
				array(
					'id'         => 'hp_banner_img',
					'type'       => 'image',
					'title'      => esc_html__( 'WishList homepage banner image', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['hp_banner_img'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'hp_subtitle',
					'type'       => 'text',
					'title'      => esc_html__( 'WishList homepage subtitle', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['hp_subtitle'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'hp_subtitle_font_size',
					'name'       => 'hp_subtitle_font_size',
					'type'       => 'select',
					'title'      => esc_html__( 'Subtitle font size', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'id' => 'hp_subtitle_font_size',
					),
					'options'    => $font_size_dropdown,
					'default'    => $default_values['hp_subtitle_font_size'],
					'class'      => 'wldcfwc-form-control',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom',
				),

				array(
					'id'         => 'hp_description',
					'type'       => 'textarea',
					'title'      => esc_html__( 'Subtitle description', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'rows' => '3',
					),
					'default'    => $default_values['hp_description'],
					'class'      => 'wldcfwc-form-control wldcfwc-text_area',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'hp_description_font_size',
					'name'       => 'hp_description_font_size',
					'type'       => 'select',
					'title'      => esc_html__( 'Description font size', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'id' => 'hp_description_font_size',
					),
					'options'    => $font_size_dropdown,
					'default'    => $default_values['hp_description_font_size'],
					'class'      => 'wldcfwc-form-control',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'          => 'hp_alignment',
					'name'        => 'hp_alignment',
					'type'        => 'select',
					'title'       => esc_html__( 'WishList homepage content alignment', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Set the alignment of the text and buttons', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'id' => 'hp_alignment',
					),
					'options'     => array(
						'text_alignment_centered' => esc_html__( 'Centered', 'wishlist-dot-com-for-woocommerce' ),
						'text_alignment_left'     => esc_html__( 'Left aligned', 'wishlist-dot-com-for-woocommerce' ),
						'text_alignment_right'    => esc_html__( 'Right aligned', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['hp_alignment'],
					'class'       => 'wldcfwc-form-control',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom',
				),
				array(
					'id'         => 'hp_footer',
					'name'       => 'hp_footer',
					'type'       => 'textarea',
					'title'      => esc_html__( 'WishList homepage footer', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'rows' => '5',
					),
					'default'    => $default_values['hp_footer'],
					'class'      => 'wldcfwc-form-control wldcfwc-text_area',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'hp_footer_font_size',
					'name'       => 'hp_footer_font_size',
					'type'       => 'select',
					'title'      => esc_html__( 'footer font size', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'id' => 'hp_footer_font_size',
					),
					'options'    => $font_size_dropdown_footer,
					'default'    => $default_values['hp_footer_font_size'],
					'class'      => 'wldcfwc-form-control',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom ',
				),

				array(
					'id'         => 'hp_show_poweredby',
					'type'       => 'radio',
					'title'      => esc_html__( 'Show "powered by WishList.com" in the footer', 'wishlist-dot-com-for-woocommerce' ),
					'options'    => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'    => $default_values['hp_show_poweredby'],
					'class'      => '',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom',
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'WishList homepage buttons', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'The homepage\'s 3 buttons provide quick access to: 1) Create a WishList, 2) Find a WishList, 3) My WishList', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-content-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				// sub tabs
				array(
					'type'              => 'content',
					'content'           => '
                        <div class="wldcfwc-sub-nav-button-group">
                            <button id="wldcfwc_sub_nav__hp_createwishlist" data-content-id="wldcfwc_section__hp_createwishlist" type="button" class="wldcfwc-sub-nav active exopite-sof-sub-nav-list-item">' . esc_html__( 'Create WishList button', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__hp_findwishlist" data-content-id="wldcfwc_section__hp_findwishlist" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Find WishList button', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__hp_mywishlist" data-content-id="wldcfwc_section__hp_mywishlist" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'My WishList button', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                        </div>
                        ',
					'description'       => '',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top wldcfwc-sub-nav-button-group-wrapper',
					'description_class' => 'wldcfwc-content-description',
				),

				// button param inserted by get_admin_fields__hp_button()
				// begin hp_createwishlist
				array(
					'name' => 'begin_hp_createwishlist',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__hp_createwishlist" class="wldcfwc-no-border-top--off wldcfwc-show_hide_section__hp_createwishlist">',
				),
				// end hp_createwishlist
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin hp_findwishlist
				array(
					'name' => 'begin_hp_findwishlist',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__hp_findwishlist" class="wldcfwc-hide wldcfwc-show_hide_section__hp_findwishlist">',
				),
				// end hp_findwishlist
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin hp_mywishlist
				array(
					'name' => 'begin_hp_mywishlist',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__hp_mywishlist" class="wldcfwc-hide wldcfwc-show_hide_section__hp_mywishlist">',
				),
				// end hp_mywishlist
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// tooltip
				array(
					'type'        => 'content',
					'content'     => esc_html__( 'Tooltip style', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Displays information when customers hover over the "Save to WishList" buttons', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'  => 'wldcfwc-content-border-top exopite-sof-fieldgroup-title-h4 no-border-bottom',
				),
				array(
					'id'         => 'tooltip_background_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Tooltip background color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['tooltip_background_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field',
				),
				array(
					'id'         => 'tooltip_border_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Tooltip border color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['tooltip_border_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field',
				),
				array(
					'id'         => 'tooltip_text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Tooltip text color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['tooltip_text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field',
				),

				array(
					'id'         => 'tooltip_show_shadow',
					'type'       => 'radio',
					'title'      => esc_html__( 'Give the tooltip a shadow', 'wishlist-dot-com-for-woocommerce' ),
					'options'    => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'    => $default_values['tooltip_show_shadow'],
					'class'      => '',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom',
				),
			),
		);

		$menu_items = json_decode( $this->options_all['wlcom_store_top_menu_links_json'], true );

		/**
		 * WishList display
		 */
		$fields[] = array(
			'name'            => 'wldcfwc_wishlist_display_template',
			'title'           => esc_html__( 'WishList Display', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'WishList.com display', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'Your WishList runs on WishList.com. These settings control your WishList\'s design and functionality that\'s provided by WishList.com. These settings make WishList.com\'s functionality match your store\'s look, so it\'s a seemless experience for your customers.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'WishList features &amp; layout', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'Customize your WishList display and management pages. ', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'id'          => 'wlcom_about_wishlists_prompt',
					'type'        => 'textarea',
					'title'       => esc_html__( 'Description of how WishLists can be used', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Shown when saving a Wish to a WishList, and various other places', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '3',
					),
					'default'     => $default_values['wlcom_about_wishlists_prompt'],
					'after'       => esc_html__( 'like: WishLists can include things you like, inspirational ideas, or gifts you\'d love for a special occasion. Placeholders: {store_favicon}', 'wishlist-dot-com-for-woocommerce' ),
					'class'       => 'wldcfwc-form-control wldcfwc-text_area-html',
					'wrap_class'  => 'no-border-bottom',
				),
				array(
					'id'          => 'wlcom_powered_by_wishlistdotcom_header_prompt',
					'type'        => 'textarea',
					'title'       => esc_html__( 'Description about your WishList and WishList.com', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Tell your customers how convenient your WishList is. Shown when saving a Wish to a WishList, and various other places.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '3',
					),
					'default'     => $default_values['wlcom_powered_by_wishlistdotcom_header_prompt'],
					'after'       => esc_html__( 'like: This WishList is powerd by WishList.com - one WishList for all stores. Placeholders: {store_favicon}', 'wishlist-dot-com-for-woocommerce' ),
					'class'       => 'wldcfwc-form-control wldcfwc-text_area-html',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'          => 'wlcom_allow_multiple_wishlists',
					'type'        => 'radio',
					'title'       => esc_html__( 'Allow customers to create multiple WishLists', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Customers can create multiple profiles with multiple WishLists under each profile. Like a profile for each child with WishLists under each profile.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_allow_multiple_wishlists'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),

				array(
					'id'          => 'wlcom_wishlist_layout',
					'type'        => 'select',
					'title'       => esc_html__( 'Default WishList Layout', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Set the default layout of Wishes within a WishList. Customers can then select their preferred layout.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'id' => 'wlcom_wishlist_layout',
					),
					'options'     => array(
						'wishlist_layout_grid'    => esc_html__( 'Grid (rows and columns)', 'wishlist-dot-com-for-woocommerce' ),
						'wishlist_layout_list'    => esc_html__( 'List (one item per row)', 'wishlist-dot-com-for-woocommerce' ),
						'wishlist_layout_collage' => esc_html__( 'Collage (a masonry layout)', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_wishlist_layout'],
					'class'       => 'wldcfwc-form-control',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'          => 'wlcom_allow_profile_image',
					'type'        => 'radio',
					'title'       => esc_html__( 'Allow customers to use their own profile image.', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Their profile image comes from their google/apple/facebook login or their WishList.com profile', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_allow_profile_image'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),

				array(
					'id'          => 'wlcom_allow_wishlist_banner',
					'type'        => 'radio',
					'title'       => esc_html__( 'Allow customers to decorate their WishList with a banner image at the top.', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Customers can upload thier own banner image to WishList.com as part of their profile.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_allow_wishlist_banner'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),
				array(
					'id'          => 'wlcom_wishlist_header_color',
					'type'        => 'color',
					'title'       => esc_html__( 'Default color of your customers\' WishList banner', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'The WishList banner is a section at the top of the WishList with the customer\'s name and profile image', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wlcom_wishlist_header_color'],
					'class'       => '',
					'wrap_class'  => 'no-border-bottom',
				),
				array(
					'id'         => 'wlcom_wishlist_header_font_color',
					'type'       => 'color',
					'title'      => esc_html__( 'Text color of customer\'s name within their WishList\'s banner section', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_wishlist_header_font_color'],
					'class'      => '',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'wlcom_empty_wishlist_prompt',
					'type'       => 'textarea',
					'title'      => esc_html__( 'Message to show the customer when their WishList is empty', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'rows' => '3',
					),
					'default'    => $default_values['wlcom_empty_wishlist_prompt'],
					'class'      => 'wldcfwc-form-control wldcfwc-text_area-html',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'wlcom_empty_wishlist_shop_button',
					'type'       => 'text',
					'title'      => esc_html__( 'Shop button for empty WishLists', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_empty_wishlist_shop_button'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'         => 'wlcom_wishlist_icon_background_color__gift',
					'type'       => 'color',
					'title'      => esc_html__( 'Background color of WishList related icons (SVGs)', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_wishlist_icon_background_color__gift'],
					'class'      => '',
					'wrap_class' => 'no-border-bottom',
				),

				array(
					'id'          => 'wlcom_profile_initials_background_color',
					'type'        => 'color',
					'title'       => esc_html__( 'Background color for profile initials', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'For when a profile image is not used or available, and the customer\'s initials are used as a profile icon instead.', 'wishlist-dot-com-for-woocommerce' ),
					'before'      => esc_html__( 'Leave blank to use a variety of subtle colors', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wlcom_profile_initials_background_color'],
					'class'       => '',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'         => 'wlcom_allow_follow',
					'type'       => 'radio',
					'title'      => esc_html__( 'Allow following. People can follow a WishList', 'wishlist-dot-com-for-woocommerce' ),
					'options'    => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'    => $default_values['wlcom_allow_follow'],
					'class'      => '',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom ',
				),

				array(
					'id'          => 'wlcom_allow_friends',
					'type'        => 'radio',
					'title'       => esc_html__( 'Allow friending. People can request to be friends. Friend requests can then be accepted or denied', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Customers can disable friending on their WishLists, if they want.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_allow_friends'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),

				array(
					'id'          => 'wlcom_allow_comments',
					'type'        => 'radio',
					'title'       => esc_html__( 'Allow comments. Friends can comment on each other\'s WishLists.', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Customers can disable commenting on their WishLists, if they want.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_allow_comments'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom ',
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'WishList template header &amp; footer', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'This controls the header, top menu and footer that\'s used when displaying your WishList on WishList.com. The header is meant to look like your store\'s header.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-content-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'id'         => 'wlcom_wishlist_template_use_html__header',
					'type'       => 'radio',
					'title'      => esc_html__( 'Select the source of your WishList template header.', 'wishlist-dot-com-for-woocommerce' ),
					'options'    => array(
						'no_str'  => esc_html__( 'Use your store\'s logo, name and top menu.', 'wishlist-dot-com-for-woocommerce' ),
						'yes_str' => esc_html__( 'Use custom html', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'    => $default_values['wlcom_wishlist_template_use_html__header'],
					'class'      => '',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom ',
				),

				array(
					'id'          => 'wlcom_header_template_sticky',
					'type'        => 'radio',
					'title'       => esc_html__( 'Make the header sticky so it\'s always showing at the top', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'If you\'re store\'s top menu is always showing at the top, then select Yes.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_header_template_sticky'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_background_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Header background color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_background_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_top_bottom_padding',
					'type'       => 'text',
					'before'     => esc_html__( 'Header top &amp; bottom padding', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_top_bottom_padding'],
					'after'      => esc_html__( 'like 16px', 'wishlist-dot-com-for-woocommerce' ),
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_bottom_border_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Header bottom border color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_bottom_border_color'],
					'after'      => esc_html__( 'to hide, leave empty', 'wishlist-dot-com-for-woocommerce' ),
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_bottom_border_width',
					'type'       => 'text',
					'before'     => esc_html__( 'Header bottom border width', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_bottom_border_width'],
					'after'      => 'like ' . $default_values['wlcom_header_template_bottom_border_width'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'          => 'wlcom_template_store_logo',
					'type'        => 'image',
					'title'       => esc_html__( 'Header store logo', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This goes in the top left of your WishList template header', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wlcom_template_store_logo'],
					'class'       => 'wldcfwc-form-control',
					'wrap_class'  => 'no-border-bottom wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'          => 'wlcom_template_store_name',
					'type'        => 'text',
					'title'       => esc_html__( 'Header store name', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This goes to the right of your logo in your WishList template header', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wlcom_template_store_name'],
					'class'       => 'wldcfwc-form-control',
					'wrap_class'  => 'no-border-bottom wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),

				// menu html
				array(
					'name'       => 'header_menu',
					'type'       => 'headermenu',
					'menu_items' => $menu_items,
				),

				array(
					'id'         => 'wlcom_template_store_name__text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Header store name color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_template_store_name__text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_menu_item__text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Menu text color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_menu_item__text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_menu_item__text_color_hover',
					'type'       => 'color',
					'before'     => esc_html__( 'Menu hover text color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_menu_item__text_color_hover'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_menu_item__text_font_size',
					'type'       => 'text',
					'before'     => esc_html__( 'Menu text font size', 'wishlist-dot-com-for-woocommerce' ),
					'after'      => esc_html__( 'like: 16px', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_menu_item__text_font_size'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),
				array(
					'id'         => 'wlcom_header_template_menu_item__text_decoration',
					'type'       => 'text',
					'before'     => esc_html__( 'Menu text decoration', 'wishlist-dot-com-for-woocommerce' ),
					'after'      => esc_html__( 'like: none or underline', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_header_template_menu_item__text_decoration'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header',
				),

				array(
					'id'          => 'wlcom_template_html__header',
					'type'        => 'textarea',
					'title'       => esc_html__( 'WishList header template custom html', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This html controls the WishList template header and its top menu. It\'s meant to make your WishList functionality that\'s displayed by WishList.com look like your store.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '7',
					),
					'default'     => $default_values['wlcom_template_html__header'],
					'class'       => 'wldcfwc-form-control wldcfwc-text_area-html',
					'wrap_class'  => 'no-border-bottom wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_html__header',
				),
				array(
					'id'         => 'wlcom_wishlist_template_use_html__footer',
					'type'       => 'radio',
					'title'      => esc_html__( 'WishList template footer.', 'wishlist-dot-com-for-woocommerce' ),
					'options'    => array(
						'no_str'  => esc_html__( 'Don\'t add a WishList template footer', 'wishlist-dot-com-for-woocommerce' ),
						'yes_str' => esc_html__( 'Use custom html', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'    => $default_values['wlcom_wishlist_template_use_html__footer'],
					'class'      => '',
					'style'      => 'fancy',
					'wrap_class' => 'no-border-bottom ',
				),

				array(
					'id'          => 'wlcom_template_html__footer',
					'type'        => 'textarea',
					'title'       => esc_html__( 'WishList footer template custom html', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This html controls the WishList template footer.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '7',
					),
					'default'     => $default_values['wlcom_template_html__footer'],
					'class'       => 'wldcfwc-form-control wldcfwc-text_area-html',
					'wrap_class'  => 'no-border-bottom wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_html__footer',
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'WishList colors, fonts, &amp; settings', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'These settings are used for your WishList\'s text, buttons and alerts that are used to display your WishList on WishList.com', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-content-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				// sub tabs
				array(
					'type'              => 'content',
					'content'           => '
                        <div class="wldcfwc-sub-nav-button-group">
                            <button id="wldcfwc_sub_nav__text"  data-content-id="wldcfwc_section__text" type="button" class="wldcfwc-sub-nav active exopite-sof-sub-nav-list-item">' . esc_html__( 'Text', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__share_button" data-content-id="wldcfwc_section__share_button" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Share WishList button', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__primary_button" data-content-id="wldcfwc_section__primary_button" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Primary button ', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__success_button" data-content-id="wldcfwc_section__success_button" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Success button', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__danger_button"  data-content-id="wldcfwc_section__danger_button" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Danger button', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__wish_received_flag"  data-content-id="wldcfwc_section__wish_received_flag" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Gift already received flag', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__alerts"  data-content-id="wldcfwc_section__alerts" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Alerts', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                        </div>
                        ',
					'description'       => '',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top wldcfwc-sub-nav-button-group-wrapper',
					'description_class' => 'wldcfwc-content-description',
				),

				// begin_text_div
				array(
					'name' => 'begin_gift_text_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__text" class="wldcfwc-show_hide_section__text">',
				),
				array(
					'id'         => 'wlcom_text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Default text color', 'wishlist-dot-com-for-woocommerce' ),
					'after'      => esc_html__( '(empty is unchanged)', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_font_size',
					'type'       => 'text',
					'before'     => esc_html__( 'Default font size', 'wishlist-dot-com-for-woocommerce' ),
					'after'      => esc_html__( '(empty is unchanged)', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_font_size'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_font_family',
					'type'       => 'text',
					'before'     => esc_html__( 'Default font family', 'wishlist-dot-com-for-woocommerce' ),
					'after'      => esc_html__( '(empty is unchanged)', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_font_family'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field',
				),
				// end_text_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin_share_button_div
				array(
					'name' => 'begin_share_button_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__share_button" class="wldcfwc-hide wldcfwc-show_hide_section__share_button">',
				),
				// end begin_share_button_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin_primary_button_div
				array(
					'name' => 'begin_primary_button_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__primary_button" class="wldcfwc-hide wldcfwc-show_hide_section__primary_button">',
				),
				// end begin_primary_button_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin_success_button_div
				array(
					'name' => 'begin_success_button_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__success_button" class="wldcfwc-hide wldcfwc-show_hide_section__success_button">',
				),
				// end begin_success_button_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin_danger_button_div
				array(
					'name' => 'begin_danger_button_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__danger_button" class="wldcfwc-hide wldcfwc-show_hide_section__danger_button">',
				),
				// end begin_danger_button_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin_wish_received_flag
				array(
					'name' => 'begin_wish_received_flag',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__wish_received_flag" class="wldcfwc-hide wldcfwc-show_hide_section__wish_received_flag">',
				),
				array(
					'id'         => 'wlcom_gift_received_button_background_color',
					'type'       => 'color',
					'before'     => esc_html__( '"Gift Already Received" flag color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_gift_received_button_background_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				// end begin_gift_received_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
				// begin_alerts_div
				array(
					'name' => 'begin_gift_alerts_div',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__alerts" class="wldcfwc-hide wldcfwc-show_hide_section__alerts">',
				),
				array(
					'id'         => 'wlcom_success_alert_background_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Success alert background color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_success_alert_background_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_success_alert_text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Success alert text color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_success_alert_text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_danger_alert_background_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Danger alert background color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_danger_alert_background_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_danger_alert_text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Danger alert text color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_danger_alert_text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_info_alert_background_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Info alert background color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_info_alert_background_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				array(
					'id'         => 'wlcom_info_alert_text_color',
					'type'       => 'color',
					'before'     => esc_html__( 'Info alert text color', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_info_alert_text_color'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                    ',
				),
				// end_alerts_div
				array(
					'type' => 'html',
					'html' => '</div>',
				),
			),
		);

		// add commone buttons using get_admin_fields__button
		$params                   = array();
		$params['section_title']  = 'Share WishList button colors';
		$params['option_suffix']  = 'share_button';
		$params['default_values'] = $default_values;
		$section_admin_fields_for_insert['share_button'] = $this->wldcfwc_get_admin_fields__wishlist_page_buttons( $params );
		$insert_fields_after_name['share_button']        = 'begin_share_button_div';

		$params                   = array();
		$params['section_title']  = 'Primary button colors';
		$params['option_suffix']  = 'primary_button';
		$params['default_values'] = $default_values;
		$section_admin_fields_for_insert['primary_button'] = $this->wldcfwc_get_admin_fields__wishlist_page_buttons( $params );
		$insert_fields_after_name['primary_button']        = 'begin_primary_button_div';

		$params                   = array();
		$params['section_title']  = 'Success button colors';
		$params['option_suffix']  = 'success_button';
		$params['default_values'] = $default_values;
		$section_admin_fields_for_insert['success_button'] = $this->wldcfwc_get_admin_fields__wishlist_page_buttons( $params );
		$insert_fields_after_name['success_button']        = 'begin_success_button_div';

		$params                   = array();
		$params['section_title']  = 'Danger button colors';
		$params['option_suffix']  = 'danger_button';
		$params['default_values'] = $default_values;
		$section_admin_fields_for_insert['danger_button'] = $this->wldcfwc_get_admin_fields__wishlist_page_buttons( $params );
		$insert_fields_after_name['danger_button']        = 'begin_danger_button_div';

		// generate homepage button fields
		$params                         = array();
		$params['section_title']        = '';
		$params['button_name']          = 'Create a WishList';
		$params['option_suffix']        = 'hp_createwishlist';
		$params['button_style_options'] = $button_style_options;
		$params['svg_icons_options']    = $svg_icons_options;
		$params['default_values']       = $default_values;
		$section_admin_fields_for_insert['hp_createwishlist'] = $this->wldcfwc_get_admin_fields__hp_button( $params );
		$insert_fields_after_name['hp_createwishlist']        = 'begin_hp_createwishlist';

		$params                         = array();
		$params['section_title']        = '';
		$params['button_name']          = 'Find a WishList';
		$params['option_suffix']        = 'hp_findwishlist';
		$params['button_style_options'] = $button_style_options;
		$params['svg_icons_options']    = $svg_icons_options;
		$params['default_values']       = $default_values;
		$section_admin_fields_for_insert['hp_findwishlist'] = $this->wldcfwc_get_admin_fields__hp_button( $params );
		$insert_fields_after_name['hp_findwishlist']        = 'begin_hp_findwishlist';

		$params                         = array();
		$params['section_title']        = '';
		$params['button_name']          = 'My WishList';
		$params['option_suffix']        = 'hp_mywishlist';
		$params['button_style_options'] = $button_style_options;
		$params['svg_icons_options']    = $svg_icons_options;
		$params['default_values']       = $default_values;
		$section_admin_fields_for_insert['hp_mywishlist'] = $this->wldcfwc_get_admin_fields__hp_button( $params );
		$insert_fields_after_name['hp_mywishlist']        = 'begin_hp_mywishlist';

		/**
		 * Store Information
		 */
		$fields[] = array(
			'name'            => 'wldcfwc_store_info',
			'title'           => esc_html__( 'Store Info', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'Store Information', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(
				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'These settings control your store\'s listing on WishList.com', 'wishlist-dot-com-for-woocommerce' ) .
						'<p>
                            <a class="button button-primary exopite-sof-button" href="' . $wlcom_store_listing_preview_url . '" target="store_listing_preview">
                                ' . esc_html__( 'Preview', 'wishlist-dot-com-for-woocommerce' ) . '
                            </a>
                        </p>',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),
				array(
					'id'         => 'wlcom_your_store_name',
					'type'       => 'text',
					'title'      => esc_html__( 'Your store\'s name', 'wishlist-dot-com-for-woocommerce' ),
					'before'     => esc_html__( 'This can be your domain name, or your business name', 'wishlist-dot-com-for-woocommerce' ),
					'default'    => $default_values['wlcom_your_store_name'],
					'class'      => 'wldcfwc-form-control',
					'wrap_class' => 'no-border-bottom',
				),
				array(
					'id'          => 'wlcom_plgn_your_store_logo',
					'type'        => 'image',
					'title'       => esc_html__( 'Your store\'s logo', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Used in various places, like your \"Add to WishList\" screens and within WishList.com\'s shopping section.', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wlcom_plgn_your_store_logo'],
					'class'       => 'wldcfwc-form-control',
					'wrap_class'  => 'no-border-bottom',
				),
				array(
					'id'          => 'wlcom_plgn_your_store_icon',
					'type'        => 'image',
					'title'       => esc_html__( 'Your store\'s icon', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'This is a favicon, used in various places, like your \"Add to WishList\" screens and within WishList.com\'s shopping section. Used for responsive display for mobile devices', 'wishlist-dot-com-for-woocommerce' ),
					'after'       => esc_html__( 'roughly 45px square', 'wishlist-dot-com-for-woocommerce' ),
					'default'     => $default_values['wlcom_plgn_your_store_icon'],
					'class'       => 'wldcfwc-form-control',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'          => 'wlcom_your_store_description',
					'name'        => 'wlcom_your_store_description',
					'type'        => 'textarea',
					'title'       => esc_html__( 'Your store\'s description', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Used in various places, like within WishList.com\'s shopping section.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '5',
					),
					'default'     => $default_values['wlcom_your_store_description'],
					'class'       => 'wldcfwc-form-control wldcfwc-text_area',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'          => 'wlcom_your_store_categories',
					'name'        => 'wlcom_your_store_categories',
					'type'        => 'textarea',
					'title'       => esc_html__( 'Categories that describe your store', 'wishlist-dot-com-for-woocommerce' ),
					'before'      => esc_html__( 'Comma separated list of category names', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Like: Women\'s Apparel, Home Appliances. Used in various places, like within WishList.com\'s shopping section.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '2',
					),
					'class'       => 'wldcfwc-form-control wldcfwc-text_area',
					'wrap_class'  => 'no-border-bottom',
				),
				array(
					'id'          => 'wlcom_your_store_tags',
					'name'        => 'wlcom_your_store_tags',
					'type'        => 'textarea',
					'title'       => esc_html__( 'Tags that describe your store', 'wishlist-dot-com-for-woocommerce' ),
					'before'      => esc_html__( 'Comma separated list of tags', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Like: Fashion, Trendy, Women\'s Apparel, Home Tech. Used in various places, like within WishList.com\'s shopping section.', 'wishlist-dot-com-for-woocommerce' ),
					'attributes'  => array(
						'rows' => '2',
					),
					'class'       => 'wldcfwc-form-control wldcfwc-text_area',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'           => 'wlcom_store_shop_url',
					'type'         => 'hidden',
					'name'         => 'wlcom_store_shop_url',
					'hidden_value' => $wlcom_store_shop_url,
				),
				array(
					'id'          => 'wlcom_website_is_featured',
					'type'        => 'radio',
					'title'       => esc_html__( 'Feature your store within WishList.com\'s shopping section', 'wishlist-dot-com-for-woocommerce' ),
					'description' => esc_html__( 'Your store will be featured on WishList.com for shoppers to find. The featured listing includes priority placement in search results and featured products from below.', 'wishlist-dot-com-for-woocommerce' ) .
						'<br><br>' .
						esc_html__( 'This listing is separate from your store\'s WishLists, which are always available on WishList.com and your store.', 'wishlist-dot-com-for-woocommerce' ),
					'options'     => array(
						'yes_str' => esc_html__( 'Yes', 'wishlist-dot-com-for-woocommerce' ),
						'no_str'  => esc_html__( 'No', 'wishlist-dot-com-for-woocommerce' ),
					),
					'default'     => $default_values['wlcom_list_on_wishlistdotcom'],
					'class'       => '',
					'style'       => 'fancy',
					'wrap_class'  => 'no-border-bottom',
				),

				array(
					'id'         => 'wlcom_featured_product_skus',
					'type'       => 'select',
					'title'      => esc_html__( 'Products to feature with your store\'s listing on WishList.com', 'wishlist-dot-com-for-woocommerce' ),
					'before'     => esc_html__( 'Select ', 'wishlist-dot-com-for-woocommerce' ) .
						$this->featured_products_count .
						esc_html__( 'products to feature', 'wishlist-dot-com-for-woocommerce' ),
					'attributes' => array(
						'data-select2-sku' => 'yes',
						'multiple'         => true,
					),
					'options'    => $wlcom_featured_product_skus_options,
					'class'      => 'choice--off select2-selection--multiple wldcfwc-form-control',
					'style'      => 'fancy--off',
					'wrap_class' => 'select2-container--bootstrap-5--off wldcfwc-form-select2 no-border-bottom',
				),
				array(
					'id'           => 'wlcom_featured_product_skus__hidden_select2',
					'type'         => 'hidden',
					'hidden_value' => '',
				),
			),
		);

		/**
		 * Emails
		 */
		$preview_email_button = '
			<div class="wldcfwc-preview-email-template">
				<button type="button" class="wldcfwc-button-margin-top wldcfwc-button-md wldcfwc-license-save-button wldcfwc-popwin-button-js" data-button-mode="preview_email_template">' . esc_html__( 'Preview welcome email', 'wishlist-dot-com-for-woocommerce' ) . '</button>
				<div id="wldcfwc-preview-email-error" class="wldcfwc-hide wldcfwc-connect-store-prompt notice notice-error">
					' . esc_html__( 'Error connecting to WishList.com. Please refresh this page.', 'wishlist-dot-com-for-woocommerce' ) . '
				</div>
			</div>
	        ';
		$fields[] = array(
			'name'            => 'wldcfwc_emails',
			'title'           => esc_html__( 'Emails', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'WishList.com email', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(

				array(
					'type'              => 'content',
					'name'              => 'email_description',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'These settings control WishList emails sent to your customers by WishList.com. These emails come from WishList.com and support your customers\' WishList management.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				// sub tabs
				array(
					'type'              => 'content',
					'content'           => '
                        <div class="wldcfwc-sub-nav-button-group">
                            <button id="wldcfwc_sub_nav__email_template" data-content-id="wldcfwc_section__email_template" type="button" class="wldcfwc-sub-nav active exopite-sof-sub-nav-list-item">' . esc_html__( 'Email Template', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__welcome_email" data-content-id="wldcfwc_section__welcome_email" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Welcome Email', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__empty_wishlist_reminder"  data-content-id="wldcfwc_section__empty_wishlist_reminder" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Empty WishList Reminder', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__reserved_conf" data-content-id="wldcfwc_section__reserved_conf" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Reservation Confirmation', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__reserved_notice"  data-content-id="wldcfwc_section__reserved_notice" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Wish Reserved Notice', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__wish_on_sale"  data-content-id="wldcfwc_section__wish_on_sale" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Wish on Sale', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                            <button id="wldcfwc_sub_nav__wish_in_stock"  data-content-id="wldcfwc_section__wish_in_stock" type="button" class="wldcfwc-sub-nav exopite-sof-sub-nav-list-item">' . esc_html__( 'Wish in Stock', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                        </div>
                        ',
					'description'       => '',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top wldcfwc-sub-nav-button-group-wrapper',
					'description_class' => 'wldcfwc-content-description',
				),

				// begin email_template
				array(
					'name' => 'begin_email_template',
					'type' => 'html',
					'html' => '
                            <div id="wldcfwc_section__email_template" class="wldcfwc-show_hide_section__email_template">
							' . $preview_email_button,
				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin welcome_email
				array(
					'name' => 'begin_welcome_email',
					'type' => 'html',
					'html' => '
                            <div id="wldcfwc_section__welcome_email" class="wldcfwc-hide wldcfwc-show_hide_section__welcome_email">
							' . $preview_email_button,

				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin empty_wishlist_reminder
				array(
					'name' => 'begin_empty_wishlist_reminder',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__empty_wishlist_reminder" class="wldcfwc-hide wldcfwc-show_hide_section__empty_wishlist_reminder">',
				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin reserved_conf
				array(
					'name' => 'begin_reserved_conf',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__reserved_conf" class="wldcfwc-hide wldcfwc-show_hide_section__reserved_conf">',
				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin reserved_notice
				array(
					'name' => 'begin_reserved_notice',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__reserved_notice" class="wldcfwc-hide wldcfwc-show_hide_section__reserved_notice">',
				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin wish_on_sale
				array(
					'name' => 'begin_wish_on_sale',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__wish_on_sale" class="wldcfwc-hide wldcfwc-show_hide_section__wish_on_sale">',
				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				// begin wish_in_stock
				array(
					'name' => 'begin_wish_in_stock',
					'type' => 'html',
					'html' => '<div id="wldcfwc_section__wish_in_stock" class="wldcfwc-hide wldcfwc-show_hide_section__wish_in_stock">',
				),
				array(
					'type' => 'html',
					'html' => '</div>',
				),

				array(
					'type' => 'html',
					'name' => 'end_emails',
					'html' => '',
				),
			),
		);

		// generate button fields
		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Email Template';
		$params['option_suffix']  = 'email_template';
		$params['default_values'] = $default_values;

		$params['omit_fields']                             = array( 'wlcom_email_conf__enabl', 'wlcom_email_conf__subject', 'wlcom_email_conf__html', 'wlcom_email_conf__use_custom_template', 'wlcom_email_conf__exclude_product_categories', 'wlcom_email_conf__exclude_product_skus' );
		$section_admin_fields_for_insert['email_template'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['email_template']        = 'begin_email_template';

		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Welcome Email';
		$params['option_suffix']  = 'welcome_email';
		$params['default_values'] = $default_values;

		$params['omit_fields']                            = array( 'wlcom_email_conf__enable_email', 'wlcom_email_conf__exclude_product_categories', 'wlcom_email_conf__exclude_product_skus' );
		$section_admin_fields_for_insert['welcome_email'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['welcome_email']        = 'begin_welcome_email';

		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Empty WishList Reminder';
		$params['option_suffix']  = 'empty_wishlist_reminder';
		$params['default_values'] = $default_values;

		$params['omit_fields']                                      = array( 'wlcom_email_conf__enable_email', 'wlcom_email_conf__exclude_product_categories', 'wlcom_email_conf__exclude_product_skus' );
		$section_admin_fields_for_insert['empty_wishlist_reminder'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['empty_wishlist_reminder']        = 'begin_empty_wishlist_reminder';

		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Reservation Confirmation';
		$params['option_suffix']  = 'reserved_conf';
		$params['default_values'] = $default_values;

		$params['omit_fields']                            = array( 'wlcom_email_conf__enable_email', 'wlcom_email_conf__exclude_product_categories', 'wlcom_email_conf__exclude_product_skus' );
		$section_admin_fields_for_insert['reserved_conf'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['reserved_conf']        = 'begin_reserved_conf';

		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Wish Reserved Notice';
		$params['option_suffix']  = 'reserved_notice';
		$params['default_values'] = $default_values;

		$params['omit_fields']                              = array( 'wlcom_email_conf__enable_email', 'wlcom_email_conf__exclude_product_categories', 'wlcom_email_conf__exclude_product_skus' );
		$section_admin_fields_for_insert['reserved_notice'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['reserved_notice']        = 'begin_reserved_notice';

		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Wish on Sale';
		$params['option_suffix']  = 'wish_on_sale';
		$params['default_values'] = $default_values;

		$section_admin_fields_for_insert['wish_on_sale'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['wish_on_sale']        = 'begin_wish_on_sale';

		$params                   = array();
		$params['options']        = $this->options_all;
		$params['section_title']  = 'Wish in Stock';
		$params['option_suffix']  = 'wish_in_stock';
		$params['default_values'] = $default_values;

		$section_admin_fields_for_insert['wish_in_stock'] = $this->wldcfwc_get_admin_fields__emails( $params );
		$insert_fields_after_name['wish_in_stock']        = 'begin_wish_in_stock';

		/**
		 * WishList Dashboard
		 */
		$WishList_dashboard_button = '
            <div class="wldcfwc-title-subsection wldcfwc-dashboard-prompt-div">
                <div class="wldcfwc-dashboard-plugin">
                    <button type="button" class="wldcfwc-button-margin-top wldcfwc-button-md wldcfwc-popwin-button-js" data-button-mode="dashboard">' . esc_html__( 'Open Dashboard', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                </div>
				<div id="wldcfwc-dashboard-plugin-error" class="wldcfwc-hide wldcfwc-connect-store-prompt notice notice-error">
					' . esc_html__( 'Error connecting to WishList.com. Please refresh this page.', 'wishlist-dot-com-for-woocommerce' ) . '
				</div>
            </div>
        ';
		$WishList_dashboard_button_script = '
            var wldcfwc_env=\'' . $store_id_a['env'] . '\';
            var wldcfwc_wlcom_plgn_plugin_display_status=\'' . $wlcom_plgn_plugin_display_status . '\';
            var wldcfwc_store_uuid=\'' . $store_id_a['store_uuid'] . '\';
            var wldcfwc_api_domain=\'' . $store_id_a['api_domain'] . '\';
            var wldcfwc_api_domain_mode=\'' . $store_id_a['api_domain_mode'] . '\';
            var wldcfwc_store_url=\'' . $store_id_a['store_url'] . '\';
            var wldcfwc_service_level=\'' . $service_level . '\';
            var wldcfwc_valid_api_key=\'' . $valid_api_key . '\';                
            var js_theme_colors_merged_saved=\'' . $this->options_all['js_theme_colors_merged_saved'] . '\';                
        ';
		$fields[] = array(
			'name'            => 'wldcfwc_dashboard',
			'title'           => esc_html__( 'Dashboard', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'WishList.com dashboard', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(

				array(
					'name'       => 'wishlist_dashboard_button',
					'type'       => 'html',
					'html'       => '<div class="wldcfwc-section-margin-top"> </div>',
					'wrap_class' => 'wldcfwc-no-border-top',
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => '<p class="wldcfwc-content-description">' . esc_html__( 'Your WishList data is provided by WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '</p><p class="wldcfwc-content-description">' . esc_html__( 'You can view & download your WishList data through your WishList.com dashboard.', 'wishlist-dot-com-for-woocommerce' ) . '</p>',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'name' => 'wishlist_dashboard_button',
					'type' => 'html',
					'html' => $WishList_dashboard_button,
				),

				array(
					'name'       => 'wishlist_dashboard_button_script',
					'type'       => 'script',
					'script'     => $WishList_dashboard_button_script,
					'wrap_class' => 'no-border-bottom wldcfwc-no-border-top',
				),

				array(
					'type'        => 'content',
					'content'     => '',
					'description' => '',
					'wrap_class'  => 'wldcfwc-margin-bottom no-border-bottom wldcfwc-no-border-top',
				),

			),
		);

		/**
		 * WishList Advanced
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
		$advanced_template_design_description = '
			' . esc_html__( 'You can also control the "Add to WishList" buttons and other elements using templates:', 'wishlist-dot-com-for-woocommerce' ) . '
			<p class="exopite-sof-description wldcfwc-content-description">
			' . esc_html__( '1) Copy the corresponding plugin template file from wp-content/plugins/', 'wishlist-dot-com-for-woocommerce' ) . WLDCFWC_SLUG . esc_html__( '/public/templates/', 'wishlist-dot-com-for-woocommerce' ) . '
			<br>
			' . esc_html__( '2) Paste the template file into your theme\'s directory, like wp-content/themes/storefront.', 'wishlist-dot-com-for-woocommerce' ) . '
			<br>
			' . esc_html__( '3) Edit that template to customize the button and other elements.', 'wishlist-dot-com-for-woocommerce' ) . '
			</p>
			';

		$WishListDotCom_button = '
            <div class="wldcfwc-title-subsection">
                <div class="wldcfwc-wldcom-plugin-settings">
                    <button type="button" class="wldcfwc-button-margin-top--off wldcfwc-button-md wldcfwc-popwin-button-js" data-button-mode="wldcom_plugin_settings">' . esc_html__( 'WishList.com CSS', 'wishlist-dot-com-for-woocommerce' ) . '</button>
                </div>
				<div id="wldcfwc-wldcom-plugin-settings-error" class="wldcfwc-hide wldcfwc-connect-store-prompt notice notice-error">
					' . esc_html__( 'Error connecting to WishList.com. Please refresh this page.', 'wishlist-dot-com-for-woocommerce' ) . '
				</div>
            </div>
        ';

		$fields[] = array(
			'name'            => 'wldcfwc_advanced_configuration',
			'title'           => esc_html__( 'Advanced', 'wishlist-dot-com-for-woocommerce' ),
			'section_title'   => esc_html__( 'Advanced', 'wishlist-dot-com-for-woocommerce' ),
			'section_tab_css' => $section_tab_css,
			'fields'          => array(

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Description', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'Below are ways to customize the design of your WishList.', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Your store\'s theme', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => esc_html__( 'You can use your theme\'s customize settings to design your WishList. There\'s typically an option like "Additional CSS".', 'wishlist-dot-com-for-woocommerce' ),
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),

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
				array(
					'type'              => 'content',
					'content'           => esc_html__( 'WishList.com CSS', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => '<p class="wldcfwc-content-description">' . esc_html__( 'This CSS controls the design of your WishList that\'s displayed on WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '</p>',
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),
				array(
					'name' => 'wishlistdotcom_button',
					'type' => 'html',
					'html' => $WishListDotCom_button,
				),

				array(
					'type'              => 'content',
					'content'           => esc_html__( 'Custom templates', 'wishlist-dot-com-for-woocommerce' ),
					'description'       => $advanced_template_design_description,
					'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
					'description_class' => 'wldcfwc-content-description',
				),
			),
		);

		// insert fields added to $section_admin_fields_for_insert.
		foreach ( $section_admin_fields_for_insert as $section => $admin_fields ) {
			foreach ( $fields as $field_index => $parent_fields ) {
				foreach ( $parent_fields['fields'] as $child_field_index => $field ) {
					// insert at the right spot
					if ( ( isset( $field['name'] ) && $field['name'] == $insert_fields_after_name[ $section ] )
						|| ( isset( $field['id'] ) && $field['id'] == $insert_fields_after_name[ $section ] )
					) {
						array_splice( $fields[ $field_index ]['fields'], $child_field_index + 1, 0, $admin_fields );
					}
				}
			}
		}

		/**
		 * Instantiate your admin page
		 */
		$options_panel = new wldcfwc_Exopite_Simple_Options_Framework( $config_submenu, $fields, $this->options_all );
	}
	/**
	 * Adjust options prior to saving
	 *
	 * @param  $options
	 * @return array
	 */
	public function wldcfwc_adjust_options_prior_to_saving( $options ) {

		// see filter exopite_sof_save_menu_options
		// $options is passed by calling hook

		// get existing options, which is a merge of all options saved and all default options
		$existing_options = $this->wldcfwc_get_options( 'all' );
		// merged existing options with the options just submitted ($options)
		$options__all = array_merge( $existing_options, $options );

		// copy button style from another button
		$copy_button_style_target_source = array(
			'wishlist_button_style__hp_findwishlist' => 'wishlist_button_style__hp_createwishlist',
			'wishlist_button_style__hp_mywishlist'   => 'wishlist_button_style__hp_createwishlist',
			'wishlist_button_style__product_loop'    => 'wishlist_button_style__product_page',
			'wishlist_button_style__cart'            => 'wishlist_button_style__product_page',
		);
		foreach ( $copy_button_style_target_source as $target => $source ) {
			$target_suffix = str_replace( 'wishlist_button_style__', '', $target );
			$source_suffix = str_replace( 'wishlist_button_style__', '', $source );

			// see if copy_style is yes
			if ( 'yes_str' == $options__all[ 'copy_style__' . $target_suffix ] ) {
				foreach ( $options__all as $key => $val ) {
					// skip button settings for copy_style__, wishlist_button_position__ and wishlist_button_text__
					if ( str_contains( $key, '__' . $target_suffix )
						&& ! str_contains( $key, 'copy_style__' ) && ! str_contains( $key, 'wishlist_button_position__' ) && ! str_contains( $key, 'wishlist_button_text__' ) && ! str_contains( $key, 'button_tooltip__' )
					) {
						$options__all[ $key ] = $options__all[ str_replace( '__' . $target_suffix, '__' . $source_suffix, $key ) ];
					}
				}
			}
		}

		// if featured product list has changed, then add featured products to product queue to be sent to wlcom
		$update_featured_queue            = false;
		$existing_featured_product_skus_a = $existing_options['existing_featured_product_skus'];

		// deal with empty select2
		$find_key_suffix = '__hidden_select2';
		foreach ( $options as $key => $val ) {
			if ( str_contains( $key, $find_key_suffix ) ) {
				$select2_var = str_replace( $find_key_suffix, '', $key );
				if ( ! isset( $options[ $select2_var ] ) ) {
					$options__all[ $select2_var ] = array();
				}
				unset( $options__all[ $key ] );
			}
		}
		$wlcom_featured_product_skus_a = $options__all['wlcom_featured_product_skus'];

		// limit to $this->featured_products_count
		if ( ! empty( $wlcom_featured_product_skus_a ) ) {
			$wlcom_featured_product_skus_a               = array_slice( $wlcom_featured_product_skus_a, 0, $this->featured_products_count );
			$options__all['wlcom_featured_product_skus'] = $wlcom_featured_product_skus_a;
		}

		// if featured product list has changed, then add featured products to product queue to be sent to wlcom
		foreach ( $wlcom_featured_product_skus_a as $featured_sku ) {
			if ( ! in_array( $featured_sku, $existing_featured_product_skus_a ) ) {
				$update_featured_queue = true;
			}
		}

		if ( $update_featured_queue ) {
			// get product ids from skus
			foreach ( $wlcom_featured_product_skus_a as $item ) {
				$item_a      = explode( '^', $item );
				$product_sku = $item_a[0];
				$product_id  = wc_get_product_id_by_sku( $product_sku );

				if ( ! empty( $product_id ) ) {
					$this->wldcfwc_add_productid_to_wlcom_queue( $product_id );
				}
			}
		}
		// save current wlcom_featured_product_skus for comparison next time
		$options__all['existing_featured_product_skus'] = $options__all['wlcom_featured_product_skus'];

		// process header template menu items
		$header_menu_items = array();
		// if top menu items passed, save to wlcom_store_top_menu_links_json.
		if ( isset( $options__all['wlcom_wishlist_menu_item_title'] ) && isset( $options__all['wlcom_wishlist_menu_item_url'] ) ) {
			$menu_titles = $options__all['wlcom_wishlist_menu_item_title'];
			$menu_urls   = $options__all['wlcom_wishlist_menu_item_url'];
			// $wlcom_wishlist_menu_item_title is an array of values from the input fields
			foreach ( $menu_titles as $key => $menu_title ) {
				// title and url are in pairs in the html form and have the same key
				if ( ! empty( $menu_title ) && ! empty( $menu_urls[ $key ] ) ) {
					$header_menu_items[ $menu_title ] = $menu_urls[ $key ];
				}
			}

			$options__all['wlcom_store_top_menu_links_json'] = wp_json_encode( $header_menu_items );
			unset( $options__all['wlcom_wishlist_menu_item_title'] );
			unset( $options__all['wlcom_wishlist_menu_item_url'] );
		}

		$wl_hp_page_id = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		if ( empty( $wl_hp_page_id ) ) {
			// Just in case. This is typically done as part of the wldcfwc_Activator class.
			wc_create_page(
				sanitize_title_with_dashes( WLDCFWC_WISHLIST_HOMEPAGE_TITLE ),
				WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION,
				WLDCFWC_WISHLIST_TITLE,
				WLDCFWC_WISHLIST_HOMEPAGE_CONTENT
			);

			// now add wl hp page to top menu json. note that this is after wlcom_store_top_menu_links_json above.
			$wl_hp_page_id    = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
			$wl_hp_page_title = WLDCFWC_WISHLIST_TITLE;
			$wl_hp_url        = get_permalink( $wl_hp_page_id );
			$menu_items       = json_decode( $this->options_all['wlcom_store_top_menu_links_json'], true );
			if ( ! array_key_exists( $wl_hp_page_title, $menu_items ) ) {
				$menu_items[ $wl_hp_page_title ] = $wl_hp_url;
				$options__all['wlcom_store_top_menu_links_json'] = wp_json_encode( $menu_items );
			}
		}

		// save wishlist homepage url
		$wl_hp_page_id                         = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		$wl_hp_url                             = get_permalink( $wl_hp_page_id );
		$options__all['wlcom_wl_homepage_url'] = $wl_hp_url;

		// trim options to avoid expansion due to merge of existing with new
		$final_options   = array();
		$skipped_options = array();
		$default_values  = $this->wldcfwc_get_field_defaults__options( 'all' );
		foreach ( $options__all as $key => $val ) {
			//For security reason, options saved are limited to those with default values.
			//This means all options, including "special keys", must have a default.
			if ( isset( $default_values[ $key ] ) ) {
				$final_options[ $key ] = $val;
			} else {
				$skipped_options[ $key ] = $val;
			}
		}

		// we do this here since there's no hook that passes the filtered options adjusted by this function
		$this->wldcfwc_save_special_options( $final_options );

		return $final_options;
	}

	/**
	 * Save special options to their own field in the db
	 *
	 * @param  $options
	 * @return void
	 */
	public function wldcfwc_save_special_options( $options ) {

		// see filter exopite_sof_save_menu_options
		// $options is passed by calling hook

		if ( ! empty( $options['delete_store_uuid'] ) ) {
			$store_uuid = get_option( WLDCFWC_SLUG . '_store_uuid' );
			if ( $store_uuid == $options['delete_store_uuid'] ) {
				// connected store_uuid was deleted on WishList.com. set option vals to empty string to be saved below
				$options['valid_api_key']       = 'no_str';
			}
		}

		// special keys that are saved in their own fields, and never updated
		// api_key is create by a message from wlcom popwindow for getting license key
		// see Wldcfwc_Trait. 'api_key','store_uuid'
		$special_keys = $this->special_option_keys;
		foreach ( $special_keys as $special_key ) {
			// save keys, like api_key, in special field outside of exopite options field
			// api_key is created via wldcfwc-admin.js from window message from wishlist.com
			$special_key_name = WLDCFWC_SLUG . '_' . $special_key;
			if ( isset( $options[ $special_key ] ) ) {
				$val = esc_html( $options[ $special_key ] );
				if ( 'store_api_subdomain' == $special_key ) {
					$val = wldcfwc_clean_subdomain_string( $val );
				}
				update_option( $special_key_name, $val );
			}
		}

		$wl_hp_page_id    = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		$wl_hp_page_title = get_the_title( $wl_hp_page_id );

		// wishlist_hp_title
		if ( isset( $options['wishlist_hp_title'] ) && $options['wishlist_hp_title'] != $wl_hp_page_title ) {
			$new_title = $options['wishlist_hp_title']; // Replace with your desired title
			$post_data = array(
				'ID'         => $wl_hp_page_id,
				'post_title' => $new_title,
			);
			wp_update_post( $post_data );
		}

		// separate groups of fields to limit number of options necessary for the store's ui
		$find_email_conf = 'wlcom_email_conf__';
		$find_wlcom_plgn = 'wlcom_plgn_';
		$find_wlcom      = 'wlcom_';
		$email_conf_a    = array();
		$options_a       = array();
		foreach ( $options as $key => $val ) {
			if ( substr( $key, 0, strlen( $find_email_conf ) ) == $find_email_conf ) {
				// email configs
				$email_conf_a[ $key ] = $val;
			} elseif ( substr( $key, 0, strlen( $find_wlcom_plgn ) ) == $find_wlcom_plgn ) {
				// fields transmitted to wishlist.com that are also used in store ui
				$options_a[ $key ] = $val;
			} else {
				$options_a[ $key ] = $val;
			}
		}
		$special_key_name = WLDCFWC_SLUG . '_email_conf';
		update_option( $special_key_name, $email_conf_a );

		// save remaining options for store ui.
		$special_key_name = WLDCFWC_SLUG . '_options';
		update_option( $special_key_name, $options_a );

		// generate buttons_css and add it to options
		$buttons_css     = $this->wldcfwc_get_button_style( $options );
		$buttons_css_key = WLDCFWC_SLUG . '_global_buttons_css';
		update_option( $buttons_css_key, $buttons_css );

		// store_uuid, api_key, valid_api_key
		// use params that are about to be saved to call wldcfwc_store_id(). Do this instead of trying to update the
		// db and then calling wldcfwc_store_id()
		$params = array();
		if ( isset( $options['store_uuid'] ) ) {
			$params['store_uuid'] = $options['store_uuid'];
		}
		if ( isset( $options['api_key'] ) ) {
			$params['api_key'] = $options['api_key'];
		}
		if ( isset( $options['valid_api_key'] ) ) {
			$params['valid_api_key'] = $options['valid_api_key'];
		}
		if ( isset( $options['store_api_subdomain'] ) ) {
			$params['store_api_subdomain'] = wldcfwc_clean_subdomain_string( $options['store_api_subdomain'] );
		}
		$store_id_a = wldcfwc_store_id( $params );

		// Generat js and save for next time. This saves about .5ms
		// get url for wishlist homepage
		$wl_hp_page_id  = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		$wl_hp_url      = get_permalink( $wl_hp_page_id );
		$wl_hp_url_path = WLDCFWC_WISHLIST_HOMEPAGE_TITLE;

		$wlcom_plgn_plugin_display_status = esc_html( $options['wlcom_plgn_plugin_display_status'] );

		$icon_url                         = WLDCFWC_URL . 'public/images/WishListIcon-trans-48.png';
		$add2wishlist_window              = esc_html( $options['add2wishlist_window'] );
		$show_add2wishlist_button_spinner = esc_html( $options['show_add2wishlist_button_spinner'] );

		$global_js_val = '
            var wldcfwc_env=\'' . esc_html( $store_id_a['env'] ) . '\';
            var wldcfwc_plugin_display_status=\'' . esc_html( $wlcom_plgn_plugin_display_status ) . '\';
            var wldcfwc_store_uuid=\'' . esc_html( $store_id_a['store_uuid'] ) . '\';
            var wldcfwc_store_api_subdomain=\'' . esc_html( $store_id_a['store_api_subdomain'] ) . '\';
            var wldcfwc_api_domain=\'' . esc_html( $store_id_a['api_domain'] ) . '\';
            var wldcfwc_api_domain_mode=\'' . esc_html( $store_id_a['api_domain_mode'] ) . '\';
            var wldcfwc_store_url=\'' . esc_html( $store_id_a['store_url'] ) . '\';
            var wldcfwc_wl_hp_url=\'' . esc_html( $wl_hp_url ) . '\';
            var wldcfwc_wl_hp_url_path=\'' . esc_html( $wl_hp_url_path ) . '\';
            var wldcfwc_wl_hp_page_id=\'' . esc_html( $wl_hp_page_id ) . '\';
            var wldcfwc_add2wishlist_window=\'' . esc_html( $add2wishlist_window ) . '\';
            var wldcfwc_wl_icon_url=\'' . esc_html( $icon_url ) . '\';
            var wldcfwc_show_add2wishlist_button_spinner=\'' . esc_html( $show_add2wishlist_button_spinner ) . '\';
            ';

		// all user inputed data is escaped by this function
		$global_js_key = WLDCFWC_SLUG . '_global_js';
		update_option( $global_js_key, $global_js_val );
	}

	/**
	 * Post necessary options to WishList.com's api. Options are used to configure WishList.com's API for this store
	 *
	 * @return void
	 */
	public function wldcfwc_post_options_to_wlcom( $transmit_all=true ) {

		// transmit options to WishList.com's API to control the display of the store's WishList on WishList.com
		$options = $this->options_all;

		// vars to transmit
		$options_wlcom = array();
		foreach ( $options as $var => $val ) {
			if ( strpos( $var, 'wlcom_' ) === 0 || $transmit_all ) {
				$options_wlcom[ $var ] = $options[ $var ];
			}
		}

		wldcfwc_save_api_options_to_wlcom( $options_wlcom );

		// transmit updated products to sync price and inventory status
		$this->wldcfwc_transmit_queued_products_wlcom();
	}

	/**
	 * Post options called by ajax to transmit to WishList.com API asynchronously
	 *
	 * @return void
	 */
	public function wldcfwc_post_options_to_wlcom_rec_ajax() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wldcfwc_post_options_to_wlcom_rec_ajax' ) ) {

			$this->wldcfwc_post_options_to_wlcom();

			$result = array( 'message' => 'Saved' );
			wp_send_json( $result );

		} else {
			die( 'Security check failed!' );
		}

		// Always exit to prevent extra output
		wp_die();
	}

	/**
	 * Connect to WishList.com called by ajax to get API key
	 *
	 * @return void
	 */
	public function wldcfwc_get_wlcom_api_key_rec_ajax() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wldcfwc_get_wlcom_api_key_rec_ajax' ) ) {

			$get_api_key_response_text = wldcfwc_get_wlcom_api_key();

			$result = array( 'message' => $get_api_key_response_text );
			wp_send_json( $result );

		} else {
			die( 'Security check failed!' );
		}

		// Always exit to prevent extra output
		wp_die();
	}

	/**
	 * Delete connection to WishList.com called by ajax to get API key
	 *
	 * @return void
	 */
	public function wldcfwc_delete_wlcom_api_key_rec_ajax() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wldcfwc_delete_wlcom_api_key_rec_ajax' ) ) {

			$delete_api_key_response_text = wldcfwc_delete_wlcom_api_key();

			$result = array( 'message' => $delete_api_key_response_text );
			wp_send_json( $result );

		} else {
			die( 'Security check failed!' );
		}

		// Always exit to prevent extra output
		wp_die();
	}

	/**
	 * Get a one-time auth token connection to WishList.com called by ajax to get token
	 *
	 * @return void
	 */
	public function wldcfwc_get_once_auth_token_rec_ajax() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wldcfwc_get_once_auth_token_rec_ajax' ) ) {

			$wldcfwc_get_once_auth_token_text = wldcfwc_get_once_auth_token();

			$result = array( 'message' => $wldcfwc_get_once_auth_token_text );
			wp_send_json( $result );

		} else {
			die( 'Security check failed!' );
		}

		// Always exit to prevent extra output
		wp_die();
	}

	/**
	 * Gets the previously saved theme_primary_colors via ajax
	 *
	 * @return void
	 */
	public function wldcfwc_get_theme_primary_colors_rec_ajax() {

		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wldcfwc_get_theme_primary_colors_rec_ajax' ) ) {

			$theme_primary_colors_json = get_option( WLDCFWC_SLUG . '_theme_primary_colors' );
			if ( ! empty( $theme_primary_colors_json ) ) {
				$theme_primary_colors = json_decode($theme_primary_colors_json, true);
				$result = array( 'status' => 'success', 'message' => [ 'theme_primary_colors' => $theme_primary_colors ] );
			} else {
				$result = array( 'status' => 'success', 'message' => [ 'theme_primary_colors' => [] ] );
			}
			wp_send_json( $result );

		} else {
			die( 'Security check failed!' );
		}

		// Always exit to prevent extra output
		wp_die();
	}

	/**
	 * A product has been updated. Add it's ID to a queue that will then be used to send updated product data to
	 * WishList.com's API for things like the store's configurued promotional emails and product pricing updates
	 *
	 * @param  $product_id
	 * @return int|void
	 */
	public function wldcfwc_add_productid_to_wlcom_queue( $product_id ) {

		// called via hooks, like woocommerce_new_product
		// we run this logic with product changes to speed up wlcom's product sync.
		// products are sync with wlcom with each wish added. but with low product wish frequency, it could take a while to sync a product

		// save product_queue that's transmitted to wishlist.com when products go on sale or are newly in stock
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return 1;
		}

		$product_a  = array();
		$is_on_sale = $product->is_on_sale();
		if ( $is_on_sale ) {
			$is_on_sale = 1;
		} elseif ( false === $is_on_sale ) {
			$is_on_sale = 0;
		} else {
			$is_on_sale = 3;
		}
		$is_in_stock = $product->is_in_stock();
		if ( $is_in_stock ) {
			$is_in_stock = 1;
		} elseif ( false === $is_in_stock ) {
			$is_in_stock = 0;
		} else {
			$is_in_stock = 3;
		}
		$product_a['onsale']  = $is_on_sale;
		$product_a['instock'] = $is_in_stock;

		// get current queue
		$wldcfwc_wlcom_product_queue_a = $this->wldcfwc_get_productid_queue( array( 'key' => 'product_queue' ) );

		// add product to queue
		$wldcfwc_wlcom_product_queue_a[ $product_id ]['onsale']  = $product_a['onsale'];
		$wldcfwc_wlcom_product_queue_a[ $product_id ]['instock'] = $product_a['instock'];

		// save updated queue
		$this->wldcfwc_save_productids_to_queue(
			array(
				'key'                 => 'product_queue',
				'wlcom_product_ids_a' => $wldcfwc_wlcom_product_queue_a,
			)
		);
	}

	/**
	 * Get product info for dropdown via ajax
	 *
	 * @return json
	 */
	public function wldcfwc_get_product_droppown_rec_ajax() {

		if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wldcfwc_get_product_droppown_rec_ajax' ) ) {

			$return_results = array();
			if ( isset($_GET['term']) ) {
				$term = sanitize_text_field( $_GET['term'] );
			} else {
				$term = '';
			}
			if ( isset($_GET['mode']) ) {
				$mode = sanitize_text_field( $_GET['mode'] );
			} else {
				$mode = '';
			}

			if ( 'category' == $mode ) {
				$results = $this->wldcfwc_get_matching_category_names( $term );
			} elseif ( 'sku' == $mode ) {
				$results = $this->wldcfwc_get_matching_woocommerce_skus( $term );
			}
			if ( ! empty( $results ) ) {
				foreach ( $results as $key => $text ) {
					if ( 'sku' == $mode ) {
						$text_tmp = $text . ' (' . $key . ')';
						// concactenate sku^text so it's stored together so we don't have to do a query to display later
						$return_results[] = array(
							'id'   => $key . '^' . $text_tmp,
							'text' => $text_tmp,
						);
					} elseif ( 'category' == $mode ) {
						$return_results[] = array(
							'id'   => $text,
							'text' => $text,
						);
					}
				}
			}

			// Return results as JSON
			wp_send_json( $return_results );

		} else {
			die( 'Security check failed!' );
		}

		// Always exit to prevent extra output
		wp_die();
	}

	public function wldcfwc_get_matching_category_names( $search_term ) {
		$args = array(
			'taxonomy'     => 'product_cat',
			'name__like'   => $search_term, // Search term
			'orderby'      => 'name',
			'show_count'   => 0,
			'pad_counts'   => 0,
			'hierarchical' => 0,
			'title_li'     => '',
			'hide_empty'   => 0,
		);

		$categories = get_terms( $args );

		// Check for errors
		if ( is_wp_error( $categories ) ) {
			return array(); // Return empty array on error
		}

		$return_categories = array();
		if ( is_array( $categories ) ) {
			foreach ( $categories as $cat ) {
				$return_categories[ $cat->name ] = $cat->name;
			}
		}

		return $return_categories; // Array of matching category names
	}

	public function wldcfwc_get_matching_woocommerce_skus( $search_term ) {
		$matching_skus = array();

		// Arguments for WP_Query
		$args = array(
			'posts_per_page' => -1, // Retrieve all products that match
			'post_type'      => 'product', // Query only WooCommerce products
			'post_status'    => 'publish', // Only retrieve published products
			's'              => $search_term, // Search term
			'fields'         => 'ids', // Retrieve only IDs for performance
		);

		// Create the WP_Query instance
		$query = new WP_Query( $args );

		// Loop through posts and get SKUs
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$product_id = get_the_ID();
				$product    = wc_get_product( $product_id );

				if ( $product ) {
					$name       = $product->get_name();
					$name_short = substr( $name, 0, 20 );
					$sku        = $product->get_sku();
					if ( ! empty( $sku ) ) {
						$matching_skus[ $sku ] = $name_short;
					}
				}
			}
		}

		// Reset post data
		wp_reset_postdata();

		return $matching_skus;
	}

	/**
	 * Get product data for a list of ids
	 *
	 * @return array
	 */
	public function wldcfwc_get_product_data_from_queued_product_ids( $product_ids_a = array() ) {
		$product_data_trans = array();
		if ( ! empty( $product_ids_a ) ) {
			$products = wc_get_products(
				array(
					'include' => $product_ids_a,
					'limit'   => -1,
				)
			);
			// add to relational array
			$product_data_trans = array();
			foreach ( $products as $key => $product ) {
				$product_data         = $this->wldcfwc_get_product_data_wish( $product );
				$product_data_trans[] = $product_data;
			}
		}
		return $product_data_trans;
	}

	/**
	 * Post to queued products to WishList.com's API
	 *
	 * @return void
	 */
	public function wldcfwc_transmit_queued_products_wlcom() {
		// get queued product_ids
		$wldcfwc_wlcom_product_queue_a = $this->wldcfwc_get_productid_queue( array( 'key' => 'product_queue' ) );

		$product_ids_a = array();
		foreach ( $wldcfwc_wlcom_product_queue_a as $product_id => $info_a ) {
			$product_ids_a[] = $product_id;
		}

		$product_data_trans = array();
		if ( ! empty( $product_ids_a ) ) {
			$product_data_trans = $this->wldcfwc_get_product_data_from_queued_product_ids( $product_ids_a );
		}

		if ( ! empty( $product_data_trans ) ) {
			$params['data']       = $product_data_trans;
			$params['api_action'] = 'sync_woo_products';
			$response             = wldcfwc_api_call( $params );

			// clear the queue
			$wlcom_products_key = WLDCFWC_SLUG . '_product_queue';
			update_option( $wlcom_products_key, '' );
		}
	}

	/**
	 * Reduce the size of html
	 *
	 * @param  $str
	 * @param  $rem_nl
	 * @return array|string|string[]|null
	 */
	private function wldcfwc_minify_html( $str, $rem_nl = false ) {
		$search  = array(
			'/\>[^\S ]+/s',
			'/[^\S ]+\</s',
			'/(\s)+/s',
			'/\> \</',
		);
		$replace = array(
			'>',
			'<',
			'\\1',
			'><',
		);
		$str     = preg_replace( $search, $replace, $str );
		if ( $rem_nl ) {
			$str = str_replace( "\n", ' ', $str );
		}
		return $str;
	}
}
