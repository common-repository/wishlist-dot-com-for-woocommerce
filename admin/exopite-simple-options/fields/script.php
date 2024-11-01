<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Text
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_script' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Script extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function wldcfwc_output() {

			$script = ( isset( $this->field['script'] ) ) ? $this->field['script'] : '';

			if ( ! empty( $script ) ) {
				echo '<script>';
				echo wp_kses( $script, array() );
				echo '</script>';
			} else {
				echo '';
			}
		}
	}

}
