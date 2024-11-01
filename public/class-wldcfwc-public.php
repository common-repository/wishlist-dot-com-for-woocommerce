<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/public
 */

class WLDCFWC_Public {


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
	 * KK The plugin options.
	 *
	 * @since  1.0.0
	 * @var    string    $options
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		if ( ! isset( $this->options ) ) {
			$this->options = $this->wldcfwc_get_options();
		}

		//create the user's wldcom session token. this is for non-secure data, like the wish id just viewed on wldcom
		if ( isset( $_GET[ 'wldcom_user_session_id' ] ) ) {
			//from wldcom
			$wldcom_user_session_id = sanitize_text_field( $_GET[ 'wldcom_user_session_id' ] );

			// Do api call to WishList.com and receive data. wldcom_user_session_id is valid for a temporary period and is WishList.com user specific
			$params['data']       = array( 'wldcom_user_session_id' => $wldcom_user_session_id );
			$params['api_action'] = 'get_wldcom_user_session';
			// response is sanitized
			$response = wldcfwc_api_call( $params );

			if ( isset( $response['error'] ) ) {
				$wldcom_api_result = array( 'error' => $response['error'] );
			} else {
				$wldcom_api_result = $response;
			}
			if ('success' === $wldcom_api_result['status'] && isset($wldcom_api_result['data']['wldcom_user_session']['res_prods']) && !empty( $wldcom_api_result['data']['wldcom_user_session']['res_prods'] )) {
				// Access the 'res_prods' key
				$res_prods = $wldcom_api_result['data']['wldcom_user_session']['res_prods'];
				$name = 'wldcfwc_res_prods';
				$expiration = time() + ( 3600 * 24 * 1 ); // days
				$secure = is_ssl(); // Only set to true if the site uses HTTPS
				$httponly = true; // Prevent JavaScript from accessing the cookie
				wldcfwc_setcookie( $name, $res_prods, $expiration, $secure, $httponly );
			}
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_enqueue_styles() {

		$is_wc_page = $this->wldcfwc_is_woocommerce_page();
		if ( $is_wc_page ) {
			$handle = WLDCFWC_SLUG . '-public';

			// local file
			if ( WLDCFWC_USE_MIN_CSS ) {
				$ext = '.min.css';
			} else {
				$ext = '.css';
			}
			$css_path = WLDCFWC_URL . 'public/css/wldcfwc-public' . $ext;

			wp_enqueue_style( $handle, $css_path, array(), $this->version, 'all' );

			$button_css = $this->wldcfwc_get_button_css( $this->options );
			$css        = $button_css;
			wp_add_inline_style( $handle, $css );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_enqueue_scripts() {

		$is_wc_page = $this->wldcfwc_is_woocommerce_page();
		if ( $is_wc_page ) {

			// local file
			if ( WLDCFWC_USE_MIN_JS ) {
				$ext = '.min.js';
			} else {
				$ext = '.js';
			}

			// public, primary javascript
			$js_path       = WLDCFWC_URL . 'public/js/wldcfwc-add2wishlist' . $ext;
			$script_handle = WLDCFWC_SLUG . '_public';
			wp_enqueue_script( $script_handle, $js_path, array( 'jquery' ), $this->version, true );
			wp_localize_script(
				$script_handle,
				'wldcfwc_api_settings',
				array(
					'root'  => esc_url_raw( rest_url() ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				)
			);

			//load javascript to get theme styles if $theme_primary_colors is empty. runs once
			$theme_primary_colors = get_option( WLDCFWC_SLUG . '_theme_primary_colors' );
			if ( empty( $theme_primary_colors ) ) {
				$store_uuid = get_option( WLDCFWC_SLUG . '_store_uuid' );
			}
			if ( ! empty( $store_uuid ) && empty( $theme_primary_colors ) ) {
				//javascript used to get theme styles for WishList.com API
				$js_path = WLDCFWC_URL . 'public/js/wldcfwc-get-theme-styles' . $ext;
				$script_handle = WLDCFWC_SLUG . '_get_theme_style_public';
				wp_enqueue_script( $script_handle, $js_path, array( 'jquery' ), $this->version, true );

				// Prepare the inline JavaScript with page_title and store_name
				$store_name = get_option( 'woocommerce_store_name' ) ? get_option( 'woocommerce_store_name' ) : get_bloginfo( 'name' );
				$store_url = home_url();

				$shop_page_title = get_the_title( wc_get_page_id( 'shop' ) );
				$shop_url         = wc_get_page_permalink( 'shop' );

				$cart_page_id = wc_get_page_id( 'cart' );
				$cart_page_title = get_the_title( $cart_page_id );
				$cart_url = wc_get_cart_url();

				$wl_hp_page_id = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
				$wl_hp_page_title = WLDCFWC_WISHLIST_TITLE;
				$wl_hp_url     = get_permalink( $wl_hp_page_id );

				// Add inline script to define `page_title` and `store_name`
				$inline_script = "
					var wldcfwc_store_name = '" . esc_js($store_name) . "';
					var wldcfwc_store_url = '" . esc_js($store_url) . "';

					var wldcfwc_shop_page_title = '" . esc_js($shop_page_title) . "';
					var wldcfwc_shop_url = '" . esc_js($shop_url) . "';

					var wldcfwc_cart_page_title = '" . esc_js($cart_page_title) . "';
					var wldcfwc_cart_url = '" . esc_js($cart_url) . "';

					var wldcfwc_wl_hp_page_title = '" . esc_js($wl_hp_page_title) . "';
					var wldcfwc_wl_hp_url = '" . esc_js($wl_hp_url) . "';
				";

				// Add the inline script after the enqueued script
				wp_add_inline_script( $script_handle, $inline_script );
			}
		}
	}

	/**
	 * Identify pages our plugin needs to interact with
	 *
	 * @return bool
	 */
	public function wldcfwc_is_woocommerce_page() {
		$is_wc_page = false;
		if ( ( is_woocommerce()
			|| is_cart()
			|| is_checkout()

			|| ( (int) get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION ) === get_the_ID() ) )
		) {
			$is_wc_page = true;
		}
		return $is_wc_page;
	}

	/**
	 * Define JS vars to be include in public facing pages so our JS can get option values and similar
	 * JS vars is inserted using wp_head hook
	 *
	 * @return void
	 */
	public function wldcfwc_global_js_var() {

		$is_wc_page = $this->wldcfwc_is_woocommerce_page( $this );

		if ( $is_wc_page ) {

			$global_js_key = WLDCFWC_SLUG . '_global_js';
			$global_js_val = get_option( $global_js_key );

			//if there's store_wpadmin_token, add it. It's used to confirm store ownership and active admin login
			$store_wpadmin_token_key = WLDCFWC_SLUG . '_store_wpadmin_token';
			$store_wpadmin_token = get_option($store_wpadmin_token_key);
			if ( ! empty( $store_wpadmin_token ) ) {
				$global_js_val .= " var store_wpadmin_token = '" . $store_wpadmin_token . "';";
			}

			// all user inputted js vars are text strings and are escaped prior to saving to the database
			echo "<script type='text/javascript'>" . wp_kses_post( $global_js_val ) . '</script>';
		}
	}
	/**
	 * Register the Add to WishList.com Buttons for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function wldcfwc_add2WishList_wish_data( $product_id ) {

		// get the current post/product ID
		$current_product_id = $product_id;

		// get the product based on the ID
		$product = wc_get_product( $current_product_id );

		if ( $product ) {

			$product_data = $this->wldcfwc_get_product_data_wish( $product );

			$span_id       = 'wldcfwc_data_span_' . $current_product_id;
			$wish_data_esc = wc_esc_json( wp_json_encode( $product_data ) );

			$html = "<span id=\"$span_id\" style=\"display:none;\" class=\"wldcfwc-data-span\" data-wldcfwc-selected-prod-id=\"$current_product_id\" data-wldcfwc-wish=\"$wish_data_esc\"></span>";

			return $html;
		}
	}
	/**
	 * Get product data used on the shopping cart page for the Add to WishList button
	 *
	 * @param  $item
	 * @return void
	 */
	public function wldcfwc_add2WishList_wish_data_cart( $item ) {

		if ( ! empty( $item['variation_id'] ) ) {
			$current_product_id = $item['variation_id'];
		} else {
			$current_product_id = $item['product_id'];
		}

		// get the product based on the ID
		$product = wc_get_product( $current_product_id );

		if ( $product ) {
			$product_data = $this->wldcfwc_get_product_data_wish( $product );

			$wish_data_esc = wc_esc_json( wp_json_encode( $product_data ) );

			$span_id = 'wldcfwc_data_span_' . $current_product_id;
			echo '
                <span id="' . esc_attr( $span_id ) . '" style="display:none;" class="wldcfwc-data-span" data-wldcfwc-selected-prod-id="' . esc_attr( $current_product_id ) . '" data-wldcfwc-wish="' . esc_attr( $wish_data_esc ) . '"></span>
                ';
		}
	}
	/**
	 * Get product data used on the checkout page for the Add to WishList button
	 *
	 * @return void
	 */
	public function wldcfwc_add2WishList_wish_data_checkout() {

		// Check if the cart is not empty
		if ( WC()->cart->is_empty() ) {
			return;
		}

		// Start with an empty array to hold our product IDs
		$product_ids = array();

		// Loop through all items in the cart
		$hidden_data_span = '';
		foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
			// Get the product object
			$product = $item['data'];

			if ( ! empty( $item['variation_id'] ) ) {
				$current_product_id = $item['variation_id'];
			} else {
				$current_product_id = $item['product_id'];
			}

			// get the product based on the ID
			$product = wc_get_product( $current_product_id );

			if ( $product ) {
				$product_data = $this->wldcfwc_get_product_data_wish( $product );

				$wish_data_esc = wc_esc_json( wp_json_encode( $product_data ) );

				$span_id           = 'wldcfwc_data_span_' . $current_product_id;
				$hidden_data_span .= '
                    <span id="' . $span_id . '" style="display:none;" class="wldcfwc-data-span" data-wldcfwc-selected-prod-id="' . $current_product_id . '" data-wldcfwc-wish="' . $wish_data_esc . '"></span>
                    ';
			}
		}
		if ( ! empty( $hidden_data_span ) ) {
			$hidden_data_span = '<div style="display:hidden;" class="wldcfwc-data-span-parent">' . $hidden_data_span . '</div>';
			// no user inputed data to escape
			echo wp_kses_post( $hidden_data_span );
		}
	}
	/**
	 * Add a message above the checkout's shipping address
	 *
	 * @return void
	 */
	public function wldcfwc_checkout_shipping_address_message_html() {
		$notice_html = "
            <ul id=\"wldcfwc_checkout_shipping_addresses_id\" class=\"woocommerce-info wldcfwc-hide\" role=\"alert\"></ul>
            <ul class=\"woocommerce-info wldcfwc-hide\" role=\"alert\"><li id='wldcfwc_different_shipping_addresses_warning_id'>Your cart might include gifts with different delivery addresses.</li></ul>
            ";

		echo wp_kses_post( $notice_html );
	}
	/**
	 * Add a message above the cart's  list of items
	 *
	 * @return void
	 */
	public function wldcfwc_cart_shipping_address_message_html() {
		$notice_html = "
            <ul id=\"wldcfwc_cart_shipping_addresses_id\" class=\"woocommerce-info wldcfwc-hide\" role=\"alert\"></ul>
            <ul class=\"woocommerce-info wldcfwc-hide\" role=\"alert\"><li id='wldcfwc_different_shipping_addresses_warning_id'>Your cart might include gifts with different delivery addresses.</li></ul>
            ";

		echo wp_kses_post( $notice_html );
	}
	/**
	 * Check the user's wishlist.com login and redirect to their wishlist
	 *
	 * @return array
	 */
	public function wldcfwc_redirect_wishlist_hp_to_mywishlist() {
		global $wp;
		$wl_hp_url_path = strtolower(WLDCFWC_WISHLIST_HOMEPAGE_TITLE);
		$current_path = home_url( add_query_arg( [], $wp->request ) );

		// Get the current query string
		$query_string = isset($_SERVER['QUERY_STRING']) ? sanitize_text_field($_SERVER['QUERY_STRING']) : '';
		if (str_contains( $current_path, $wl_hp_url_path ) && str_contains( $query_string, 'mywishlist=1' )) {
			$wl_hp_url_path = WLDCFWC_WISHLIST_HOMEPAGE_TITLE;
			$store_id_a = wldcfwc_store_id();
			$api_domain = $store_id_a[ 'api_domain' ];
			$protocal = is_ssl() ? 'https' : 'http';

			//change to wldcom public api call, with return url
			$wl_hp_page_id = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
			$wl_hp_page_title = WLDCFWC_WISHLIST_TITLE;
			$wl_hp_url = get_permalink( $wl_hp_page_id );

			//add wl_hp_url as the return url when they're not logged in
			$wl_hp_url_param = wldcfwc_create_url_qsparam('wl_hp_url', $wl_hp_url);
			$my_wishlists_url = $protocal . '://' . $api_domain . '/wooCommApi?api_action=mywishlistiflogin&wl_hp_url=' . $wl_hp_url_param;

			//go to their wishlist instead of the store's wishlist homepage
			//they return if not logged in
			wp_redirect( $my_wishlists_url );
			exit; // Ensure no further code runs after the redirection
		}
	}

	/**
	 * Get html for the Add to WishList button on the product page
	 *
	 * @return void
	 */
	public function wldcfwc_add2wishlist_button__product_page() {
		$mode           = 'product_page';
		$params['mode'] = $mode;

		global $product;
		if ( isset( $product ) ) {
			// if a shortcode is added to the wrong screen, the screen will no longer be editable if $product is not present for that screen
			$product_id                  = $product->get_id();
			$params['button_parameters'] = 'product_id=' . $product_id;
			$return_html                 = $this->wldcfwc_get_wishlist_button( $this->options, $params );

			// get hidden wish data
			$wish_data = $this->wldcfwc_add2WishList_wish_data( $product_id );
			// append hidden data to button.
			$return_html .= $wish_data;

		} else {
			// just in case
			$return_html = '';
		}

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		echo wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Get html for the Add to WishList button on the product page,
	 * when that button is positioned using JS
	 *
	 * @return void
	 */
	public function wldcfwc_add2wishlist_button__product_page_js_pos() {
		$mode                              = 'product_page';
		$params['mode']                    = $mode;
		$params['add_to_button_div_class'] = 'wldcfwc-hide';

		global $product;
		if ( isset( $product ) ) {
			// if a shortcode is added to the wrong screen, the screen will no longer be editable if $product is not present for that screen
			$product_id                  = $product->get_id();
			$params['button_parameters'] = 'product_id=' . $product_id;
			$return_html                 = $this->wldcfwc_get_wishlist_button( $this->options, $params );
		} else {
			// just in case
			$return_html = '';
		}

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		echo wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Get html for the Add to WishList button on the product loop
	 *
	 * @return void
	 */
	public function wldcfwc_add2wishlist_button__product_loop() {
		$mode           = 'product_loop';
		$params['mode'] = $mode;

		global $product;
		if ( isset( $product ) ) {
			// if a shortcode is added to the wrong screen, the screen will no longer be editable if $product is not present for that screen
			$product_id                  = $product->get_id();
			$params['button_parameters'] = 'product_id=' . $product_id;
			$return_html                 = $this->wldcfwc_get_wishlist_button( $this->options, $params );

			// get hidden wish data
			$wish_data = $this->wldcfwc_add2WishList_wish_data( $product_id );
			// append hidden data to button.
			$return_html .= $wish_data;

		} else {
			// just in case
			$return_html = '';
		}

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		echo wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Get html for the Add to WishList button on the product loop,
	 * when that button is positioned using JS
	 *
	 * @return void
	 */
	public function wldcfwc_add2wishlist_button__product_loop_js_pos() {
		$mode                              = 'product_loop';
		$params['mode']                    = $mode;
		$params['add_to_button_div_class'] = 'wldcfwc-hide';

		global $product;
		if ( isset( $product ) ) {
			// if a shortcode is added to the wrong screen, the screen will no longer be editable if $product is not present for that screen
			$product_id                  = $product->get_id();
			$params['button_parameters'] = 'product_id=' . $product_id;
			$return_html                 = $this->wldcfwc_get_wishlist_button( $this->options, $params );

			// get hidden wish data
			$wish_data = $this->wldcfwc_add2WishList_wish_data( $product_id );
			// append hidden data to button.
			$return_html .= $wish_data;

		} else {
			// just in case
			$return_html = '';
		}

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		echo wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Get html for the Add to WishList button on the cart
	 *
	 * @return void
	 */
	public function wldcfwc_add2wishlist_button__cart() {
		$atts_passed['mode'] = 'cart';
		$return_html         = $this->wldcfwc_get_add2wishlist_button_for_a_list( $this->options, $atts_passed );

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		echo wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Get html for the Add to WishList button on the cart,
	 * when that button is positioned using JS
	 *
	 * @return void
	 */
	public function wldcfwc_add2wishlist_button__cart_js_pos() {
		$atts_passed['mode']                    = 'cart';
		$atts_passed['add_to_button_div_class'] = 'wldcfwc-hide';
		$return_html                            = $this->wldcfwc_get_add2wishlist_button_for_a_list( $this->options, $atts_passed );

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		echo wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Get all the options
	 *
	 * @return array
	 */
	public function wldcfwc_return_options() {
		return $this->options;
	}

	/**
	 * Called when product page Add to WishList button is positioned using a shortcode
	 *
	 * @return string
	 */
	public function wldcfwc_product_page_button_shortcode() {

		$return_html = $this->wldcfwc_add2wishlist_button__product_page();
		return wp_kses( $return_html, $allowed_tags );
	}
	/**
	 * Called when product loop Add to WishList button is positioned using a shortcode
	 *
	 * @return string
	 */
	public function wldcfwc_poduct_loop_button_shortcode() {
		$return_html = $this->wldcfwc_add2wishlist_button__product_loop();

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		return wp_kses( $return_html, $allowed_tags );
	}
	/**
	 * Called when cart Add to WishList button is positioned using a shortcode
	 *
	 * @return string
	 */
	public function wldcfwc_cart_button_shortcode() {

		$atts_passed = array();

		// change container class so it's not positioned like other buttons
		$atts_passed['button_div_id']    = 'wldcfwc_save_all_wishlist_shortcode_button_div_id__cart';
		$atts_passed['button_div_class'] = 'wldcfwc-add2wishlist-button-short-code-div wldcfwc-list-button-div--show-hide';

		$atts_passed['wp-block-button__link--turnoff'] = true;

		$atts_passed['mode'] = 'cart';

		$return_html = $this->wldcfwc_get_add2wishlist_button_for_a_list( $this->options, $atts_passed );

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		return wp_kses( $return_html, $allowed_tags );
	}
	/**
	 * Used by the template page that displays the store's WishList homepage. Hooked by wldcfwc_hp_shortcode
	 *
	 * @return string
	 */
	public function wldcfwc_hp_shortcode() {
		$return_html = $this->wldcfwc_get_wishlist_hp_html();

		$allowed_tags = wldcfwc_set_allowed_tags( 'post,svg' );
		return wp_kses( $return_html, $allowed_tags );
	}

	/**
	 * Used to remove the template page used to display WishList pages from the top menu
	 *
	 * @param  $args
	 * @param  $params
	 * @return mixed
	 */
	public function wldcfwc_remove_page_from_menu( $args, $params ) {
		$page_ids_a    = $params['remove_page_ids_a'];
		$page_ids_list = implode( ',', $page_ids_a );

		if ( ! isset( $args['exclude'] ) ) {
			$args['exclude'] = $page_ids_list;
		} else {
			$list            = $args['exclude'];
			$list_a          = explode( ',', $list );
			$list_a          = array_merge( $list_a, $page_ids_a );
			$list            = implode( ',', $list_a );
			$args['exclude'] = $list;
		}
		return $args;
	}

	/**
	 * Used to remove any WishList top menu items when the plugin is not set to live. Hooked with wp_page_menu_args
	 *
	 * @param  $args
	 * @return mixed
	 */
	public function wldcfwc_remove_all_wishlist_pages_from_menu( $args ) {
		// hook wp_page_menu_args()
		// update $args['exclude'] to exclude our template

		// This is only called when the plugin is active, but it's status is 'Testing' or 'Off'.
		// This removes the "WishList" link from the top menu for everyone when the plugin status is 'Off' and
		// for all non-admin users when the plugin status is 'Test'.
		$remove_page_id                = (int) get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		$params['remove_page_ids_a'][] = $remove_page_id;

		$args = $this->wldcfwc_remove_page_from_menu( $args, $params );
		return $args;
	}
}
