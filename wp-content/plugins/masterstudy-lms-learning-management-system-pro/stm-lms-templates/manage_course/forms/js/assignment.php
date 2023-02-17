<?php ob_start(); ?>

	<script>
		Vue.component('stm-assignment', {
			data: function () {
				return {
					id: '',
					title: '',
					loading: false,
					fields: {
						content: '',
					}
				}
			},
			mounted() {
				var _this = this;
				WPCFTO_EventBus.$on('STM_LMS_Curriculum_item', function (item) {
					_this.id = item.id;
					_this.title = item.title;
					_this.opened = true;
					_this.loading = true;

					var url = stm_lms_ajaxurl + '?action=stm_curriculum_get_item&nonce=' + stm_lms_nonces['stm_curriculum_get_item'] + '&id=' + _this.id;
					this.$http.get(url).then(function (response) {

						var json = response.body;
						var json_meta = response.body['meta'];

						if (json.content) {
							_this.$set(_this.fields, 'content', json.content);
						} else {
							_this.$set(_this.fields, 'content', '');
						}

						if(json_meta['assignment_tries']) {
							_this.$set(_this.fields, 'assignment_tries', json_meta['assignment_tries']);
						} else {
							_this.$set(_this.fields, 'assignment_tries', '');
						}

						WPCFTO_EventBus.$emit('STM_LMS_Editor_Changed', _this.fields.content);

						_this.loading = false;
					});
				});
			},
			template: '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo preg_replace(
					"/\r|\n/",
					'',
					addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/assignment' ) )
				);
				/* phpcs:ignore Squiz.PHP.EmbeddedPhp */ ?>',
			methods: {
				dateChanged(value, option) {
					var _this = this;
					_this.$set(_this.fields, option, value);
				},
				saveChanges: function () {
					var _this = this;
					_this.loading = true;

					var data = new FormData();
					data.append('action', 'stm_lms_pro_save_lesson');
					data.append('nonce', stm_lms_pro_nonces['stm_lms_pro_save_lesson']);
					data.append('post_id', _this.id);
					data.append('post_type', 'stm-assignments');
					data.append('post_title', _this.title);

					Object.keys(_this.fields).map(function (objectKey) {

						data.append(objectKey, _this.fields[objectKey]);

					});

					_this.$http.post(stm_lms_ajaxurl, data, {emulateJSON: true}).then(function (r) {
						WPCFTO_EventBus.$emit('STM_LMS_Close_Modal');
						_this.loading = false;
					});
				},
				discardChanges: function () {
					WPCFTO_EventBus.$emit('STM_LMS_Close_Modal');
				}
			}
		});
	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
