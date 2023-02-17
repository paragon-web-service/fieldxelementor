<?php
/**
 * @var $field_key
 *
 */
?>

<div class="stm_lms_manage_course__form">
	<div class="stm_lms_manage_course__add">
		<div class="form-group">
			<label>
				<h4 v-html="i18n['<?php echo esc_attr( $field_key ); ?>_label']"></h4>
				<input type="text" class="form-control" v-model="fields['<?php echo esc_attr( $field_key ); ?>']"/>
			</label>
		</div>
	</div>
</div>
