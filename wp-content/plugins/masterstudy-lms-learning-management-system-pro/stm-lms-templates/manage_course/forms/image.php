<?php
/**
 * @var $field_key
 *
 */
?>

<?php STM_LMS_Templates::show_lms_template( 'manage_course/forms/js/image' ); ?>

<stm-image
		v-bind:image_id="fields['<?php echo esc_attr( $field_key ); ?>']"
		v-on:image-changed="fields['<?php echo esc_attr( $field_key ); ?>'] = $event"></stm-image>

<input type="hidden" v-model="fields['<?php echo esc_attr( $field_key ); ?>']" />
