<?php
/**
 * @var $assignment_id
 */

$assignment = STM_LMS_User_Assignment::get_assignment( $assignment_id );

?>

	<h2><?php echo esc_html( $assignment['assignment_title'] ); ?></h2>

	<div class="stm_lms_assignment__edit">

		<div class="inner">

			<div class="assignment_approved_content">
				<?php echo wp_kses_post( $assignment['content'] ); ?>
			</div>

			<?php if ( ! empty( $assignment['files'] ) ) : ?>
				<div class="assignment_files">
					<?php foreach ( $assignment['files'] as $file ) : ?>
						<a href="<?php echo esc_url( wp_get_attachment_url( $file->ID ) ); ?>" class="assignment_file
							<?php
							if ( STM_LMS_User_Assignment::has_preview( $file ) ) {
								echo esc_attr( 'image' );}
							?>
							" target="_blank" download="">
							<i class="far fa-<?php echo esc_attr( STM_LMS_User_Assignment::get_file_icon( $file ) ); ?>"></i>
							<span class="heading_font"><?php echo esc_attr( $file->post_title . '.' . STM_LMS_User_Assignment::get_file_ext( $file ) ); ?></span>
							<?php if ( STM_LMS_User_Assignment::has_preview( $file ) ) : ?>
								<img src="<?php echo esc_url( wp_get_attachment_url( $file->ID ) ); ?>" />
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>

	</div>


<?php STM_LMS_Templates::show_lms_template(
	'account/private/instructor_parts/user_assignments/' . get_post_status( $assignment_id ),
	compact( 'assignment_id', 'assignment' )
); ?>
