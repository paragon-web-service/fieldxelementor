<table class="stm_lms_assignments_table" :class="{'loading' : loading}">
	<thead>
	<tr>
		<th class="name"><?php esc_html_e( 'Name', 'masterstudy-lms-learning-management-system-pro' ); ?></th>
		<th class="course_name"><?php esc_html_e( 'Course', 'masterstudy-lms-learning-management-system-pro' ); ?></th>
		<th class="date"><?php esc_html_e( 'Date', 'masterstudy-lms-learning-management-system-pro' ); ?></th>
		<th class="attempts"><?php esc_html_e( 'Attempts', 'masterstudy-lms-learning-management-system-pro' ); ?></th>
		<th class="status"><?php esc_html_e( 'Status', 'masterstudy-lms-learning-management-system-pro' ); ?></th>
	</tr>
	</thead>

	<tbody>
		<tr v-for="assignment in assignments" @click="window.open(assignment.url,'_blank');">
			<td class="name" data-title="<?php esc_attr_e( 'Name', 'masterstudy-lms-learning-management-system-pro' ); ?>">
				<div class="user_image" v-html="assignment.user.avatar"></div>
				<div class="user_name" v-html="assignment.user.login"></div>
			</td>
			<td class="course" data-title="<?php esc_attr_e( 'Course', 'masterstudy-lms-learning-management-system-pro' ); ?>" v-html="assignment.course_name"></td>
			<td class="date" data-title="<?php esc_attr_e( 'Date', 'masterstudy-lms-learning-management-system-pro' ); ?>" v-html="assignment.start_time"></td>
			<td class="attempts" data-title="<?php esc_attr_e( 'Attempts', 'masterstudy-lms-learning-management-system-pro' ); ?>" v-html="assignment.try_num"></td>
			<td class="status" data-title="<?php esc_attr_e( 'Status', 'masterstudy-lms-learning-management-system-pro' ); ?>">

				<div class="unpassed" v-if="assignment.status === 'not_passed'">
					<i class="far fa-times-circle"></i>
					<span><?php esc_html_e( 'Non passed', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				</div>

				<div class="passed" v-if="assignment.status === 'passed'">
					<i class="far fa-check-circle"></i>
					<span><?php esc_html_e( 'Passed', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				</div>

				<div class="pending" v-if="assignment.status === 'pending'">
					<i class="far fa-clock"></i>
					<span><?php esc_html_e( 'Pending', 'masterstudy-lms-learning-management-system-pro' ); ?></span>
				</div>

			</td>
		</tr>
	</tbody>
</table>

<h4 v-if="!assignments.length"><?php esc_html_e( 'Nothing found', 'masterstudy-lms-learning-management-system-pro' ); ?></h4>
