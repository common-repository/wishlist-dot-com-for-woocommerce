<?php

/**
 * This trait provides functionality shared between wldcfwc_Public and wldcfwc_Admin classes.
 *
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */

trait Wldcfwc_Trait {


	// special keys that are saved directly in db and not changed by admin panel options saved
	private $special_option_keys = array( 'api_key', 'valid_api_key', 'store_uuid', 'store_api_subdomain' );

	/**
	 * Gets options saved within the admin panel. Uses default values as necessary.
	 *
	 * @param  $mode
	 * @return array|string[]
	 */
	public function wldcfwc_get_options( $mode = '', $special_keys = true ) {

		$default_values = $this->wldcfwc_get_field_defaults__options( $mode );

		if ( 'all' == $mode ) {
			$options_key = WLDCFWC_SLUG;
		} else {
			// only pull what's necessary from the db. Email templates and other wlcom are not necessary for public ui
			$options_key = WLDCFWC_SLUG . '_options';
		}

		$options_saved = get_option( $options_key );
		if ( ! empty( $options_saved ) && is_array( $options_saved ) ) {
			$options = array_merge( $default_values, $options_saved );
		} else {
			$options = $default_values;
		}

		// add seperately saved items to options
		if ( $special_keys ) {
			$special_keys = $this->special_option_keys;
			foreach ( $special_keys as $special_key ) {
				$special_key_name = WLDCFWC_SLUG . '_' . $special_key;
				if ( ! empty( get_option( $special_key_name ) ) ) {
					$options[ $special_key ] = get_option( $special_key_name );
				}
			}
		}

		return $options;
	}
	/**
	 * Get button attributes that are then used to generate button css and html
	 *
	 * @param  $options
	 * @param  $mode
	 * @return array
	 */
	private function wldcfwc_get_button_atrributes( $options, $mode ) {
		$atts = array();

		$option_val_suffix = $mode;

		$option_val_suffix_style = $option_val_suffix;
		$button_style            = $options[ 'wishlist_button_style__' . $option_val_suffix_style ];
		$button_icon             = $options[ 'addwishlist_button_icon__' . $option_val_suffix_style ];

		$atts['button_icon_title']        = esc_html__( 'add to wishlist', 'wishlist-dot-com-for-woocommerce' );
		$atts['button_div_hide_me_class'] = '';
		$atts['button_id']                = 'addwishlist_button_button_id__' . $option_val_suffix;
		$atts['button_icon_extra_class']  = '';

		if ( str_contains( $button_style, 'text_link' ) ) {
			// 'button' html tag is changed to 'a' html tag in the template
			$atts['button_or_link_tag']    = 'a';
			$atts['link_tag_href']         = '#';
			$atts['button_type_attr']      = '';
			$atts['logo_icon_extra_class'] = '';
		} else {
			$atts['button_or_link_tag']    = 'button';
			$atts['link_tag_href']         = '';
			$atts['button_type_attr']      = 'button';
			$atts['logo_icon_extra_class'] = '';
		}

		$atts['img_wrapper_button_css'] = 'wldcfwc-img-wrapper-button--' . $option_val_suffix_style . ' wldcfwc-img-wrapper-button';

		$button_for_a_product_modes_a = array( 'product_page', 'product_loop', 'cart', 'hp_mywishlist', 'hp_findwishlist', 'hp_createwishlist' );
		$button_for_homepage_modes_a  = array( 'hp_mywishlist', 'hp_findwishlist', 'hp_createwishlist' );
		$button_for_modal_modes_a     = array( 'save_wish_modal', 'save_cart_modal' );
		// add css which is then defined either by custom css or by our public.css
		if ( in_array( $mode, $button_for_a_product_modes_a ) && str_contains( $button_style, 'custom_button' ) ) {
			$atts['button_div_class'] = 'wldcfwc-wishlist-button-div--' . $option_val_suffix_style . ' wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			$atts['button_class']     = 'wldcfwc-wishlist-button--' . $option_val_suffix_style;
		} elseif ( in_array( $mode, $button_for_a_product_modes_a ) && str_contains( $button_style, 'theme_button' ) ) {
			// add theme classes
			$atts['button_div_class'] = 'wp-block-button wldcfwc-wishlist-button-div--' . $option_val_suffix_style . ' wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			// wp-block-button__link prevent <a> tag with button class from having it's link text underlined
			$atts['button_class'] = 'button alt wp-block-button__link wldcfwc-wishlist-button--' . $option_val_suffix_style;
		} elseif ( in_array( $mode, $button_for_a_product_modes_a ) && str_contains( $button_style, 'text_link' ) ) {
			// 'button' html tag is changed to 'a' html tag in the template
			$atts['button_div_class'] = 'wldcfwc-wishlist-button-div--' . $option_val_suffix_style . ' wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			$atts['button_class']     = 'wldcfwc-wishlist-text-link--' . $option_val_suffix_style;
		} elseif ( in_array( $mode, $button_for_a_product_modes_a ) ) {
			$atts['button_div_class'] = 'wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			$atts['button_class']     = '';

		} elseif ( in_array( $mode, $button_for_modal_modes_a ) && str_contains( $button_style, 'custom_button' ) ) {
			$atts['button_div_class'] = 'wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			$atts['button_class']     = 'wldcfwc-wishlist-button--' . $option_val_suffix_style;
		} elseif ( in_array( $mode, $button_for_modal_modes_a ) && str_contains( $button_style, 'theme_button' ) ) {
			$atts['button_div_class'] = 'wp-block-button wldcfwc-wishlist-button-div--' . $option_val_suffix_style . ' wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			// wp-block-button__link prevent <a> tag with button class from having it's link text underlined
			$atts['button_class'] = 'button alt wp-block-button__link wldcfwc-wishlist-button--' . $option_val_suffix_style;
		} elseif ( in_array( $mode, $button_for_modal_modes_a ) && str_contains( $button_style, 'text_link' ) ) {
			// 'button' html tag is changed to 'a' html tag in the template
			$atts['button_div_class'] = 'wldcfwc-wishlist-button-div--' . $option_val_suffix_style . ' wldcfwc-wishlist-button-div-align--' . $option_val_suffix;
			$atts['button_class']     = 'wldcfwc-wishlist-text-link--' . $option_val_suffix_style;
		}

		if ( in_array( $mode, $button_for_homepage_modes_a ) ) {
			$atts['button_div_class'] .= ' wldcfwc_inline_block wldcfwc_button_margin_left';
		}

		$atts['button_tooltip'] = esc_html( $options[ 'button_tooltip__' . $option_val_suffix ] );

		// get svg file. it's an svg if there's no .extension to the file name, like 'heart' and not heart.gif
		if ( ! empty( $options[ 'addwishlist_button_icon__' . $mode ] ) && strtolower( $options[ 'addwishlist_button_icon__' . $mode ] ) != 'custom_image' && ! str_contains( $options[ 'addwishlist_button_icon__' . $mode ], '.' ) && ! str_contains( $options[ 'addwishlist_button_icon__' . $mode ], 'none' ) ) {
			$svg_source = $this->wldcfwc_get_button_svg( esc_html( $options[ 'addwishlist_button_icon__' . $mode ] ) );
		}
		if ( ! empty( $svg_source ) ) {
			$atts['button_icon_svg'] = $svg_source;
			$atts['button_text_css'] = 'wldcfwc-add2wishlist-button-text-custom';
			$atts['button_class']   .= ' wldcfwc-wishlist-icon-button wldcfwc-wishlist-icon-button--' . $option_val_suffix_style;
		} elseif ( ! empty( $options[ 'addwishlist_button_icon__' . $mode ] ) && strtolower( $options[ 'addwishlist_button_icon__' . $mode ] ) == 'wldcfwc_icon' ) {
			$icon_url                = WLDCFWC_URL . 'public/images/WishListIcon-trans-48.png';
			$atts['button_icon_src'] = $icon_url;
			$atts['button_text_css'] = 'wldcfwc-add2wishlist-button-text-custom';
			$atts['button_class']   .= ' wldcfwc-wishlist-icon-button wldcfwc-wishlist-icon-button--' . $option_val_suffix_style;
		} elseif ( ! empty( $options[ 'addwishlist_button_icon__' . $mode ] ) && strtolower( $options[ 'addwishlist_button_icon__' . $mode ] ) == 'custom_image' && ! empty( $options[ 'addwishlist_button_icon_image_upload__' . $mode ] ) ) {
			$atts['button_icon_src'] = esc_html( $options[ 'addwishlist_button_icon_image_upload__' . $mode ] );
			$atts['button_text_css'] = 'wldcfwc-add2wishlist-button-text-custom';
			$atts['button_class']   .= ' wldcfwc-wishlist-icon-button wldcfwc-wishlist-icon-button--' . $option_val_suffix_style;
		} else {
			$atts['button_icon_src'] = '';
			$atts['button_text_css'] = '';
		}

		$atts['button_text'] = esc_html( $options[ 'wishlist_button_text__' . $option_val_suffix ] );

		return $atts;
	}

	/**
	 * Get buttons that are displayed on a page with a list, like the shopping cart
	 *
	 * @param  $options
	 * @param  $atts_passed
	 * @return mixed|null
	 */
	public function wldcfwc_get_add2wishlist_button_for_a_list( $options = array(), $atts_passed = array() ) {

		$template_path = 'wldcfwc-button.php';

		if ( empty( $atts_passed['mode'] ) ) {
			$atts['mode'] = 'cart';
		} else {
			$atts['mode'] = $atts_passed['mode'];
		}
		$mode = $atts['mode'];

		$button_atts = $this->wldcfwc_get_button_atrributes( $options, $mode );
		$atts        = array_merge( $atts, $button_atts );

		// set mode for button listener
		if ( 'cart' == $mode ) {
			$button_param_mode = 'save_cart_modal';
		} else {
			// no other modes right now
			$button_param_mode = 'save_cart_modal';
		}

		$atts['button_div_id']     = 'wldcfwc_save_all_wishlist_button_div_id__' . $mode;
		$atts['button_id']         = 'wldcfwc_save_all_wishlist_button_id__' . $mode;
		$atts['button_parameters'] = 'select_list_items=1,mode=' . $button_param_mode;

		foreach ( $atts_passed as $key => $value ) {
			$atts[ $key ] = $value;
		}

		if ( ! empty( $atts_passed['wp-block-button__link--turnoff'] ) ) {
			$atts['button_class'] = str_replace( 'wp-block-button__link', 'wp-block-button__link--turnoff', $atts['button_class'] );
		}

		if ( empty( $atts_passed['add_to_button_div_class'] ) ) {
			$do_nothing = true;
		} elseif ( ! empty( $atts['button_div_class'] ) ) {
			$atts['button_div_class'] .= ' ' . $atts_passed['add_to_button_div_class'];
		} else {
			$atts['button_div_class'] = $atts_passed['add_to_button_div_class'];
		}

		$html = wldcfwc_get_template_html( $template_path, $atts, true );

		$return_html = apply_filters( 'wldcfwc_wishlist_button', $html );

		return $return_html;
	}

	/**
	 * Get a button's html.
	 * This is called only when there are changes to the options saved
	 * The resulting html and css is saved as an option that is used in the public ui
	 *
	 * @param  $options
	 * @param  $params
	 * @return mixed|null
	 */
	public function wldcfwc_get_wishlist_button( $options, $params ) {

		$mode = $params['mode'];

		$template_path = 'wldcfwc-button.php';

		$button_atts = $this->wldcfwc_get_button_atrributes( $options, $mode );
		$atts        = $button_atts;

		// overwrite default atts with params passed to get_wishlist_button()
		foreach ( $params as $key => $value ) {
			if ( isset( $atts[ $key ] ) ) {
				$atts[ $key ] = $value;
			}
		}

		$atts['button_tooltip'] = esc_html( $options[ 'button_tooltip__' . $mode ] );
		$atts['button_text']    = esc_html( $options[ 'wishlist_button_text__' . $mode ] );

		if ( empty( $params['add_to_button_div_class'] ) ) {
			$do_nothing = true;
		} elseif ( ! empty( $atts['button_div_class'] ) ) {
			$atts['button_div_class'] .= ' ' . $params['add_to_button_div_class'];
		} else {
			$atts['button_div_class'] = $params['add_to_button_div_class'];
		}
		if ( ! empty( $params['button_parameters'] ) ) {
			$atts['button_parameters'] = $params['button_parameters'];
		} else {
			$atts['button_parameters'] = '';
		}

		$html = wldcfwc_get_template_html( $template_path, $atts, true );

		$return_html = apply_filters( 'wldcfwc_wishlist_button', $html );

		return $return_html;
	}

	/**
	 * Get a button's CSS styling
	 * This is called only when there are changes to the options saved
	 * The resulting css is saved as an option that is used in the public ui
	 *
	 * @param  $options
	 * @return string
	 */
	public function wldcfwc_get_button_style( $options ) {

		// $options are those currently being saved.

		// !!
		// begin code to add a button or style option that's configurable within the admin panel
		// !!

		// 'selector_type' => '.some-css-selectore'
		// 'selector_type' roughly corresponds to field like option_suffix['product_page'] in the admin ui.
		// below, 'selector_type' is used to get the option's css and assign it to the selector. The selector is then used in the element output
		// the selector css must be used in the button html. See get_button_atrributes() which is where the button's css selector is set;
		// to troubleshoot, find the element in the html that has the wrong style. review it's css and confirm it's listed below.
		$selector_types        = array(
			'product_page'                   => '.wldcfwc-wishlist-button--product_page, .wldcfwc-wishlist-text-link--product_page',
			'product_page-icon-button'       => '.wldcfwc-wishlist-button--product_page.wldcfwc-wishlist-icon-button--product_page',
			'product_page-icon-wrapper'      => '.wldcfwc-img-wrapper-button--product_page.wldcfwc-img-wrapper-button',
			'product_page-icon-img'          => '.wldcfwc-img-wrapper-button--product_page.wldcfwc-img-wrapper-button svg, .wldcfwc-img-wrapper-button--product_page.wldcfwc-img-wrapper-button img',
			'product_page-icon-text'         => '.wldcfwc-wishlist-button--product_page .wldcfwc-add2wishlist-button-text-custom, .wldcfwc-wishlist-text-link--product_page .wldcfwc-add2wishlist-button-text-custom',

			'product_loop'                   => '.wldcfwc-wishlist-button--product_loop, .wldcfwc-wishlist-text-link--product_loop',
			'product_loop-icon-button'       => '.wldcfwc-wishlist-button--product_loop.wldcfwc-wishlist-icon-button--product_loop',
			'product_loop-icon-wrapper'      => '.wldcfwc-img-wrapper-button--product_loop.wldcfwc-img-wrapper-button',
			'product_loop-icon-img'          => '.wldcfwc-img-wrapper-button--product_loop.wldcfwc-img-wrapper-button svg, .wldcfwc-img-wrapper-button--product_loop.wldcfwc-img-wrapper-button img',
			'product_loop-icon-text'         => '.wldcfwc-wishlist-button--product_loop .wldcfwc-add2wishlist-button-text-custom, .wldcfwc-wishlist-text-link--product_loop .wldcfwc-add2wishlist-button-text-custom',

			'cart'                           => '.wldcfwc-wishlist-button--cart, .wldcfwc-wishlist-text-link--cart',
			'cart-icon-button'               => '.wldcfwc-wishlist-button--cart.wldcfwc-wishlist-icon-button--cart',
			'cart-icon-wrapper'              => '.wldcfwc-img-wrapper-button--cart.wldcfwc-img-wrapper-button',
			'cart-icon-img'                  => '.wldcfwc-img-wrapper-button--cart.wldcfwc-img-wrapper-button svg, .wldcfwc-img-wrapper-button--cart.wldcfwc-img-wrapper-button img',
			'cart-icon-text'                 => '.wldcfwc-wishlist-button--cart .wldcfwc-add2wishlist-button-text-custom, .wldcfwc-wishlist-text-link--cart .wldcfwc-add2wishlist-button-text-custom',
			'cart-align'                     => '.wldcfwc-wishlist-button-div-align--cart',

			'hp_mywishlist'                  => '.wldcfwc-wishlist-button--hp_mywishlist, .wldcfwc-wishlist-text-link--hp_mywishlist',
			'hp_mywishlist-icon-button'      => '.wldcfwc-wishlist-button--hp_mywishlist.wldcfwc-wishlist-icon-button--hp_mywishlist',
			'hp_mywishlist-icon-wrapper'     => '.wldcfwc-img-wrapper-button--hp_mywishlist.wldcfwc-img-wrapper-button',
			'hp_mywishlist-icon-img'         => '.wldcfwc-wishlist-button--hp_mywishlist .wldcfwc-img-wrapper-button svg, .wldcfwc-wishlist-text-link--hp_mywishlist .wldcfwc-img-wrapper-button svg, .wldcfwc-wishlist-button--hp_mywishlist .wldcfwc-img-wrapper-button img, .wldcfwc-wishlist-text-link--hp_mywishlist .wldcfwc-img-wrapper-button img',
			'hp_mywishlist-icon-text'        => '.wldcfwc-wishlist-button--hp_mywishlist .wldcfwc-add2wishlist-button-text-custom, .wldcfwc-wishlist-text-link--hp_mywishlist .wldcfwc-add2wishlist-button-text-custom',

			'hp_findwishlist'                => '.wldcfwc-wishlist-button--hp_findwishlist, .wldcfwc-wishlist-text-link--hp_findwishlist',
			'hp_findwishlist-icon-button'    => '.wldcfwc-wishlist-button--hp_findwishlist.wldcfwc-wishlist-icon-button--hp_findwishlist',
			'hp_findwishlist-icon-wrapper'   => '.wldcfwc-img-wrapper-button--hp_findwishlist.wldcfwc-img-wrapper-button',
			'hp_findwishlist-icon-img'       => '.wldcfwc-wishlist-button--hp_findwishlist .wldcfwc-img-wrapper-button svg, .wldcfwc-wishlist-text-link--hp_findwishlist .wldcfwc-img-wrapper-button svg, .wldcfwc-wishlist-button--hp_findwishlist .wldcfwc-img-wrapper-button img, .wldcfwc-wishlist-text-link--hp_findwishlist .wldcfwc-img-wrapper-button img',
			'hp_findwishlist-icon-text'      => '.wldcfwc-wishlist-button--hp_findwishlist .wldcfwc-add2wishlist-button-text-custom, .wldcfwc-wishlist-text-link--hp_findwishlist .wldcfwc-add2wishlist-button-text-custom',

			'hp_createwishlist'              => '.wldcfwc-wishlist-button--hp_createwishlist, .wldcfwc-wishlist-text-link--hp_createwishlist',
			'hp_createwishlist-icon-button'  => '.wldcfwc-wishlist-button--hp_createwishlist.wldcfwc-wishlist-icon-button--hp_createwishlist',
			'hp_createwishlist-icon-wrapper' => '.wldcfwc-img-wrapper-button--hp_createwishlist.wldcfwc-img-wrapper-button',
			'hp_createwishlist-icon-img'     => '.wldcfwc-wishlist-button--hp_createwishlist .wldcfwc-img-wrapper-button svg, .wldcfwc-wishlist-text-link--hp_createwishlist .wldcfwc-img-wrapper-button svg, .wldcfwc-wishlist-button--hp_createwishlist .wldcfwc-img-wrapper-button img, .wldcfwc-wishlist-text-link--hp_createwishlist .wldcfwc-img-wrapper-button img',
			'hp_createwishlist-icon-text'    => '.wldcfwc-wishlist-button--hp_createwishlist .wldcfwc-add2wishlist-button-text-custom, .wldcfwc-wishlist-text-link--hp_createwishlist .wldcfwc-add2wishlist-button-text-custom',

			'tooltip'                        => '.wldcfwc-tooltip',
		);
		$selector_type_options = array();

		// for each selector type, create array of selector string and option variable name where its setting is stored
		foreach ( $selector_types as $selector_type => $selector ) {
			// vriable suffix based on where the element is located, like in a modal, on the WishList page or on the shopping cart page
			if ( str_contains( $selector_type, 'product_page' ) ) {
				$selector_type_option_suffix = 'product_page';
			} elseif ( str_contains( $selector_type, 'product_loop' ) ) {
				$selector_type_option_suffix = 'product_loop';
			} elseif ( str_contains( $selector_type, 'cart' ) ) {
				$selector_type_option_suffix = 'cart';
			} elseif ( str_contains( $selector_type, 'hp_mywishlist' ) ) {
				$selector_type_option_suffix = 'hp_mywishlist';
			} elseif ( str_contains( $selector_type, 'hp_findwishlist' ) ) {
				$selector_type_option_suffix = 'hp_findwishlist';
			} elseif ( str_contains( $selector_type, 'hp_createwishlist' ) ) {
				$selector_type_option_suffix = 'hp_createwishlist';
			} elseif ( str_contains( $selector_type, 'default' ) ) {
				$selector_type_option_suffix = 'default';
			}

			// set selector string and option variable name where its setting is stored
			// set selector options based on $selector_type
			if ( isset( $options[ 'wishlist_button_style__' . $selector_type_option_suffix ] ) ) {
				$button_style = $options[ 'wishlist_button_style__' . $selector_type_option_suffix ];
			} else {
				$button_style = '';
			}

			// margin for all buttons
			if ( in_array( $selector_type, array( 'product_page', 'product_loop', 'cart', 'hp_mywishlist', 'hp_findwishlist', 'hp_createwishlist', 'default' ) ) ) {
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'style'        => 'margin',
							'option_var'   => 'wishlist_button_margin__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
					),
				);
			}
			// up/down left/right position buttons
			if ( in_array( $selector_type, array( 'product_page', 'product_loop', 'cart', ) ) ) {
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'style'        => 'position',
							'value'   => 'relative',
							'style_suffix' => ' !important',
						),
						array(
							'style'        => 'left',
							'option_var'   => 'wishlist_button_margin_left__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
						array(
							'style'        => 'top',
							'option_var'   => 'wishlist_button_margin_top__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
					),
				);
			}

			if ( in_array( $selector_type, array( 'product_page', 'product_loop', 'cart', 'hp_mywishlist', 'hp_findwishlist', 'hp_createwishlist', 'default' ) ) && str_contains( $button_style, 'custom_button' ) ) {
				// button, normal and hover
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						// style def => option variable where it's stored
						array(
							'style'      => 'background-color',
							'option_var' => 'wishlist_button_background_color__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'color',
							'option_var' => 'wishlist_button_text_color__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'border-color',
							'option_var' => 'wishlist_button_border_color__' . $selector_type_option_suffix,
						),
						array(
							'style' => 'border-width',
							'value' => '1px',
						),
						array(
							'style' => 'border-style',
							'value' => 'solid',
						),

						array(
							'style'      => 'border-radius',
							'option_var' => 'wishlist_button_border_radius__' . $selector_type_option_suffix,
						),
						array(
							'style'      => '-webkit-border-radius',
							'option_var' => 'wishlist_button_border_radius__' . $selector_type_option_suffix,
						),
						array(
							'style'      => '-moz-border-radius',
							'option_var' => 'wishlist_button_border_radius__' . $selector_type_option_suffix,
						),

						array(
							'style'      => 'padding-top',
							'option_var' => 'wishlist_button_padding_top_bottom__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'padding-bottom',
							'option_var' => 'wishlist_button_padding_top_bottom__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'padding-left',
							'option_var' => 'wishlist_button_padding_left_right__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'padding-right',
							'option_var' => 'wishlist_button_padding_left_right__' . $selector_type_option_suffix,
						),

						array(
							'style'      => 'margin',
							'option_var' => 'wishlist_button_margin__' . $selector_type_option_suffix,
						),
					),
					'hover'         => array(
						// style def => option variable where it's stored
						array(
							'style'      => 'background-color',
							'option_var' => 'wishlist_button_background_color_hover__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'color',
							'option_var' => 'wishlist_button_text_color_hover__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'border-color',
							'option_var' => 'wishlist_button_border_color_hover__' . $selector_type_option_suffix,
						),
					),
				);
			} elseif ( in_array( $selector_type, array( 'cart-align' ) ) ) {
				// alignment of the button
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'option_var'         => 'wishlist_button_position__' . $selector_type_option_suffix,
							// based on strings in the option value's name
							'find_in_option_val' => array(
								'centered'    => 'clear:both;text-align:center;margin-left:15px;margin-right:15px;',
								'_left'       => 'clear:both;text-align:left;margin-right:15px;',
								'above_right' => 'clear:both;text-align:right;margin-left:15px;',
								'below_right' => 'clear:both;text-align:right;margin-left:15px',
							),
						),
						array(
							'style'      => 'margin',
							'option_var' => 'wishlist_button_margin__' . $selector_type_option_suffix,
						),
					),
				);
			} elseif ( in_array( $selector_type, array( 'product_page-icon-button', 'product_loop-icon-button', 'cart-icon-button', 'hp_mywishlist-icon-button', 'hp_findwishlist-icon-button', 'hp_createwishlist-icon-button' ) ) ) {
				// icon within the button
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						// adding !important becuase the icon can be used with the theme's buttons which has its own padding
						array(
							'style'        => 'padding-top',
							'option_var'   => 'wishlist_icon_button_padding_top_bottom__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
						array(
							'style'        => 'padding-bottom',
							'option_var'   => 'wishlist_icon_button_padding_top_bottom__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
						array(
							'style'        => 'padding-left',
							'option_var'   => 'wishlist_icon_button_padding_left_right__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
						array(
							'style'        => 'padding-right',
							'option_var'   => 'wishlist_icon_button_padding_left_right__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
					),
				);
			} elseif ( in_array( $selector_type, array( 'product_page-icon-wrapper', 'product_loop-icon-wrapper', 'cart-icon-wrapper', 'hp_mywishlist-icon-wrapper', 'hp_findwishlist-icon-wrapper', 'hp_createwishlist-icon-wrapper', 'save_wish_modal-icon', 'save_wishlist_modal-icon', 'save_cart_modal-icon', 'delete_wish_modal-icon', 'wishlist-icon', 'cart-icon' ) ) ) {
				// icon wrapper within the button
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'style'      => 'top',
							'option_var' => 'wishlist_icon_top_pos__' . $selector_type_option_suffix,
						),
						array(
							'style'      => 'left',
							'option_var' => 'wishlist_icon_left_pos__' . $selector_type_option_suffix,
						),
						array(
							'option_var' => 'addwishlist_button_icon__' . $selector_type_option_suffix,
							// based on strings in the option value's name
							'find_in_option_val' => array(
								'none' => 'display:none',
							),
						),
					),
				);
			} elseif ( in_array( $selector_type, array( 'product_page-icon-text', 'product_loop-icon-text', 'cart-icon-text', 'hp_mywishlist-icon-text', 'hp_findwishlist-icon-text', 'hp_createwishlist-icon-text' ) ) ) {

				//icon has absolute positioning. so emulate icon margin-right by calculating text margin-left
				$icon = $options[ 'addwishlist_button_icon__' . $selector_type_option_suffix];
				if ( 'none' == $icon ) {
					$margin_left = null;
				} else {
					$icon_width = esc_html( $options[ 'wishlist_icon_width__' . $selector_type_option_suffix ] );
					$icon_left_pos = esc_html( $options[ 'wishlist_icon_left_pos__' . $selector_type_option_suffix ] );
					$icon_margin_right = esc_html( $options[ 'wishlist_icon_margin_right__' . $selector_type_option_suffix ] );
					$wishlist_button_style = esc_html( $options[ 'wishlist_button_style__' . $selector_type_option_suffix ] );
					if ( in_array( $wishlist_button_style, [ 'theme_button', 'custom_button' ] ) ) {
						$button_lr_padding = esc_html( $options[ 'wishlist_icon_button_padding_left_right__' . $selector_type_option_suffix ] );
					} else {
						$button_lr_padding = 0;
					}
					$margin_left_temp = intval($icon_left_pos) + intval($icon_width) + intval($icon_margin_right) - intval($button_lr_padding);
					if ($margin_left_temp > 0) {
						$margin_left = $margin_left_temp . 'px';
					} else {
						$margin_left = null;
					}
				}

				// icon text within the button
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'style' => 'margin-left',
							'value' => $margin_left
						),
					),
				);
			} elseif ( in_array( $selector_type, array( 'product_page-icon-img', 'product_loop-icon-img', 'cart-icon-img', 'hp_mywishlist-icon-img', 'hp_findwishlist-icon-img', 'hp_createwishlist-icon-img' ) ) ) {
				// icon image within the button
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'style'        => 'width',
							'option_var'   => 'wishlist_icon_width__' . $selector_type_option_suffix,
							'style_suffix' => ' !important',
						),
						array(
							'style'      => 'fill',
							'option_var' => 'wishlist_icon_fill_color__' . $selector_type_option_suffix,
						),
					),
				);
			} elseif ( in_array( $selector_type, array( 'tooltip' ) ) ) {
				// tooltip for the button hover
				$selector_type_options[] = array(
					'selector_type' => $selector_type,
					'selector'      => $selector,
					'normal'        => array(
						array(
							'style'      => 'background-color',
							'option_var' => 'tooltip_background_color',
						),
						array(
							'style'      => 'border-color',
							'option_var' => 'tooltip_border_color',
						),
						array(
							'style'      => 'color',
							'option_var' => 'tooltip_text_color',
						),
						array(
							'option_var'         => 'tooltip_show_shadow',
							// based on strings in the option value's name
							'find_in_option_val' => array(
								'no_str' => 'box-shadow:unset',
							),
						),
					),
				);
			}
		}

		// !!
		// end code to add a button or style option that's configurable within the admin panel
		// !!

		$css_a = array();
		// begin converting $selector_type_options into a CSS string. this is independent of code above and typically should not be changed
		// create array of styles for each selector
		// use the def's option var to set def's value
		foreach ( $selector_type_options as $selector_type_option ) {
			// button, normal
			$selector_sub_types = array( 'normal', 'hover' );
			foreach ( $selector_sub_types as $selector_sub_type ) {
				if ( ! empty( $selector_type_option[ $selector_sub_type ] ) ) {
					// set selector's definitions, looping over each selector's definition array
					foreach ( $selector_type_option[ $selector_sub_type ] as $style_item ) {
						$val = null;
						if ( isset( $style_item['style'] ) ) {
							$set_style = $style_item['style'];
						} else {
							$set_style = null;
						}
						if ( isset( $style_item['style_suffix'] ) ) {
							$set_style_suffix = $style_item['style_suffix'];
						} else {
							$set_style_suffix = '';
						}
						$style_val_string = null;
						if ( isset( $style_item['value'] ) ) {
							// value from $selector_type_options array, not options table
							$val = $style_item['value'];
						} elseif ( isset( $style_item['find_in_option_val'] ) && isset( $options[ $style_item['option_var'] ] ) ) {
							// value based on a sring found option_var name. from item's find_in_option_val array above
							$var_val = esc_html( $options[ $style_item['option_var'] ] );
							foreach ( $style_item['find_in_option_val'] as $find => $style_val_pair ) {
								if ( str_contains( $var_val, $find ) ) {
									// found var with string in its value. set string and break this loop
									$style_val_string = $style_val_pair;
									break;
								}
							}
						} elseif ( isset( $style_item['option_var'] ) && isset( $options[ $style_item['option_var'] ] ) ) {
							// use stored option value
							$val = esc_html( $options[ $style_item['option_var'] ] );

							// update admin panel range vals to have units
							$find_rand_a = array( 'wishlist_icon_width__', 'wishlist_icon_top_pos__', 'wishlist_icon_left_pos__', 'wishlist_icon_margin_right__', 'wishlist_button_margin_left__', 'wishlist_button_margin_top__' );
							foreach ( $find_rand_a as $item ) {
								// add unit
								if ( str_contains( $style_item['option_var'], $item ) && ! str_contains( $val, 'px' ) ) {
									$val .= 'px';
								}
							}
							//udpate special default vals
							$find_rand_a = array( 'wishlist_icon_fill_color__' );
							foreach ( $find_rand_a as $item ) {
								// add unit
								if ( str_contains( $style_item['option_var'], $item ) && empty( $val ) ) {
									//use parent's color to fill the svg
									$val = 'currentColor';
								}
							}
						}

						// set the css
						if ( empty( $val ) && empty( $style_val_string ) ) {
							$do_nothing = true;
						} elseif ( 'normal' == $selector_sub_type ) {
							if ( ! empty( $style_val_string ) ) {
								$css_a[ $selector_type_option['selector'] ][] = $style_val_string;
							} else {
								$css_a[ $selector_type_option['selector'] ][] = $set_style . ':' . $val . $set_style_suffix;
							}
						} elseif ( 'hover' == $selector_sub_type ) {
							// add hover to each comma separated selector
							$selectors_a = explode( ',', $selector_type_option['selector'] );
							foreach ( $selectors_a as $this_selector ) {
								$css_a[ $this_selector . ':hover' ][] = $set_style . ':' . $val . $set_style_suffix;
							}
						}
					}
				}
			}
		}

		// create the final css srting to be included in the page
		$css_str = '';
		foreach ( $css_a as $selector => $style ) {
			$css_str .= $selector . '{' . implode( ';', $style ) . '} ';
		}

		// add global button css, like spinner
		if ( 'yes_str' == $options['show_add2wishlist_button_spinner'] ) {
			$add2wishlist_button_spinner_color  = esc_html( $options['add2wishlist_button_spinner_color'] );
			$add2wishlist_button_spinner_height = esc_html( $options['add2wishlist_button_spinner_height'] );
			$spinner_css                        = '.wldcfwc_spinner-icon{width:' . $add2wishlist_button_spinner_height . ';height:' . $add2wishlist_button_spinner_height . ';border-left-color:' . $add2wishlist_button_spinner_color . ';}';
			$css_str                           .= $spinner_css;
		}

		// all user inputted data is escaped by this function
		return wp_kses_post( $css_str );
	}

	/**
	 * Get the inline CSS to be used on the plublic ui
	 *
	 * @param  $options
	 * @return string
	 */
	public function wldcfwc_get_button_css( $options ) {

		$buttons_css_key = WLDCFWC_SLUG . '_global_buttons_css';
		$buttons_css     = get_option( $buttons_css_key );

		if ( empty( $buttons_css ) || 'dev' == WLDCFWC_ENV ) {
			// generate buttons_css and add it to options
			$buttons_css = $this->wldcfwc_get_button_style( $options );
		}

		$css = $buttons_css;

		return $css;
	}

	/**
	 * Get the icons that can be used within a button
	 *
	 * @param  $none_label
	 * @param  $custom_label
	 * @return array
	 */
	public function wldcfwc_add2wishlist_icons_drop( $none_label = '', $custom_label = '' ) {
		if ( ! function_exists( 'list_files' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
		}

		global $wp_filesystem;
		WP_Filesystem();

		$plugin_svg_path = WLDCFWC_DIR . 'public/images/add2wishlistbutton';
		$sort_icons      = array( 'heart', 'star', 'add', 'plus', 'check', 'circle', 'list' );
		$icon_files      = list_files( $plugin_svg_path, 1 );

		$icons      = array();
		$file_index = 0;
		$custom_ctn = 0;

		if ( empty( $none_label ) ) {
			$name = 'None';
		} else {
			$name = $none_label;
		}
		$icons[ $file_index ]['name']   = $name;
		$icons[ $file_index ]['value']  = 'none';
		$icons[ $file_index ]['type']   = 'none';
		$icons[ $file_index ]['source'] = '';
		$icons[ $file_index ]['sort']   = $file_index;
		++$file_index;
		++$custom_ctn;

		if ( empty( $custom_label ) ) {
			$name = 'Custom';
		} else {
			$name = $custom_label;
		}
		$icons[ $file_index ]['name']   = $name;
		$icons[ $file_index ]['value']  = 'custom_image';
		$icons[ $file_index ]['type']   = 'custom_image';
		$icons[ $file_index ]['source'] = '';
		$icons[ $file_index ]['sort']   = $file_index;
		++$file_index;
		++$custom_ctn;

		$icons[ $file_index ]['name']  = 'WishList.com icon';
		$icons[ $file_index ]['value'] = 'wldcfwc_icon';
		$icons[ $file_index ]['type']  = 'wldcfwc_icon';

		$icon_url   = WLDCFWC_URL . 'public/images/WishListIcon-trans-48.png';
		$icon_url_a = wp_parse_url( $icon_url );
		if ( isset( $icon_url_a['path'] ) ) {
			$icon_url = $icon_url_a['path'];
		}
		$icons[ $file_index ]['source'] = $icon_url;
		$icons[ $file_index ]['sort']   = $file_index;
		++$file_index;
		++$custom_ctn;

		foreach ( $icon_files as $file ) {
			if ( is_file( $file ) ) {
				$filename      = basename( $file );
				$filename_a    = explode( '.', $filename );
				$filename_base = $filename_a[0];
				if ( ! empty( $filename_a[1] ) && 'svg' == $filename_a[1] && $wp_filesystem->exists( $file ) ) {
					$svg_string = $wp_filesystem->get_contents( $file );
					if ( $svg_string ) {
						$icons[ $file_index ]['source'] = $svg_string;
					}
				}
				$icons[ $file_index ]['name']  = $filename_base;
				$icons[ $file_index ]['value'] = $filename_base;
				$icons[ $file_index ]['type']  = 'svg';
				$icons[ $file_index ]['sort']  = 100;
				foreach ( $sort_icons as $i => $sort_icon ) {
					if ( str_contains( $filename_base, $sort_icon ) ) {
						$icons[ $file_index ]['sort'] = $i + $custom_ctn;
					}
				}
				++$file_index;
			}
		}

		array_multisort( array_column( $icons, 'sort' ), SORT_ASC, SORT_NUMERIC, array_column( $icons, 'name' ), SORT_ASC, $icons );

		return $icons;
	}

	/**
	 * Get the SVG that can be used within a button
	 *
	 * @param  $svg_base_name
	 * @return false|mixed|string
	 */
	public function wldcfwc_get_button_svg( $svg_base_name ) {
		if ( ! function_exists( 'list_files' ) ) {
			include_once ABSPATH . 'wp-admin/includes/file.php';
		}

		global $wp_filesystem;
		WP_Filesystem();

		$svg_dir  = 'public/images/add2wishlistbutton/';
		$svg_path = WLDCFWC_DIR . $svg_dir . $svg_base_name . '.svg';

		$return_string = '';
		if ( $wp_filesystem->exists( $svg_path ) ) {
			$svg_string = $wp_filesystem->get_contents( $svg_path );
			if ( $svg_string ) {
				$return_string = $svg_string;
			}
		}
		return $return_string;
	}

	/**
	 * Get the fields to be used within the admin panel. These button option fields are used within wldcfwc_Admin
	 * to display the various button configuration options
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_admin_fields__button( $params ) {
		if ( isset( $params['omit_fields'] ) ) {
			$omit_fields = $params['omit_fields'];
		}
		if ( isset( $params['search_replace'] ) ) {
			$search_replace = $params['search_replace'];
		}
		$section_title = $params['section_title'];
		$option_suffix = $params['option_suffix'];

		if ( isset( $params['button_name'] ) ) {
			$button_name = $params['button_name'];
		} else {
			$button_name = esc_html__( 'Add to WishList', 'wishlist-dot-com-for-woocommerce' );
		}

		if ( isset( $params['position_title'] ) ) {
			$position_title = $params['position_title'];
		} else {
			$position_title = '';
		}
		if ( isset( $params['button_position_options'] ) ) {
			$button_position_options = $params['button_position_options'];
		} else {
			$button_position_options = '';
		}
		if ( isset( $params['button_shortcode'] ) ) {
			$button_shortcode = $params['button_shortcode'];
		} else {
			$button_shortcode = '';
		}
		$button_style_options = $params['button_style_options'];
		$svg_icons_options    = $params['svg_icons_options'];
		$default_values       = $params['default_values'];

		$base_section_fields = array(
			// product page
			array(
				'id'                => 'section_title__' . $option_suffix,
				'type'              => 'content',
				'content'           => $section_title,
				'description'       => '',
				'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
				'description_class' => 'wldcfwc-content-description',
			),

			array(
				'id'          => 'wishlist_button_position__' . $option_suffix,
				'type'        => 'select',
				'title'       => $position_title,
				'description' => '',
				'attributes'  => array(
					'id' => 'wishlist_button_position__' . $option_suffix,
				),
				'options'     => $button_position_options,
				'default'     => $default_values[ 'wishlist_button_position__' . $option_suffix ],
				'wrap_class'  => 'no-border-bottom',
				'class'       => 'wldcfwc-form-control',
			),

			// begin button position show/hide div
			array(
				'id'   => 'begin_wishlist_button_position__' . $option_suffix,
				'name' => 'begin_wishlist_button_position__' . $option_suffix,
				'type' => 'html',
				'html' => '<div id="begin_wishlist_button_position__' . $option_suffix . '"><!-- begin position div -->',
			),

			array(
				'id'         => 'button_shortcode__' . $option_suffix,
				'type'       => 'content',
				'content'    => esc_html__( 'Copy and paste this shortcode into your page:', 'wishlist-dot-com-for-woocommerce' ) .
					'<br><pre class="wldcfwc-code-display">' . esc_html( $button_shortcode ) . '</pre>',
				'wrap_class' => 'wldcfwc-compact_top_bottom_padding exopite-sof-fieldgroup-title-h4 no-border-bottom wldcfwc-no-border-top
                wldcfwc-hide wldcfwc-show_hide_wishlist_button_use_shortcode__' . $option_suffix,
			),
			array(
				'id'         => 'button_javascript__' . $option_suffix,
				'type'       => 'content',
				'content'    => '
                    <div class="wldcfwc-code-display">
                        ' . esc_html__( 'The ', 'wishlist-dot-com-for-woocommerce' ) . '"' . $button_name . '" ' . esc_html__( 'button will be included in the DOM and hidden with the CSS class "wldcfwc-hide". Add your own Javascript to move the botton to where you want it.', 'wishlist-dot-com-for-woocommerce' ) . '
                    </div>',
				'wrap_class' => 'wldcfwc-compact_top_bottom_padding exopite-sof-fieldgroup-title-h4 no-border-bottom wldcfwc-no-border-top
                wldcfwc-hide wldcfwc-show_hide_wishlist_button_use_javascript__' . $option_suffix,
			),

			array(
				'id'          => 'wishlist_button_text__' . $option_suffix,
				'type'        => 'text',
				'title'       => '"' . $button_name . '" button text',
				'default'     => $default_values[ 'wishlist_button_text__' . $option_suffix ],
				'description' => '',
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom
                wldcfwc-show_hide_button_position__' . $option_suffix,
			),

			array(
				'id'         => 'wishlist_button_style__' . $option_suffix,
				'type'       => 'select',
				'title'      => 'Style of "' . $button_name . '" button',
				'attributes' => array(
					'id' => 'wishlist_button_style__' . $option_suffix,
				),
				'options'    => $button_style_options,
				'default'    => $default_values[ 'wishlist_button_style__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'style'      => 'fancy',
				'wrap_class' => 'no-border-bottom
                wldcfwc-show_hide_button_position__' . $option_suffix,
			),

			array(
				'id'         => 'wishlist_button_background_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_background_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_text_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'text color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_text_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_border_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Border color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_border_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_background_color_hover__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Hover background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_background_color_hover__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_text_color_hover__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Hover text color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_text_color_hover__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_border_color_hover__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Hover border color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_border_color_hover__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			array(
				'type'       => 'content',
				'content'    => '',
				'wrap_class' => 'wldcfwc_content_new_line
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			array(
				'id'         => 'wishlist_button_border_radius__' . $option_suffix,
				'type'       => 'text',
				'before'     => '"' . $button_name . '" ' . esc_html__( 'button border radius', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => 'like ' . $default_values[ 'wishlist_button_border_radius__' . $option_suffix ],
				'default'    => $default_values[ 'wishlist_button_border_radius__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-field-tight-top-bottom-margin
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_padding_top_bottom__' . $option_suffix,
				'type'       => 'text',
				'before'     => esc_html__( 'Button top/bottom padding (px)', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => 'like ' . $default_values[ 'wishlist_button_padding_top_bottom__' . $option_suffix ],
				'default'    => $default_values[ 'wishlist_button_padding_top_bottom__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-field-tight-top-bottom-margin
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_button_padding_left_right__' . $option_suffix,
				'type'       => 'text',
				'before'     => esc_html__( 'Button left/right padding (px)', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => 'like ' . $default_values[ 'wishlist_button_padding_left_right__' . $option_suffix ],
				'default'    => $default_values[ 'wishlist_button_padding_left_right__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-field-tight-top-bottom-margin
                wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			// icon list
			array(
				'id'         => 'addwishlist_button_icon__' . $option_suffix,
				'attributes' => array(
					'id' => 'addwishlist_button_icon__' . $option_suffix,
				),
				'type'       => 'select',
				'title'      => '"' . $button_name . '" button icon',
				'options'    => $svg_icons_options,
				'default'    => $default_values[ 'addwishlist_button_icon__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control svg_icon_drop',
				'wrap_class' => 'no-border-bottom wldcfwc-addwishlist_button_icon__' . $option_suffix . '
                wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			// upload icon
			array(
				'id'         => 'addwishlist_button_icon_image_upload__' . $option_suffix,
				'type'       => 'image',
				'title'      => '"' . $button_name . '" custom icon',
				'after'      => esc_html__( 'Upload an icon (32px x 32px is recommended)', 'wishlist-dot-com-for-woocommerce' ),
				'class'      => 'wldcfwc-form-control wldcfwc-form-control-image',
				'wrap_class' => 'no-border-bottom wldcfwc-addwishlist_button_icon_image_upload__' . $option_suffix . ' wldcfwc-hide',
			),

			// icon style
			array(
				'id'         => 'wishlist_icon_fill_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => ' <br>' . esc_html__( 'Icon color', 'wishlist-dot-com-for-woocommerce' ),
				'after'     => '(empty for default)',
				'default'    => $default_values[ 'wishlist_icon_fill_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_icon_color__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_icon_width__' . $option_suffix,
				'type'       => 'range',
				'min'        => 10,
				'max'        => 35,
				'step'       => 1,
				'before'     => esc_html__( 'Icon width (px)', 'wishlist-dot-com-for-woocommerce' ) . '<br><div style="float:left">' . esc_html__( 'small', 'wishlist-dot-com-for-woocommerce' ) . '</div><div style="float:right">' . esc_html__( 'large', 'wishlist-dot-com-for-woocommerce' ) . '</div>',
				'after'      => esc_html__( 'like ', 'wishlist-dot-com-for-woocommerce' ) . $default_values[ 'wishlist_icon_width__' . $option_suffix ],
				'default'    => $default_values[ 'wishlist_icon_width__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_icon_left_pos__' . $option_suffix,
				'type'       => 'range',
				'min'        => -160,
				'max'        => 160,
				'step'       => 1,
				'before'     => esc_html__( 'Icon left/right position (px)', 'wishlist-dot-com-for-woocommerce' ) . ' <br><div style="float:left">' . esc_html__( 'left', 'wishlist-dot-com-for-woocommerce' ) . '</div><div style="float:right">' . esc_html__( 'right', 'wishlist-dot-com-for-woocommerce' ) . '</div>',
				'after'      => esc_html__( 'like ', 'wishlist-dot-com-for-woocommerce' ) . $default_values[ 'wishlist_icon_left_pos__' . $option_suffix ],
				'default'    => $default_values[ 'wishlist_icon_left_pos__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_icon_top_pos__' . $option_suffix,
				'type'       => 'range',
				'min'        => -35,
				'max'        => 35,
				'step'       => 1,
				'before'     => esc_html__( 'Icon up/down position (px)', 'wishlist-dot-com-for-woocommerce' ) . ' <br><div style="float:left">' . esc_html__( 'up', 'wishlist-dot-com-for-woocommerce' ) . '</div><div style="float:right">' . esc_html__( 'down', 'wishlist-dot-com-for-woocommerce' ) . '</div>',
				'after'      => esc_html__( 'like ', 'wishlist-dot-com-for-woocommerce' ) . $default_values[ 'wishlist_icon_top_pos__' . $option_suffix ],
				'default'    => $default_values[ 'wishlist_icon_top_pos__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_icon_margin_right__' . $option_suffix,
				'type'       => 'text',
				'before'     => '<br>' . esc_html__( 'Icon margin right (px)', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => esc_html__( 'like 0, 10, 15', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_icon_margin_right__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			array(
				'type'       => 'content',
				'content'    => '',
				'wrap_class' => 'wldcfwc_content_new_line
                --wldcfwc-show_hide_icon_style__' . $option_suffix . ' --wldcfwc-show_hide_icon_color__' . $option_suffix . ' --wldcfwc-show_hide_button_position__' . $option_suffix . ' --wldcfwc-hide',
			),

			array(
				'id'         => 'wishlist_button_margin_left__' . $option_suffix,
				'type'       => 'range',
				'min'        => -200,
				'max'        => 200,
				'step'       => 1,
				'before'     => esc_html__( 'Button left/right position (px)', 'wishlist-dot-com-for-woocommerce' ) . '<br><div style="float:left">' . esc_html__( 'left', 'wishlist-dot-com-for-woocommerce' ) . '</div><div style="float:right">' . esc_html__( 'right', 'wishlist-dot-com-for-woocommerce' ) . '</div>',
				'after'      => esc_html__( 'like 0, 80, -70', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_margin_left__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field 
                wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			array(
				'id'         => 'wishlist_button_margin_top__' . $option_suffix,
				'type'       => 'range',
				'min'        => -200,
				'max'        => 200,
				'step'       => 1,
				'before'     => esc_html__( 'Button up/down position (px)', 'wishlist-dot-com-for-woocommerce' ) . '<br><div style="float:left">' . esc_html__( 'up', 'wishlist-dot-com-for-woocommerce' ) . '</div><div style="float:right">' . esc_html__( 'down', 'wishlist-dot-com-for-woocommerce' ) . '</div>',
				'after'      => esc_html__( 'like 0, 80, -70', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_button_margin_top__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),

			array(
				'id'         => 'wishlist_icon_button_padding_top_bottom__' . $option_suffix,
				'type'       => 'text',
				'before'     => esc_html__( 'Button top/bottom padding (px)', 'wishlist-dot-com-for-woocommerce' ) . '<br>' . esc_html__( '(empty is unchanged)', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => esc_html__( 'like 16px or leave empty', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_icon_button_padding_top_bottom__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_button_padding_style__' . $option_suffix . ' off--wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'         => 'wishlist_icon_button_padding_left_right__' . $option_suffix,
				'type'       => 'text',
				'before'     => esc_html__( 'Button left/right padding (px)', 'wishlist-dot-com-for-woocommerce' ) . '<br>' . esc_html__( '(empty is unchanged)', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => esc_html__( 'like 22px or leave empty', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wishlist_icon_button_padding_left_right__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                wldcfwc-show_hide_icon_button_padding_style__' . $option_suffix . ' off--wldcfwc-show_hide_icon_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
			array(
				'id'          => 'wishlist_button_margin__' . $option_suffix,
				'type'        => 'text',
				'title'       => '"' . $button_name . '" button margin',
				'default'     => $default_values[ 'wishlist_button_margin__' . $option_suffix ],
				'after'       => esc_html__( '(optional)', 'wishlist-dot-com-for-woocommerce' ),
				'description' => esc_html__( 'Optional. CSS "margin:" setting, like "8px 8px 8px 8px"', 'wishlist-dot-com-for-woocommerce' ),
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom
                wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide',
			),
		);

		// add additional fields like copy_style radios and tooltip
		$option_copy_style_a            = array( 'hp_findwishlist', 'hp_mywishlist', 'product_loop', 'cart' );
		$option_tooltip_a               = array( 'hp_createwishlist', 'hp_findwishlist', 'hp_mywishlist', 'product_page', 'product_loop', 'cart' );
		$suffix_specific_section_fields = array();
		foreach ( $base_section_fields as $index => $field ) {

			// add the base field to the $suffix_specific_section_fields. $suffix_specific_section_fields is added after a base field is identified
			$suffix_specific_section_fields[] = $field;

			if ( in_array( $option_suffix, $option_copy_style_a ) ) {

				// add a radio button to use the preceding button's style
				// flag section html insert
				if ( in_array( $option_suffix, array( 'hp_findwishlist', 'hp_mywishlist' ) ) ) {
					$copy_style_button_name = esc_html__( 'Create a WishList button', 'wishlist-dot-com-for-woocommerce' );
				} elseif ( in_array( $option_suffix, array( 'product_loop', 'cart' ) ) ) {
					$copy_style_button_name = esc_html__( 'Add to WishList button', 'wishlist-dot-com-for-woocommerce' );
				}

				if ( isset( $field['id'] ) && 'wishlist_button_text__' . $option_suffix == $field['id'] ) {

					// add the radio after the correct base field from above
					$suffix_specific_section_fields[] =
						array(
							'id'         => 'copy_style__' . $option_suffix,
							'type'       => 'radio',
							'id_attr'    => 'copy_style__' . $option_suffix,
							'title'      => esc_html__( 'Same style as the product page', 'wishlist-dot-com-for-woocommerce' ) . ' "' . $copy_style_button_name . '"',
							'options'    => array(
								'yes_str' => 'Yes',
								'no_str'  => 'No',
							),
							'default'    => $default_values[ 'copy_style__' . $option_suffix ],
							'class'      => '',
							'style'      => 'fancy',
							'wrap_class' => 'no-border-bottom',
						);

					$suffix_specific_section_fields[] =
						array(
							'id'   => 'begin_copy_style__' . $option_suffix,
							'name' => 'begin_copy_style__' . $option_suffix,
							'type' => 'html',
							'html' => '<div id="wldcfwc_section__copy_style_' . $option_suffix . '">',
						);

				}

				if ( isset( $field['id'] ) && 'wishlist_button_margin__' . $option_suffix == $field['id'] ) {
					// end copy_style radio
					$suffix_specific_section_fields[] =
						array(
							'type' => 'html',
							'html' => '</div><!-- end radio div -->',
						);
				}
			}
			if ( in_array( $option_suffix, $option_tooltip_a ) ) {
				// the tooltip text box
				if ( isset( $field['id'] ) && 'wishlist_button_margin__' . $option_suffix == $field['id'] ) {
					$suffix_specific_section_fields[] =
						array(
							'id'          => 'button_tooltip__' . $option_suffix,
							'type'        => 'textarea',
							'title'       => esc_html__( 'Tooltip for hover over', 'wishlist-dot-com-for-woocommerce' ) . ' "' . $button_name . '" button',
							'default'     => $default_values[ 'button_tooltip__' . $option_suffix ],
							'description' => '',
							'attributes'  => array(
								'rows' => '3',
							),
							'class'       => ' wldcfwc-form-control wldcfwc-text_area',
							'wrap_class'  => 'no-border-bottom
                            wldcfwc-show_hide_button_position__' . $option_suffix . '--off wldcfwc-hide--off',
						);
				}
			}
		}

		// end button position show/hide div
		$suffix_specific_section_fields[] =
			array(
				'type' => 'html',
				'html' => '</div><!-- end position div -->',
			);

		$section_fields = $suffix_specific_section_fields;

		foreach ( $section_fields as $index => $field ) {
			if ( isset( $field['id'] ) && ! isset( $field['name'] ) ) {
				$section_fields[ $index ]['name'] = $field['id'];
			}
		}

		// exclude fields from some types of button configurations
		if ( ! empty( $omit_fields ) ) {
			foreach ( $section_fields as $index => $field ) {
				foreach ( $omit_fields as $omit_field ) {
					if ( isset( $field['id'] ) && strpos( $field['id'], $omit_field ) === 0 ) {
						unset( $section_fields[ $index ] );
					}
				}
			}
		}

		// adjust fields for some types of button configurations
		if ( ! empty( $search_replace ) ) {
			foreach ( $section_fields as $index => $field ) {
				foreach ( $search_replace as $sr ) {
					if ( isset( $field['id'] ) && strpos( $field['id'], $sr['id'] ) === 0 ) {
						if ( isset( $section_fields[ $index ][ $sr['in_attr'] ] ) ) {
							$section_fields[ $index ][ $sr['in_attr'] ] = str_replace( $sr['find'], $sr['replace'], $section_fields[ $index ][ $sr['in_attr'] ] );
						}
					}
				}
			}
		}

		return $section_fields;
	}

	/**
	 * Shortcut for calling get_admin_fields__button() for the WishList homepage buttons
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_admin_fields__hp_button( $params ) {
		// 'in_attr' => 'wrap_class','find' => 'wldcfwc-no-border-top','replace' => 'wldcfwc-content-border-top'
		$params['omit_fields']    = array( 'section_title__', 'wishlist_button_position__', 'button_shortcode__', 'browse_wishlist_text__', 'item_alread_added_text__', 'item_added_text__', 'wishlist_button_margin_left__', 'wishlist_button_margin_top__' );
		$params['search_replace'] = array(
			array(
				'id'      => 'addwishlist_button_icon__',
				'in_attr' => 'wrap_class',
				'find'    => 'wldcfwc-hide',
				'replace' => 'wldcfwc-hide--off',
			),
			array(
				'id'      => 'addwishlist_button_icon_image_upload__',
				'in_attr' => 'wrap_class',
				'find'    => 'wldcfwc-hide',
				'replace' => 'wldcfwc-hide--off',
			),
			array(
				'id'      => 'button_tooltip__',
				'in_attr' => 'wrap_class',
				'find'    => 'wldcfwc-hide',
				'replace' => 'wldcfwc-hide--off',
			),
			array(
				'id'      => 'wishlist_button_margin__',
				'in_attr' => 'wrap_class',
				'find'    => 'wldcfwc-hide',
				'replace' => 'wldcfwc-hide--off',
			),
		);
		$section_fields = $this->wldcfwc_get_admin_fields__button( $params );

		return $section_fields;
	}

	/**
	 * Get buttons used to configure WishList.com's api presentation of a store's wishlist
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_admin_fields__wishlist_page_buttons( $params ) {
		$option_suffix  = $params['option_suffix'];
		$default_values = $params['default_values'];

		if ( 'share_button' == $option_suffix ) {
			$befor_prefix = 'Share button';
		} elseif ( 'primary_button' == $option_suffix ) {
			$befor_prefix = 'Primary button';
		} elseif ( 'success_button' == $option_suffix ) {
			$befor_prefix = 'Success button';
		} elseif ( 'danger_button' == $option_suffix ) {
			$befor_prefix = 'Danger button';
		}

		$section_fields = array(
			array(
				'type'       => 'content',
				'content'    => '',
				'wrap_class' => 'wldcfwc_content_new_line
                    wldcfwc-show_hide_button_style__' . $option_suffix . ' wldcfwc-show_hide_button_position__' . $option_suffix . ' wldcfwc-hide--off',
			),

			array(
				'id'         => 'wlcom_wishlist_button_background_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => $befor_prefix . esc_html__( 'background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_background_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                ',
			),
			array(
				'id'         => 'wlcom_wishlist_button_text_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => $befor_prefix . esc_html__( 'text color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_text_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                ',
			),
			array(
				'id'         => 'wlcom_wishlist_button_border_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => $befor_prefix . esc_html__( 'border color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_border_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                ',
			),
			array(
				'id'         => 'wlcom_wishlist_button_background_color_hover__' . $option_suffix,
				'type'       => 'color',
				'before'     => $befor_prefix . esc_html__( 'Hover background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_background_color_hover__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                ',
			),
			array(
				'id'         => 'wlcom_wishlist_button_text_color_hover__' . $option_suffix,
				'type'       => 'color',
				'before'     => $befor_prefix . esc_html__( 'Hover text color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_text_color_hover__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                ',
			),
			array(
				'id'         => 'wlcom_wishlist_button_border_color_hover__' . $option_suffix,
				'type'       => 'color',
				'before'     => $befor_prefix . esc_html__( 'hover border color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_border_color_hover__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field
                ',
			),
		);

		if ( 'share_button' == $option_suffix ) {
			$share_text_a[] = array(
				'id'         => 'wlcom_wishlist_button_text__' . $option_suffix,
				'type'       => 'text',
				'before'     => esc_html__( 'Button text', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_wishlist_button_text__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field wldcfwc-field-tight-top-bottom-margin',
			);
			$share_text_a[] = array(
				'type'       => 'content',
				'content'    => '',
				'wrap_class' => 'wldcfwc_content_new_line',
			);
			$section_fields = array_merge( $share_text_a, $section_fields );
		}

		return $section_fields;
	}

	/**
	 * Get fields for the admin panel's email configuration
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_admin_fields__emails( $params ) {
		$default_values = $params['default_values'];
		$section_title  = $params['section_title'];
		$option_suffix  = $params['option_suffix'];
		$options        = $params['options'];

		$select_options[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ] = array();
		if ( isset( $options[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ] ) ) {
			// not storing id => text, just id which is passed as array of strings
			if ( is_array( $options[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ] ) ) {
				foreach ( $options[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ] as $item ) {
					$select_options[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ][ $item ] = $item;
				}
			}
		}
		$select_options[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ] = array();
		if ( isset( $options[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ] ) ) {
			if ( is_array( $options[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ] ) ) {
				// not storing id => text, just id which is passed as array of strings
				foreach ( $options[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ] as $item ) {
					// concactenate key^text so it's stored together so we don't have to do a query to display later
					if ( ! empty( $item ) ) {
						$item_a = explode( '^', $item );
						if ( isset( $item_a[1] ) ) {
							$item_name = $item_a[1];
						} else {
							$item_name = $item_a[0];
						}
						$select_options[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ][ $item ] = $item_a[1];
					}
				}
			}
		}

		if ( ! empty( $params['omit_fields'] ) ) {
			$omit_fields = $params['omit_fields'];
		}

		$font_size_dropdown   = $this->wldcfwc_get_front_size_dropdown();
		$font_size_h_dropdown = $font_size_dropdown;

		if ( 'email_template' != $option_suffix ) {
			$hide_css = 'wldcfwc-hide';
		} else {
			$hide_css = '';
		}

		$email_content_placeholders     = '';
		$email_subject_placeholders     = '';
		$email_header_text_placeholders = '';
		$description                    = '';
		if ( 'email_template' == $option_suffix ) {
			$description = esc_html__( 'This is the default email template used by WishList.com when sending your store\'s WishList emails.', 'wishlist-dot-com-for-woocommerce' );
		} elseif ( 'welcome_email' == $option_suffix ) {
			$description                = esc_html__( 'This is the welcome email sent to those who create a new WishList on your store.', 'wishlist-dot-com-for-woocommerce' );
			$email_content_placeholders = 'Placeholders: {user_name} {user_first_name} {user_last_name} {user_email} {user_wishlist_url} {find_wishlists_url}';
		} elseif ( 'empty_wishlist_reminder' == $option_suffix ) {
			$description                = esc_html__( 'This is the notice email sent to the WishList maker when they\'ve created a WishList but haven\'t added anything to it yet. It\'s sent out just once, a few days after making a new wishlist.', 'wishlist-dot-com-for-woocommerce' );
			$email_content_placeholders = 'Placeholders: {user_first_name} {user_wishlist_url} {user_wishlist_name} {email_preferences_url}';
		} elseif ( 'reserved_conf' == $option_suffix ) {
			$description                    = esc_html__( 'This is the confirmation email sent to those who reserve an item to buy as a gift off someone\'s wishlist.', 'wishlist-dot-com-for-woocommerce' );
			$email_content_placeholders     = 'Placeholders: {reserver_first_name} {reserved_quantity} {purchase_wish_url} {recipient_name} {recipient_profile_image_and_name} {wishes_display_table} {edit_reservation_url}';
			$email_subject_placeholders     = 'Placeholders: {recipient_name}';
			$email_header_text_placeholders = $email_subject_placeholders;
		} elseif ( 'reserved_notice' == $option_suffix ) {
			$description                = esc_html__('This is the notice email sent to the WishList maker when someone reserves a gift off their wishlist. It\'s sent only if the WishList maker configured their email preferences to receive it.', 'wishlist-dot-com-for-woocommerce' );
			$email_content_placeholders = 'Placeholders: {user_first_name} {user_wishlist_url} {user_wishlist_name} {email_preferences_url}';
		} elseif ( 'wish_on_sale' == $option_suffix ) {
			$description                = esc_html__( 'This is the notice email sent to the WishList maker when an item in their WishList goes on sale.', 'wishlist-dot-com-for-woocommerce' );
			$email_content_placeholders = 'Placeholders: {user_first_name} {user_wishlist_url} {user_wishlist_name} {wishes_display_table} {email_preferences_url}';
		} elseif ( 'wish_in_stock' == $option_suffix ) {
			$description                = esc_html__( 'This is the notice email sent to the WishList maker when an item in their WishList is back in stock.', 'wishlist-dot-com-for-woocommerce' );
			$email_content_placeholders = 'Placeholders: {user_first_name} {user_wishlist_url} {user_wishlist_name} {wishes_display_table} {email_preferences_url}';
			$email_content_placeholders = 'Placeholders: {user_first_name} {user_wishlist_url} {user_wishlist_name} {wishes_display_table} {email_preferences_url}';
		}

		$section_fields = array(
			array(
				'id'                => 'section_title__' . $option_suffix,
				'type'              => 'content',
				'content'           => $section_title,
				'description'       => $description,
				'wrap_class'        => 'exopite-sof-fieldgroup-title no-border-bottom wldcfwc-no-border-top',
				'description_class' => 'wldcfwc-content-description',
			),

			array(
				'id'         => 'wlcom_email_conf__enable_email__' . $option_suffix,
				'type'       => 'radio',
				'title'      => esc_html__( 'Enable this email', 'wishlist-dot-com-for-woocommerce' ),
				'options'    => array(
					'yes_str' => esc_html__( 'Enabled', 'wishlist-dot-com-for-woocommerce' ),
					'no_str'  => esc_html__( 'Disabled', 'wishlist-dot-com-for-woocommerce' ),
				),
				'default'    => $default_values[ 'wlcom_email_conf__enable_email__' . $option_suffix ],
				'class'      => '',
				'style'      => 'fancy',
				'wrap_class' => 'no-border-bottom',
			),

			array(
				'id'          => 'wlcom_email_conf__reply_to_name__' . $option_suffix,
				'type'        => 'text',
				'title'       => esc_html__( 'Reply to name', 'wishlist-dot-com-for-woocommerce' ),
				'default'     => $default_values[ 'wlcom_email_conf__reply_to_name__' . $option_suffix ],
				'description' => '',
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom',
			),

			array(
				'id'          => 'wlcom_email_conf__reply_to_address__' . $option_suffix,
				'type'        => 'text',
				'title'       => esc_html__( 'Reply to email address', 'wishlist-dot-com-for-woocommerce' ),
				'default'     => $default_values[ 'wlcom_email_conf__reply_to_address__' . $option_suffix ],
				'description' => '',
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom',
			),

			array(
				'id'         => 'wlcom_email_conf__exclude_product_categories__' . $option_suffix,
				'type'       => 'select',
				'title'      => esc_html__( 'Exclude product categories', 'wishlist-dot-com-for-woocommerce' ),
				'before'     => esc_html__( 'Select categories to exclude', 'wishlist-dot-com-for-woocommerce' ),
				'attributes' => array(
					'data-select2-category' => 'yes',
					'multiple'              => true,
				),
				'options'    => $select_options[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ],
				'class'      => 'choice--off select2-selection--multiple wldcfwc-form-control',
				'style'      => 'fancy--off',
				'wrap_class' => 'select2-container--bootstrap-5--off wldcfwc-form-select2 no-border-bottom',
			),
			array(
				'id'           => 'wlcom_email_conf__exclude_product_categories__' . $option_suffix . '__hidden_select2',
				'type'         => 'hidden',
				'hidden_value' => '',
			),

			array(
				'id'         => 'wlcom_email_conf__exclude_product_skus__' . $option_suffix,
				'type'       => 'select',
				'title'      => esc_html__( 'Exclude products', 'wishlist-dot-com-for-woocommerce' ),
				'before'     => esc_html__( 'Select products to exclude', 'wishlist-dot-com-for-woocommerce' ),
				'attributes' => array(
					'data-select2-sku' => 'yes',
					'multiple'         => true,
				),
				'options'    => $select_options[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ],
				'class'      => 'choice--off select2-selection--multiple wldcfwc-form-control',
				'style'      => 'fancy--off',
				'wrap_class' => 'select2-container--bootstrap-5--off wldcfwc-form-select2 no-border-bottom',
			),
			array(
				'id'           => 'wlcom_email_conf__exclude_product_skus__' . $option_suffix . '__hidden_select2',
				'type'         => 'hidden',
				'hidden_value' => '',
			),

			array(
				'id'          => 'wlcom_email_conf__subject__' . $option_suffix,
				'type'        => 'text',
				'title'       => esc_html__( 'Email subject', 'wishlist-dot-com-for-woocommerce' ),
				'after'       => $email_subject_placeholders,
				'default'     => $default_values[ 'wlcom_email_conf__subject__' . $option_suffix ],
				'description' => '',
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom
                wldcfwc-show_hide_button_position__' . $option_suffix,
			),

			array(
				'id'          => 'wlcom_email_conf__header_text__' . $option_suffix,
				'type'        => 'text',
				'title'       => esc_html__( 'Email header text', 'wishlist-dot-com-for-woocommerce' ),
				'after'       => $email_subject_placeholders,
				'default'     => $default_values[ 'wlcom_email_conf__header_text__' . $option_suffix ],
				'description' => '',
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom
                wldcfwc-show_hide_button_position__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__html__' . $option_suffix,
				'type'       => 'textarea',
				'title'      => esc_html__( 'Email content', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => $email_content_placeholders,
				'attributes' => array(
					'rows' => '5',
				),
				'default'    => $default_values[ 'wlcom_email_conf__html__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control wldcfwc-text_area-html',
				'wrap_class' => 'no-border-bottom',
			),

			array(
				'id'         => 'wlcom_email_conf__use_custom_template__' . $option_suffix,
				'type'       => 'radio',
				'title'      => esc_html__( 'Email template', 'wishlist-dot-com-for-woocommerce' ),
				'options'    => array(
					'email_template' => esc_html__( 'Use default WishList email template', 'wishlist-dot-com-for-woocommerce' ),
					'custom_email'   => esc_html__( 'Customize email', 'wishlist-dot-com-for-woocommerce' ),
				),
				'default'    => $default_values[ 'wlcom_email_conf__use_custom_template__' . $option_suffix ],
				'class'      => '',
				'style'      => 'fancy',
				'wrap_class' => 'no-border-bottom wldcfwc-use_email_template',
			),

			array(
				'id'          => 'wlcom_email_conf__header_image__' . $option_suffix,
				'type'        => 'image',
				'title'       => esc_html__( 'Email header image', 'wishlist-dot-com-for-woocommerce' ),
				'description' => esc_html__( 'This header image goes directly above the header text.', 'wishlist-dot-com-for-woocommerce' ),
				'default'     => $default_values[ 'wlcom_email_conf__header_image__' . $option_suffix ],
				'class'       => 'wldcfwc-form-control',
				'wrap_class'  => 'no-border-bottom ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'          => 'wlcom_email_conf__header_image_max_width__' . $option_suffix,
				'type'        => 'text',
				'title'       => esc_html__( 'Email header max image width', 'wishlist-dot-com-for-woocommerce' ),
				'after'       => esc_html__( 'like ', 'wishlist-dot-com-for-woocommerce' ) . $default_values[ 'wlcom_email_conf__header_image_max_width__' . $option_suffix ],
				'default'     => $default_values[ 'wlcom_email_conf__header_image_max_width__' . $option_suffix ],
				'description' => esc_html__( 'This is the max width of the header image. It can be smaller', 'wishlist-dot-com-for-woocommerce' ),
				'class'       => 'wldcfwc-form-control wldcfwc-short_text_field',
				'wrap_class'  => 'no-border-bottom ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__header_font_size__' . $option_suffix,
				'type'       => 'select',
				'before'     => esc_html__( 'Header font size', 'wishlist-dot-com-for-woocommerce' ),
				'attributes' => array(
					'id' => 'wlcom_email_conf__header_font_size__' . $option_suffix,
				),
				'options'    => $font_size_dropdown,
				'default'    => $default_values[ 'wlcom_email_conf__header_font_size__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'style'      => 'fancy',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__header_background_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Header background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__header_background_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__header_text_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Header text color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__header_text_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__background_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Email border color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__background_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__body_background_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Email body background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__body_background_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__body_text_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Email body text color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__body_text_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__link_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Email link text color', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => esc_html__( 'leave blank for default color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__link_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__link_decoration__' . $option_suffix,
				'type'       => 'text',
				'before'     => esc_html__( 'Email link css text-decoration', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__link_decoration__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__footer_background_color__' . $option_suffix,
				'type'       => 'color',
				'before'     => esc_html__( 'Email footer background color', 'wishlist-dot-com-for-woocommerce' ),
				'default'    => $default_values[ 'wlcom_email_conf__footer_background_color__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control',
				'wrap_class' => 'no-border-bottom wldcfwc-inline-field ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),

			array(
				'id'         => 'wlcom_email_conf__footer_html__' . $option_suffix,
				'type'       => 'textarea',
				'title'      => esc_html__( 'Email footer', 'wishlist-dot-com-for-woocommerce' ),
				'after'      => esc_html__( 'Placeholders: {email_preferences_url} {unsubscribe_url} {privacy_url}', 'wishlist-dot-com-for-woocommerce' ),
				'attributes' => array(
					'rows' => '5',
				),
				'default'    => $default_values[ 'wlcom_email_conf__footer_html__' . $option_suffix ],
				'class'      => 'wldcfwc-form-control wldcfwc-text_area-html',
				'wrap_class' => 'no-border-bottom ' . $hide_css . ' wldcfwc-show_hide_email_custom_template__' . $option_suffix,
			),
		);

		foreach ( $section_fields as $index => $field ) {
			if ( isset( $field['id'] ) && ! isset( $field['name'] ) ) {
				$section_fields[ $index ]['name'] = $field['id'];
			}
			// "enable" is not an option for email_template
			if ( 'email_template' == $option_suffix && isset( $field['id'] ) && strpos( $field['id'], 'wlcom_email_conf__enable_email__' ) === 0 ) {
				unset( $section_fields[ $index ] );
			}
		}

		// exclude some fields for some email configutions
		if ( ! empty( $omit_fields ) ) {
			foreach ( $section_fields as $index => $field ) {
				foreach ( $omit_fields as $omit_field ) {
					if ( isset( $field['id'] ) && str_contains( $field['id'], $omit_field . '__' . $option_suffix ) ) {
						unset( $section_fields[ $index ] );
					}
				}
			}
		}

		// insert premium sections used by WishList.com's api
		$return_a = array();
		$cnt      = 0;
		foreach ( $section_fields as $key => $feild ) {

			$return_a[ $cnt ] = $feild;
			++$cnt;

		}

		return $return_a;
	}

	/**
	 * For font options within admin panel
	 *
	 * @param  $mode
	 * @return string[]
	 */
	public function wldcfwc_get_front_size_dropdown( $mode = 'title,text' ) {
		$font_size_dropdown = array();
		if ( str_contains( $mode, 'title' ) ) {
			$font_size_dropdown['font_size_h1'] = 'H1 title';
			$font_size_dropdown['font_size_h2'] = 'H2 title';
			$font_size_dropdown['font_size_h3'] = 'H3 title';
			$font_size_dropdown['font_size_h4'] = 'H4 title';
			$font_size_dropdown['font_size_h5'] = 'H5 title';
			$font_size_dropdown['font_size_h6'] = 'H6 title';
			// $font_size_dropdown['font_size_h7'] = 'H7 title';
		}
		if ( str_contains( $mode, 'text' ) ) {
			$font_size_dropdown['font_size_normal_bold'] = 'Normal bold';
			$font_size_dropdown['font_size_normal']      = 'Normal';
			$font_size_dropdown['font_size_medium']      = 'Medium';
			$font_size_dropdown['font_size_small']       = 'Small';
		}
		return $font_size_dropdown;
	}

	/**
	 * Get the default values to be used within the admin panel and wldcfwc_get_options()
	 *
	 * @param  $mode
	 * @return array|string[]
	 */
	public function wldcfwc_get_field_defaults__options( $mode = '' ) {

		$wlcom_your_store_name = get_option( 'woocommerce_store_name' ) ? get_option( 'woocommerce_store_name' ) : get_bloginfo( 'name' );
		if ( empty( $wlcom_your_store_name ) && isset( $_SERVER['HTTP_HOST'] ) ) {
			$wlcom_your_store_name = sanitize_text_field( $_SERVER['HTTP_HOST'] );
		}

		$wlcom_admin_email = '';
		$logo_url = $this->wldcfwc_get_store_logo_url();
		$icon_url = '';

		$wlcom_your_store_description = get_bloginfo( 'description' );

		$hp_banner_img = '';

		$store_id_a           = wldcfwc_store_id();
		//wlcom_your_store_url is default option that's not in admin panel but is merged with saved options
		$wlcom_your_store_url = $store_id_a[ 'store_url' ];
		//wlcom_store_shop_url is default option that's not in admin panel but is merged with saved options
		$wlcom_store_shop_url = $store_id_a[ 'store_url_no_scheme' ];
		$store_domain_path    = $wlcom_store_shop_url;

		$wl_hp_page_id    = get_option( WLDCFWC_WISHLIST_HOMEPAGE_PAGE_OPTION );
		$wl_hp_page_title = get_the_title( $wl_hp_page_id );
		$wl_hp_url        = get_permalink( $wl_hp_page_id );
		$menu_items       = array(
			'Cart'       => wc_get_cart_url(),
			'Checkout'   => wc_get_checkout_url(),
			'My Account' => wc_get_page_permalink( 'myaccount' ),
			'Shop'       => wc_get_page_permalink( 'shop' ),
		);
		if ( ! empty( $wl_hp_page_title ) && ! empty( $wl_hp_url ) ) {
			$menu_items[ $wl_hp_page_title ] = $wl_hp_url;
		}
		$wlcom_store_top_menu_links_json = wp_json_encode( $menu_items );

		$default_vals = array();

		//For security reason, options saved are limited to those with default values.
		//This means all options, including "special keys", must have a default.
		$default_option_vals = array(
			'wlcom_plgn_plugin_display_status'            => 'status_live',

			'wlcom_website_is_featured'                   => 'no_str',
			'wlcom_your_store_name'                       => $wlcom_your_store_name,
			'wlcom_admin_email'                           => $wlcom_admin_email,
			'wlcom_plgn_your_store_logo'                  => $logo_url,
			'wlcom_plgn_your_store_icon'                  => $icon_url,
			'wlcom_your_store_url'                        => $wlcom_your_store_url,
			'wlcom_your_store_description'                => $wlcom_your_store_description,
			'wlcom_store_shop_url'                        => $wlcom_store_shop_url,
			'wlcom_featured_product_skus'                 => array(),
			'existing_featured_product_skus'              => array(),
			'wlcom_list_on_wishlistdotcom'                => 'no_str',
			'wlcom_wl_template_url'                       => '',
			'wlcom_wl_homepage_url'                       => '',

			'wlcom_your_store_categories'                 => '',
			'wlcom_your_store_tags'                       => '',

			'wlcom_wishlist_layout'                       => 'wishlist_layout_grid',

			'wlcom_allow_multiple_wishlists'              => 'yes_str',
			'wlcom_wishlist_header_color'                 => '#FFFFFF',
			'wlcom_wishlist_header_font_color'            => '#333333',
			'wlcom_empty_wishlist_prompt'                 => esc_html__( 'Your WishList is empty. Shop and use the "Add to WishList" buttons to add items you like to your WishList.', 'wishlist-dot-com-for-woocommerce' ),
			'wlcom_empty_wishlist_shop_button'            => esc_html__( 'Shop now...', 'wishlist-dot-com-for-woocommerce' ),
			'wlcom_wishlist_icon_background_color__gift'  => '#9f9f9f',
			'wlcom_allow_profile_image'                   => 'yes_str',
			'wlcom_profile_initials_background_color'     => '',
			'wlcom_allow_wishlist_banner'                 => 'no_str',

			'wlcom_allow_follow'                          => 'no_str',
			'wlcom_allow_friends'                         => 'no_str',
			'wlcom_allow_comments'                        => 'no_str',
			'add2wishlist_window'                         => 'main_window',
			'show_add2wishlist_button_spinner'            => 'yes_str',
			'add2wishlist_button_spinner_color'           => '#FFF',
			'add2wishlist_button_spinner_height'          => '30px',
			//'wlcom_powered_by_wishlistdotcom_header_prompt' => '{store_favicon}' . $wlcom_your_store_name . '\'s WishList is by WishList.com - one WishList for all stores.',
			'wlcom_powered_by_wishlistdotcom_header_prompt' => '',
			//'wlcom_about_wishlists_prompt'                => 'WishLists can include things you like, inspirational ideas, or gifts you\'d love for a special occasion.',
			'wlcom_about_wishlists_prompt'                => '',

			'wlcom_wishlist_button_background_color__share_button' => '#315D7C',
			'wlcom_wishlist_button_background_color__primary_button' => '#315D7C',
			'wlcom_wishlist_button_background_color__success_button' => '#427960',
			'wlcom_wishlist_button_background_color__danger_button' => '#6E4342',

			'wlcom_wishlist_button_text_color__share_button' => '#ffffff',
			'wlcom_wishlist_button_text_color__primary_button' => '#ffffff',
			'wlcom_wishlist_button_text_color__success_button' => '#ffffff',
			'wlcom_wishlist_button_text_color__danger_button' => '#ffffff',

			'wlcom_wishlist_button_border_color__share_button' => '#315D7C',
			'wlcom_wishlist_button_border_color__primary_button' => '#315D7C',
			'wlcom_wishlist_button_border_color__success_button' => '#427960',
			'wlcom_wishlist_button_border_color__danger_button' => '#6E4342',

			'wlcom_wishlist_button_background_color_hover__share_button' => '#264861',
			'wlcom_wishlist_button_background_color_hover__primary_button' => '#264861',
			'wlcom_wishlist_button_background_color_hover__success_button' => '#335f4b',
			'wlcom_wishlist_button_background_color_hover__danger_button' => '#533231',

			'wlcom_wishlist_button_text_color_hover__share_button' => '#ffffff',
			'wlcom_wishlist_button_text_color_hover__primary_button' => '#ffffff',
			'wlcom_wishlist_button_text_color_hover__success_button' => '#ffffff',
			'wlcom_wishlist_button_text_color_hover__danger_button' => '#ffffff',

			'wlcom_wishlist_button_border_color_hover__share_button' => '#264861',
			'wlcom_wishlist_button_border_color_hover__primary_button' => '#264861',
			'wlcom_wishlist_button_border_color_hover__success_button' => '#335f4b',
			'wlcom_wishlist_button_border_color_hover__danger_button' => '#533231',

			'wlcom_wishlist_button_text__share_button'    => esc_html__( 'Share WishList!', 'wishlist-dot-com-for-woocommerce' ),

			'wlcom_gift_received_button_background_color' => '#D67673',

			'wlcom_success_alert_background_color'        => '#d1e7dd',
			'wlcom_success_alert_text_color'              => '#0f5132',
			'wlcom_danger_alert_background_color'         => '#E6DCDC',
			'wlcom_danger_alert_text_color'               => '#813232',
			'wlcom_info_alert_background_color'           => '#DEEAF3',
			'wlcom_info_alert_text_color'                 => '#4d7c9f',

			'wishlist_hp_title'                           => WLDCFWC_WISHLIST_TITLE,
			'hp_alignment'                                => 'text_alignment_centered',
			'hp_banner_img'                               => $hp_banner_img,
			'hp_subtitle'                                 => esc_html__( 'A WishList for all occasions', 'wishlist-dot-com-for-woocommerce' ),
			'hp_subtitle_font_size'                       => 'font_size_h4',
			'hp_description'                              => esc_html__( 'WishLists can include things you like, inspirational ideas, or gifts you\'d love for a special occasion.', 'wishlist-dot-com-for-woocommerce' ),
			'hp_description_font_size'                    => 'font_size_h5',
			//'hp_footer'                                   => esc_html__( 'Your WishList is available on ', 'wishlist-dot-com-for-woocommerce' ) . $store_domain_path . __( ' and WishList.com. Share your WishLists with friends and family here, and at WishList.com.', 'wishlist-dot-com-for-woocommerce' ),
			'hp_footer'                                   => '',
			'hp_footer_font_size'                         => 'font_size_small',
			//'hp_show_poweredby'                           => 'yes_str',
			'hp_show_poweredby'                           => 'no_str',

			'tooltip_background_color'                    => '#FFFFFF',
			'tooltip_border_color'                        => '#E1E1E1',
			'tooltip_text_color'                          => '#2c352e',
			'tooltip_show_shadow'                         => 'yes_str',

			'js_theme_colors_merged_saved'                => 'no_str',
			'icon_src'                                    => '',
			'buttons_css'                                 => '',

			'service_level'                               => 'free',
			'api_key'                                     => '',
			'valid_api_key'                               => '',
			'initial_get_api_key_attempted'               => '',
			'api_key_deleted'				              => '',
			'store_uuid'                                  => '',
			'store_api_subdomain'                         => '',

			'wlcom_template_store_logo'                   => $logo_url,
			'wlcom_template_store_name'                   => $wlcom_your_store_name,
			'wlcom_store_top_menu_links_json'             => $wlcom_store_top_menu_links_json,
			'wlcom_wishlist_template_use_html__header'    => 'no_str',
			'wlcom_template_html__header'                 => '',
			'wlcom_wishlist_template_use_html__footer'    => 'no_str',
			'wlcom_template_html__footer'                 => '',

			'wlcom_header_template_store_name_style'      => '',
			'wlcom_header_template_menu_item_style'       => '',
			'wlcom_default_body_font_style'               => '',

			'wlcom_text_color'                            => '',
			'wlcom_font_size'                             => '',
			'wlcom_font_family'                           => '',

			'wlcom_template_store_name__text_color'       => '#222',
			'wlcom_header_template_menu_item__text_decoration' => 'none',
			'wlcom_header_template_menu_item__text_color' => '#222',
			'wlcom_header_template_menu_item__text_color_hover' => '#222',
			'wlcom_header_template_menu_item__text_font_size' => '16px',

			'wlcom_header_template_background_color'      => '#fff',
			'wlcom_header_template_top_bottom_padding'    => '16px',
			'wlcom_header_template_bottom_border_color'   => '#eaeaea',
			'wlcom_header_template_bottom_border_width'   => '1px',
			'wlcom_header_template_bottom_margin'         => '50px',
			'wlcom_header_template_sticky'                => 'no_str',
		);

		// add placeholders for button collor hidden fields
		$color_types = array( 'backgroundColor', 'color', 'borderColor' );
		foreach ( $color_types as $type ) {
			$default_option_vals[ 'primary_' . $type . '__regular' ] = '';
			$default_option_vals[ 'primary_' . $type . '__hover' ]   = '';
			$default_option_vals[ 'danger_' . $type . '__regular' ]  = '';
			$default_option_vals[ 'danger_' . $type . '__hover' ]    = '';
		}

		// add default for keeping same button style as previous button style
		$option_copy_style_a = array( 'hp_findwishlist', 'hp_mywishlist', 'product_loop', 'cart' );
		foreach ( $option_copy_style_a as $type ) {
			$default_option_vals[ 'copy_style__' . $type ] = 'yes_str';
		}

		$button_tooltip      = esc_html__( 'Save this item to your WishList', 'wishlist-dot-com-for-woocommerce' );
		$button_tooltip_cart = esc_html__( 'Save this cart to your WishList. ', 'wishlist-dot-com-for-woocommerce' ) . $button_tooltip;

		$params                       = array();
		$params['option_suffix']      = 'product_page';
		$params['button_position']    = 'wishlist_button_after_product_info';
		//$params['button_tooltip']     = $button_tooltip;
		$default_vals['product_page'] = $this->wldcfwc_get_field_defaults__button( $params );

		$default_option_vals = array_merge( $default_option_vals, $default_vals['product_page'] );

		$params                       = array();
		$params['option_suffix']      = 'product_loop';
		$params['button_position']    = 'wishlist_button_before_add_to_cart_button';
		//$params['button_tooltip']     = $button_tooltip;
		$default_vals['product_loop'] = $this->wldcfwc_get_field_defaults__button( $params );
		$default_option_vals          = array_merge( $default_option_vals, $default_vals['product_loop'] );

		$params                    = array();
		$params['option_suffix']   = 'cart';
		$params['button_position'] = 'wishlist_button_above_right';
		$params['button_margin']   = '8px 0px 8px 8px';
		//$params['button_tooltip']  = $button_tooltip_cart;
		$default_vals['cart']      = $this->wldcfwc_get_field_defaults__button( $params );
		$default_option_vals       = array_merge( $default_option_vals, $default_vals['cart'] );

		$params                        = array();
		$params['option_suffix']       = 'hp_mywishlist';
		$params['button_text']         = esc_html__( 'My WishList', 'wishlist-dot-com-for-woocommerce' );
		$default_vals['hp_mywishlist'] = $this->wldcfwc_get_field_defaults__button( $params );
		$default_option_vals           = array_merge( $default_option_vals, $default_vals['hp_mywishlist'] );

		$params                          = array();
		$params['option_suffix']         = 'hp_findwishlist';
		$params['button_text']           = esc_html__( 'Find a WishList', 'wishlist-dot-com-for-woocommerce' );
		$default_vals['hp_findwishlist'] = $this->wldcfwc_get_field_defaults__button( $params );
		$default_option_vals             = array_merge( $default_option_vals, $default_vals['hp_findwishlist'] );

		$params                            = array();
		$params['option_suffix']           = 'hp_createwishlist';
		$params['button_text']             = esc_html__( 'Create a WishList', 'wishlist-dot-com-for-woocommerce' );
		$default_vals['hp_createwishlist'] = $this->wldcfwc_get_field_defaults__button( $params );
		$default_option_vals               = array_merge( $default_option_vals, $default_vals['hp_createwishlist'] );

		if ( 'all' == $mode ) {
			$email_default_vals  = $this->wldcfwc_get_field_defaults__emails();
			$default_option_vals = array_merge( $default_option_vals, $email_default_vals );
		}

		// these keys are saved in their own fields
		$special_keys = $this->special_option_keys;
		foreach ( $special_keys as $special_key ) {
			$special_key_name = WLDCFWC_SLUG . '_' . $special_key;
			if ( ! empty( get_option( $special_key_name ) ) ) {
				$default_option_vals[ $special_key ] = get_option( $special_key_name );
			}
		}

		return $default_option_vals;
	}

	/**
	 * Get defaults for button fields
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_field_defaults__button( $params ) {
		$option_suffix = $params['option_suffix'];
		if ( isset( $params['button_text'] ) ) {
			$button_text = $params['button_text'];
		} else {
			$button_text = 'add to wishlist';
		}
		if ( isset( $params['button_tooltip'] ) ) {
			$button_tooltip = $params['button_tooltip'];
		} else {
			$button_tooltip = '';
		}
		if ( isset( $params['button_margin'] ) ) {
			$button_margin = $params['button_margin'];
		} else {
			$button_margin = '';
		}

		if ( isset( $params['button_position'] ) ) {
			$button_position = $params['button_position'];
		} else {
			$button_position = '';
		}

		if ( in_array( $option_suffix, array( 'hp_findwishlist', 'hp_mywishlist', 'product_loop', 'cart' ) ) ) {
			$button_style = 'copy_style';
		}
		if ( in_array( $option_suffix, array( 'hp_createwishlist', 'hp_findwishlist', 'hp_mywishlist' ) ) ) {
			$button_style = 'theme_button';
			$button_icon = 'none';
		} else {
			$button_style = 'text_link';
			$button_icon = 'heart';
		}

		if ( isset( $params['button_position'] ) ) {
			$button_position = $params['button_position'];
		} else {
			$button_position = '';
		}

		// $option_suffix
		$button_defaults = array(
			'wishlist_button_position__' . $option_suffix => $button_position,
			'javascript_button_position__' . $option_suffix => '',
			'wishlist_button_text__' . $option_suffix     => $button_text,
			'wishlist_button_style__' . $option_suffix    => $button_style,
			'wishlist_button_background_color__' . $option_suffix => '#0c64a2',
			'wishlist_button_text_color__' . $option_suffix => '#ffffff',
			'wishlist_button_border_color__' . $option_suffix => '#0c64a2',
			'wishlist_button_background_color_hover__' . $option_suffix => '#0a5082',
			'wishlist_button_text_color_hover__' . $option_suffix => '#ffffff',
			'wishlist_button_border_color_hover__' . $option_suffix => '#0a5082',
			'wishlist_button_border_radius__' . $option_suffix => '16px',
			'wishlist_button_padding_top_bottom__' . $option_suffix => '16px',
			'wishlist_button_padding_left_right__' . $option_suffix => '22px',

			'button_tooltip__' . $option_suffix           => $button_tooltip,
			'wishlist_button_margin__' . $option_suffix   => $button_margin,
			'item_added_text__' . $option_suffix          => esc_html__( 'Item added!', 'wishlist-dot-com-for-woocommerce' ),
			'browse_wishlist_text__' . $option_suffix     => esc_html__( 'Browse WishList', 'wishlist-dot-com-for-woocommerce' ),
			'item_alread_added_text__' . $option_suffix   => esc_html__( 'In your WishList', 'wishlist-dot-com-for-woocommerce' ),

			'addwishlist_button_icon__' . $option_suffix  => $button_icon,
			'wishlist_icon_fill_color__' . $option_suffix => '',
			'addwishlist_button_icon_image_upload__' . $option_suffix => '',

			// range, so 'px' string is added later
			'wishlist_icon_width__' . $option_suffix      => '17',
			'wishlist_icon_top_pos__' . $option_suffix    => '-4',
			'wishlist_icon_left_pos__' . $option_suffix   => '0',

			//10 for text link as default
			'wishlist_icon_margin_right__' . $option_suffix => '10',

			'wishlist_button_margin_left__' . $option_suffix => '',
			'wishlist_button_margin_top__' . $option_suffix => '',
			'wishlist_icon_button_padding_top_bottom__' . $option_suffix => '',
			'wishlist_icon_button_padding_left_right__' . $option_suffix => '22px',

		);
		return $button_defaults;
	}

	/**
	 * Get defaults for email fields
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_field_defaults__emails( $params = array() ) {
		if ( ! empty( $params['omit_fields'] ) ) {
			$omit_fields = $params['omit_fields'];
		}

		// email types
		$option_suffix_a = array( 'email_template', 'welcome_email', 'reserved_conf', 'reserved_notice', 'wish_on_sale', 'wish_in_stock', 'empty_wishlist_reminder' );

		$store_name  = get_bloginfo( 'name' );
		$store_email = '';
		$store_logo = $this->wldcfwc_get_store_logo_url();

		$store_id_a = wldcfwc_store_id();
		$api_domain = '{wldcfwc_api_domain}';

		$email_footer = '';

		$email_defaults = array();
		foreach ( $option_suffix_a as $option_suffix ) {
			if ( in_array( $option_suffix, array( 'wish_on_sale', 'wish_in_stock' ) ) ) {
				$email_defaults[ 'wlcom_email_conf__enable_email__' . $option_suffix ] = 'no_str';
			} else {
				$email_defaults[ 'wlcom_email_conf__enable_email__' . $option_suffix ] = 'yes_str';
			}
			$email_defaults[ 'wlcom_email_conf__reply_to_name__' . $option_suffix ]           = $store_name;
			$email_defaults[ 'wlcom_email_conf__reply_to_address__' . $option_suffix ]        = $store_email;
			$email_defaults[ 'wlcom_email_conf__subject__' . $option_suffix ]                 = '';
			$email_defaults[ 'wlcom_email_conf__header_text__' . $option_suffix ]             = esc_html__( 'Wishlist', 'wishlist-dot-com-for-woocommerce' );
			$email_defaults[ 'wlcom_email_conf__html__' . $option_suffix ]                    = '';
			$email_defaults[ 'wlcom_email_conf__use_custom_template__' . $option_suffix ]     = 'email_template';
			$email_defaults[ 'wlcom_email_conf__footer_html__' . $option_suffix ]             = $email_footer;
			$email_defaults[ 'wlcom_email_conf__header_image__' . $option_suffix ]            = $store_logo;
			$email_defaults[ 'wlcom_email_conf__header_image_max_width__' . $option_suffix ]  = '400px';
			$email_defaults[ 'wlcom_email_conf__header_font_size__' . $option_suffix ]        = 'font_size_h1';
			$email_defaults[ 'wlcom_email_conf__header_background_color__' . $option_suffix ] = '#757575';
			$email_defaults[ 'wlcom_email_conf__header_text_color__' . $option_suffix ]       = '#ffffff';
			$email_defaults[ 'wlcom_email_conf__background_color__' . $option_suffix ]        = '#efefef';
			$email_defaults[ 'wlcom_email_conf__body_background_color__' . $option_suffix ]   = '#ffffff';
			$email_defaults[ 'wlcom_email_conf__body_text_color__' . $option_suffix ]         = '#3c3c3c';
			$email_defaults[ 'wlcom_email_conf__link_color__' . $option_suffix ]              = '';
			$email_defaults[ 'wlcom_email_conf__link_decoration__' . $option_suffix ]         = 'underline';
			$email_defaults[ 'wlcom_email_conf__footer_background_color__' . $option_suffix ] = '#efefef';

			if ( in_array( $option_suffix, array( 'wish_on_sale', 'wish_in_stock' ) ) ) {
				$email_defaults[ 'wlcom_email_conf__exclude_product_categories__' . $option_suffix ] = '';
				$email_defaults[ 'wlcom_email_conf__exclude_product_skus__' . $option_suffix ]       = '';
			}
		}

		$email_defaults['wlcom_email_conf__subject__welcome_email']               = $store_name . esc_html__( ' Wishlist', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__subject__reserved_conf']               = esc_html__( 'Your gift reservation for', 'wishlist-dot-com-for-woocommerce' ) . ' {recipient_name}';
		$email_defaults['wlcom_email_conf__subject__reserved_notice']             = esc_html__( 'Your wish was reserved', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__subject__wish_on_sale']                = esc_html__( 'Your Wish is On Sale', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__subject__wish_in_stock']               = esc_html__( 'Your Wish is in Stock', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__subject__empty_wishlist_reminder']     = esc_html__( 'No Wishes Yet?', 'wishlist-dot-com-for-woocommerce' );

		$email_defaults['wlcom_email_conf__header_text__welcome_email']           = $store_name . esc_html__( ' Wishlist', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__header_text__reserved_conf']           = esc_html__( 'Your gift reservation for', 'wishlist-dot-com-for-woocommerce' ) . ' {recipient_name}';
		$email_defaults['wlcom_email_conf__header_text__reserved_notice']         = esc_html__( 'Your wish was reserved', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__header_text__wish_on_sale']            = esc_html__( 'Your Wish is On Sale', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__header_text__wish_in_stock']           = esc_html__( 'Your Wish is in Stock', 'wishlist-dot-com-for-woocommerce' );
		$email_defaults['wlcom_email_conf__header_text__empty_wishlist_reminder'] = esc_html__( 'No Wishes Yet?', 'wishlist-dot-com-for-woocommerce' );

		$email_defaults['wlcom_email_conf__html__welcome_email'] = '
<p>
    {user_first_name}, ' . esc_html__( 'welcome to ', 'wishlist-dot-com-for-woocommerce' ) . $store_name . esc_html__( '\'s wishlist', 'wishlist-dot-com-for-woocommerce' ) . '.
</p>
<p>
    ' . esc_html__( 'Here\'s a link to your wishlist:', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{user_wishlist_url}">' . esc_html__( 'My wishlist... ', 'wishlist-dot-com-for-woocommerce' ) . '</a>
</p>
<p>
    ' . esc_html__( 'Find people\'s WishLists here:', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{find_wishlists_url}">' . esc_html__( 'Find a wishlist... ', 'wishlist-dot-com-for-woocommerce' ) . '</a>
</p>
<p>
    ' . esc_html__( 'WishLists can include things you like, inspirational ideas, or gifts you\'d love for a special occasion.', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
<p>
    ' . esc_html__( 'Our WishList is provided by WishList.com. Share your WishLists with friends and family here, and at WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
<p>
    ' . esc_html__( 'Enjoy!', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
';

		$email_defaults['wlcom_email_conf__html__reserved_conf'] = '
<p>
    ' . esc_html__( 'Hi', 'wishlist-dot-com-for-woocommerce' ) . ' {reserver_first_name},
</p>
<p>
    ' . esc_html__( 'You\'ve reserved', 'wishlist-dot-com-for-woocommerce' ) . ' {reserved_quantity} ' . esc_html__( 'of the gift below for', 'wishlist-dot-com-for-woocommerce' ) . ' {recipient_name}.   
</p>
<p>
    ' . esc_html__( 'If you haven\'t purchased this gift,', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{purchase_wish_url}">' . esc_html__( 'purchase it now.', 'wishlist-dot-com-for-woocommerce' ) . '</a>
</p>
<p>
    ' . esc_html__( 'If you\'re not going to purchase this gift,', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{edit_reservation_url}">' . esc_html__( 'undo your reservation now.', 'wishlist-dot-com-for-woocommerce' ) . '</a>
</p>
<p>
    {recipient_profile_image_and_name}
</p>
<p>
    {wishes_display_table}
</p>
<p>
    <a href="{edit_reservation_url}">' . esc_html__( 'You can edit your reservation here...', 'wishlist-dot-com-for-woocommerce' ) . '</a>
</p>
<p>
    ' . esc_html__( 'Thanks!', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
';

		$email_defaults['wlcom_email_conf__html__reserved_notice'] = '
<p>
    ' . esc_html__( 'Hi', 'wishlist-dot-com-for-woocommerce' ) . ' {user_first_name},
</p>
<p>
    ' . esc_html__( 'Someone just reserved a wish off your WishList to buy as a gift:', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{user_wishlist_url}">{user_wishlist_name}</a>.
</p>
<p>
    ' . esc_html__( 'Here are links to the wishes. The wish names are hidden below to keep the suprize.', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
<p>
    {wishes_display_table}
</p>
<p>
    ' . esc_html__( 'Best wishes!', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
';

		$email_defaults['wlcom_email_conf__html__wish_on_sale'] = '
<p>
    ' . esc_html__( 'Hi', 'wishlist-dot-com-for-woocommerce' ) . ' {user_first_name},
</p>
<p>
    ' . esc_html__( 'Item(s) on your wishlist, are now on sale!', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{user_wishlist_url}">' . esc_html__( 'View WishList', 'wishlist-dot-com-for-woocommerce' ) . '</a>
</p>
<p>
    {wishes_display_table}
</p>
<p>
    ' . esc_html__( 'Best wishes!', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
';

		$email_defaults['wlcom_email_conf__html__wish_in_stock'] = '
<p>
    ' . esc_html__( 'Hi', 'wishlist-dot-com-for-woocommerce' ) . ' {user_first_name},
</p>
<p>
    ' . esc_html__( 'Item(s) on your wishlist, are now in stock!', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{user_wishlist_url}">' . esc_html__( 'View WishList ', 'wishlist-dot-com-for-woocommerce' ) . ' </a>
</p>
<p>
    {wishes_display_table}
</p>
<p>
    ' . esc_html__( 'Best wishes!', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
';

		$email_defaults['wlcom_email_conf__html__empty_wishlist_reminder'] = '
<p>
    ' . esc_html__( 'Hi', 'wishlist-dot-com-for-woocommerce' ) . ' {user_first_name},
</p>
<p>
    ' . esc_html__( 'Don\'t forget to save your first Wish on', 'wishlist-dot-com-for-woocommerce' ) . $store_name . '!
</p>
<p>
    ' . esc_html__( 'Here\'s a link to your wishlist:', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{user_wishlist_url}">' . esc_html__( 'My wishlist... ', 'wishlist-dot-com-for-woocommerce' ) . ' </a>
</p>
<p>
    ' . esc_html__( 'Find people\'s WishLists here:', 'wishlist-dot-com-for-woocommerce' ) . ' <a href="{find_wishlists_url}">' . esc_html__( 'Find a wishlist... ', 'wishlist-dot-com-for-woocommerce' ) . ' </a>
</p>
<p>
    ' . esc_html__( 'WishLists can include things you like, inspirational ideas, or gifts you\'d love for a special occasion.', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
<p>
    ' . esc_html__( 'Our WishList is provided by WishList.com. Share your WishLists with friends and family here, and at WishList.com.', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
<p>
    ' . esc_html__( 'Best wishes!', 'wishlist-dot-com-for-woocommerce' ) . '
</p>
';

		if ( ! empty( $omit_fields ) ) {
			foreach ( $email_defaults as $index => $field ) {
				foreach ( $omit_fields as $omit_field ) {
					if ( isset( $field['id'] ) && str_contains( $field['id'], $omit_field ) ) {
						unset( $email_defaults[ $index ] );
					}
				}
			}
		}

		return $email_defaults;
	}

	/**
	 * Store logo for presentation on WishList.com
	 *
	 * @return mixed|string
	 */
	public function wldcfwc_get_store_logo_url() {
		if ( has_custom_logo() ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( is_array( $logo ) ) {
				return $logo[0];  // This is the URL for the logo image.
			}
		}
		return ''; // Return empty if there's no custom logo.
	}

	/**
	 * Get data to be used by the "Add to WishList" buttons
	 *
	 * @param  $product
	 * @return array
	 */
	private function wldcfwc_get_product_data_wish( $product ) {

		$product_id               = $product->get_id();
		$product_data['wlprodid'] = $product_id;
		$product_data['wlsku']    = $product->get_sku();
		$product_url              = $product->get_permalink();
		$product_data['wlurl']    = $product_url;
		$product_url_a            = explode( '?', $product_url );
		// add_to_cart_url() returns ?add-to-cart=22
		$cart_qs = $product->add_to_cart_url();
		if ( str_contains( $cart_qs, 'http' ) ) {
			$product_data['wlburl'] = $product->add_to_cart_url();
		} elseif ( substr( $cart_qs, 0, 1 ) == '?' ) {
			$product_data['wlburl'] = $product_url_a[0] . $product->add_to_cart_url();
		} elseif ( str_contains( $cart_qs, 'add-to-cart' ) ) {
			// should not happen. just in case
			$product_data['wlburl'] = $product_url_a[0] . '?' . $product->add_to_cart_url();
		} else {
			$product_data['wlburl'] = $product_url;
		}

		$product_data['wlpname'] = $product->get_name();
		$product_data['wldesc']  = $product->get_description();
		$wlprice                 = $product->get_price();
		// $wlpricecurrency = get_woocommerce_currency();
		$wlpricecurrency                 = 'USD';
		$product_data['wlpricecurrency'] = $wlpricecurrency;
		$product_data['wlprice']         = $wlprice;
		$image_id                        = $product->get_image_id();
		$product_data['wliurl']          = wp_get_attachment_image_url( $image_id, 'full' );
		$product_data['product_type']    = $product->get_type();
		$product_category_ids            = $product->get_category_ids();
		$product_category_id_list        = implode( ',', $product_category_ids );

		$product_categories_a = array();
		foreach ( $product_category_ids as $category_id ) {
			$term = get_term_by( 'id', $category_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$product_categories_a[ $category_id ] = $term->name;
			}
		}
		$product_data['product_categories_json'] = $product_categories_a;

		$product_data['is_in_stock']   = $product->is_in_stock();
		$product_data['is_on_sale']    = $product->is_on_sale();
		$product_data['price']         = $product->get_price();
		$product_data['sale_price']    = $product->get_sale_price();
		$product_data['regular_price'] = $product->get_regular_price();
		$product_data['on_sale_from']  = $product->get_date_on_sale_from();
		$product_data['on_sale_to']    = $product->get_date_on_sale_to();

		return $product_data;
	}

	/**
	 * Get html for the WishList homepage that's linked to from "Wishlist" in the top menu
	 *
	 * @return string|null
	 */
	public function wldcfwc_get_wishlist_hp_html() {
		$store_id_a = wldcfwc_store_id();
		$api_domain = $store_id_a['api_domain'];

		$icon_url = WLDCFWC_URL . 'public/images/WishListIcon-trans-48.png';

		$hp_base_url = 'https://' . $store_id_a['api_domain'] . '/wooCommApi';

		$qm                               = str_contains( $hp_base_url, '?' ) ? '&' : '?';
		$mode                             = 'hp_createwishlist';
		$params                           = array();
		$params['mode']                   = $mode;
		$params['button_or_link_tag']     = 'a';
		$params['link_tag_href']          = $hp_base_url . $qm . 'api_action=createwishlist';
		$params['button_type_attr']       = '';
		$params['img_wrapper_button_css'] = 'wldcfwc-img-wrapper-button--hp_createwishlist wldcfwc-img-wrapper-button';
		$create_html                      = $this->wldcfwc_get_wishlist_button( $this->options, $params );

		$mode                             = 'hp_findwishlist';
		$params                           = array();
		$params['mode']                   = $mode;
		$params['button_or_link_tag']     = 'a';
		$params['link_tag_href']          = $hp_base_url . $qm . 'api_action=findwishlist';
		$params['button_type_attr']       = '';
		$params['img_wrapper_button_css'] = 'wldcfwc-img-wrapper-button--hp_findwishlist wldcfwc-img-wrapper-button';
		$find_html                        = $this->wldcfwc_get_wishlist_button( $this->options, $params );

		$mode                             = 'hp_mywishlist';
		$params                           = array();
		$params['mode']                   = $mode;
		$params['button_or_link_tag']     = 'a';
		$params['link_tag_href']          = $hp_base_url . $qm . 'api_action=mywishlist';
		$params['button_type_attr']       = '';
		$params['img_wrapper_button_css'] = 'wldcfwc-img-wrapper-button--hp_mywishlist wldcfwc-img-wrapper-button';
		$my_html                          = $this->wldcfwc_get_wishlist_button( $this->options, $params );

		$hp_buttons_html = $create_html . $find_html . $my_html;

		if ( 'text_alignment_left' == $this->options['hp_alignment'] ) {
			$atts['hp_container_div_class'] = 'wldcfwc_text_left';
		} elseif ( 'text_alignment_centered' == $this->options['hp_alignment'] ) {
			$atts['hp_container_div_class'] = 'wldcfwc_text_centered';
		} elseif ( 'text_alignment_right' == $this->options['hp_alignment'] ) {
			$atts['hp_container_div_class'] = 'wldcfwc_text_right';
		}
		$atts['hp_image']              = $this->options['hp_banner_img'];
		$atts['hp_subtitle']           = $this->options['hp_subtitle'];
		$atts['hp_subtitle_tag']       = 'span';
		$atts['hp_subtitle_div_class'] = 'wldcfwc_hp_subtitle_div_margin_bottom';
		if ( str_contains( $this->options['hp_subtitle_font_size'], 'font_size_h' ) ) {
			$atts['hp_subtitle_tag']       = str_replace( 'font_size_', '', $this->options['hp_subtitle_font_size'] );
			$atts['hp_subtitle_div_class'] = '';
		} elseif ( 'normal_bold' == $this->options['hp_subtitle_font_size'] ) {
			$atts['hp_subtitle_div_class'] .= ' wldcfwc_font_normal_bold';
		} elseif ( 'normal' == $this->options['hp_subtitle_font_size'] ) {
			$atts['hp_subtitle_div_class'] .= ' wldcfwc_font_normal';
		} elseif ( 'Medium' == $this->options['hp_subtitle_font_size'] ) {
			$atts['hp_subtitle_div_class'] .= ' wldcfwc_font_medium';
		} elseif ( 'Small' == $this->options['hp_subtitle_font_size'] ) {
			$atts['hp_subtitle_div_class'] .= ' wldcfwc_font_small';
		}

		$atts['hp_description']           = $this->options['hp_description'];
		$atts['hp_description_tag']       = 'span';
		$atts['hp_description_div_class'] = 'wldcfwc_hp_description_div_margin_bottom';
		if ( str_contains( $this->options['hp_description_font_size'], 'font_size_h' ) ) {
			$atts['hp_description_tag']       = str_replace( 'font_size_', '', $this->options['hp_description_font_size'] );
			$atts['hp_description_div_class'] = '';
		} elseif ( 'font_size_normal_bold' == $this->options['hp_description_font_size'] ) {
			$atts['hp_description_div_class'] .= ' wldcfwc_font_normal_bold';
		} elseif ( 'font_size_normal' == $this->options['hp_description_font_size'] ) {
			$atts['hp_description_div_class'] .= ' wldcfwc_font_normal';
		} elseif ( 'font_size_medium' == $this->options['hp_description_font_size'] ) {
			$atts['hp_description_div_class'] .= ' wldcfwc_font_medium';
		} elseif ( 'font_size_small' == $this->options['hp_description_font_size'] ) {
			$atts['hp_description_div_class'] .= ' wldcfwc_font_small';
		}

		$atts['hp_buttons_html'] = $hp_buttons_html;

		$atts['hp_footer']           = $this->options['hp_footer'];
		$atts['hp_footer_div_class'] = 'wldcfwc_hp_footer_div_margin_bottom';
		if ( 'font_size_normal_bold' == $this->options['hp_footer_font_size'] ) {
			$atts['hp_footer_div_class'] .= ' wldcfwc_font_normal_bold';
		} elseif ( 'font_size_normal' == $this->options['hp_footer_font_size'] ) {
			$atts['hp_footer_div_class'] .= ' wldcfwc_font_normal';
		} elseif ( 'font_size_medium' == $this->options['hp_footer_font_size'] ) {
			$atts['hp_footer_div_class'] .= ' wldcfwc_font_medium';
		} elseif ( 'font_size_small' == $this->options['hp_footer_font_size'] ) {
			$atts['hp_footer_div_class'] .= ' wldcfwc_font_small';
		}

		if ( 'yes_str' == $this->options['hp_show_poweredby'] && ! empty( $this->options['wlcom_your_store_url'] ) ) {
			$store_shop_url = $this->options['wlcom_your_store_url'];
			$atts['hp_poweredby'] = '
                <div class="wldcfwc_powered-by-wishlist-com">
                    <a href="https://wishlist.com?show_powered_by_msg=yes&show_close=1&store_shop_url=' . $store_shop_url . '__store_shop_url__end" target="wishlistdotcom">' . esc_html__( 'Powered by', 'wishlist-dot-com-for-woocommerce' ) . ' <img class="wldcfwc_logo-icon-inline-text" src="' . $icon_url . '" width="48" height="48">' . esc_html__( 'WishList.com ', 'wishlist-dot-com-for-woocommerce' ) . ' </a>
                </div>
            ';
			$atts['hp_poweredby_div_class'] = 'wldcfwc_hp_poweredby_div_margin_bottom';
		}

		$template_path = 'wldcfwc-wishlist-hp.php';
		$return_html   = wldcfwc_get_template_html( $template_path, $atts, true );

		return $return_html;
	}

	/**
	 * Get products that have gone on sale or are back in stock.
	 * These were previously saved by 'add_productid_to_wlcom_queue', hooked to woocommerce_new_product and similar
	 *
	 * @param  $params
	 * @return array
	 */
	public function wldcfwc_get_productid_queue( $params ) {
		$key                = $params['key'];
		$wlcom_products_key = WLDCFWC_SLUG . '_' . $key;

		// using a delimited list to save about 133% in characters
		$wlcom_products_csv = get_option( $wlcom_products_key );
		if ( ! empty( $wlcom_products_csv ) ) {
			$wlcom_products_a = explode( ',', $wlcom_products_csv );
		} else {
			$wlcom_products_a = array();
		}

		$wlcom_product_ids_a = array();
		// convert to relational array
		foreach ( $wlcom_products_a as $product_info_str ) {
			$info_a = explode( ':', $product_info_str );
			if ( isset( $info_a[1] ) ) {
				$wlcom_product_ids_a[ $info_a[0] ]['onsale'] = $info_a[1];
			} else {
				$wlcom_product_ids_a[ $info_a[0] ]['onsale'] = 3;
			}
			if ( isset( $info_a[2] ) ) {
				$wlcom_product_ids_a[ $info_a[0] ]['instock'] = $info_a[2];
			} else {
				$wlcom_product_ids_a[ $info_a[0] ]['instock'] = 3;
			}
		}
		return $wlcom_product_ids_a;
	}

	/**
	 * Add a product that's changed to a queue that will be sent to WishList.com so
	 * WishLists can be updated and promotional emails configured in the admin panel can be sent
	 * Saved by 'add_productid_to_wlcom_queue', hooked to woocommerce_new_product and similar
	 *
	 * @param  $params
	 * @return void
	 */
	public function wldcfwc_save_productids_to_queue( $params ) {
		$key                 = $params['key'];
		$wlcom_product_ids_a = $params['wlcom_product_ids_a'];
		$wlcom_products_key  = WLDCFWC_SLUG . '_' . $key;

		// convert to delimted list
		$wlcom_products_updated = array();
		foreach ( $wlcom_product_ids_a as $product_id => $info_a ) {
			$wlcom_products_updated[] = $product_id . ':' . $info_a['onsale'] . ':' . $info_a['instock'];
		}
		// using a delimited list to save about 133% in characters
		$wlcom_products_updated_csv = implode( ',', $wlcom_products_updated );

		// save list of products with a wish
		update_option( $wlcom_products_key, $wlcom_products_updated_csv );
	}
}
