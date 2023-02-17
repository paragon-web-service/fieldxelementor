<?php
ob_start();
$locale = get_locale();
$lang   = ! empty( $locale ) ? explode( '_', $locale )[0] : 'en';
?>
	<script>
		Vue.component('date-picker', DatePicker.default);
		Vue.component('stm-date', {
			props: ['current_date'],
			data: function () {
				return {
					date: [],
					language: '<?php echo esc_html( $lang ); ?>',
				}
			},
			mounted: function () {
				if (typeof this.current_date !== 'undefined') {
					this.date.push(new Date(parseInt(this.current_date)));
				}

			},
			template: `
				<div>
					<date-picker v-model="date" :lang="language" @change="dateChanged(date)"></date-picker>
				</div>
			`,
			methods: {
				dateChanged(newDate) {
					console.log(this.language);
					this.$emit('date-changed', new Date(newDate + ' UTC').getTime());
				},

			},
		});
	</script>

<?php wp_add_inline_script( 'stm-lms-manage_course', str_replace( array( '<script>', '</script>' ), '', ob_get_clean() ) ); ?>
