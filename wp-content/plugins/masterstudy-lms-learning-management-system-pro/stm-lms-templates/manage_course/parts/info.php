<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

stm_lms_register_style( 'course_info' );
?>

<div class="stm-lms-course-info heading_font">

	<div class="stm-lms-course-info__single stm-lms-course-info__single_enrolled">
		<div class="stm-lms-course-info__single_label"><span><?php esc_html_e( 'Enrolled', 'masterstudy-lms-learning-management-system-pro' ); ?></span>:
			<strong><?php esc_html_e( '0 students', 'masterstudy-lms-learning-management-system-pro' ); ?></strong>
		</div>
		<div class="stm-lms-course-info__single_icon"><i class="fa-icon-stm_icon_users"></i></div>
	</div>

	<div class="stm_lms_manage_course stm_lms_manage_course__text stm_lms_manage_course__duration">
		<div class="stm-lms-course-info__single">
			<div class="stm-lms-course-info__single_label"><span><?php esc_html_e( 'Duration', 'masterstudy-lms-learning-management-system-pro' ); ?></span>:
				<strong v-html="fields['duration_info']" v-if="fields['duration_info']"></strong>
			</div>
			<div class="stm-lms-course-info__single_icon"><i class="fa-icon-stm_icon_clock"></i></div>
		</div>
		<?php STM_LMS_Templates::show_lms_template( 'manage_course/forms/text', array( 'field_key' => 'duration_info' ) ); ?>
	</div>

	<div class="stm-lms-course-info__single stm-lms-course-info__single_lectures">
		<div class="stm-lms-course-info__single_label"><span><?php esc_html_e( 'Lectures', 'masterstudy-lms-learning-management-system-pro' ); ?></span>:
			<strong>0</strong>
		</div>
		<div class="stm-lms-course-info__single_icon"><i class="fa-icon-stm_icon_bullhorn"></i></div>
	</div>

	<div class="stm_lms_manage_course stm_lms_manage_course__text stm_lms_manage_course__video">
		<div class="stm-lms-course-info__single">
			<div class="stm-lms-course-info__single_label"><span><?php esc_html_e( 'Video', 'masterstudy-lms-learning-management-system-pro' ); ?></span>:
				<strong v-html="fields['video_duration']" v-if="fields['video_duration']"></strong>
			</div>
			<div class="stm-lms-course-info__single_icon"><i class="fa-icon-stm_icon_film-play"></i></div>
		</div>
		<?php STM_LMS_Templates::show_lms_template( 'manage_course/forms/text', array( 'field_key' => 'video_duration' ) ); ?>
	</div>

	<?php $levels = STM_LMS_Helpers::get_course_levels(); ?>
	<div class="stm_lms_manage_course stm_lms_manage_course__text stm_lms_manage_course__level">
		<div class="stm-lms-course-info__single">
			<div class="stm-lms-course-info__single_label"><span><?php esc_html_e( 'Level', 'masterstudy-lms-learning-management-system-pro' ); ?></span>:
				<strong v-html="selects['level']" v-if="fields['level']"></strong>
			</div>
			<div class="stm-lms-course-info__single_icon"><i class="lnr lnr-sort-amount-asc"></i></div>
		</div>
		<?php
		STM_LMS_Templates::show_lms_template(
			'manage_course/forms/select',
			array(
				'field_key' => 'level',
				'select'    => $levels,
			)
		);
		?>
	</div>

</div>
