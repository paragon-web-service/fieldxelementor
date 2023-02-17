<?php
if ( class_exists( 'STM_LMS_Enterprise_Courses' ) ) :
	$symbol = STM_LMS_Options::get_option( 'currency_symbol', '$' );
	/* translators: %s Currency Symbol */
	$enterprise_price_placeholder = sprintf( esc_html__( 'Add Enterprise Price (%s)', 'masterstudy-lms-learning-management-system-pro' ), $symbol );
	?>

	<div class="form-group">
		<label>
			<h4><?php echo esc_attr( $enterprise_price_placeholder ); ?></h4>
			<input v-bind:placeholder="'<?php echo esc_attr( $enterprise_price_placeholder ); ?>'"
					name="enterprise_price"
					v-model="enterprise_price"
					type="number"
					class="form-control"/>
		</label>
	</div>

	<?php
endif;
