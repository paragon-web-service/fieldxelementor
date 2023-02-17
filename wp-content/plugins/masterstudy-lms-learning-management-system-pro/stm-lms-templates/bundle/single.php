<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;} //Exit if accessed directly ?>

<?php
stm_lms_register_style( 'login' );
stm_lms_register_style( 'register' );
?>
<?php enqueue_login_script(); ?>
<?php enqueue_register_script(); ?>
<?php stm_lms_register_style( 'course' ); ?>
<?php stm_lms_register_style( 'bundles/single' ); ?>
<?php do_action( 'stm_lms_single_bundle_start', get_the_ID() ); ?>

<div class="row">

	<div class="col-md-9">

		<h1 class="stm_lms_course__title"><?php the_title(); ?></h1>

		<?php STM_LMS_Templates::show_lms_template( 'bundle/parts/panel_info' ); ?>

		<?php STM_LMS_Templates::show_lms_template( 'bundle/parts/courses' ); ?>

		<?php STM_LMS_Templates::show_lms_template( 'bundle/parts/description' ); ?>

	</div>

	<div class="col-md-3">

		<?php STM_LMS_Templates::show_lms_template( 'bundle/sidebar' ); ?>

	</div>

</div>
