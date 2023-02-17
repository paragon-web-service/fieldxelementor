<div class="pull-left xs-product-cats-left">
	<div class="stm_lms_manage_course stm_lms_manage_course__text stm_lms_manage_course__category stm_lms_wizard_step_2">
		<div class="meta-unit categories clearfix">
			<div class="pull-left">
				<i class="fa-icon-stm_icon_category secondary_color"></i>
			</div>
			<div class="meta_values">
				<div class="label h6"><?php esc_html_e( 'Category:', 'masterstudy-lms-learning-management-system-pro' ); ?></div>

				<div class="value h6" v-html="selects['category']" v-if="fields['category']"></div>
				<div class="value h6 stm_lms_phantom" v-html="i18n['category']" v-if="!fields['category']"></div>

				<div class="stm_lms_samples_categories">
					<?php
					STM_LMS_Templates::show_lms_template(
						'manage_course/forms/select',
						array(
							'field_key' => 'category',
							'select'    => STM_LMS_Manage_Course::get_terms(
								'stm_lms_course_taxonomy',
								array(
									'hide_empty' => false,
									'parent'     => 0,
								),
								true
							),
						)
					);
					?>
				</div>

			</div>

		</div>
	</div>
</div>
