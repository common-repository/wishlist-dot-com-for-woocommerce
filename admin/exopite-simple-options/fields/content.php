<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Content
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_content' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Content extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function wldcfwc_output() {

			$content = ( isset( $this->field['content'] ) ) ? $this->field['content'] : '';
			// WLDC
			$description       = ( isset( $this->field['description'] ) ) ? $this->field['description'] : '';
			$description_class = ( isset( $this->field['description_class'] ) ) ? $this->field['description_class'] : '';
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			if ( isset( $this->field['callback'] ) ) {

				$callback = $this->field['callback'];
				if ( is_callable( $callback['function'] ) ) {

					$args    = ( isset( $callback['args'] ) ) ? $callback['args'] : '';
					$content = call_user_func( $callback['function'], $args );

				}
			}

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );
			echo '<div' . wp_kses( $this->wldcfwc_element_class(), array() ) . wp_kses( $this->wldcfwc_element_attributes(), array() ) . '>' . wp_kses( $content, $allowed_html_tags ) . '</div>';

			// WLDC
			if ( ! empty( $description ) ) {
				echo '<p class="exopite-sof-description ' . esc_attr( $description_class ) . '">' . wp_kses( $description, $allowed_html_tags ) . '</p>';
			}

			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}
	}

}
