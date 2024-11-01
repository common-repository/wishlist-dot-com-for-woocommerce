jQuery( document ).ready(
	function ($) {
		// Initialize the color picker on input elements with the class 'my-color-field'
		//js is loaded via wp_enqueue_style( 'wp-color-picker' )
		$( '.minicolor' ).wpColorPicker();
	}
);