<?php

new STM_LMS_Assignments_Columns();

class STM_LMS_Assignments_Columns {

	public function __construct() {
		add_filter( 'manage_stm-user-assignment_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_stm-user-assignment_posts_custom_column', array( $this, 'column_fields' ), 10, 2 );

		add_filter(
			'wpcfto_field_assignment_files',
			function () {
				return STM_LMS_PRO_ADDONS . '/assignments/templates/files.php';
			}
		);

		add_filter( 'parse_query', array( $this, 'filter_assignments' ) );

		add_action( 'restrict_manage_posts', array( $this, 'reset_filters' ) );

		add_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );

		add_action( 'wp_insert_post_data', array( $this, 'assignment_before_saved' ), 100, 2 );
	}

	public function columns( $columns ) {
		$columns['lms_status']     = esc_html__( 'Status', 'masterstudy-lms-learning-management-system-pro' );
		$columns['student']        = esc_html__( 'Student', 'masterstudy-lms-learning-management-system-pro' );
		$columns['course']         = esc_html__( 'Course', 'masterstudy-lms-learning-management-system-pro' );
		$columns['attempt_number'] = esc_html__( '# Attempt', 'masterstudy-lms-learning-management-system-pro' );

		unset( $columns['date'] );

		return $columns;
	}

	public function column_fields( $columns, $post_id ) {
		switch ( $columns ) {
			case 'lms_status':
				switch ( get_post_status( $post_id ) ) {
					case 'draft':
						esc_html_e(
							'Student is currently working on an assignment',
							'masterstudy-lms-learning-management-system-pro'
						);
						break;
					case 'pending':
						esc_html_e(
							'Awaiting teacher review',
							'masterstudy-lms-learning-management-system-pro'
						);
						break;
					default:
						$status = get_post_meta( $post_id, 'status', true );
						if ( 'passed' === $status ) {
							esc_html_e( 'Student passed the assignment', 'masterstudy-lms-learning-management-system-pro' );
						} else {
							esc_html_e( 'Student failed the assignment', 'masterstudy-lms-learning-management-system-pro' );
						}

						break;
				}
				break;
			case 'attempt_number':
				echo esc_html( get_post_meta( $post_id, 'try_num', true ) );
				break;
			case 'course':
				$course_id = get_post_meta( $post_id, 'course_id', true );
				if ( empty( $course_id ) ) :
					echo '---';
				else : ?>
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							'lms_course_id',
							$course_id
						)
					);
					?>
						"><?php echo esc_html( get_the_title( $course_id ) ); ?></a>
				<?php endif; ?>
				<?php
				break;
			case 'student':
				$student_id = get_post_meta( $post_id, 'student_id', true );
				$student    = STM_LMS_User::get_current_user( $student_id );
				?>
				<a href="
				<?php
				echo esc_url(
					add_query_arg(
						'lms_student_id',
						$student_id
					)
				);
				?>
					"><?php echo esc_html( $student['login'] ); ?></a>
				<?php
				break;
		}
	}

	public function filter_assignments( $query ) {
		if ( is_admin() && 'stm-user-assignment' === $query->query['post_type'] && ! wp_doing_ajax() ) {
			$qv = &$query->query_vars;

			$qv['meta_query'] = array();

			if ( ! empty( $_GET['lms_student_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$qv['meta_query'][] = array(
					'field' => 'student_id',
					'value' => intval( $_GET['lms_student_id'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
			}

			if ( ! empty( $_GET['lms_course_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$qv['meta_query'][] = array(
					'field' => 'course_id',
					'value' => intval( $_GET['lms_course_id'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
			}
		}
	}

	public function reset_filters( $post_type ) {
		if ( 'stm-user-assignment' === $post_type ) {
			echo '<ul class="subsubsub lms_filter">';
			if ( ! empty( $_GET['lms_student_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$student_id = intval( $_GET['lms_student_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$student    = STM_LMS_User::get_current_user( $student_id );
				?>
				<li>
					<a href="<?php echo esc_url( remove_query_arg( 'lms_student_id' ) ); ?>">
						<?php echo esc_html( $student['login'] ); ?>
					</a>
				</li>
				<?php
			}

			if ( ! empty( $_GET['lms_course_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$course_id = intval( $_GET['lms_course_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<li>
					<a href="<?php echo esc_url( remove_query_arg( 'lms_course_id' ) ); ?>">
						<?php echo esc_html( get_the_title( $course_id ) ); ?>
					</a>
				</li>
				<?php
			}

			echo '</ul>';
		}
	}

	public function assignment_saved( $post_id ) {
		/* Remove parse_query filter */
		remove_filter( 'parse_query', array( $this, 'filter_assignments' ) );

		if ( get_post_type( $post_id ) === 'stm-user-assignment' ) {
			/* We cant have status on draft/pending assignment */
			if ( in_array( get_post_status( $post_id ), array( 'draft', 'pending' ), true ) ) {
				update_post_meta( $post_id, 'status', '' );
			}

			/* We cant have empty status on any post status except pending */
			if ( is_admin() &&
				( isset( $_POST['status'] ) && '' === $_POST['status'] ) && // phpcs:ignore WordPress.Security.NonceVerification
				( isset( $_POST['post_status'] ) && 'draft' !== $_POST['post_status'] ) // phpcs:ignore WordPress.Security.NonceVerification
			) {
				remove_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );

				wp_update_post(
					array(
						'ID'          => $post_id,
						'post_status' => 'pending',
					)
				);

				add_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );
			}

			/* Update Course Progress */
			$student_id = get_post_meta( $post_id, 'student_id', true );
			$course_id  = get_post_meta( $post_id, 'course_id', true );
			STM_LMS_Course::update_course_progress( $student_id, $course_id );
		}
	}

	public function assignment_before_saved( $data, $postarr ) {
		/* Notify the Student about Status changes */
		if ( 'stm-user-assignment' === $postarr['post_type'] &&
			isset( $postarr['status'] ) && isset( $postarr['student_id'] ) &&
			get_post_meta( $postarr['ID'], 'status', true ) !== $postarr['status']
		) {
			$student = STM_LMS_User::get_current_user( $postarr['student_id'] );
			$message = esc_html__( 'Your assignment has been checked', 'masterstudy-lms-learning-management-system-pro' );

			STM_LMS_Helpers::send_email(
				$student['email'],
				esc_html__( 'Assignment status change.', 'masterstudy-lms-learning-management-system-pro' ),
				$message,
				'stm_lms_assignment_checked',
				compact( 'message' )
			);
		}

		return $data;
	}
}
