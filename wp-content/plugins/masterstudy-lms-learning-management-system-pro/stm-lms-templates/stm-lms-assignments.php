<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} //Exit if accessed directly ?>

<?php
get_header();

if ( ! is_user_logged_in() ) {
	STM_LMS_User::js_redirect( STM_LMS_User::login_page_url() );
}

stm_lms_register_style( 'instructor_assignments' );
stm_lms_register_script( 'instructor_assignments', array( 'vue.js', 'vue-resource.js' ) );
wp_localize_script(
	'stm-lms-instructor_assignments',
	'stm_lms_assignments',
	array(
		'tasks'        => STM_LMS_Instructor_Assignments::get_instructor_assignments(
			array(
				'posts_per_page' => STM_LMS_Instructor_Assignments::per_page(),
			)
		),
		'courses'      => STM_LMS_Instructor::get_courses(
			array(
				'posts_per_page' => 5,
			),
			true
		),
		'translations' => array(
			'group_limit' => esc_html__( 'Group Limit:', 'masterstudy-lms-learning-management-system-pro' ),
		),
	)
);

do_action( 'stm_lms_template_main' );

$style = STM_LMS_Options::get_option( 'profile_style', 'default' );

?>

<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

	<div class="stm-lms-wrapper stm-lms-wrapper--assignments user-account-page">

		<div class="container">

			<?php do_action( 'stm_lms_admin_after_wrapper_start', STM_LMS_User::get_current_user() ); ?>

			<div id="stm_lms_instructor_assignments">
				<?php STM_LMS_Templates::show_lms_template( 'account/private/instructor_parts/assignments/grid' ); ?>
				<div v-bind:class="{'has-pagination' : pages !== 1}">
				</div>

				<div class="asignments_grid__pagination" v-if="pages !== 1">
					<ul class="page-numbers">

						<li v-for="single_page in pages">

							<a class="page-numbers"
							   href="#"
							   v-if="single_page !== page"
							   @click.prevent="page = single_page; getAssignments()"
							   v-bind:class="computePage(single_page)">
								{{single_page}}
							</a>
							<span v-else class="page-numbers current">{{single_page}}</span>
						</li>
					</ul>
				</div>

			</div>

		</div>

	</div>

<?php get_footer(); ?>
