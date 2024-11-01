<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Color
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_color' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Color extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array(), $multilang = null ) {

			parent::__construct( $field, $value, $unique, $config, $multilang );
		}

		public function wldcfwc_output() {

			$classes           = ( isset( $this->field['class'] ) ) ? implode( ' ', explode( ' ', $this->field['class'] ) ) : '';
			$controls          = array( 'hue', 'brightness', 'saturation', 'wheel' );
			$control           = ( isset( $this->field['control'] ) ) ? $this->field['control'] : 'saturation';
			$formats           = array( 'rgb', 'hex' );
			$format            = ( isset( $this->field['format'] ) ) ? $this->field['format'] : 'rgb';
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );
			echo '<input type="';
			if ( isset( $this->field['picker'] ) && 'html5' == $this->field['picker'] ) {
				echo 'color';
			} else {
				echo 'text';
			}
			echo '" ';
			if ( ! isset( $this->field['picker'] ) || 'html5' != $this->field['picker'] ) {
				echo 'class="minicolor ' . esc_attr( $classes ) . '" ';
			}
			if ( isset( $this->field['rgba'] ) && $this->field['rgba'] ) {
				echo 'data-opacity="1" ';
			}
			if ( in_array( $control, $controls ) ) {
				echo 'data-control="' . esc_attr( $control ) . '" '; // hue, brightness, saturation, wheel
			}
			if ( in_array( $format, $formats ) ) {
				echo 'data-format="' . esc_attr( $format ) . '" '; // hue, brightness, saturation, wheel
			}
			echo 'name="' . esc_attr( $this->wldcfwc_element_name() ) . '" value="' . wp_kses( $this->wldcfwc_element_value(), array() ) . '" ';
			if ( isset( $this->field['default'] ) ) {
				echo 'data-default="' . wp_kses( $this->field['default'], array() ) . '" ';
			}
			if ( isset( $this->field['picker'] ) && 'html5' == $this->field['picker'] ) {
				echo wp_kses( $this->wldcfwc_element_class(), array() );
			}
			echo wp_kses( $this->wldcfwc_element_attributes(), array() ) . '/>';
			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}

		public static function wldcfwc_enqueue( $args ) {

			wp_enqueue_style( 'wp-color-picker' );

			// this file initializes the wp-color-picker-script
			wp_enqueue_script( 'wp-color-picker-script', plugins_url( '../assets/init_wp-color-picker-script.js', __FILE__ ), array( 'wp-color-picker', 'jquery' ), '1.0', true );
		}
	}

}
