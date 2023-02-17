<?php
$parents = STM_LMS_Manage_Course::get_terms(
	'stm_lms_course_taxonomy',
	array(
		'hide_empty' => false,
		'parent'     => 0,
	),
	false
);
?>

<div class="stm_lms_add_new_category__wrapper">

	<h4 class="stm_lms_add_new_category__title">
		<?php esc_html_e( 'Add category', 'masterstudy-lms-learning-management-system-pro' ); ?>
	</h4>

	<div class="stm_lms_add_new_category">

		<input type="text"
				id="new_category"
				v-model="new_category"
				placeholder="<?php esc_attr_e( 'Enter name', 'masterstudy-lms-learning-management-system-pro' ); ?>"/>

		<i class="lnricons-plus-circle" @click="add_new_category()" v-if="new_category.length > 4"></i>

		<div class="is_child">
			<h5><?php esc_html_e( 'Choose parent category', 'masterstudy-lms-learning-management-system-pro' ); ?></h5>
			<select class="form-control disable-select" v-model="fields['parent_category']">
				<?php foreach ( $parents as $parent_id => $parent ) : ?>
					<option value="<?php echo intval( $parent_id ); ?>"><?php echo esc_html( $parent ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

	</div>

</div>
