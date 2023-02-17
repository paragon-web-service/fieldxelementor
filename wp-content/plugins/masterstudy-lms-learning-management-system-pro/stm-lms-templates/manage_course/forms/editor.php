<?php
/**
 * @var $field_key
 * @var $listener
 * @var $excerpt
 *
 */

$listener = ( ! empty( $listener ) );
$excerpt  = ( ! empty( $excerpt ) );

if ( $excerpt ) {

	STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/excerpt-editor' ); ?>

	<div class="stm_lms_manage_course__editor">
		<stm-excerpt-editor v-bind:excerpt="fields['<?php echo esc_attr( $field_key ); ?>']"
							v-bind:listener="'<?php echo $listener; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'"
							v-bind:excerpt_edited.sync="fields['<?php echo esc_attr( $field_key ); ?>']"
							v-on:excerpt-changed="fields['<?php echo esc_attr( $field_key ); ?>'] = $event"></stm-excerpt-editor>

		<textarea class="hidden" v-model="fields['<?php echo esc_attr( $field_key ); ?>']"></textarea>
	</div>

	<?php
} else {

	STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/editor' );
	?>

	<div class="stm_lms_manage_course__editor">
		<stm-editor v-bind:content="fields['<?php echo esc_attr( $field_key ); ?>']"
					v-bind:listener="'<?php echo $listener; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'"
					v-bind:content_edited.sync="fields['<?php echo esc_attr( $field_key ); ?>']"
					v-on:content-changed="fields['<?php echo esc_attr( $field_key ); ?>'] = $event"></stm-editor>

		<textarea class="hidden" v-model="fields['<?php echo esc_attr( $field_key ); ?>']"></textarea>
	</div>

<?php } ?>
