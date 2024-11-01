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
<div id='wldcfwc_hp_container_div_id' class='wldcfwc_hp_container_div <?php echo esc_attr( $hp_container_div_class ); ?>'>
	<div id='wldcfwc_hp_image_div_id' class='wldcfwc_hp_image_div wldcfwc_hp_image_div_margin_bottom'>
		<?php if ( ! empty( $hp_image ) ) : ?>
			<img class='wldcfwc_hp_image' src="<?php echo esc_attr( $hp_image ); ?>">
		<?php else : ?>
			<div id='wldcfwc_hp_empty_image_div_id' class='wldcfwc_hp_empty_image_div wldcfwc_hp_image_div_margin_bottom'></div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $hp_subtitle ) ) : ?>
		<div id='wldcfwc_hp_subtitle_div_id' class='wldcfwc_hp_subtitle_div <?php echo esc_attr( $hp_subtitle_div_class ); ?>'>
			<<?php echo esc_attr( $hp_subtitle_tag ); ?> class='wldcfwc_hp_subtitle'><?php echo esc_attr( $hp_subtitle ); ?></<?php echo esc_attr( $hp_subtitle_tag ); ?>>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $hp_description ) ) : ?>
		<div id='wldcfwc_hp_description_div_id' class='wldcfwc_hp_description_div <?php echo esc_attr( $hp_description_div_class ); ?>'>
			<<?php echo esc_attr( $hp_description_tag ); ?> class='wldcfwc_hp_description'><?php echo esc_attr( $hp_description ); ?></<?php echo esc_attr( $hp_description_tag ); ?>>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $hp_buttons_html ) ) : ?>
		<div id='wldcfwc_hp_buttons_div_id' class='wldcfwc_hp_buttons_div'>
			<?php echo wp_kses( $hp_buttons_html, wldcfwc_set_allowed_tags( 'post,svg' ) ); ?>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $hp_footer ) || ! empty( $hp_show_poweredby ) ) : ?>
		<div id='wldcfwc_hp_footer_parent_div_id' class='wldcfwc_hp_footer_parent_div'>
			<?php if ( ! empty( $hp_footer ) ) : ?>
				<div id='wldcfwc_hp_footer_id' class='wldcfwc_hp_footer_div <?php echo esc_attr( $hp_footer_div_class ); ?>'>
					<span class='wldcfwc_hp_footer'><?php echo esc_attr( $hp_footer ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $hp_poweredby ) ) : ?>
				<div id='wldcfwc_hp_poweredby_id' class='wldcfwc_hp_poweredby_div <?php echo wp_kses_post( $hp_poweredby_div_class ); ?>'>
					<span class='wldcfwc_hp_poweredby wldcfwc_font_small'><?php echo wp_kses_post( $hp_poweredby ); ?></span>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
