<?php ob_start(); ?>

	<script>

		Vue.component('stm-excerpt-editor', {
			props: ['excerpt', 'listener'],
			data: function () {
				return {
					excerpt_edited: '',
					/* Your data and models here */
					myPlugins: [
						'advlist autolink lists link image textcolor',
						'searchreplace visualblocks code fullscreen',
						'insertdatetime media table contextmenu paste code directionality template colorpicker textpattern'
					],
					myToolbar1: 'undo redo | bold blockquote numlist bullist forecolor link removeformat',
					myToolbar2: '',
					myOtherOptions: {
						menubar: false,
						height: 300,
					},
					relative_urls: false,
					remove_script_host: false
				}
			},
			mounted: function () {
				var _this = this;

				if (typeof _this.excerpt !== 'undefined') {
					_this.excerpt_edited = _this.excerpt;
				}
				if (_this.listener) {
					WPCFTO_EventBus.$on('STM_LMS_Editor_Lesson_Excerpt_Changed', function (excerpt) {
						_this.excerpt_edited = excerpt;
					});
				}

			},
			components: {
				//'editor': TinymceVue.default
				'tinymce': VueEasyTinyMCE
			},
			template: '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo preg_replace(
					"/\r|\n/",
					'',
					addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/excerpt-editor' ) )
				);
				/* phpcs:ignore Squiz.PHP.EmbeddedPhp */ ?>',
			methods: {},
			watch: {
				excerpt_edited: function () {
					var _this = this;
					this.$emit('excerpt-changed', _this.excerpt_edited)
				}
			}
		});

	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
