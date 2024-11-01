<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Range
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_range' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Range extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		public function wldcfwc_output() {

			/**
			 * Update input if range changed
			 *
			 * @link https://stackoverflow.com/questions/10004723/html5-input-type-range-show-range-value/45210546#45210546
			 */

			// Initialize attributes array
			$attr = array();

			// Set and sanitize min, max, and step values
			if (!empty($this->field['min'])) {
				$min = (float) esc_attr($this->field['min']);
				$attr[] = 'min="' . $min . '"';
			} else {
				$min = null; // Set as null if not provided
			}

			if (!empty($this->field['max'])) {
				$max = (float) esc_attr($this->field['max']);
				$attr[] = 'max="' . $max . '"';
			} else {
				$max = null; // Set as null if not provided
			}

			if (!empty($this->field['step'])) {
				$step = (float) esc_attr($this->field['step']);
				$attr[] = 'step="' . $step . '"';
			} else {
				$step = null; // Set as null if not provided
			}

			// Get the current value and ensure it is numeric
			$value = (float) esc_attr($this->wldcfwc_element_value());

			// Adjust the value to fit within min, max, and step constraints
			if (isset($min) && $value < $min) {
				$value = $min; // Set to min if below min
			}

			if (isset($max) && $value > $max) {
				$value = $max; // Set to max if above max
			}

			// Adjust value to fit the step constraint if step is set and greater than 0
			if (isset($step) && $step > 0) {
				// Adjust the value to the nearest valid step
				if (isset($min)) {
					$value = round(( $value - $min ) / $step) * $step + $min; // Align with step and min
				} else {
					$value = round($value / $step) * $step; // Align with step without min reference
				}

				// Final range check to ensure value is within min and max bounds
				if (isset($min) && $value < $min) {
					$value = $min;
				}
				if (isset($max) && $value > $max) {
					$value = $max;
				}
			}

			// Build the attributes string
			$attrs = ( !empty($attr) ) ? ' ' . trim(implode(' ', $attr)) : '';

			// Prepare other fields
			$unit = ( isset($this->field['unit']) ) ? '<em>' . esc_attr($this->field['unit']) . '</em>' : '';
			$classes = ( isset($this->field['class']) ) ? esc_attr(implode(' ', explode(' ', $this->field['class']))) : '';
			$allowed_html_tags = wldcfwc_set_allowed_tags('post,svg');

			// Output the elements
			echo wp_kses($this->wldcfwc_element_before(), $allowed_html_tags);

			echo '<input type="range" name="' . esc_attr($this->wldcfwc_element_name()) . '" oninput="updateRangeInput(this)" class="range ' . esc_attr($classes) . '" ' . wp_kses($attrs, array()) . ' value="' . esc_attr($value) . '" ' . wp_kses($this->wldcfwc_element_attributes(), array()) . '>' . wp_kses($unit, $allowed_html_tags);

			echo '<input type="number" value="' . esc_attr($value) . '" oninput="updateInputRange(this)" class="exopite-range-input" ' . wp_kses($attrs, array()) . '>';

			echo wp_kses($this->wldcfwc_element_after(), $allowed_html_tags);

		}
	}

}
