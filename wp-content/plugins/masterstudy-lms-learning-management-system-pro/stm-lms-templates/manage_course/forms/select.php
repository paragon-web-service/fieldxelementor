<?php
/**
 * @var $field_key
 * @var $select
 *
 */

$encoded = str_replace( "'", '`', wp_json_encode( $select ) );
?>

<div class="stm_lms_manage_course__form">
	<div class="stm_lms_manage_course__add">

		<select class="form-control disable-select"
				@change='getSelectedOption(fields["<?php echo esc_attr( $field_key ); ?>"], <?php echo stm_lms_filtered_output( $encoded ); ?>, "<?php echo esc_attr( $field_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>")'
				v-init='getSelectedOption(fields["<?php echo esc_attr( $field_key ); ?>"], <?php echo stm_lms_filtered_output( $encoded ); ?>, "<?php echo esc_attr( $field_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>")'
				v-model="fields['<?php echo esc_attr( $field_key ); ?>']" name="<?php echo esc_attr( $field_key ); ?>">
			<?php foreach ( $select as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"
						label="<?php echo sanitize_text_field( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
					<?php echo sanitize_text_field( $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php do_action( "stm_lms_after_{$field_key}_field" ); ?>

	</div>
</div>
