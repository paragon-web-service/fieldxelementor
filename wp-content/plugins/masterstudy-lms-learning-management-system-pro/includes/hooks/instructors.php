<?php

add_action( 'stm_lms_instructor_links', 'stm_lms_instructor_links_pro' );

function stm_lms_instructor_links_pro( $links ) {
	return apply_filters(
		'stm_lms_instructor_links_pro',
		array(
			'add_new' => STM_LMS_Manage_Course::manage_course_url(),
		)
	);
}

add_action( 'stm_lms_course_edit_link', 'stm_lms_pro_course_edit_link', 10, 2 );

function stm_lms_pro_course_edit_link( $link, $post_id ) {
	return STM_LMS_Manage_Course::manage_course_url() . "/{$post_id}";
}
