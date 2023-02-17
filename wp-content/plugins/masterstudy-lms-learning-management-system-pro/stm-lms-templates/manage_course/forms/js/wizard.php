<?php ob_start(); ?>

	<script>
		Vue.component('stm-wizard', {
			props: ['fields'],
			data: function () {
				return {
					active: false,
					sample_data : true,
					steps: [
						{
							step: '14',
							section: '<?php esc_html_e( 'Welcome to Wizard', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'A simple tour about all fields included in course', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Simple help with adding courses', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_wizard_step_1',
						},
						{
							step: '1',
							section: '<?php esc_html_e( 'Title', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding a course title', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add a title', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_wizard_step_1'
						},
						{
							step: '2',
							section: '<?php esc_html_e( 'Category', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course category', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add a category', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_wizard_step_2'
						},
						{
							step: '3',
							section: '<?php esc_html_e( 'Image', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course image', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add an image', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_wizard_step_3',
							tab: 'description'
						},
						{
							step: '4',
							section: '<?php esc_html_e( 'Content', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course description', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add course description', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_wizard_step_4',
							tab: 'description'
						},
						{
							step: '15',
							section: '<?php esc_html_e( 'Course materials', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course materials', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add course materials (you can add several files via "plus" button)', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '#stm_lms_add_new_section_materials',
							tab: 'description'
						},
						{
							step: '8',
							section: '<?php esc_html_e( 'Curriculum', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Add a course Curriculum', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add curriculum Section and hit enter after adding Section name', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '#stm_lms_add_new_section',
							tab: 'curriculum'
						},
						{
							step: '9',
							section: '<?php esc_html_e( 'Curriculum', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Add a course Lesson', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add curriculum Lesson and hit enter after adding Lesson name', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '#stm_lms_add_new_lesson',
							tab: 'curriculum'
						},
						{
							step: '10',
							section: '<?php esc_html_e( 'Curriculum', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Add a course Quiz', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add curriculum Quiz and hit enter after adding Quiz name', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '#stm_lms_add_new_quiz',
							tab: 'curriculum'
						},
						{
							step: '11',
							section: '<?php esc_html_e( 'Course FAQ', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Add a course FAQ', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'FAQ is an accordion of FAQ items. Add FAQ title, then hit enter. After adding FAQ item, you can add description for it. Add as many FAQ items as you need.', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm-lms-faq',
							tab: 'faq'
						},
						{
							step: '12',
							section: '<?php esc_html_e( 'Announcement', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Add a course announcement', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Enter Announcement for students.', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_announcement',
							tab: 'announcement'
						},
						{
							step: '5',
							section: '<?php esc_html_e( 'Pricing', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course price', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add course prices', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_wizard_step_5'
						},
						{
							step: '6',
							section: '<?php esc_html_e( 'Info', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course duration', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add course duration', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_manage_course__duration'
						},
						{
							step: '7',
							section: '<?php esc_html_e( 'Info', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course video duration', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add course video duration', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_manage_course__video'
						},
						{
							step: '13',
							section: '<?php esc_html_e( 'Info', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							title: '<?php esc_html_e( 'Adding course level', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							hint: '<?php esc_html_e( 'Add course level', 'masterstudy-lms-learning-management-system-pro' ); ?>',
							selector: '.stm_lms_manage_course__level'
						},
					],
					current_step: 0
				}
			},
			mounted: function () {
				this.loadWizard();
				this.initStep(this.current_step);
			},
			template: '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp
				echo preg_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					"/\r|\n/",
					'',
					addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/wizard' ) )
				);
				/* phpcs:ignore Squiz.PHP.EmbeddedPhp */ ?>',
			methods: {
				loadSampleData() {
					console.log('data');
				},
				prevStep() {
					this.current_step = this.current_step - 1;
				},
				nextStep() {
					this.current_step = this.current_step + 1;
				},
				initStep(s) {
					var _this = this;
					_this.$nextTick(function () {
						var $ = jQuery;
						var step = _this.steps[s];
						var $selector = $(step['selector']);

						var $manage_course = $('.stm_lms_manage_course');
						$('*').removeClass('active-hint');
						$selector.addClass('active-hint');

						$('.stm_lms_wizard__hint').removeClass('removed');

						if(typeof step.tab !== 'undefined') {
							$('a[href="#' + step.tab + '"]').click();
							_this.sleep(100);
						}

						var childPos = $selector.offset();
						var parentPos = $('#stm_lms_manage_course').parent().offset();
						var position = {
							top: childPos.top - parentPos.top,
							left: childPos.left - parentPos.left
						};


						// $('.stm_lms_wizard__hint').css({
						//     top: position.top + 130,
						//     left: position.left - 314,
						// });

						$([document.documentElement, document.body]).animate({
							scrollTop: $selector.offset().top - 250
						}, 500, function(){

						});

						$selector.on('click', function(){
							$('.stm_lms_wizard__hint').addClass('removed');
						});

					})
				},
				sleep(milliseconds) {
					var start = new Date().getTime();
					for (var i = 0; i < 1e7; i++) {
						if ((new Date().getTime() - start) > milliseconds){
							break;
						}
					}
				},
				loadWizard() {
					var wizard_active = localStorage.getItem('wizard_active');
					if(typeof wizard_active != null && wizard_active === 'false') this.active = false;

					var wizard_step = localStorage.getItem('wizard_step');
					if(isNaN(wizard_step) || wizard_step == null) wizard_step = 0;
					if(typeof wizard_step != null) this.current_step = parseInt(wizard_step);
				}
			},
			watch: {
				current_step: function (step) {
					var _this = this;

					_this.initStep(step);
					localStorage.setItem('wizard_step', step);
				},
				active: function(active) {
					localStorage.setItem('wizard_active', active);
					if( document.querySelector('.__position_left') !== null ) {
						document.querySelector('.__position_left').classList.add('__collapsed');
					}
				}
			}
		});

	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
