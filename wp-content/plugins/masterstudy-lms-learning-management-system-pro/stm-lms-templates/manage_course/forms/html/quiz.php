<div>

	<div class="stm_lms_item_modal__inner" v-bind:class="{'loading' : loading}">
		<h3 class="text-center">{{title}}</h3>

		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active">
				<a href="quiz_home" aria-controls="home" role="tab" data-toggle="tab">
					<?php esc_html_e( 'Content', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</a>
			</li>
			<li role="presentation">
				<a href="quiz_questions" aria-controls="profile" role="tab" data-toggle="tab">
					<?php esc_html_e( 'Quiz Questions', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</a>
			</li>
			<li role="presentation">
				<a href="quiz_settings" aria-controls="profile" role="tab" data-toggle="tab">
					<?php esc_html_e( 'Quiz Settings', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</a>
			</li>
		</ul>

		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="quiz_home">
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
			<div role="tabpanel" class="tab-pane" id="quiz_questions">
				<div class="stm_metaboxes_grid">
					<div class="stm_metaboxes_grid__inner">
						<stm_questions_v2 inline-template
										v-bind:posts="['stm-questions']"
										v-bind:stored_ids="fields['questions']"
										v-on:get-questions="fields['questions'] = $event">
							<?php stm_lms_questions_v2_load_template( 'main' ); ?>
						</stm_questions_v2>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="quiz_settings">

				<div class="form-group">
					<label>
						<h4><?php esc_html_e( 'Quiz description', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						<?php
						STM_LMS_Templates::show_lms_template(
							'manage_course/forms/editor',
							array(
								'field_key' => 'lesson_excerpt',
								'listener'  => true,
								'excerpt'   => true,
							)
						);
						?>
					</label>
				</div>

				<div class="form-group form-group-combine">
					<label>
						<h4><?php esc_html_e( 'Quiz duration', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						<input class="form-control"
								v-model="fields['duration']"
								placeholder="<?php esc_html_e( 'Enter quiz duration', 'masterstudy-lms-learning-management-system-pro' ); ?>"/>
						<select v-model="fields['duration_measure']" class="form-control">
							<option value=""><?php esc_html_e( 'Minutes', 'masterstudy-lms-learning-management-system-pro' ); ?></option>
							<option value="hours"><?php esc_html_e( 'Hours', 'masterstudy-lms-learning-management-system-pro' ); ?></option>
							<option value="days"><?php esc_html_e( 'Days', 'masterstudy-lms-learning-management-system-pro' ); ?></option>
						</select>
					</label>
				</div>

				<div class="form-group">
					<div class="stm-lms-admin-checkbox">
						<label>
							<h4><?php esc_html_e( 'Show correct answer', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						</label>
						<div class="stm-lms-admin-checkbox-wrapper"
							v-bind:class="{'active' : fields['correct_answer']}">
							<div class="wpcfto-checkbox-switcher"></div>
							<input type="checkbox" name="preview" v-model="fields['correct_answer']">
						</div>
					</div>
				</div>

				<div class="form-group">
					<label>
						<h4><?php esc_html_e( 'Passing grade (%)', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						<input class="form-control" v-model="fields['passing_grade']"/>
					</label>
				</div>

				<div class="form-group">
					<label>
						<h4><?php esc_html_e( 'Points total cut after re-take (%)', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						<input class="form-control" v-model="fields['re_take_cut']"/>
					</label>
				</div>
				<div class="form-group">
					<div class="stm-lms-admin-checkbox">
						<label>
							<h4><?php esc_html_e( 'Randomize questions', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
						</label>
						<div class="stm-lms-admin-checkbox-wrapper"
							v-bind:class="{'active' : fields['random_questions']}">
							<div class="stm-lms-checkbox-switcher"></div>
							<input type="checkbox" name="preview" v-model="fields['random_questions']">
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>

	<div class="stm_lms_item_modal__bottom">
		<a href="#" @click.prevent="saveChanges()"
			class="btn btn-default save-btn">
			<span><?php esc_html_e( 'Save Changes', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
		</a>
		<a href="#" @click.prevent="discardChanges()"
			class="btn btn-default btn-cancel"><?php esc_html_e( 'Cancel', 'masterstudy-lms-learning-management-system-pro' ); ?></a>
	</div>

</div>
