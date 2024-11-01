<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.
/**
 * Field: Text
 */
if ( ! class_exists( 'WLDCFWC_Exopite_Simple_Options_Framework_Field_headermenu' ) ) {

	class WLDCFWC_Exopite_Simple_Options_Framework_Field_Headermenu extends WLDCFWC_Exopite_Simple_Options_Framework_Fields {

		public function wldcfwc_output() {

			if ( isset( $this->field['menu_items'] ) ) {
				echo "<div id='wldcfwc_header_menu' class='exopite-sof-field--off exopite-sof-field-text no-border-bottom wldcfwc-hide wldcfwc-show_hide_section__wishlist_template_items__header'><h4 class='exopite-sof-title'>";
				echo esc_html__( 'Header menu', 'wishlist-dot-com-for-woocommerce' );
				echo "<p class='exopite-sof-description'>";
				echo esc_html__( "This is the menu in your WishList template header. It should typically match your store's top menu.", 'wishlist-dot-com-for-woocommerce' );
				echo '</p>';
				echo "</h4><div class='exopite-sof-fieldset'><table>";

				foreach ( $this->field['menu_items'] as $title => $url ) {
					echo "
                        <tr>
                            <td class=''><input class='wldcfwc-form-control' type='text' name='wishlist-dot-com-for-woocommerce[wlcom_wishlist_menu_item_title][]' value='" . esc_attr( $title ) . "'  placeholder=\"like '" . esc_attr( $title ) . "'\"></td>
                            <td class='wldcfwc-table-td-md-width'><input class='wldcfwc-form-control' type='text' name='wishlist-dot-com-for-woocommerce[wlcom_wishlist_menu_item_url][]' value='" . esc_url( $url ) . "' placeholder=\"like '" . esc_url( $url ) . "'\"></td>
                            <td><a class='wldcfwc-tr-delete wldcfwc-alert-error-color' href='#'>" . esc_html__( 'del', 'wishlist-dot-com-for-woocommerce' ) . "</a>&nbsp;&nbsp;<a class='wldcfwc-tr-up' href='#'>" . esc_html__( 'up', 'wishlist-dot-com-for-woocommerce' ) . "</a>&nbsp;&nbsp;<a class='wldcfwc-tr-down' href='#'>" . esc_html__( 'down', 'wishlist-dot-com-for-woocommerce' ) . '</a></td>
                        </tr>';
				}

				echo "<tr><td colspan='3' align='right'><a class='wldcfwc-tr-add exopite-sof-btn wldcfwc-button-md' href='#'>";
				echo esc_html__( 'add menu item', 'wishlist-dot-com-for-woocommerce' );
				echo '</a></td></tr></table></div></div>';

			} else {
				echo '';
			}
		}
	}

}
