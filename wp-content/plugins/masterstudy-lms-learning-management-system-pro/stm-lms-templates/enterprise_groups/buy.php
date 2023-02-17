<?php
/**
 * @var $course_id
 * @var $price
 */

$has_course = STM_LMS_User::has_course_access( $course_id, false );

if ( is_user_logged_in() ) :
	stm_lms_register_style( 'enterprise-course' );
	stm_lms_register_script( 'enterprise-course' );
	?>
	<span class="or heading_font enterprise-or">- <?php esc_html_e( 'For Business', 'masterstudy-lms-learning-management-system-pro' ); ?> -</span>

	<div class="stm-lms-buy-buttons stm-lms-buy-buttons-enterprise"
			data-lms-params='<?php echo wp_json_encode( compact( 'course_id' ) ); ?>'
			data-lms-modal="buy-enterprise"
			data-target=".stm-lms-modal-buy-enterprise">
		<div class="btn btn-default btn_big heading_font text-center">
			<span><?php esc_html_e( 'Buy for group', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
		</div>
	</div>
	<?php
endif;
