<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Select
 */

if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_select' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Select extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function wldcfwc_output() {

			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );
			echo wp_kses( $this->wldcfwc_element_prepend(), $allowed_html_tags );

			if ( isset( $this->field['options'] ) || isset( $this->field['query'] ) ) {

				$options    = ( isset( $this->field['options'] ) && is_array( $this->field['options'] ) ) ? $this->field['options'] : array();
				$select     = $options;
				$extra_name = ( isset( $this->field['attributes']['multiple'] ) ) ? '[]' : '';

				echo '<select name="' . esc_attr( $this->wldcfwc_element_name( $extra_name ) ) . '" ' . wp_kses( $this->wldcfwc_element_class(), array() ) . wp_kses( $this->wldcfwc_element_attributes(), array() ) . '>';

				echo ( isset( $this->field['default_option'] ) ) ? '<option value="">' . wp_kses( $this->field['default_option'], $allowed_html_tags ) . '</option>' : '';

				if ( ! empty( $select ) ) {

					foreach ( $select as $key => $value ) {

						// WLDC see if value is an array
						$attr_str = '';
						if ( is_array( $value ) ) {
							if ( isset( $value['attribute'] ) ) {
								$attr_str = $value['attribute'];
							}
							$value_str = $value['value'];
						} else {
							$value_str = $value;
						}

						// can't use esc_attr( $this->wldcfwc_element_value() )
						echo '<option ' . wp_kses( $attr_str, array() ) . ' value="' . esc_attr( $key ) . '" ' . wp_kses( $this->wldcfwc_checked( $this->wldcfwc_element_value(), esc_attr( $key ), 'selected' ), array() ) . '>' . wp_kses( $value_str, $allowed_html_tags ) . '</option>';

					}
				}

				echo '</select>';

			}

			echo wp_kses( $this->wldcfwc_element_append(), $allowed_html_tags );
			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}
		public static function wldcfwc_enqueue( $args ) {

			$resources = array(
				array(
					'name'       => 'jquery-select2',
					'fn'         => 'select2.min.css',
					'type'       => 'style',
					'dependency' => array(),
					'version'    => '4.1.0',
					'attr'       => 'all',
				),
				array(
					'name'       => 'bootstrap-select2',
					'fn'         => 'select2-bootstrap-5-theme.min.css',
					'type'       => 'style',
					'dependency' => array(),
					'version'    => '1.3.0',
					'attr'       => 'all',
				),
				array(
					'name'       => 'jquery-select2',
					'fn'         => 'select2.full.min.js',
					'type'       => 'script',
					'dependency' => array( 'jquery' ),
					'version'    => '4.1.0',
					'attr'       => true,
				),
			);
			parent::wldcfwc_do_enqueue( $resources, $args );
		}
	}

}
