<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} // Exit if accessed directly ?>

<?php

add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'stm_lms_manage_course_page';
		return $classes;
	}
);

do_action( 'stm_lms_template_main' );

$course_id = ( ! empty( $course_id ) && is_string( $course_id ) ) ? $course_id : '';

/*If we not editing course*/
if ( ! empty( $course_id ) && get_post_type( $course_id ) !== 'stm-courses' ) {
	require_once get_404_template();
	die;
};

?>

<?php get_header(); ?>

<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

	<div class="stm-lms-wrapper user-account-page">
		<div class="container">
			<?php STM_LMS_Templates::show_lms_template( 'manage_course/single', array( 'course_id' => $course_id ) ); ?>
		</div>
	</div>

<?php get_footer(); ?>
