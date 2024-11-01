<?php
/**
 * Add to WishLIst template
 *
 * @link  https://www.wishlist.com/wishlist-dot-com-for-woocommerce
 * @since 1.0.0
 *
 * @package    wishlist-dot-com-for-woocommerce
 * @subpackage wishlist-dot-com-for-woocommerce/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div 
<?php
if ( ! empty( $button_div_id ) ) :
	?>
	id="<?php echo esc_attr( $button_div_id ); ?>"<?php endif; ?> class="<?php echo esc_attr( $button_div_class ); ?> <?php echo esc_attr( $button_div_hide_me_class ); ?>">
	<<?php echo esc_attr( $button_or_link_tag ); ?> id="<?php echo esc_attr( $button_id ); ?>" class="wldcfwc-wishlist-button wldcfwc_doTooltip <?php echo esc_attr( $button_class ); ?>" data-wldcfwc-button-parameters="<?php echo esc_attr( $button_parameters ); ?>" data-wldcfwc-tooltip-title="<?php echo esc_attr( $button_tooltip ); ?>" 
				<?php
				if ( ! empty( $link_tag_href ) ) :
					?>
		href="<?php echo esc_attr( $link_tag_href ); ?>" <?php endif; ?> <?php echo ! empty( $button_type_attr )?"type='" . esc_attr( $button_type_attr ) . "'":''; ?>>
		<span class="wldcfwc-add2wishlist-button-wrapper-img-text">
			<?php if ( ! empty( $button_icon_src ) ) : ?>
				<span class="<?php echo esc_attr( $img_wrapper_button_css ); ?>"><img class="wldcfwc-trans_icon_img_button <?php echo esc_attr( $button_icon_extra_class ); ?>" src="<?php echo esc_attr( $button_icon_src ); ?>" title="<?php echo esc_attr( $button_icon_title ); ?>"></span>
			<?php elseif ( ! empty( $button_icon_svg ) ) : ?>
				<span class="<?php echo esc_attr( $img_wrapper_button_css ); ?>"><?php echo wp_kses( $button_icon_svg, wldcfwc_set_allowed_tags( 'post,svg' ) ); ?></span>
			<?php endif; ?>

			<span class="wldcfwc-add2wishlist-button-text <?php echo esc_attr( $button_text_css ); ?>"><?php echo esc_attr( $button_text ); ?></span>
		</span>
	</<?php echo esc_attr( $button_or_link_tag ); ?>>
</div>
