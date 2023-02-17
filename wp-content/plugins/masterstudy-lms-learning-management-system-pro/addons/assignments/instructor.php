<?php
new STM_LMS_Instructor_Assignments();

class STM_LMS_Instructor_Assignments {

	public function __construct() {
		add_action( 'wp_ajax_stm_lms_get_instructor_assingments', array( $this, 'stm_lms_get_instructor_assingments' ) );

		add_action( 'wp_ajax_stm_lms_get_assignment_data', array( $this, 'stm_lms_get_assignment_data' ) );
	}

	public static function assignments_url() {
		$settings = get_option( 'stm_lms_settings', array() );

		if ( empty( $settings['user_url'] ) || ! did_action( 'init' ) ) {
			return home_url( '/' );
		}

		return get_the_permalink( $settings['user_url'] ) . 'assignments';
	}

	public static function assignment_url( $id ) {
		return self::assignments_url() . '/' . $id;
	}

	public static function per_page() {
		return apply_filters( 'stm_lms_instructor_assignments', 9 );
	}

	public static function total_pending_assignments( $user_id ) {
		$args = array(
			'author'         => $user_id,
			'post_type'      => 'stm-assignments',
			'posts_per_page' => -1,
		);

		$current_user_posts = wp_list_pluck( get_posts( $args ), 'ID' );

		if ( empty( $current_user_posts ) ) {
			return 0;
		}

		$args = array(
			'post_type'      => 'stm-user-assignment',
			'posts_per_page' => 1,
			'post_status'    => array( 'pending' ),
			'meta_query'     => array(
				array(
					'key'     => 'assignment_id',
					'value'   => $current_user_posts,
					'compare' => 'IN',
				),
			),
		);

		$q = new WP_Query( $args );

		return $q->found_posts;
	}

	public function stm_lms_get_instructor_assingments() {
		check_ajax_referer( 'stm_lms_get_instructor_assingments', 'nonce' );

		$page     = intval( $_GET['page'] );
		$per_page = self::per_page();

		$args = array();

		$args['posts_per_page'] = $per_page;
		$args['offset']         = ( $page * $per_page ) - $per_page;
		$args['s']              = ( ! empty( $_GET['s'] ) ) ? sanitize_text_field( $_GET['s'] ) : '';
		if ( ! empty( $_GET['course_id'] ) ) {
			$course_id        = intval( $_GET['course_id'] );
			$curriculum       = STM_LMS_Course::get_course_curriculum( $course_id );
			$args['post__in'] = $curriculum['curriculum'];
		}

		$assignments = self::get_instructor_assignments( $args );

		wp_send_json( $assignments );
	}

	public static function get_instructor_assignments( $args = array() ) {
		$instructor = STM_LMS_User::get_current_user();
		if ( empty( $instructor['id'] ) ) {
			die;
		}
		$instructor_id = $instructor['id'];

		$r = array(
			'posts' => array(),
		);

		$default_args = array(
			'post_type' => 'stm-assignments',
			'author'    => $instructor_id,
		);

		$args = wp_parse_args( $args, $default_args );

		$q = new WP_Query( $args );

		$r['total'] = intval( $q->found_posts );
		$r['pages'] = ceil( $r['total'] / self::per_page() );

		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();

				$id = get_the_ID();

				$r['posts'][] = array(
					'id'     => $id,
					'title'  => get_the_title(),
					'url'    => self::assignment_url( $id ),
					'viewed' => true,
				);

			}
		}

		return $r;
	}

	public static function count_all( $assignment_id ) {
		$args = array(
			'post_type'      => 'stm-user-assignment',
			'post_status'    => array( 'publish', 'pending', 'draft' ),
			'posts_per_page' => 0,
			'meta_query'     => array(
				array(
					'key'     => 'assignment_id',
					'value'   => $assignment_id,
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		return intval( $q->found_posts );
	}

	public static function count_passed( $assignment_id ) {
		$args = array(
			'post_type'      => 'stm-user-assignment',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => 0,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $assignment_id,
					'compare' => '=',
				),
				array(
					'key'     => 'status',
					'value'   => 'passed',
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		return intval( $q->found_posts );
	}

	public static function count_unpassed( $assignment_id ) {
		$args = array(
			'post_type'      => 'stm-user-assignment',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => 0,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $assignment_id,
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

		return intval( $q->found_posts );
	}

	public static function count_pending( $assignment_id ) {
		$args = array(
			'post_type'      => 'stm-user-assignment',
			'post_status'    => array( 'pending' ),
			'posts_per_page' => 0,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'assignment_id',
					'value'   => $assignment_id,
					'compare' => '=',
				),
			),
		);

		$q = new WP_Query( $args );

		return intval( $q->found_posts );
	}

	public static function pending_viewed_transient_name( $assignment_id ) {
		return "stm_lms_pending_assignments_seen_{$assignment_id}";
	}

	public function stm_lms_get_assignment_data() {
		check_ajax_referer( 'stm_lms_get_assignment_data', 'nonce' );

		$instructor = STM_LMS_User::get_current_user();
		if ( empty( $instructor['id'] ) ) {
			die;
		}

		$assignment_id = intval( $_GET['id'] );

		$pending_watched = get_transient( self::pending_viewed_transient_name( $assignment_id ) );
		$pending_watched = ( ! empty( $pending_watched ) ) ? intval( $pending_watched ) : 0;

		$r = array(
			'total'           => self::count_all( $assignment_id ),
			'passed'          => self::count_passed( $assignment_id ),
			'unpassed'        => self::count_unpassed( $assignment_id ),
			'pending'         => self::count_pending( $assignment_id ),
			'pending_watched' => $pending_watched,
		);

		wp_send_json( $r );
	}

}
