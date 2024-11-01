<?php
/**
 * The file that defines functions
 *
 * @link  https://www.wishlist.com/wishlist-dot-com-for-woocommerce
 * @since 1.0.0
 *
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */


if ( ! function_exists( 'wldcfwc_get_template_html' ) ) {
	/**
	 * Get html from a template file.
	 *
	 * @param string $path   Path to get.
	 * @param mixed  $var    Variables to send to template.
	 * @param bool   $return Whether to return or print the template.
	 *
	 * @return string|void
	 * @since  1.0.0
	 */
	function wldcfwc_get_template_html( $path, $var = null, $return = false ) {
		$safe_template_path = wldcfwc_get_template_path( $path, $var );

		if ( $var && is_array( $var ) ) {
			$atts = $var;
			extract( $var ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		// buffer
		if ( $return ) {
			ob_start();
		}

		// output
		include $safe_template_path;

		// return output
		if ( $return ) {
			return ob_get_clean();
		}
	}
}
if ( ! function_exists( 'wldcfwc_get_template_path' ) ) {
	/**
	 * Get the template path
	 *
	 * @param string $path Path to locate.
	 * @param array  $var  Unused.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	function wldcfwc_get_template_path( $path, $var = null ) {
		$woocommerce_base = WC()->template_path();

		$template_woocommerce_path = $woocommerce_base . $path;

		// theme path
		$template_path = '/' . $path;
		// plugin's default path
		if ( ! str_contains( $path, '/' ) ) {
			$plugin_path = WLDCFWC_DIR . 'public/templates/' . $path;
		} else {
			$plugin_path = $path;
		}

		$file_name = basename( $path );
		//allowed template paths
		$allowed_templates = [
			'wldcfwc-button.php',
			'wldcfwc-wishlist-hp.php',
		];
		if ( in_array( $file_name, $allowed_templates ) ) {
			$safe_template = true;
		} else {
			$safe_template = false;
		}

		// search for template file ($path) in theme and woocommerce locations
		$custom_template_path = locate_template(
			array(
				$template_woocommerce_path, // <theme>/woocommerce/.
				$template_path, // <theme>/.
			)
		);

		if ( $safe_template && ! $custom_template_path && file_exists( $plugin_path ) ) {
			// return our plugin path, with filter if it's a safe template and $custom_template_path isn't found
			return apply_filters( 'wldcfwc_template_path', $plugin_path, $path );
		} elseif ( ! $safe_template) {
			// not an allowed path
			return null;
		}

		/**
		 * APPLY_FILTERS: wldcfwc_template_path
		 *
		 * Filter the location of the templates.
		 *
		 * @param string $custom_template_path Template found
		 * @param string $path    Template path
		 *
		 * @return string
		 */
		return apply_filters( 'wldcfwc_template_path', $custom_template_path, $path );
	}
}
if ( ! function_exists( 'wldcfwc_is_single' ) ) {
	/**
	 * True if it's on a product page
	 * False if in a loop
	 *
	 * @return bool
	 * @since  1.0.0
	 */
	function wldcfwc_is_single() {
		/**
		 * APPLY_FILTERS: wldcfwc_set_is_single
		 *
		 * Filter whether the button is on a product page or in a loop
		 *
		 * @param bool $is_product True if on a product page
		 *
		 * @return bool
		 */

		$check_is_product = (
			is_product()
			&& ! in_array( wc_get_loop_prop( 'name' ), array( 'related', 'up-sells' ), true )
			&& ! wc_get_loop_prop( 'is_shortcode' )
		);

		return apply_filters( 'wldcfwc_set_is_single', $check_is_product );
	}
}
if ( ! function_exists( 'wldcfwc_url_to_domain_path' ) ) {
	function wldcfwc_url_to_domain_path( $url ) {
		// remove correct and incorrect schema
		$url_no_scheme = str_replace( array( 'https://', 'http://', 'https', 'http', ':' ), array( '', '', '', '', '' ), $url );
		// remove multiple slashes, like abc.com//path
		$url_no_scheme = preg_replace( '#/{2,}#', '/', $url_no_scheme );
		// remove leading and trailing / and \, like /abc.com/test/
		$url_no_scheme = rtrim( $url_no_scheme, '/\\' );
		$url_no_scheme = ltrim( $url_no_scheme, '/\\' );
		$store_domain  = explode( '/', $url_no_scheme )[0];
		$return_a      = array(
			'store_domain'  => $store_domain,
			'url_no_scheme' => $url_no_scheme,
		);
		return $return_a;
	}
}
if ( ! function_exists( 'wldcfwc_clean_subdomain_string' ) ) {
	function wldcfwc_clean_subdomain_string( $input ) {
		// Step 1: Remove non-alphanumeric characters except for hyphens
		$processed = preg_replace( '/[^a-zA-Z0-9-]/', '', $input );

		// Step 2: Replace two or more consecutive hyphens with a single hyphen
		$processed = preg_replace( '/-{2,}/', '-', $processed );

		// Step 3: Remove leading and trailing hyphens
		$processed = trim( $processed, '-' );

		return $processed;
	}
}
if ( ! function_exists( 'wldcfwc_set_allowed_tags' ) ) {
	function wldcfwc_set_allowed_tags( $mode = 'post' ) {
		$mode_a = explode( ',', $mode );
		if ( in_array( 'all', $mode_a ) ) {
			$all = true;
		} else {
			$all = false;
		}

		if ( in_array( 'post', $mode_a ) || $all ) {
			$allowed_tags = wp_kses_allowed_html( 'post' );
		}
		if ( in_array( 'svg', $mode_a ) || $all ) {
			$allowed_tags['svg']  = array( // Add SVG and its attributes to the list.
				'class'       => true,
				'aria-hidden' => true,
				'role'        => true,
				'xmlns'       => true,
				'width'       => true,
				'height'      => true,
				'viewbox'     => true, // Note: Should be 'viewBox', but HTML attributes are lowercase in this context.
			);
			$allowed_tags['path'] = array( // Allow 'path' elements and their attributes.
				'd'    => true,
				'fill' => true,
			);
		}
		if ( in_array( 'script', $mode_a ) || $all ) {
			$allowed_tags['script'] = array();
		}
		if ( in_array( 'admin_panel', $mode_a ) || $all ) {
			$allowed_form_html_tags = array(
				'div' => array(
					'class' => array(),
					'style' => array(),
					'data-depend-id' => array(),
					'clearfix' => array(),
					'id' => array(),
				),
				'select' => array(
					'name' => array(),
					'class' => array(),
					'data-depend-id' => array(),
					'id' => array(),
					'data-select2-id' => array(),
					'tabindex' => array(),
					'aria-hidden' => array(),
					'data-select2-sku' => array(),
					'multiple' => array(),
				),
				'option' => array(
					'data-type' => array(),
					'value' => array(),
					'data-source' => array(),
					'selected' => array(),
					'data-select2-id' => array(),
				),
				'span' => array(
					'class' => array(),
					'dir' => array(),
					'data-select2-id' => array(),
					'role' => array(),
					'aria-haspopup' => array(),
					'aria-expanded' => array(),
					'tabindex' => array(),
					'aria-disabled' => array(),
					'aria-labelledby' => array(),
					'aria-controls' => array(),
					'aria-hidden' => array(),
					'aria-label' => array(),
				),
				'b' => array(
					'role' => array(),
				),
				'img' => array(
					'src' => array(),
					'alt' => array(),
				),
				'title' => array(),
				'path' => array(
					'd' => array(),
				),
				'input' => array(
					'type' => array(),
					'name' => array(),
					'oninput' => array(),
					'class' => array(),
					'min' => array(),
					'max' => array(),
					'step' => array(),
					'value' => array(),
					'data-depend-id' => array(),
					'checked' => array(),
					'data-control' => array(),
					'data-format' => array(),
					'data-default' => array(),
					'placeholder' => array(),
				),
				'br' => array(),
				'clearfix' => array(),
				'h4' => array(
					'class' => array(),
				),
				'p' => array(
					'class' => array(),
				),
				'ul' => array(
					'class' => array(),
					'id' => array(),
				),
				'li' => array(),
				'label' => array(
					'class' => array(),
				),
				'textarea' => array(
					'id' => array(),
					'class' => array(),
					'type' => array(),
					'tabindex' => array(),
					'autocorrect' => array(),
					'autocapitalize' => array(),
					'spellcheck' => array(),
					'role' => array(),
					'aria-autocomplete' => array(),
					'autocomplete' => array(),
					'aria-label' => array(),
					'aria-describedby' => array(),
					'placeholder' => array(),
					'style' => array(),
					'name' => array(),
					'data-depend-id' => array(),
					'rows' => array(),
				),
				'button' => array(
					'type' => array(),
					'class' => array(),
					'aria-expanded' => array(),
					'style' => array(),
					'id' => array(),
					'data-content-id'=>array(),
					'data-button-mode'=>array(),
					'data-button-url'=>array(),
					'data-button-params'=>array(),
					'data-toptab'=>array(),
					'data-depend-id' => array(),
				),
			);

			$allowed_tags = array_merge($allowed_tags, $allowed_form_html_tags);
		}
		return $allowed_tags;
	}
}
if (!function_exists('wldcfwc_create_url_qsparam')) {
	function wldcfwc_create_url_qsparam($param_name,$param_val) {
		//just for urls. not for other params
		if (!empty($param_val)) {
			$param_val = str_replace(['?','&','=','%'], ['__qm__','__amp__','__eq__','__per__'], $param_val);
			$param_val.='__' . $param_name . '__end';
		}
		return $param_val;
	}
}

