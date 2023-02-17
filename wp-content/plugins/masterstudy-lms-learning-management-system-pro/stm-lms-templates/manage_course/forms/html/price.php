<div>

	<?php
	$symbol            = STM_LMS_Options::get_option( 'currency_symbol', '$' );
	$price_placeholder = sprintf(
		/* translators: %s Currency Symbol */
		esc_html__( 'Add Price* (%s)', 'masterstudy-lms-learning-management-system-pro' ),
		$symbol
	);
	$sale_price_placeholder = sprintf(
		/* translators: %s Currency Symbol */
		esc_html__( 'Add Sale Price (%s)', 'masterstudy-lms-learning-management-system-pro' ),
		$symbol
	);

	?>

	<div class="stm_lms_manage_course_price">
		<a href="#" class="btn btn-default btn_big heading_font" v-on:click.prevent="addPrices()">
			<span v-if="price || sale_price">
				<?php esc_html_e( 'Enroll now', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>
				<span v-else>
				<?php esc_html_e( 'Add Prices', 'masterstudy-lms-learning-management-system-pro' ); ?>
			</span>

			<div class="btn-prices" v-if="price && sale_price">
				<label class="sale_price" v-html="'<?php echo esc_html( $symbol ); ?>' + price"></label>
				<label class="price" v-html="'<?php echo esc_html( $symbol ); ?>' + sale_price"></label>
			</div>

			<div class="btn-prices" v-if="price && !sale_price">
				<label class="price" v-html="'<?php echo esc_html( $symbol ); ?>' + price"></label>
			</div>

		</a>

		<div class="stm_lms_manage_course__form">
			<div class="stm_lms_manage_course__add">
				<div class="form-group">
					<label>
						<h4><?php echo esc_attr( $price_placeholder ); ?></h4>
						<input v-bind:placeholder="'<?php echo esc_attr( $price_placeholder ); ?>'"
								v-model="price"
								type="number"
								class="form-control"/>
					</label>
				</div>
				<div class="form-group">
					<label>
						<h4><?php echo esc_attr( $sale_price_placeholder ); ?></h4>
						<input v-bind:placeholder="'<?php echo esc_attr( $sale_price_placeholder ); ?>'"
								v-model="sale_price"
								type="number"
								class="form-control"/>
					</label>
				</div>

				<?php STM_LMS_Templates::show_lms_template( 'manage_course/global/enterprise' ); ?>
			</div>
		</div>

	</div>

</div>
