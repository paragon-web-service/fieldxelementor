<?php

require_once STM_LMS_PRO_ADDONS . '/assignments/instructor.php';
require_once STM_LMS_PRO_ADDONS . '/assignments/single_assignment.php';
require_once STM_LMS_PRO_ADDONS . '/assignments/user_assignment.class.php';
require_once STM_LMS_PRO_ADDONS . '/assignments/columns.php';

new STM_LMS_Assignments();

class STM_LMS_Assignments {

	public function __construct() {
		/*ACTIONS*/
		add_action( 'init', array( $this, 'stm_lms_start_assignment' ) );

		/*AJAX*/
		add_action( 'wp_ajax_stm_lms_upload_assignment_file', array( $this, 'stm_lms_upload_assignment_file' ) );
		add_action( 'wp_ajax_stm_lms_delete_assignment_file', array( $this, 'stm_lms_delete_assignment_file' ) );
		add_action( 'wp_ajax_stm_lms_save_draft_content', array( $this, 'stm_lms_save_draft_content' ) );
		add_action( 'wp_ajax_stm_lms_accept_draft_assignment', array( $this, 'stm_lms_accept_draft_assignment' ) );

		/*FILTERS*/
		add_filter( 'stm_lms_post_types_array', array( $this, 'assignment_post_type' ), 10, 1 );
		add_filter( 'stm_lms_curriculum_post_types', array( $this, 'assignment_stm_lms_curriculum_post_types' ), 5, 1 );
		add_filter( 'stm_lms_post_types', array( $this, 'assignment_stm_lms_post_types' ), 5, 1 );
		add_filter( 'stm_lms_completed_label', array( $this, 'stm_lms_completed_label' ), 5, 2 );
		add_filter( 'stm_wpcfto_boxes', array( $this, 'stm_lms_boxes' ), 10, 1 );
		add_filter( 'stm_wpcfto_fields', array( $this, 'stm_lms_fields' ), 10, 1 );

		/*Filters*/
		add_filter( 'upload_mimes', array( $this, 'enable_extended_upload' ) );

		add_filter( 'wpcfto_options_page_setup', array( $this, 'stm_lms_settings_page' ) );

		add_filter(
			'stm_lms_header_messages_counter',
			function( $counter ) {
				$user_id = get_current_user_id();
				return $counter + STM_LMS_Instructor_Assignments::total_pending_assignments( $user_id ) + STM_LMS_User_Assignment::my_assignments_statuses( $user_id );
			}
		);

		add_filter(
			'stm_lms_menu_items',
			function ( $menus ) {
				if ( STM_LMS_Instructor::is_instructor() ) {
					$menus[] = array(
						'order'        => 40,
						'id'           => 'assignments',
						'slug'         => 'assignments',
						'lms_template' => 'stm-lms-assignments',
						'menu_title'   => esc_html__( 'Assignments', 'masterstudy-lms-learning-management-system-pro' ),
						'menu_icon'    => 'fa-pen-nib',
						'menu_url'     => STM_LMS_Instructor_Assignments::assignments_url(),
						'badge_count'  => STM_LMS_Instructor_Assignments::total_pending_assignments( get_current_user_id() ),
						'menu_place'   => 'main',
					);
				}

				return $menus;
			}
		);

	}

	public static function uploaded_attachments( $draft_id ) {
		$data        = self::get_draft_attachments( $draft_id );
		$attachments = array();
		if ( ! empty( $data ) ) {
			foreach ( $data as $attachment ) {
				$attachments[] = array(
					'data' => array(
						'name'   => $attachment->post_title,
						'id'     => $attachment->ID,
						'status' => 'uploaded',
						'error'  => false,
						'link'   => wp_get_attachment_url( $attachment->ID ),
					),
				);
			}
		}

		return $attachments;
	}

	public static function get_draft_attachments( $draft_id ) {
		return get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
				'post_parent'    => $draft_id,
				's'              => 'stm_lms_assignment',
			)
		);
	}

	public static function limit_files() {
		$settings = self::stm_lms_get_settings();

		return ( ! empty( $settings['max_files'] ) ) ? $settings['max_files'] : 5;
	}

	public static function attempts_num( $item_id ) {
		$settings = self::stm_lms_get_settings();

		$attempt_tries = ( ! empty( $settings['attempt_tries'] ) ) ? $settings['attempt_tries'] : 0;

		$item_attempts = get_post_meta( $item_id, 'assignment_tries', true );

		if ( ! empty( $item_attempts ) ) {
			$attempt_tries = $item_attempts;
		}

		return $attempt_tries;
	}

	public static function get_current_url() {
		return ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}

	public function is_assignment( $post_id ) {
		return ( get_post_type( $post_id ) === 'stm-assignments' );
	}

	public static function number_of_assignments( $item_id ) {
		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}

		$args = array(
			'post_type'   => 'stm-user-assignment',
			'post_status' => array(
				'publish',
				'draft',
				'pending',
			),
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		return $q->found_posts;
	}

	public static function has_current_assignments( $item_id, $user_id = '' ) {
		$user = STM_LMS_User::get_current_user( $user_id );
		if ( empty( $user['id'] ) ) {
			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}

		$args = array(
			'posts_per_page' => 1,
			'post_type'      => 'stm-user-assignment',
			'post_status'    => array(
				'draft',
				'pending',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => '',
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		$post = array();

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$id              = get_the_ID();
				$post['id']      = $id;
				$post['title']   = get_the_title();
				$post['meta']    = get_post_meta( get_the_ID() );
				$post['content'] = get_the_content();
			}
		}

		return $post;
	}

	public static function has_passed_assignment( $item_id, $user_id = '' ) {
		$user = STM_LMS_User::get_current_user( $user_id );

		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => 1,
			'post_status'    => array(
				'publish',
				'draft',
				'pending',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => 'passed',
					'compare' => '=',
				),
			),
		);

		$q    = new WP_Query( $args );
		$post = array();

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$id = get_the_ID();

				$post['id']        = $id;
				$post['title']     = get_the_title();
				$post['meta']      = get_post_meta( get_the_ID() );
				$post['content']   = get_the_content();
				$post['editor_id'] = get_post_field( 'post_author', get_post_meta( $id, 'assignment_id', true ) );
			}

			wp_reset_postdata();
		}

		return $post;
	}

	public static function passed_assignments( $course_id, $user_id = '' ) {
		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => 1,
			'post_status'    => array(
				'publish',
				'draft',
				'pending',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'course_id',
					'value'   => $course_id,
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => 'passed',
					'compare' => '=',
				),
			),
		);

		if ( ! empty( $user_id ) ) {
			$args['meta_query'][] = array(
				'key'     => 'student_id',
				'value'   => $user_id,
				'compare' => '=',
			);
		}

		$q = new WP_Query( $args );
		wp_reset_postdata();
		return $q->found_posts;
	}

	public static function has_unpassed_assignment( $item_id ) {
		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}

		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => 1,
			'post_status'    => array(
				'publish',
				'draft',
				'pending',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => 'not_passed',
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		$post = array();

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$id = get_the_ID();

				$post['id']        = $id;
				$post['title']     = get_the_title();
				$post['meta']      = get_post_meta( get_the_ID() );
				$post['content']   = get_the_content();
				$post['editor_id'] = get_post_field( 'post_author', get_post_meta( $id, 'assignment_id', true ) );
			}
		}

		return $post;
	}

	public static function unpassed_assignment_num( $item_id ) {
		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}

		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => -1,
			'post_status'    => array(
				'publish',
				'draft',
				'pending',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => 'not_passed',
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		return $q->found_posts;
	}

	public static function has_reviewing_assignment( $item_id ) {
		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}

		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => 1,
			'post_status'    => array(
				'pending',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => '',
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		$post = array();

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$id              = get_the_ID();
				$post['id']      = $id;
				$post['title']   = get_the_title();
				$post['meta']    = get_post_meta( get_the_ID() );
				$post['content'] = get_the_content();
			}
		}

		return $post;
	}

	public static function has_draft_assignment( $item_id ) {
		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}

		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => 1,
			'post_status'    => array(
				'draft',
			),
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $item_id,
					'compare' => '=',
				),
				array(
					'key'     => 'student_id',
					'value'   => $user['id'],
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => '',
					'compare' => '=',
				),
			),
		);

		$q    = new WP_Query( $args );
		$post = array();

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$id              = get_the_ID();
				$post['id']      = $id;
				$post['title']   = get_the_title();
				$post['meta']    = get_post_meta( get_the_ID() );
				$post['content'] = get_the_content();
			}
		}

		return $post;
	}

	/*Settings*/
	public function stm_lms_settings_page( $setups ) {
		$setups[] = array(
			'page'        => array(
				'parent_slug' => 'stm-lms-settings',
				'page_title'  => 'Assignments Settings',
				'menu_title'  => 'Assignments Settings',
				'menu_slug'   => 'assignments_settings',
			),
			'fields'      => $this->stm_lms_settings(),
			'option_name' => 'stm_lms_assignments_settings',
		);

		return $setups;

	}

	public function stm_lms_settings() {
		return apply_filters(
			'stm_lms_assignments_settings',
			array(
				'credentials' => array(
					'name'   => esc_html__( 'Credentials', 'masterstudy-lms-learning-management-system-pro' ),
					'fields' => array(
						'attempt_tries' => array(
							'type'  => 'number',
							'label' => esc_html__( 'Number of allowed attempts to pass assignment', 'masterstudy-lms-learning-management-system-pro' ),
							'value' => false,
							'hint'  => esc_html__( 'Leave field empty to disable the number of attempts', 'masterstudy-lms-learning-management-system-pro' ),
						),
						'max_files'     => array(
							'type'  => 'number',
							'label' => esc_html__( 'Number of allowed attachments', 'masterstudy-lms-learning-management-system-pro' ),
							'value' => false,
						),
						'max_file_size' => array(
							'type'  => 'number',
							'label' => esc_html__( 'Max file size (Mb)', 'masterstudy-lms-learning-management-system-pro' ),
							'value' => false,
						),
						'files_ext'     => array(
							'type'  => 'textarea',
							'label' => esc_html__( 'File extensions allowed to upload (comma separated without spaces)', 'masterstudy-lms-learning-management-system-pro' ),
							'value' => 'jpg,jpeg,png,pdf,doc,docx,ppt,pptx,pps,ppsx,xls,xlsx,psd,mp3,ogg,wav,mp4,m4v,mov,wmv,avi,mpg,zip',
						),
					),
				),
			)
		);
	}

	public static function stm_lms_get_settings() {
		return get_option( 'stm_lms_assignments_settings', array() );
	}

	/*FILTERS*/
	public function assignment_post_type( $posts ) {
		$posts['stm-assignments'] = array(
			'single' => esc_html__( 'Assignment', 'masterstudy-lms-learning-management-system-pro' ),
			'plural' => esc_html__( 'Assignments', 'masterstudy-lms-learning-management-system-pro' ),
			'args'   => array(
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_in_menu'        => 'admin.php?page=stm-lms-settings',
				'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'author' ),
			),
		);

		$posts['stm-user-assignment'] = array(
			'single' => esc_html__( 'Student Assignment', 'masterstudy-lms-learning-management-system-pro' ),
			'plural' => esc_html__( 'Student Assignments', 'masterstudy-lms-learning-management-system-pro' ),
			'args'   => array(
				'public'              => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_in_menu'        => 'admin.php?page=stm-lms-settings',
				'supports'            => array( 'title', 'editor' ),
			),
		);

		return $posts;
	}

	public function assignment_stm_lms_post_types( $post_types ) {
		$post_types[] = 'stm-assignments';
		$post_types[] = 'stm-user-assignment';

		return $post_types;
	}

	public function assignment_stm_lms_curriculum_post_types( $post_types ) {
		$post_types[] = 'stm-assignments';

		return $post_types;
	}

	public function stm_lms_completed_label( $label, $item_id ) {
		if ( ! self::is_assignment( $item_id ) ) {
			return $label;
		}

		return '';
	}

	public function stm_lms_boxes( $boxes ) {
		$boxes['stm_student_assignment_files'] = array(
			'post_type' => array( 'stm-user-assignment' ),
			'label'     => esc_html__( 'Attached Files', 'masterstudy-lms-learning-management-system-pro' ),
		);

		$boxes['stm_student_assignment'] = array(
			'post_type' => array( 'stm-user-assignment' ),
			'label'     => esc_html__( 'Assignment', 'masterstudy-lms-learning-management-system-pro' ),
		);

		return $boxes;
	}

	public function stm_lms_fields( $fields ) {
		$fields['stm_student_assignment_files'] = array(
			'section_files_assignment' => array(
				'name'   => esc_html__( 'Assignment Review', 'masterstudy-lms-learning-management-system-pro' ),
				'fields' => array(
					'assignment_files' => array(
						'type'  => 'assignment_files',
						'label' => esc_html__( 'Student uploaded files', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
			),
		);

		$fields['stm_student_assignment'] = array(
			'section_group' => array(
				'name'   => esc_html__( 'Assignment Review', 'masterstudy-lms-learning-management-system-pro' ),
				'fields' => array(
					'status'         => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Status', 'masterstudy-lms-learning-management-system-pro' ),
						'options' => array(
							''           => esc_html__( 'Not checked', 'masterstudy-lms-learning-management-system-pro' ),
							'passed'     => esc_html__( 'Passed', 'masterstudy-lms-learning-management-system-pro' ),
							'not_passed' => esc_html__( 'Not passed', 'masterstudy-lms-learning-management-system-pro' ),
						),
					),
					'editor_comment' => array(
						'type'  => 'editor',
						'label' => esc_html__( 'Editor Comment', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
			),
			'section_data'  => array(
				'name'   => esc_html__( 'Assignment Data', 'masterstudy-lms-learning-management-system-pro' ),
				'fields' => array(
					'try_num'       => array(
						'type'  => 'number',
						'label' => esc_html__( '# of try', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'start_time'    => array(
						'type'  => 'date',
						'label' => esc_html__( 'Start Date', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'end_time'      => array(
						'type'  => 'date',
						'label' => esc_html__( 'End Date', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'assignment_id' => array(
						'type'  => 'number',
						'label' => esc_html__( 'Assignment ID (dont change)', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'student_id'    => array(
						'type'  => 'number',
						'label' => esc_html__( 'Student ID (dont change)', 'masterstudy-lms-learning-management-system-pro' ),
					),
					'course_id'     => array(
						'type'  => 'number',
						'label' => esc_html__( 'Course ID (dont change)', 'masterstudy-lms-learning-management-system-pro' ),
					),
				),
			),
		);

		return $fields;
	}

	/*ACTIONS*/
	public function stm_lms_start_assignment() {
		if ( ! empty( $_GET['start_assignment'] ) && ! empty( $_GET['course_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$user = STM_LMS_User::get_current_user();
			if ( empty( $user['id'] ) ) {
				wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
				die;
			}

			$user_id         = $user['id'];
			$course_id       = intval( $_GET['course_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$item_id         = intval( $_GET['start_assignment'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$assignment_name = get_the_title( $item_id );

			$has_access = STM_LMS_User::has_course_access( $course_id, $item_id );
			if ( ! $has_access ) {
				wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
				die;
			}

			$has_current_assignments = self::has_current_assignments( $item_id );
			if ( $has_current_assignments ) {
				wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
				die;
			}

			$assignment_try = self::number_of_assignments( $item_id ) + 1;

			$new_assignment = array(
				'post_type'   => 'stm-user-assignment',
				'post_status' => 'draft',
				'post_title'  => "{$user['login']} on \"{$assignment_name}\"",
			);

			$assignment_id = wp_insert_post( $new_assignment );

			update_post_meta( $assignment_id, 'try_num', $assignment_try );
			update_post_meta( $assignment_id, 'start_time', time() * 1000 );
			update_post_meta( $assignment_id, 'status', '' );
			update_post_meta( $assignment_id, 'assignment_id', $item_id );
			update_post_meta( $assignment_id, 'student_id', $user_id );
			do_action( 'stm_lms_assignment_before_drafting', $assignment_id );

			wp_safe_redirect( remove_query_arg( array( 'start_assignment', 'course_id' ), self::get_current_url() ) );
		}
	}

	/*AJAX*/
	public static function stm_lms_upload_assignment_file( $draft_id = '', $file = '', $return = false ) {
		check_ajax_referer( 'stm_lms_upload_file_assignment', 'nonce' );

		$draft_id = ( empty( $draft_id ) ) ? $_POST['draft_id'] : $draft_id;
		if ( empty( $file ) && ! empty( $_FILES['file'] ) ) {
			$file = $_FILES['file'];
		}

		if ( empty( $draft_id ) ) {
			die;
		}

		$draft_id = intval( $draft_id );

		$allowed_extensions = array(
			'jpg',
			'jpeg',
			'png',
			'pdf',
			'doc',
			'docx',
			'ppt',
			'pptx',
			'pps',
			'ppsx',
			'xls',
			'xlsx',
			'psd',
			'mp3',
			'ogg',
			'wav',
			'mp4',
			'm4v',
			'mov',
			'wmv',
			'avi',
			'mpg',
			'zip',
		);

		$r = array();

		if ( empty( $file ) ) {
			$res = array(
				'error'   => true,
				'message' => esc_html__( 'Invalid File', 'masterstudy-lms-learning-management-system-pro' ),
			);

			if ( $return ) {
				return $res;
			} else {
				wp_send_json( $res );
			}
		}

		$path = $file['name'];
		$ext  = pathinfo( $path, PATHINFO_EXTENSION );

		/*Check file size*/
		$settings      = self::stm_lms_get_settings();
		$max_file_size = ! empty( $settings['max_file_size'] ) ? $settings['max_file_size'] : 5; /*MB*/
		$max_file_size = $max_file_size * 1024 * 1024;
		$filesize      = filesize( $file['tmp_name'] );

		if ( ! empty( $settings['files_ext'] ) ) {
			$allowed_extensions = explode( ',', $settings['files_ext'] );
		}

		if ( $filesize > $max_file_size ) {
			$res = ( array(
				'error'   => true,
				'message' => esc_html__( 'File is too large.', 'masterstudy-lms-learning-management-system-pro' ),
			) );

			if ( $return ) {
				return $res;
			} else {
				wp_send_json( $res );
			}
		}

		if ( ! in_array( $ext, $allowed_extensions, true ) ) {
			$res = ( array(
				'error'   => true,
				'message' => esc_html__( 'Invalid file extension', 'masterstudy-lms-learning-management-system-pro' ),
			) );

			if ( $return ) {
				return $res;
			} else {
				wp_send_json( $res );
			}
		}

		/*Check Limit*/
		$attachments = self::get_draft_attachments( $draft_id );

		if ( count( $attachments ) >= self::limit_files() ) {
			$res = ( array(
				'error'   => true,
				'message' => esc_html__( 'You are out of file limit.', 'masterstudy-lms-learning-management-system-pro' ),
			) );

			if ( $return ) {
				return $res;
			} else {
				wp_send_json( $res );
			}
		}

		do_action( 'stm_lms_upload_files', $return );

		$filename = basename( $path );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$upload_file = wp_upload_bits( $filename, null, file_get_contents( $file['tmp_name'] ) );

		if ( ! $upload_file['error'] ) {
			$wp_filetype   = wp_check_filetype( $filename, null );
			$attachment    = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_parent'    => $draft_id,
				'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
				'post_content'   => '',
				'post_excerpt'   => 'stm_lms_assignment',
				'post_status'    => 'inherit',
			);
			$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $draft_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
				wp_update_attachment_metadata( $attachment_id, $attachment_data );
			}

			$res = ( array(
				'error' => false,
				'id'    => $attachment_id,
				'link'  => wp_get_attachment_url( $attachment_id ),
			) );

			if ( $return ) {
				return $res;
			} else {
				wp_send_json( $res );
			}
		} else {
			$res = ( array(
				'error'   => true,
				'message' => $upload_file['error'],
			) );

			if ( $return ) {
				return $res;
			} else {
				wp_send_json( $res );
			}
		}

		if ( $return ) {
			return $r;
		} else {
			wp_send_json( $r );
		}
	}

	public function stm_lms_delete_assignment_file() {
		check_ajax_referer( 'stm_lms_delete_assignment_file', 'nonce' );

		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			die;
		}

		if ( empty( $_POST['file_id'] ) ) {
			die;
		}

		$current_user = $user['id'];
		$file_id      = intval( $_POST['file_id'] );

		$attachment = get_post( $file_id );
		$draft_id   = $attachment->post_parent;

		$assignment_student_id = intval( get_post_meta( $draft_id, 'student_id', true ) );

		/*Delete if current user delete his own file*/
		if ( $current_user === $assignment_student_id ) {
			wp_delete_attachment( $file_id, true );
		}

		wp_send_json( 'OK' );
	}


	/*FILTERS*/
	public function enable_extended_upload( $mime_types = array() ) {
		$mime_types['pdf'] = 'application/pdf';

		$mime_types['doc']  = 'application/msword';
		$mime_types['docx'] = 'application/vnd.openxmlformats officedocument.wordprocessingml.document';

		$mime_types['ppt']  = 'application/mspowerpoint';
		$mime_types['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

		$mime_types['xls']  = 'application/vnd.ms-excel';
		$mime_types['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

		$mime_types['mp3'] = 'audio/mpeg';
		$mime_types['ogg'] = 'audio/ogg';
		$mime_types['wav'] = 'audio/wav';

		$mime_types['zip'] = 'application/zip';

		return $mime_types;
	}

	public function stm_lms_save_draft_content() {
		check_ajax_referer( 'stm_lms_save_draft_content', 'nonce' );

		if ( empty( $_POST['draft_id'] ) ) {
			die;
		}

		$draft_id = intval( $_POST['draft_id'] );

		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			die;
		}
		$user_id = $user['id'];

		$assignment_student_id = intval( get_post_meta( $draft_id, 'student_id', true ) );

		if ( $user_id === $assignment_student_id ) {

			$content = ( ! empty( $_POST['content'] ) ) ? wp_kses_post( $_POST['content'] ) : '';

			wp_update_post(
				array(
					'ID'           => $draft_id,
					'post_type'    => 'stm-user-assignment',
					'post_status'  => 'draft',
					'post_title'   => get_the_title( $draft_id ),
					'post_content' => $content,
				)
			);

		}

		wp_send_json( 'OK' );
	}

	public function stm_lms_accept_draft_assignment() {
		check_ajax_referer( 'stm_lms_accept_draft_assignment', 'nonce' );

		if ( empty( $_POST['draft_id'] ) || empty( $_POST['course_id'] ) ) {
			$return = array(
				'message' => 'Failed',
			);
			wp_send_json( $return );
		}
		$content = ( ! empty( $_POST['content'] ) ) ? wp_kses_post( $_POST['content'] ) : '';

		wp_send_json( self::stm_lms_accept_draft_assignment_static( $_POST['draft_id'], $_POST['course_id'], $content ) );
	}

	public static function stm_lms_accept_draft_assignment_static( $draft_id = '', $course_id = '', $content = '' ) {
		$course_id = intval( $course_id );
		$draft_id  = intval( $draft_id );

		$user = STM_LMS_User::get_current_user();
		if ( empty( $user['id'] ) ) {
			return 'Failed';
		}
		$user_id = $user['id'];

		$assignment_student_id = intval( get_post_meta( $draft_id, 'student_id', true ) );
		$post_author_id        = get_post_field( 'post_author', get_post_meta( $draft_id, 'assignment_id', true ) );
		$instructor            = STM_LMS_User::get_current_user( $post_author_id );

		if ( $user_id === $assignment_student_id ) {

			wp_update_post(
				array(
					'ID'           => $draft_id,
					'post_type'    => 'stm-user-assignment',
					'post_status'  => 'pending',
					'post_title'   => get_the_title( $draft_id ),
					'post_content' => $content,
				)
			);

			update_post_meta( $draft_id, 'end_time', time() * 1000 );
			update_post_meta( $draft_id, 'course_id', $course_id );

			$user_login       = $user['login'];
			$course_title     = get_the_title( $course_id );
			$assignment_title = get_the_title( $draft_id );
			$assignment_meta  = get_post_meta( $draft_id );
			if ( ! empty( $assignment_meta ) && $assignment_meta['assignment_id'] ) {
				$assignment_title = get_the_title( $assignment_meta['assignment_id'][0] );
			}
			$message = sprintf(
			/* translators: %1$s Course Title, %2$s User Login */
				esc_html__( 'Check the new assignment that was submitted by the student. Assignment on %1$s sent by %2$s in the course %3$s', 'masterstudy-lms-learning-management-system' ),
				$assignment_title,
				$user_login,
				$course_title,
			);
			STM_LMS_Helpers::send_email(
				$instructor['email'],
				esc_html__( 'New assignment', 'masterstudy-lms-learning-management-system-pro' ),
				$message,
				'stm_lms_new_assignment',
				compact( 'user_login', 'course_title', 'assignment_title' )
			);

		}

		return 'OK';
	}

	public static function get_attempts( $item_id ) {
		$total_attempts = self::attempts_num( $item_id );

		if ( empty( $total_attempts ) ) {
			return array(
				'can_attempt' => 1,
			);
		}

		$attempts = self::unpassed_assignment_num( $item_id );

		return array(
			'total'       => $total_attempts,
			'attempts'    => $attempts,
			'can_attempt' => ( intval( $total_attempts ) - $attempts > 0 ),
		);

	}

	public static function student_view_update( $item_id ) {
		$item_id    = intval( $item_id );
		$student_id = get_post_meta( $item_id, 'student_id', true );

		if ( get_current_user_id() === intval( $student_id ) ) {
			update_post_meta( $item_id, 'who_view', 1 );
		}
	}
}
