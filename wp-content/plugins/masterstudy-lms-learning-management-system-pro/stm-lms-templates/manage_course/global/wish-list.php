<?php
/**
 * @var $course_id
 *
 */

stm_lms_register_style( 'wishlist' );
stm_lms_register_script( 'wishlist' );
?>
<div class="stm-lms-wishlist"
		data-add="<?php esc_html_e( 'Add to Wishlist', 'masterstudy-lms-learning-management-system-pro' ); ?>"
		data-add-icon="far fa-heart"
		data-remove="<?php esc_html_e( 'Wishlisted', 'masterstudy-lms-learning-management-system-pro' ); ?>"
		data-remove-icon="fa fa-heart">
	<i class="far fa-heart"></i>
	<span><?php esc_html_e( 'Add to Wishlist', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
</div>
