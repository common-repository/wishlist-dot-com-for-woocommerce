<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Radio
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_radio' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Radio extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function wldcfwc_output() {

			$classes           = ( isset( $this->field['class'] ) ) ? implode( ' ', explode( ' ', $this->field['class'] ) ) : '';
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );

			if ( isset( $this->field['options'] ) ) {

				$options = $this->field['options'];
				$options = ( is_array( $options ) ) ? $options : array_filter( $this->wldcfwc_element_data( $options ) );
				$style   = ( isset( $this->field['style'] ) ) ? $this->field['style'] : '';

				if ( ! empty( $options ) ) {

					echo '<ul' . wp_kses( $this->wldcfwc_element_class(), array() ) . '>';
					foreach ( $options as $key => $value ) {

						switch ( $style ) {
							case 'fancy':
								echo '<li>';
								echo '<label class="radio-button ' . wp_kses( $classes, array() ) . '">';
								echo '<input type="radio" class="radio-button__input" name="' . esc_attr( $this->wldcfwc_element_name() ) . '" value="' . esc_attr( $key ) . '" ' . wp_kses( $this->wldcfwc_element_attributes( $key ), array() ) . wp_kses( $this->wldcfwc_checked( $this->wldcfwc_element_value(), esc_attr( $key ) ), array() ) . '>';
								echo '<div class="radio-button__checkmark"></div>';
								echo wp_kses( $value, $allowed_html_tags );
								echo '</label>';
								echo '</li>';
								break;

							default:
								echo '<li><label><input type="radio" name="' . esc_attr( $this->wldcfwc_element_name() ) . '" value="' . esc_attr( $key ) . '" ' . wp_kses( $this->wldcfwc_element_attributes( $key ), array() ) . wp_kses( $this->wldcfwc_checked( $this->wldcfwc_element_value(), esc_attr( $key ) ), array() ) . '/> ' . esc_attr( $value ) . '</label></li>';
								break;
						}
					}
					echo '</ul>';
				}
			} else {
				$label = ( isset( $this->field['label'] ) ) ? $this->field['label'] : '';

				switch ( $this->field['style'] ) {
					case 'fancy':
						echo '<label class="radio-button ' . wp_kses( $classes, array() ) . '">';
						echo '<input type="radio" class="radio-button__input" name="' . esc_attr( $this->wldcfwc_element_name() ) . '" ' . wp_kses( $this->wldcfwc_element_attributes(), array() ) . checked( esc_attr( $this->wldcfwc_element_value() ), 1, false ) . '>';
						echo '<div class="radio-button__checkmark"></div>';
						echo wp_kses( $label, $allowed_html_tags );
						echo '</label>';
						break;

					default:
						echo '<label><input type="radio" name="' . esc_attr( $this->wldcfwc_element_name() ) . '" value="1" ' . wp_kses( $this->wldcfwc_element_class(), array() ) . wp_kses( $this->wldcfwc_element_attributes(), array() ) . checked( esc_attr( $this->wldcfwc_element_value() ), 1, false ) . '/> ' . wp_kses( $label, $allowed_html_tags ) . '</label>';
						break;
				}
			}

			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}
	}

}
