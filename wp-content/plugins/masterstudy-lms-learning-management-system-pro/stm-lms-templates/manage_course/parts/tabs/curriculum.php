<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} //Exit if accessed directly ?>

<?php

ob_start();

require STM_LMS_PATH . '/settings/answers/components_js/answers.php';
require STM_LMS_PATH . '/settings/questions_v1/components_js/questions.php';

$script = ob_get_clean();

wp_add_inline_script(
	'stm-lms-manage_course',
	str_replace(
		array(
			'<script>',
			'</script>',
		),
		array( '', '' ),
		$script
	),
	'before'
);

STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/modal' );
STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/lesson' );
STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/assignment' );
STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/quiz' );
?>
<stm-modal></stm-modal>

<div class="stm_metaboxes_grid">
	<div class="stm_metaboxes_grid__inner">
		<?php
		do_action( 'stm_lms_manage_course_before_curriculum' );

		$field_value = "fields['curriculum']";

		ob_start();

		require STM_LMS_PATH . '/settings/curriculum/field.php';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo str_replace( 'item_edit_link"', 'item_edit_link" @click.prevent="emitMethod(item)"', ob_get_clean() );
		?>
	</div>
</div>

<input type="hidden" name="curriculum" v-model="fields['curriculum']"/>
