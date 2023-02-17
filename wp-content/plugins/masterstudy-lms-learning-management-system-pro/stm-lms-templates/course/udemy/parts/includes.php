<?php
/**
 * @var $course_id
 */

$video       = get_post_meta( $course_id, 'udemy_content_length_video', true );
$assets      = get_post_meta( $course_id, 'udemy_num_additional_assets', true );
$articles    = get_post_meta( $course_id, 'udemy_num_article_assets', true );
$certificate = get_post_meta( $course_id, 'udemy_has_certificate', true );
$duration = get_post_meta(get_the_id(), 'duration', true);
?>

<div class="stm_lms_udemy_includes">

	<h4><?php esc_html_e( 'Includes', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>

	<?php if ( ! empty( $video ) ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-play primary_color"></i>
			<?php
			printf(
				/* translators: %s Hours */
				esc_html__( '%s hours on-demand video', 'masterstudy-lms-learning-management-system-pro' ),
				round( $video / 3600, 0 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			?>
		</div>
		<?php
	else :
		$video = get_post_meta( $course_id, 'video_duration', true );
		if ( ! empty( $video ) ) :
			?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-play primary_color"></i>
			<?php echo esc_html( $video ); ?>
		</div>
			<?php
		endif;
	endif;
	?>


	<?php if ( ! empty( $articles ) ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-text-format primary_color"></i>
			<?php
			printf(
				/* translators: %s Articles */
				_n( '%s article', '%s articles', $articles, 'masterstudy-lms-learning-management-system-pro' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$articles // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
			?>
		</div>
	<?php endif; ?>

	<div class="stm_lms_udemy_include heading_font">
		<i class="lnricons-clock3 primary_color"></i>
		<?php esc_html_e( 'Over 20+ Lessons', 'masterstudy-lms-learning-management-system-pro' ); ?>
	</div>

	<div class="stm_lms_udemy_include heading_font">
		<i class="lnricons-laptop-phone primary_color"></i>
		<?php esc_html_e( 'Access on mobile and TV', 'masterstudy-lms-learning-management-system-pro' ); ?>
	</div>

	<?php if ( $certificate ) : ?>
		<div class="stm_lms_udemy_include heading_font">
			<i class="lnricons-license2 primary_color"></i>
			<?php esc_html_e( 'Certificate of Completion', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</div>
	<?php endif; ?>

</div>


