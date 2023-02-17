<div class="stm-lms-course__sidebar">
	<?php STM_LMS_Templates::show_lms_template( 'manage_course/global/wish-list' ); ?>

	<?php STM_LMS_Templates::show_lms_template( 'manage_course/global/buy-button' ); ?>

	<?php STM_LMS_Templates::show_lms_template( 'manage_course/global/prerequisites' ); ?>

	<?php STM_LMS_Templates::show_lms_template( 'manage_course/parts/info', array( 'course_id' => $post_id ) ); ?>
</div>
