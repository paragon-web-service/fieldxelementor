<?php

new STM_LMS_Point_System_Interface();

class STM_LMS_Point_System_Interface {


	public function __construct() {
		add_action( 'stm_lms_user_info_top', array( $this, 'my_points' ), 10, 1 );

		add_action( 'stm_lms_after_mixed_button_list', array( $this, 'buy_for_points' ), 10, 1 );

		add_filter( 'stm_lms_header_messages_counter', array( $this, 'counter' ), 10, 1 );

		add_filter(
			'stm_lms_menu_items',
			function ( $menus ) {
				$menus[] = array(
					'order'        => 175,
					'id'           => 'my_points',
					'slug'         => 'points-history',
					'lms_template' => 'stm-lms-user-points-history',
					'menu_title'   => esc_html__( 'My Points', 'masterstudy-lms-learning-management-system-pro' ),
					'menu_icon'    => 'fa-trophy',
					'menu_url'     => STM_LMS_Point_History::points_history_url(),
					'badge_count'  => count( stm_lms_get_incompleted_user_points( get_current_user_id() ) ),
					'menu_place'   => 'learning',
				);

				return $menus;
			}
		);
	}

	public function my_points( $current_user ) {
		STM_LMS_Templates::show_lms_template( 'account/private/parts/points/my-points', array( 'user' => $current_user ) );
	}

	public function buy_for_points( $course_id ) {
		if ( is_user_logged_in() ) {
			STM_LMS_Templates::show_lms_template( 'points/buy', array( 'course_id' => $course_id ) );
		}
	}

	public function counter( $counter ) {
		$user        = STM_LMS_User::get_current_user();
		$user_id     = $user['id'];
		$incompleted = stm_lms_get_incompleted_user_points( $user_id );

		if ( ! empty( $incompleted ) ) {
			$counter += count( $incompleted );
		}

		return $counter;
	}

}
