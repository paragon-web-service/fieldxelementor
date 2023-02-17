<?php
if ( class_exists( 'STM_LMS_Prerequisites' ) ) :
	?>
	<div class="stm-lms-manage-prereq">
		<h4><?php esc_html_e( 'Prerequisite Courses', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>

		<wpcfto_autocomplete v-bind:fields="{'post_type' : ['stm-courses'] }"
							v-bind:field_value="fields['prerequisites']"
							v-on:wpcfto-get-value="fields['prerequisites'] = $event"></wpcfto_autocomplete>

		<input type="hidden" name="prerequisites" v-model="fields['prerequisites']"/>

		<h4><?php esc_html_e( 'Prerequisite Courses Passing Percent (%)', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>

		<input type="text" name="prerequisite_passing_level" v-model="fields['prerequisite_passing_level']"/>
	</div>
	<?php
endif;
