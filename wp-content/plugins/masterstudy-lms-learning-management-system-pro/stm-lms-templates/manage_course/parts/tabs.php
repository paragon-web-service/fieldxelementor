<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$tabs = array(
	'description'  => esc_html__( 'Description', 'masterstudy-lms-learning-management-system-pro' ),
	'curriculum'   => esc_html__( 'Curriculum', 'masterstudy-lms-learning-management-system-pro' ),
	'faq'          => esc_html__( 'FAQ', 'masterstudy-lms-learning-management-system-pro' ),
	'announcement' => esc_html__( 'Announcement', 'masterstudy-lms-learning-management-system-pro' ),
);

$active = 'description';
?>

<div class="nav-tabs-wrapper">
	<ul class="nav nav-tabs" role="tablist">

		<?php foreach ( $tabs as $slug => $name ) : ?>
			<li role="presentation" class="<?php echo ( $slug === $active ) ? 'active' : ''; ?>">
				<a href="<?php echo esc_attr( $slug ); ?>"
					data-toggle="tab">
					<?php echo wp_kses_post( $name ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>


<div class="tab-content">
	<?php foreach ( $tabs as $slug => $name ) : ?>
		<div role="tabpanel"
			class="tab-pane <?php echo ( $slug === $active ) ? 'active' : ''; ?>"
			id="<?php echo esc_attr( $slug ); ?>">

			<?php if ( ! STM_LMS_Options::get_option( "course_tab_{$slug}", true ) ) : ?>
				<div class="stm-lms-message error">
					<?php
					printf(
						/* translators: %s Tab Title */
						esc_html__( 'The %s tab was temporarily disabled by the admin', 'masterstudy-lms-learning-management-system-pro' ),
						esc_html( $name )
					);
					?>
				</div>
			<?php endif; ?>

			<?php STM_LMS_Templates::show_lms_template( 'manage_course/parts/tabs/' . $slug ); ?>

		</div>
	<?php endforeach; ?>
</div>
