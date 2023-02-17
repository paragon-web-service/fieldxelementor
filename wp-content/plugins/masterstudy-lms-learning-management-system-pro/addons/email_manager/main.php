<?php
require_once STM_LMS_PRO_ADDONS . '/scorm/db.php';

new STM_LMS_Email_Manager();


class STM_LMS_Email_Manager {

	public function __construct() {
		add_filter( 'stm_lms_filter_email_data', array( $this, 'rewrite_email' ), 10, 1 );

		add_filter( 'wpcfto_options_page_setup', array( $this, 'stm_lms_settings_page' ), 100 );
	}

	public function rewrite_email( $data ) {
		if ( ! isset( $data['vars'] ) || empty( $data['filter_name'] ) ) {
			return $data;
		}
		$vars        = $data['vars'];
		$filter_name = $data['filter_name'];

		$settings = self::stm_lms_get_settings();

		if ( isset( $settings[ "{$filter_name}_subject" ] ) ) {
			$data['subject'] = $settings[ "{$filter_name}_subject" ];
		}

		if ( empty( $settings[ $filter_name ] ) ) {
			return $data;
		}

		$data['enabled'] = ( ! empty( $settings[ "{$filter_name}_enable" ] ) && $settings[ "{$filter_name}_enable" ] );

		$message = $settings[ $filter_name ];

		$occurences = self::findReplace( $message );
		if ( empty( $occurences[0] ) || empty( $occurences[1] ) ) {
			$data['message'] = $message;

			return $data;
		}

		$data['message'] = $message;

		foreach ( $occurences[1] as $occurence_index => $occurence ) {
			if ( empty( $vars[ $occurence ] ) ) {
				continue;
			}

			$data['message'] =
				str_replace( $occurences[0][ $occurence_index ], $vars[ $occurence ], $data['message'] );
		}

		return $data;
	}

	/*Settings*/
	public function stm_lms_settings_page( $setups ) {
		$setups[] = array(
			'page'        => array(
				'parent_slug' => 'stm-lms-settings',
				'page_title'  => 'Email Manager',
				'menu_title'  => 'Email Manager',
				'menu_slug'   => 'email_manager_settings',
			),
			'fields'      => $this->stm_lms_settings(),
			'option_name' => 'stm_lms_email_manager_settings',
		);

		return $setups;
	}

	public function stm_lms_settings() {
		$sections = array(
			'instructors' => esc_html__(
				'Instructors',
				'masterstudy-lms-learning-management-system-pro'
			),
			'lessons'     => esc_html__( 'Lessons', 'masterstudy-lms-learning-management-system-pro' ),
			'account'     => esc_html__( 'Account', 'masterstudy-lms-learning-management-system-pro' ),
			'enterprise'  => esc_html__( 'Enterprise', 'masterstudy-lms-learning-management-system-pro' ),
			'order'       => esc_html__( 'Orders', 'masterstudy-lms-learning-management-system-pro' ),
			'course'      => esc_html__( 'Course', 'masterstudy-lms-learning-management-system-pro' ),
			'assignment'  => esc_html__( 'Assignment', 'masterstudy-lms-learning-management-system-pro' ),
		);

		$emails = array(
			'stm_lms_course_added'                 => array(
				'section' => 'instructors',
				'notice'  => esc_html__( 'Send an email to the admin when the instructor has added their course', 'masterstudy-lms-learning-management-system-pro' ),
				'subject' => esc_html__( 'Course added', 'masterstudy-lms-learning-management-system-pro' ),
				'message' => esc_html__( 'Course {{course_title}} {{action}} by instructor, your ({{user_login}}). Please review this information from the admin Dashboard', 'masterstudy-lms-learning-management-system-pro' ),
				'vars'    => array(
					'action'       => esc_html__( 'Added or updated action made by instructor', 'masterstudy-lms-learning-management-system-pro' ),
					'user_login'   => esc_html__( 'Instructor login', 'masterstudy-lms-learning-management-system-pro' ),
					'course_title' => esc_html__( 'Course name', 'masterstudy-lms-learning-management-system-pro' ),
				),
			),
			'stm_lms_course_published'             => array(
				'section' => 'instructors',
				'notice'  => esc_html__(
					'Send an email to the instructor when the course is published',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Course published',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => esc_html__(
					'Your course - {{course_title}} was approved, and is now live on the website',
					'masterstudy-lms-learning-management-system-pro'
				),
				'vars'    => array(
					'course_title' => esc_html__(
						'Course Title',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_become_instructor_email'      => array(
				'section' => 'instructors',
				'notice'  => esc_html__(
					'Become an instructor',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New Instructor Application',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'User {{user_login}} with id - {{user_id}}, wants to become an Instructor. Degree - {{degree}}. Expertize - {{expretize}}',
				'vars'    => array(
					'user_login' => esc_html__(
						'User login',
						'masterstudy-lms-learning-management-system-pro'
					),
					'user_id'    => esc_html__(
						'User ID',
						'masterstudy-lms-learning-management-system-pro'
					),
					'degree'     => esc_html__(
						'Degree',
						'masterstudy-lms-learning-management-system-pro'
					),
					'expertize'  => esc_html__(
						'Expertize',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_lesson_comment'               => array(
				'section' => 'lessons',
				'notice'  => esc_html__(
					'A new lesson comment ',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New lesson comment',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => '{{user_login}} commented - "{{comment_content}}" on lesson {{lesson_title}} in the course {{course_title}}',
				'vars'    => array(
					'user_login'      => esc_html__(
						'User login',
						'masterstudy-lms-learning-management-system-pro'
					),
					'comment_content' => esc_html__(
						'Comment content',
						'masterstudy-lms-learning-management-system-pro'
					),
					'lesson_title'    => esc_html__(
						'Lesson title',
						'masterstudy-lms-learning-management-system-pro'
					),
					'course_title'    => esc_html__(
						'Course title',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_account_premoderation'        => array(
				'section' => 'account',
				'notice'  => esc_html__(
					'Account Premoderation',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Activate your account',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'Please activate your account via this link - {{reset_url}}',
				'vars'    => array(
					'reset_url' => esc_html__(
						'Reset URL',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_user_registered_on_site'      => array(
				'section' => 'account',
				'notice'  => esc_html__(
					'Register on the website',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'You have successfully registered on the website.',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'You are an active user on the website - {{blog_name}}. Add your information and start enrolling in courses with ease.',
				'vars'    => array(
					'blog_name' => esc_html__(
						'Blog name',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_password_change'              => array(
				'section' => 'account',
				'notice'  => esc_html__(
					'Password changed',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Password change',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'Password changed successfully.',
				'vars'    => array(),
			),
			'stm_lms_enterprise'                   => array(
				'section' => 'enterprise',
				'notice'  => esc_html__(
					'Enterprise Request',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Enterprise Request',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'Name - {{name}}; Email - {{email}}; Message - {{text}}',
				'vars'    => array(
					'name'  => esc_html__( 'Name', 'masterstudy-lms-learning-management-system-pro' ),
					'email' => esc_html__(
						'Email',
						'masterstudy-lms-learning-management-system-pro'
					),
					'text'  => esc_html__( 'Text', 'masterstudy-lms-learning-management-system-pro' ),
				),
			),
			'stm_lms_new_order'                    => array(
				'section' => 'order',
				'notice'  => esc_html__(
					'New order (for admin)',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New order',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'New Order from the user {{user_login}}.',
				'vars'    => array(
					'user_login' => esc_html__(
						'User login',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_new_order_accepted'           => array(
				'section' => 'order',
				'notice'  => esc_html__(
					'New Order (for user)',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New Order',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'Your Order has been Accepted.',
				'vars'    => array(),
			),
			'stm_lms_course_added_to_user'         => array(
				'section' => 'course',
				'notice'  => esc_html__(
					'Course added to User (for admin)',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Course added to User',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'Course {{course_title}} was added to {{login}}.',
				'vars'    => array(
					'course_title' => esc_html__(
						'Course title',
						'masterstudy-lms-learning-management-system-pro'
					),
					'login'        => esc_html__(
						'Login',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_course_available_for_user'    => array(
				'section' => 'course',
				'notice'  => esc_html__(
					'Course added to User (for user)',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Course added.',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => 'Course {{course_title}} is now available to learn.',
				'vars'    => array(
					'course_title' => esc_html__(
						'Course title',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_course_quiz_completed_for_user'    => array(
				'section' => 'course',
				'notice'  => esc_html__(
					'Quiz Completed',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Quiz Completed',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => '{{user_login}} completed the {{quiz_name}} on the course {{course_title}} with a Passing grade of {{passing_grade}}.',
				'vars'    => array(
					'user_login' => esc_html__(
						'User login',
						'masterstudy-lms-learning-management-system-pro'
					),
					'course_title' => esc_html__(
						'Course title',
						'masterstudy-lms-learning-management-system-pro'
					),
					'quiz_name' => esc_html__(
						'Quiz name',
						'masterstudy-lms-learning-management-system-pro'
					),
					'passing_grade' => esc_html__(
						'Passing grade',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_membership_course_available_for_admin'    => array(
				'section' => 'course',
				'notice'  => esc_html__( 'Course added with Membership Plan to User (for admin)', 'masterstudy-lms-learning-management-system-pro' ),
				'subject' => esc_html__( 'Course added with Membership Plan.', 'masterstudy-lms-learning-management-system-pro' ),
				'message' => 'Course {{course_title}} was added to {{login}} with {{membership_plan}}.',
				'vars'    => array(
					'course_title' => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
					'membership_plan' => esc_html__( 'Plan name', 'masterstudy-lms-learning-management-system-pro' ),
				),
			),
			'stm_lms_membership_course_available_for_user'    => array(
				'section' => 'course',
				'notice'  => esc_html__( 'Course added with Membership Plan to User (for user)', 'masterstudy-lms-learning-management-system-pro' ),
				'subject' => esc_html__( 'Course added to User', 'masterstudy-lms-learning-management-system-pro' ),
				'message' => 'Course {{course_title}} is now available to learn with {{membership_plan}}.',
				'vars'    => array(
					'course_title' => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
					'membership_plan' => esc_html__( 'Plan name', 'masterstudy-lms-learning-management-system-pro' ),
				),
			),
			'stm_lms_announcement_from_instructor' => array(
				'section' => 'instructors',
				'notice'  => esc_html__(
					'Announcement from the Instructor',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Announcement from the Instructor',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => '{{mail}}',
				'vars'    => array(
					'mail' => esc_html__(
						'Instructor message',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_assignment_checked'           => array(
				'section' => 'assignment',
				'notice'  => esc_html__(
					'Assignment status change (for student)',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'Assignment status change.',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => esc_html__(
					'Your assignment has been checked',
					'masterstudy-lms-learning-management-system-pro'
				),
				'vars'    => array(),
			),
			'stm_lms_new_assignment'               => array(
				'section' => 'assignment',
				'notice'  => esc_html__(
					'New assignment (for instructor)',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New assignment',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => esc_html__(
					'Check the new assignment that was submitted by the student. Assignment on "{{assignment_title}}" sent by {{user_login}} in the course "{{course_title}}"',
					'masterstudy-lms-learning-management-system-pro'
				),
				'vars'    => array(
					'user_login'       => esc_html__( 'User Login', 'masterstudy-lms-learning-management-system-pro' ),
					'course_title'     => esc_html__( 'Course title', 'masterstudy-lms-learning-management-system-pro' ),
					'assignment_title' => esc_html__( 'Assignment title', 'masterstudy-lms-learning-management-system-pro' ),

				),
			),
			'stm_lms_new_group_invite'             => array(
				'section' => 'enterprise',
				'notice'  => esc_html__(
					'New group invite',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New group invite',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => esc_html__(
					'You were added to the group: {{site_name}}. Now you can check their new courses.',
					'masterstudy-lms-learning-management-system-pro'
				),
				'vars'    => array(
					'site_name' => esc_html__(
						'Site name',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_new_user_creds'               => array(
				'section' => 'enterprise',
				'notice'  => esc_html__(
					'New user credentials for enterprise group',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New user credentials for enterprise group',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => esc_html__(
					'Login: {{username}} Password: {{password}} Site URL: {{site_url}}',
					'masterstudy-lms-learning-management-system-pro'
				),
				'vars'    => array(
					'username' => esc_html__(
						'Username',
						'masterstudy-lms-learning-management-system-pro'
					),
					'password' => esc_html__(
						'Password',
						'masterstudy-lms-learning-management-system-pro'
					),
					'site_url' => esc_html__(
						'Site URL',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
			'stm_lms_enterprise_new_group_course'  => array(
				'section' => 'enterprise',
				'notice'  => esc_html__(
					'New course available for enterprise group',
					'masterstudy-lms-learning-management-system-pro'
				),
				'subject' => esc_html__(
					'New course available for enterprise group',
					'masterstudy-lms-learning-management-system-pro'
				),
				'message' => __(
					'<p>{{admin_login}} invited you to the group {{group_name}} on {{blog_name}}. You were added to the {{course_title}} course.</p>',
					'masterstudy-lms-learning-management-system-pro'
				),
				'vars'    => array(
					'admin_login'  => esc_html__(
						'Admin login',
						'masterstudy-lms-learning-management-system-pro'
					),
					'group_name'   => esc_html__(
						'Group name',
						'masterstudy-lms-learning-management-system-pro'
					),
					'blog_name'    => esc_html__(
						'Blog name',
						'masterstudy-lms-learning-management-system-pro'
					),
					'course_title' => esc_html__(
						'Course title',
						'masterstudy-lms-learning-management-system-pro'
					),
					'user_url'     => esc_html__(
						'User url',
						'masterstudy-lms-learning-management-system-pro'
					),
				),
			),
		);

		$emails = apply_filters( 'stm_lms_email_manager_emails', $emails );

		$data = array();

		foreach ( $sections as $section_key => $section ) {
			$data[ $section_key ] = array(
				'name'   => $section,
				'fields' => array(),
			);
		}

		foreach ( $emails as $email_key => $email ) {
			$data[ $email['section'] ]['fields'][ "{$email_key}_enable" ] = array(
				'group' => 'started',
				'type'  => 'checkbox',
				'label' => $email['notice'],
				'value' => true,
			);
			$data[ $email['section'] ]['fields'][ "{$email_key}_subject" ] = array(
				'type'       => 'text',
				'label'      => esc_html__( 'Subject', 'masterstudy-lms-learning-management-system-pro' ),
				'value'      => $email['subject'],
				'dependency' => array(
					'key'   => "{$email_key}_enable",
					'value' => 'not_empty',
				),
			);
			$email_textarea = 'hint_textarea';
			if ( defined( 'STM_WPCFTO_VERSION' ) && STM_LMS_Helpers::is_pro_plus() ) {
				$email_textarea = 'trumbowyg';
			}
			$data[ $email['section'] ]['fields'][ $email_key ] = array(
				'type'       => $email_textarea,
				'group'      => ( STM_LMS_Helpers::is_pro_plus() ) ? '' : 'ended',
				'label'      => esc_html__( 'Message', 'masterstudy-lms-learning-management-system-pro' ),
				'value'      => $email['message'],
				'hints'      => $email['vars'],
				'dependency' => array(
					'key'   => "{$email_key}_enable",
					'value' => 'not_empty',
				),
			);
			if ( STM_LMS_Helpers::is_pro_plus() ) {
				$data[ $email['section'] ]['fields'][ "{$email_key}_hidden" ] = array(
					'type'       => 'send_email',
					'group'      => 'ended',
					'label'      => esc_html__( 'Hidden', 'masterstudy-lms-learning-management-system-pro' ),
					'value'      => $email_key,
					'dependency' => array(
						'key'   => "{$email_key}_enable",
						'value' => 'not_empty',
					),
				);
			}
		}

		return apply_filters( 'stm_lms_email_manager_settings', $data );
	}

	public static function stm_lms_get_settings() {
		return get_option( 'stm_lms_email_manager_settings', array() );
	}

	public static function findReplace( $string ) {
		preg_match_all( '~\{\{\s*(.*?)\s*\}\}~', $string, $values );

		return $values;
	}

	public static function stm_lms_get_image_by_id( $id ) {
		if ( ! empty( $id ) ) {
			$image_url = wp_get_attachment_image_url( $id, 'full' );
			if ( ! empty( $image_url ) ) {
				return $image_url;
			}
		}

		return '';
	}

}
