<div>

	<div class="stm_lms_item_modal__inner" v-bind:class="{'loading' : loading}">
		<h3 class="text-center">{{title}}</h3>

		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="assignment_home" aria-controls="home" role="tab" data-toggle="tab">
					<?php esc_html_e( 'Content', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</a>
			</li>
			<li role="presentation">
				<a href="assignment_settings" aria-controls="profile" role="tab" data-toggle="tab">
					<?php esc_html_e( 'Assignment Settings', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</a>
			</li>
		</ul>

		<div class="tab-content">

			<div role="tabpanel" class="tab-pane active" id="assignment_home">

				<?php
				STM_LMS_Templates::show_lms_template(
					'manage_course/forms/editor',
					array(
						'field_key' => 'content',
						'listener'  => true,
					)
				);
				?>

			</div>

			<div role="tabpanel" class="tab-pane" id="assignment_settings">

				<div class="form-group">
					<label>
						<h4><?php esc_html_e( 'Assignment attempts', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						<input type="number" class="form-control" v-model="fields['assignment_tries']"/>
					</label>
				</div>

			</div>

		</div>

	</div>

	<div class="stm_lms_item_modal__bottom">
		<a href="#" @click.prevent="saveChanges()"
			class="btn btn-default save-btn"><?php esc_html_e( 'Save Changes', 'masterstudy-lms-learning-management-system-pro' ); ?></a>
		<a href="#" @click.prevent="discardChanges()"
			class="btn btn-default btn-cancel"><?php esc_html_e( 'Cancel', 'masterstudy-lms-learning-management-system-pro' ); ?></a>
	</div>

</div>
