<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Text
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_text' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Text extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		// WLDC adding default for $multilang
		public function __construct( $field, $value = '', $unique = '', $config = array(), $multilang = null ) {
			parent::__construct( $field, $value, $unique, $config, $multilang );
		}

		public function wldcfwc_output() {
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );
			echo wp_kses( $this->wldcfwc_element_prepend(), $allowed_html_tags );

			echo '<input type="' . esc_attr( $this->wldcfwc_element_type() ) . '" name="' . esc_attr( $this->wldcfwc_element_name() ) . '" value="' . esc_attr( $this->wldcfwc_element_value() ) . '" ' . wp_kses( $this->wldcfwc_element_class(), array() ) . wp_kses( $this->wldcfwc_element_attributes(), array() ) . '/>';

			echo wp_kses( $this->wldcfwc_element_append(), $allowed_html_tags );
			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}
	}

}
