<?php ob_start(); ?>

	<script>

		Vue.component('stm-editor', {
			props: ['content', 'listener'],
			data: function () {
				return {
					content_edited: '',
					/* Your data and models here */
					myPlugins: [
						'advlist autolink lists link image textcolor',
						'searchreplace visualblocks code fullscreen',
						'insertdatetime media table contextmenu paste code directionality template colorpicker textpattern'
					],
					myToolbar1: 'undo redo | bold italic strikethrough | forecolor backcolor | template link | bullist numlist | ltr rtl | removeformat',
					myToolbar2: '',
					myOtherOptions: {
						height: 300,
					},
					image_upload: '',
					relative_urls: false,
					remove_script_host: false
				}
			},
			mounted: function () {
				var _this = this;

				if (typeof _this.content !== 'undefined') {
					_this.content_edited = _this.content;
				}
				if (_this.listener) {
					WPCFTO_EventBus.$on('STM_LMS_Editor_Changed', function (content) {
						_this.content_edited = content;
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
					addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/editor' ) )
				);
				/* phpcs:ignore Squiz.PHP.EmbeddedPhp */ ?>',
			methods: {},
			watch: {
				content_edited: function () {
					var _this = this;
					this.$emit('content-changed', _this.content_edited)
				},
				image_upload: function () {
					var _this = this;
					if (_this.image_upload) {
						var image = '<img src="' + _this.image_upload + '" />';
						_this.$set(_this, 'content_edited', image + ' ' + _this.content_edited);
					}
				}
			}
		});

	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
