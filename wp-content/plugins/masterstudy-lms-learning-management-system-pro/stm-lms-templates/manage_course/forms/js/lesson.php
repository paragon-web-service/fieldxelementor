<?php ob_start(); ?>

	<script>

		function WpcftoIsJsonString(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		}

		Vue.component('stm-lesson', {
			data: function () {
				return {
					id: '',
					title: '',
					loading: false,
					mediaLibrary: false,
					fields: {
						content: '',
						type: '',
						duration: '',
						lesson_excerpt: '',
						preview: '',
						video_type: '<?php echo esc_attr( apply_filters( 'ms_plugin_get_default_source', '' ) ); ?>',
						presto_player_idx: '',
						lesson_lock_from_start: '',
						lesson_lock_start_days: '',
						poster_id: '',
						lesson_video_poster: '',
						lesson_video_poster_url: '',
						lesson_shortcode: '',
						lesson_video_width: '',
						lesson_embed_ctx: '',
						lesson_youtube_url: '',
						lesson_stream_url: '',
						lesson_vimeo_url: '',
						lesson_ext_link_url: '',
						lesson_video: '',
						lesson_files_pack : '',
						uploaded_lesson_video : '',
					},
					lesson_files_pack_data : stm_lms_manage_course['lesson_file_pack_data']
				}
			},
			mounted() {
				var _this = this;
				WPCFTO_EventBus.$on('STM_LMS_Curriculum_item', function (item) {
					_this.id = item.id;
					_this.title = item.title;
					_this.opened = true;
					_this.loading = true;
					_this.fields.lesson_video = '';
					_this.fields.lesson_video_poster = '';
					var url = stm_lms_ajaxurl + '?action=stm_curriculum_get_item&nonce=' + stm_lms_nonces['stm_curriculum_get_item'] + '&id=' + _this.id;
					this.$http.get(url).then(function (response) {

						var json = response.body;
						var json_meta = response.body['meta'];

						if (json.content) {
							_this.$set(_this.fields, 'content', json.content);
						} else {
							_this.$set(_this.fields, 'content', '');
						}

						if (json_meta['lesson_ext_link_url']) {
							_this.$set(_this.fields, 'lesson_ext_link_url', json_meta['lesson_ext_link_url']);
						} else {
							_this.$set(_this.fields, 'lesson_ext_link_url', '');
						}

						if (json_meta['lesson_vimeo_url']) {
							_this.$set(_this.fields, 'lesson_vimeo_url', json_meta['lesson_vimeo_url']);
						} else {
							_this.$set(_this.fields, 'lesson_vimeo_url', '');
						}

						if (json_meta['lesson_stream_url']) {
							_this.$set(_this.fields, 'lesson_stream_url', json_meta['lesson_stream_url']);
						} else {
							_this.$set(_this.fields, 'lesson_stream_url', '');
						}

						if (json_meta['lesson_youtube_url']) {
							_this.$set(_this.fields, 'lesson_youtube_url', json_meta['lesson_youtube_url']);
						} else {
							_this.$set(_this.fields, 'lesson_youtube_url', '');
						}

						if (json_meta['lesson_embed_ctx']) {
							_this.$set(_this.fields, 'lesson_embed_ctx', json_meta['lesson_embed_ctx']);
						} else {
							_this.$set(_this.fields, 'lesson_embed_ctx', '');
						}

						if (json_meta['lesson_video_width']) {
							_this.$set(_this.fields, 'lesson_video_width', json_meta['lesson_video_width']);
						} else {
							_this.$set(_this.fields, 'lesson_video_width', '');
						}

						if (json_meta['lesson_shortcode']) {
							_this.$set(_this.fields, 'lesson_shortcode', json_meta['lesson_shortcode']);
						} else {
							_this.$set(_this.fields, 'lesson_shortcode', '');
						}

						if (json_meta['video_type']) {
							_this.$set(_this.fields, 'video_type', json_meta['video_type']);
						} else {
							_this.$set(_this.fields, 'video_type', '');
						}

						if (json_meta['presto_player_idx']) {
							_this.$set(_this.fields, 'presto_player_idx', json_meta['presto_player_idx']);
						} else {
							_this.$set(_this.fields, 'presto_player_idx', '');
						}

						if (json_meta['type']) {
							_this.$set(_this.fields, 'type', json_meta['type']);
						} else {
							_this.$set(_this.fields, 'type', '');
						}

						if (json_meta['duration']) {
							_this.$set(_this.fields, 'duration', json_meta['duration']);
						} else {
							_this.$set(_this.fields, 'duration', '');
						}

						if (json_meta['lesson_excerpt']) {
							_this.$set(_this.fields, 'lesson_excerpt', json_meta['lesson_excerpt']);
						} else {
							_this.$set(_this.fields, 'lesson_excerpt', '');
						}

						if (json_meta['preview']) {
							_this.$set(_this.fields, 'preview', json_meta['preview']);
						} else {
							_this.$set(_this.fields, 'preview', '');
						}
						if (json_meta['lesson_video_poster_url']) {
							_this.$set(_this.fields, 'lesson_video_poster_url', json_meta['lesson_video_poster_url']);
						} else {
							_this.$set(_this.fields, 'lesson_video_poster_url', '');
						}


						if (json_meta['lesson_video_url']) {
							_this.$set(_this.fields, 'lesson_video_url', json_meta['lesson_video_url']);
						} else {
							_this.$set(_this.fields, 'lesson_video_url', '');
						}

						if (json_meta['uploaded_lesson_video']) {
							_this.$set(_this.fields, 'uploaded_lesson_video', json_meta['uploaded_lesson_video']);
						} else {
							_this.$set(_this.fields, 'uploaded_lesson_video', '');
						}

						if (json_meta['lesson_files_pack']) {
							_this.$set(_this.fields, 'lesson_files_pack', json_meta['lesson_files_pack']);
						} else {
							_this.$set(_this.fields, 'lesson_files_pack', '');
						}

						if (typeof json_meta['stream_start_date'] !== 'undefined') {
							_this.$set(_this.fields, 'stream_start_date', json_meta['stream_start_date']);
						}

						if (typeof json_meta['stream_start_time'] !== 'undefined') {
							_this.$set(_this.fields, 'stream_start_time', json_meta['stream_start_time']);
						}

						if ( 'true' === json_meta['lesson_lock_from_start'] ) {
							_this.$set(_this.fields, 'lesson_lock_from_start', json_meta['lesson_lock_from_start']);
							_this.$set(_this.fields, 'lesson_lock_start_days', json_meta['lesson_lock_start_days']);
						} else {
							_this.$set(_this.fields, 'lesson_lock_start_days', '');
							_this.$set(_this.fields, 'lesson_lock_from_start', '');
						}

						if (typeof json_meta['lesson_start_date'] !== 'undefined') {
							_this.$set(_this.fields, 'lesson_start_date', json_meta['lesson_start_date']);
						}

						if (typeof json_meta['lesson_start_time'] !== 'undefined') {
							_this.$set(_this.fields, 'lesson_start_time', json_meta['lesson_start_time']);
						}

						if (typeof json_meta['stream_end_date'] !== 'undefined') {
							_this.$set(_this.fields, 'stream_end_date', json_meta['stream_end_date']);
						}

						if (typeof json_meta['stream_end_time'] !== 'undefined') {
							_this.$set(_this.fields, 'stream_end_time', json_meta['stream_end_time']);
						}

						if (typeof json_meta['timezone'] !== 'undefined') {
							_this.$set(_this.fields, 'timezone', json_meta['timezone']);
						}

						if (typeof json_meta['stm_password'] !== 'undefined') {
							_this.$set(_this.fields, 'stm_password', json_meta['stm_password']);
						}

						if (json_meta['join_before_host']) {
							_this.$set(_this.fields, 'join_before_host', json_meta['join_before_host']);
						} else {
							_this.$set(_this.fields, 'join_before_host', '');
						}

						if (json_meta['option_host_video']) {
							_this.$set(_this.fields, 'option_host_video', json_meta['option_host_video']);
						} else {
							_this.$set(_this.fields, 'option_host_video', '');
						}

						if (json_meta['option_participants_video']) {
							_this.$set(_this.fields, 'option_participants_video', json_meta['option_participants_video']);
						} else {
							_this.$set(_this.fields, 'option_participants_video', '');
						}

						if (json_meta['option_mute_participants']) {
							_this.$set(_this.fields, 'option_mute_participants', json_meta['option_mute_participants']);
						} else {
							_this.$set(_this.fields, 'option_mute_participants', '');
						}
						if (json_meta['option_enforce_login']) {
							_this.$set(_this.fields, 'option_enforce_login', json_meta['option_enforce_login']);
						} else {
							_this.$set(_this.fields, 'option_enforce_login', '');
						}

						WPCFTO_EventBus.$emit('STM_LMS_Editor_Changed', _this.fields.content);
						WPCFTO_EventBus.$emit('STM_LMS_Editor_Lesson_Excerpt_Changed', _this.fields.lesson_excerpt);

						_this.loading = false;
					});
				});
			},
			template: `
			<?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo preg_replace(
				"/\r|\n/",
				'',
				addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/lesson' ) )
			);
			?>
			`,
			methods: {
				closeModal() {
					this.mediaLibrary = false
				},

				checkedImage(file) {
					this.fields.poster_id = file.id;
					this.fields.lesson_video_poster_url = file.url
					this.mediaLibrary = false;
				},

				showMediaLibrary() {
					this.mediaLibrary = true
				},

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
					data.append('post_title', _this.title);

					Object.keys(_this.fields).map(function (objectKey) {
						if (objectKey === 'lesson_lock_start_days') {
								if ( ! _this.fields.lesson_lock_from_start ) { 
									_this.fields.lesson_lock_start_days = '';
								}
						}
						if (objectKey === 'lesson_video_poster') {
							if (_this.$refs.video_poster) {
								if (typeof _this.$refs.video_poster.files[0] !== 'undefined') {
									data.append('image', _this.$refs.video_poster.files[0]);
								}
							}
							if ( _this.fields.poster_id ) {
								data.append('image', _this.fields.lesson_video_poster_url);
							}
						}
						else if (objectKey === 'lesson_video') {
							if (typeof _this.$refs.lesson_video !== 'undefined' && typeof _this.$refs.lesson_video.files[0] !== 'undefined') {
								data.append('lesson_video', _this.$refs.lesson_video.files[0]);
							}
						}
						else {
							data.append(objectKey, _this.fields[objectKey]);
						}
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
