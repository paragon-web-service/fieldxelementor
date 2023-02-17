<?php ob_start(); ?>

	<script>
		Vue.component('stm-price', {
			props: ['course_price', 'course_sale_price', 'course_enterprise_price'],
			data: function () {
				return {
					price: '',
					sale_price: '',
					enterprise_price: '',
				}
			},
			mounted: function () {
				var _this = this;

				if(_this.course_price) _this.price = _this.course_price;
				if(_this.course_sale_price) _this.sale_price = _this.course_sale_price;
				if(_this.course_enterprise_price) _this.enterprise_price = _this.course_enterprise_price;

				Vue.nextTick(function() {
					var $ = jQuery;
					$('.stm_lms_manage_course_price').on('click', function(event){
						var $this = $(this);
						$this.addClass('active');
					});
					$(document).click(function(event) {
						if(!$(event.target).closest('.stm_lms_manage_course_price').length) {
							$('.stm_lms_manage_course_price').removeClass('active');
						}
					});
				});
			},
			template: '<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp
				echo preg_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					"/\r|\n/",
					'',
					addslashes( STM_LMS_Templates::load_lms_template( 'manage_course/forms/html/price' ) )
				);
				/* phpcs:ignore Squiz.PHP.EmbeddedPhp */ ?>',
			methods: {
				addPrices() {

				}
			},
			watch: {
				price: function () {
					var _this = this;
					this.$emit('price-changed', _this.price);
				},
				sale_price: function () {
					var _this = this;
					this.$emit('sale_price-changed', _this.sale_price);
				},
				enterprise_price: function () {
					var _this = this;
					this.$emit('enterprise_price-changed', _this.enterprise_price);
				},
			}
		});

	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
