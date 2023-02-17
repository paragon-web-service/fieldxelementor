<?php
/*Point system*/
function stm_lms_point_system() {
	$points = array(
		'user_registered'             => array(
			'label'       => esc_html__( 'User registered', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Register on site to get start points.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Once only', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 50,
		),
		'course_purchased'            => array(
			'label'       => esc_html__( 'Course purchased', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Purchase Course on site.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 50,
		),
		'assignment_passed'           => array(
			'label'       => esc_html__( 'Assignment Passed', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Pass Assignment with approved label from Instructor.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 25,
		),
		'certificate_received'        => array(
			'label'       => esc_html__( 'Certificate Received', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Complete course with minimum threshold, and download certificate from your account.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 25,
		),
		'quiz_passed'                 => array(
			'label'       => esc_html__( 'Quiz Passed', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Complete quiz with minimum threshold.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 15,
		),
		'perfect_quiz'                => array(
			'label'       => esc_html__( 'Quiz Passed Perfectly', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Complete quiz with 100% progress.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 15,
		),
		'lesson_passed'               => array(
			'label'       => esc_html__( 'Lesson Passed', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Complete lesson in a course.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 10,
		),
		'group_joined'                => array(
			'label'       => esc_html__( 'Group Joined', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Become a member of a group.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 5,
		),
		'friends_friendship_accepted' => array(
			'label'       => esc_html__( 'Friendship accepted', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Accept a friendship from another student.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => 5,
		),
		'course_bought'               => array(
			'label' => esc_html__( 'Course Bought', 'masterstudy-lms-learning-management-system-pro' ),
		),
	);

	$points_settings = get_option( 'stm_lms_point_system_settings', array() );

	foreach ( $points as $point_slug => &$point_data ) {
		if ( empty( $point_data['score'] ) ) {
			continue;
		}

		$value = ( isset( $points_settings[ $point_slug ] ) ) ? $points_settings[ $point_slug ] : $point_data['score'];

		$point_data['score'] = $value;
	}

	if ( ! empty( $points_settings['affiliate_points'] ) && $points_settings['affiliate_points'] ) {
		$rate = STM_LMS_Point_System_Affiliate::affiliate_rate();

		$points['user_registered_affiliate'] = array(
			'label'       => esc_html__( 'User registered (Affiliate)', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Share your affiliate link ang get points for users, using your link.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Once only', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => $points['user_registered']['score'] * $rate,
		);

		$points['course_purchased_affiliate'] = array(
			'label'       => esc_html__( 'Course purchased (Affiliate)', 'masterstudy-lms-learning-management-system-pro' ),
			'description' => esc_html__( 'Share your affiliate link ang get points for users, using your link.', 'masterstudy-lms-learning-management-system-pro' ),
			'repeat'      => esc_html__( 'Repeated', 'masterstudy-lms-learning-management-system-pro' ),
			'score'       => $points['course_purchased']['score'] * $rate,
		);
	}

	return $points;
}
