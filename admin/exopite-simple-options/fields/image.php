<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Image
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_image' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Image extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {


		public function __construct( $field, $value = '', $unique = '', $config = array() ) {
			parent::__construct( $field, $value, $unique, $config );
		}

		/**
		 * Get an attachment ID given a URL.
		 *
		 * @param string $url
		 *
		 * @return int Attachment ID on success, 0 on failure
		 */
		public function wldcfwc_get_attachment_id( $url ) {
			$attachment_id = 0;
			$dir           = wp_upload_dir();

			// To handle relative urls
			if ( substr( $url, 0, strlen( '/' ) ) === '/' ) {

				$url = get_site_url() . $url;
			}
			if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?

				$file       = basename( $url );
				$query_args = array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'fields'      => 'ids',
					// rarely used in admin panel, so this query can use meta_query.
					'meta_query'  => array(
						array(
							'value'   => $file,
							'compare' => 'LIKE',
							'key'     => '_wp_attachment_metadata',
						),
					),
				);
				$query      = new WP_Query( $query_args );
				if ( $query->have_posts() ) {
					foreach ( $query->posts as $post_id ) {
						$meta                = wp_get_attachment_metadata( $post_id );
						$original_file       = basename( $meta['file'] );
						$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
						if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
								$attachment_id = $post_id;
								break;
						}
					}
				}
			}

			return $attachment_id;
		}

		public function wldcfwc_output() {

			/**
			 * Open WordPress Media Uploader with PHP and JavaScript
			 *
			 * @link https://rudrastyh.com/wordpress/customizable-media-uploader.html
			 */

			$preview           = '';
			$value             = esc_html( $this->wldcfwc_element_value() );
			$add               = ( ! empty( $this->field['add_title'] ) ) ? $this->field['add_title'] : esc_attr__( 'Add Image', 'exopite-sof' );
			$hidden            = ( empty( $value ) ) ? ' hidden' : '';
			$classes           = ( isset( $this->field['class'] ) ) ? implode( ' ', explode( ' ', $this->field['class'] ) ) : '';
			$allowed_html_tags = wldcfwc_set_allowed_tags( 'post,svg' );

			echo wp_kses( $this->wldcfwc_element_before(), $allowed_html_tags );

			if ( ! empty( $value ) ) {
				$attachment = wp_get_attachment_image_src( $this->wldcfwc_get_attachment_id( $value ), 'full' );
				if ( $attachment ) {
					$preview = $attachment[0];
				}
			}

			if ( isset( $this->field['default'] ) ) {
				$default_att = "data-default='" . $this->field['default'] . " '";
			} else {
				$default_att = '';
			}

			echo '<div class="exopite-sof-media exopite-sof-image ' . wp_kses( $classes, array() ) . '" ' . wp_kses( $this->wldcfwc_element_attributes(), array() ) . '>';
			echo '<div class="exopite-sof-image-preview' . esc_attr( $hidden ) . '">';
			echo '<div class="exopite-sof-image-inner"><i class="fa fa-times exopite-sof-image-remove"></i><img src="' . esc_url( $preview ) . '" alt="preview" /></div>';
			echo '</div>';

			echo '<input type="text" name="' . esc_attr( $this->wldcfwc_element_name() ) . '" value="' . esc_attr( $this->wldcfwc_element_value() ) . '" ' . wp_kses( $default_att, array() ) . '>';
			echo '<a href="#" class="button button-primary exopite-sof-button">' . wp_kses( $add, $allowed_html_tags ) . '</a>';
			echo '</div>';

			echo wp_kses( $this->wldcfwc_element_after(), $allowed_html_tags );
		}

		public static function wldcfwc_enqueue( $args ) {

			wp_enqueue_media();
		}
	}

}
