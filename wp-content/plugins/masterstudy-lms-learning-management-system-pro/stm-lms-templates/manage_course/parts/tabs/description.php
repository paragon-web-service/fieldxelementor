<div class="stm_lms_course__image stm_lms_wizard_step_3">
	<?php STM_LMS_Templates::show_lms_template(
		'manage_course/forms/image',
		array(
			'field_key' => 'image',
		)
	) ?>
</div>

<div class="stm_lms_course__content stm_lms_wizard_step_4">
	<?php
	STM_LMS_Templates::show_lms_template(
		'manage_course/forms/editor',
		array(
			'field_key' => 'content',
		)
	)
	?>
</div>

<div class="stm_lms_course__file_attachment">
	<?php
	STM_LMS_Templates::show_lms_template(
		'manage_course/forms/course_files',
		array(
			'field_key' => 'course_files',
		)
	)
	?>
</div>
