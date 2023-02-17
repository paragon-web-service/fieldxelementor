<div class="asignments_grid__search">
	<h2><?php esc_html_e( 'Assignments', 'masterstudy-lms-learning-management-system-pro' ); ?></h2>

	<input type="text" class="form-control"
			v-model="s"
			placeholder="<?php esc_attr_e( 'Search assignment', 'masterstudy-lms-learning-management-system-pro' ); ?>"
			@keyup="initSearch()">

	<div class="sort_assignments" @mouseover="active_sort = true" @mouseout="active_sort = false">
		<div class="sort_assignments__statuses" v-bind:class="{'active_sort' : active_sort, 'active_course': active_course}">
			<div class="active">
				<span v-if="!active_course">
					<?php esc_html_e( 'Choose course', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</span>
				<span v-else v-html="active_course.title"></span>
			</div>
			<div class="sort_assignments__statuses_available" v-bind:class="{'loading' : course_loading}">
				<div class="sort_assignments__status search_course">
					<input v-model="course_search"
							@keyup="initCoursesSearch()"
							placeholder="<?php esc_attr_e( 'Search course', 'masterstudy-lms-learning-management-system-pro' ); ?>" />
				</div>
				<div class="sort_assignments__status" v-if="active_course" @click="active_course = false; getAssignments(); active_sort = false;">
					<?php esc_html_e( 'Choose course', 'masterstudy-lms-learning-management-system-pro' ); ?>
				</div>
				<div class="sort_assignments__status"
						@click="active_course = course; getAssignments(); active_sort = false;"
						v-for="course in courses"
						v-if="course.id !== active_course.id"
						v-html="course.title">
				</div>
			</div>
		</div>
	</div>

</div>

<div class="multiseparator"></div>

<div class="asignments_grid" v-bind:class="{'loading' : loading}">
	<div class="asignments_grid__single" v-for="assignment in assignments">
		<a :href="assignment.url" class="inner" v-bind:class="{'unviewed' : !assignment.viewed}">
			<i class="fa fa-bell"></i>
			<h4 v-html="assignment.title"></h4>
			<div class="info" :class="{'loading' : assignment.loading}">

				<div class="loading_data"><?php esc_html_e( 'Loading data...', 'masterstudy-lms-learning-management-system-pro' ); ?></div>

				<div class="total" v-if="assignment.data && typeof assignment.data.total === 'number'">
					<i class="fa fa-tasks"></i>
					<span>{{assignment.data.total}}</span>
				</div>

				<div class="unpassed" v-if="assignment.data && typeof assignment.data.unpassed === 'number'">
					<i class="far fa-times-circle"></i>
					<span>{{assignment.data.unpassed}}</span>
				</div>

				<div class="passed" v-if="assignment.data && typeof assignment.data.passed === 'number'">
					<i class="far fa-check-circle"></i>
					<span>{{assignment.data.passed}}</span>
				</div>

				<div class="pending" v-if="assignment.data && typeof assignment.data.pending === 'number'">
					<i class="far fa-clock"></i>
					<span>{{assignment.data.pending}}</span>
				</div>

			</div>
		</a>
	</div>
</div>

<h3 class="not_found" v-if="!assignments.length && !loading">
	<?php esc_html_e( 'Nothing found.', 'masterstudy-lms-learning-management-system-pro' ); ?>
</h3>


<div class="asignments_grid__pagination" v-if="pages.length > 1">
	<ul class="page-numbers">
		<li v-for="single_page in pages">
			<a class="page-numbers" href="#" v-if="single_page !== page" @click.prevent="page = single_page; getAssignments();">{{single_page}}</a>
			<span v-else class="page-numbers current">{{single_page}}</span>
		</li>
	</ul>
</div>
