<?php
/**
 * @var $assignment_id
 */

$pending           = STM_LMS_Instructor_Assignments::count_pending( $assignment_id );
$pending_transient = STM_LMS_Instructor_Assignments::pending_viewed_transient_name( $assignment_id );
set_transient( $pending_transient, $pending, 7 * 24 * 60 * 60 );

?>

<div class="info">

	<div class="total">
		<i class="fa fa-tasks"></i>
		<span>
		<?php
		printf(
			/* translators: %s Assignments Count */
			esc_html__( 'Total: %s', 'masterstudy-lms-learning-management-system-pro' ),
			STM_LMS_Instructor_Assignments::count_all( $assignment_id ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		?>
			</span>
	</div>

	<div class="unpassed">
		<i class="far fa-times-circle"></i>
		<span>
		<?php
		printf(
			/* translators: %s Assignments Count */
			esc_html__( 'Non passed: %s', 'masterstudy-lms-learning-management-system-pro' ),
			STM_LMS_Instructor_Assignments::count_unpassed( $assignment_id ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		?>
			</span>
	</div>

	<div class="passed">
		<i class="far fa-check-circle"></i>
		<span>
		<?php
		printf(
			/* translators: %s Assignments Count */
			esc_html__( 'Passed: %s', 'masterstudy-lms-learning-management-system-pro' ),
			STM_LMS_Instructor_Assignments::count_passed( $assignment_id ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		?>
			</span>
	</div>

	<div class="pending">
		<i class="far fa-clock"></i>
		<span>
		<?php
		printf(
			/* translators: %s Assignments Count */
			esc_html__( 'Pending: %s', 'masterstudy-lms-learning-management-system-pro' ),
			$pending // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		?>
		</span>
	</div>

</div>
