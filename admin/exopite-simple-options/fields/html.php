<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Text
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_html' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Html extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function wldcfwc_output() {

			if ( isset( $this->field['allowed_html_flags'] ) && 'all' != $this->field['allowed_html_flags'] ) {
				$allowed_html_flags = $this->field['allowed_html_flags'];
			} elseif ( isset( $this->field['allowed_html_flags'] ) && 'all' == $this->field['allowed_html_flags'] ) {
				$allowed_html_tags = array();
			} else {
				$allowed_html_flags = 'post,svg';
			}
			if ( ! isset( $allowed_html_tags ) ) {
				$allowed_html_tags = wldcfwc_set_allowed_tags( $allowed_html_flags );
			}

			$html = ( isset( $this->field['html'] ) ) ? $this->field['html'] : '';

			echo wp_kses( $html, $allowed_html_tags );
		}
	}

}
