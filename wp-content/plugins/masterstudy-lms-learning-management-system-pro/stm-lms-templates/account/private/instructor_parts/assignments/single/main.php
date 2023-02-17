<?php
/**
 * @var $assignment_id
 */
?>

<h2><?php echo esc_html( get_the_title( $assignment_id ) ); ?></h2>

<div class="stm_lms_instructor_assignment__actions">
	<?php STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/assignments/single/sort' ); ?>
	<?php STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/assignments/single/info', compact( 'assignment_id' ) ); ?>
</div>

<?php STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/assignments/single/list' ); ?>

<?php STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/assignments/single/pages' ); ?>
