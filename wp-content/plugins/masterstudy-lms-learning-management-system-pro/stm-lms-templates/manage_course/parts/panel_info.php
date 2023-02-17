<?php
/**
 * @val $course_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$course_id = ( ! empty( $course_id ) ) ? $course_id : '';
?>

<div class="single_product_after_title">
	<div class="clearfix">
		<div class="pull-left meta_pull">
			<?php STM_LMS_Templates::show_lms_template( 'manage_course/parts/panel_info/teacher', array( 'course_id' => $course_id ) ); ?>
			<?php STM_LMS_Templates::show_lms_template( 'manage_course/parts/panel_info/categories' ); ?>
		</div>

		<div class="pull-right xs-comments-left">
			<div class="stm_lms_course__panel_rate">
				<?php STM_LMS_Templates::show_lms_template( 'manage_course/parts/panel_info/rate' ); ?>
			</div>
		</div>
	</div>
</div>
