<?php
$average = 0;
$percent = 0;
$reviews = 0;
?>

<div class="average-rating-stars">
	<div class="average-rating-stars__top">
		<div class="star-rating"
			title="<?php /* translators: %s Average Rating */ printf( esc_html__( 'Rated %s out of 5', 'masterstudy-lms-learning-management-system-pro' ), esc_html( $average ) ); ?>">
		<span style="width:<?php echo esc_attr( $percent ); ?>%">
			<strong class="rating"><?php echo esc_attr( $average ); ?></strong>
			<?php esc_html_e( 'out of 5', 'masterstudy-lms-learning-management-system-pro' ); ?>
		</span>
		</div>
		<div class="average-rating-stars__av heading_font"><?php echo floatval( $average ); ?></div>
	</div>

	<div class="average-rating-stars__reviews">
		<?php
		printf(
			esc_html(
				/* translators: %s Reviews */
				_n(
					'%s review',
					'%s reviews',
					$reviews,
					'masterstudy-lms-learning-management-system-pro'
				)
			),
			$reviews // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		?>
	</div>

</div>
