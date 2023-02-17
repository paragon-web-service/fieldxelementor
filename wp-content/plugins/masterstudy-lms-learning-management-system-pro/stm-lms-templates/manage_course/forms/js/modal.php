<?php ob_start();
// phpcs:ignoreFile
?>

	<script>
		Vue.component('stm-modal', {
			data: function () {
				return {
					post_type: '',
					opened: false,
				}
			},
			mounted() {
				var _this = this;
				WPCFTO_EventBus.$on('STM_LMS_Curriculum_item', function (item) {
					_this.post_type = item.post_type;
					_this.opened = true;
				});

				WPCFTO_EventBus.$on('STM_LMS_Close_Modal', function () {
					_this.opened = false;
				})
			},
			template:
				'<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo preg_replace(
						'/\r|\n/',
						'',
						addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/modal' ) )
					);
					?>',
			watch: {
				opened: function (opened) {
					var _this = this;
					if (!opened) {
						jQuery('html').removeClass('locked');
						jQuery('footer').removeClass('mobile_scroll');
					} else {
						jQuery('html').addClass('locked');
						jQuery('footer').addClass('mobile_scroll');
					}
				}
			}
		}
		);
	</script>

<?php
wp_add_inline_script(
	'stm-lms-manage_course',
	str_replace(
		array(
			'<script>',
			'</script>',
		),
		'',
		ob_get_clean()
	)
);
?>
