<?php
/**
 * @var $course_id
 */

stm_lms_register_style( 'teacher' );

$author_id = ( ! empty( $course_id ) ) ? intval( get_post_field( 'post_author', $course_id ) ) : '';

$author = STM_LMS_User::get_current_user( $author_id );
?>

<div class="pull-left stm_lms_add_teacher">

	<a href="<?php echo esc_url( STM_LMS_User::user_public_page_url( $author['id'] ) ); ?>">
		<div class="meta-unit teacher clearfix">
			<div class="pull-left">
				<?php echo wp_kses_post( $author['avatar'] ); ?>
			</div>
			<div class="meta_values">
				<div class="label h6"><?php esc_html_e( 'Teacher', 'masterstudy-lms-learning-management-system-pro' ); ?></div>
				<div class="value heading_font h6"><?php echo esc_html( $author['login'] ); ?></div>
			</div>
		</div>
	</a>

	<?php do_action( 'stm_lms_manage_course_after_teacher' ); ?>

</div>
