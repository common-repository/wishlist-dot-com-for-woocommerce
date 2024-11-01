<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Textarea
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_textarea' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Textarea extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function wldcfwc_output() {
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );
			// using wp_kses_post($var ?? '') to avoid error: PHP Deprecated:  preg_replace(): Passing null to parameter #3 ($subject) of type array|string is deprecated
			echo '<textarea name="' . esc_attr( $this->wldcfwc_element_name() ) . '" ' . wp_kses( $this->wldcfwc_element_class(), array() ) . wp_kses( $this->wldcfwc_element_attributes(), array() ) . '>' . wp_kses_post( $this->wldcfwc_element_value() ?? '' ) . '</textarea>';
			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}
	}

}
