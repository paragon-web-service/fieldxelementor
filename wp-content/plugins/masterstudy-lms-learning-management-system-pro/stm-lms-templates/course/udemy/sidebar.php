<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>

<?php
$post_id = get_the_ID();
?>

<div class="stm-lms-course__sidebar-holder">
	<div class="stm-lms-course__sidebar">

		<div class="stm_lms_course__image">
			<?php
			if ( function_exists( 'stm_get_VC_attachment_img_safe' ) ) {
				$allowed_tags = stm_lms_pro_allowed_html();
				echo wp_kses( stm_get_VC_attachment_img_safe( get_post_thumbnail_id( $post_id ), 'full' ), $allowed_tags );
			} else {
				echo get_the_post_thumbnail( $post_id, 'large' );
			}
			?>
		</div>

		<?php STM_LMS_Templates::show_lms_template( 'global/expired_course', array( 'course_id' => get_the_ID() ) ); ?>
		<?php STM_LMS_Templates::show_lms_template( 'global/buy-button', array( 'course_id' => $post_id ) ); ?>

		<div class="stm_lms_money_back">
			<i class="fa fa-info-circle"></i>
			<?php esc_html_e( 'Click Above to Get Started', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>

		<?php STM_LMS_Templates::show_lms_template( 'global/completed_info', array( 'course_id' => $post_id ) ); ?>

		<?php STM_LMS_Templates::show_lms_template( 'course/udemy/parts/includes', array( 'course_id' => $post_id ) ); ?>

	

	</div>
</div>
