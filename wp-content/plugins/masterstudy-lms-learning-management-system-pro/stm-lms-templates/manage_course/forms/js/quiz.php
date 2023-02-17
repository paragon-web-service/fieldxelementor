<?php ob_start(); ?>

<script>
	Vue.component('stm-quiz', {
		data: function () {
			return {
				id: '',
				title: '',
				loading: false,
				fields: {
					content: '',
					lesson_excerpt: '',
					questions: '',
					duration: '',
					duration_measure: '',
					correct_answer: '',
					passing_grade: '',
					re_take_cut: '',
				}
			}
		},
		mounted () {
			var _this = this;
			WPCFTO_EventBus.$on('STM_LMS_Curriculum_item', function (item) {
				_this.id = item.id;
				_this.title = item.title;
				_this.opened = true;
				_this.loading = true;

				var url = stm_lms_ajaxurl + '?action=stm_curriculum_get_item&nonce=' + stm_lms_nonces['stm_curriculum_get_item'] + '&id=' + _this.id;
				this.$http.get(url).then(function(response){

					var json = response.body;
					var json_meta = response.body['meta'];

					if(json.content) {
						_this.$set(_this.fields, 'content', json.content);
					} else {
						_this.$set(_this.fields, 'content', '');
					}


					var questions = (json_meta['questions']) ? json_meta['questions'] : '';
					WPCFTO_EventBus.$emit('STM_LMS_Questions_Update', questions);
					_this.$set(_this.fields, 'questions', questions);


					if(json_meta['lesson_excerpt']) {
						_this.$set(_this.fields, 'lesson_excerpt', json_meta['lesson_excerpt']);
					} else {
						_this.$set(_this.fields, 'lesson_excerpt', '');
					}

					if(json_meta['duration']) {
						_this.$set(_this.fields, 'duration', json_meta['duration']);
					} else {
						_this.$set(_this.fields, 'duration', '');
					}

					if(json_meta['duration_measure']) {
						_this.$set(_this.fields, 'duration_measure', json_meta['duration_measure']);
					} else {
						_this.$set(_this.fields, 'duration_measure', '');
					}

					if(json_meta['correct_answer']) {
						_this.$set(_this.fields, 'correct_answer', json_meta['correct_answer']);
					} else {
						_this.$set(_this.fields, 'correct_answer', '');
					}

					if(json_meta['passing_grade']) {
						_this.$set(_this.fields, 'passing_grade', json_meta['passing_grade']);
					} else {
						_this.$set(_this.fields, 'passing_grade', '');
					}

					if(json_meta['re_take_cut']) {
						_this.$set(_this.fields, 're_take_cut', json_meta['re_take_cut']);
					} else {
						_this.$set(_this.fields, 're_take_cut', '');
					}

					if(json_meta['random_questions']) {
						_this.$set(_this.fields, 'random_questions', json_meta['random_questions']);
					} else {
						_this.$set(_this.fields, 'random_questions', '');
					}

					WPCFTO_EventBus.$emit('STM_LMS_Editor_Changed', _this.fields.content);
					WPCFTO_EventBus.$emit('STM_LMS_Editor_Lesson_Excerpt_Changed', _this.fields.lesson_excerpt);


					_this.loading = false;
				});
			});
		},
		template: '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp
			echo preg_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				"/\r|\n/",
				'',
				addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/quiz' ) )
			);
			/* phpcs:ignore Squiz.PHP.EmbeddedPhp */ ?>',
		methods: {
			dateChanged(value, option) {
					var _this = this;
					_this.$set(_this.fields, option, value);
			},
			saveChanges: function() {
				var _this = this;
				_this.loading = true;

				var data = new FormData();
					data.append('action', 'stm_lms_pro_save_quiz');
					data.append('nonce', stm_lms_pro_nonces['stm_lms_pro_save_quiz']);
					data.append('post_id', _this.id);
					data.append('post_title', _this.title);

				Object.keys(_this.fields).map(function(objectKey) {
					data.append(objectKey, _this.fields[objectKey]);
				});

				_this.$http.post(stm_lms_ajaxurl, data, {emulateJSON: true}).then(function (r) {
					WPCFTO_EventBus.$emit('STM_LMS_Close_Modal');
					_this.loading = false;
				});
			},
			discardChanges: function() {
				WPCFTO_EventBus.$emit('STM_LMS_Close_Modal');
			}
		}
	});
</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
