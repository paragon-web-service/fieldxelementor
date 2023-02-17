<div>
	<div class="stm_lms_manage_course__image" v-bind:class="{'filled' : image['id'], 'loading' : loading}">

		<div class="stm_lms_manage_course__image_empty">
			<i class="fas fa-image lnr-upload"></i>
			<h4 v-html="desc"></h4>
			<?php if ( class_exists( 'STM_LMS_Media_Library' ) ) : ?>
				<a href="#" @click="showMediaLibrary"
				class="btn btn-default media_library_upload_btn"><?php esc_html_e( 'Browse file', 'masterstudy-lms-learning-management-system-pro' ); ?></a>
			<?php endif; ?>
		</div>

		<img v-bind:src="image['url']" v-if="image['url']"/>

		<input v-model="image['file']"
			<?php if ( class_exists( 'STM_LMS_Media_Library' ) ) : ?>
				@click.prevent="showMediaLibrary"
			<?php endif; ?>
				type="file"
				accept="image/jpeg,image/png,.jpeg,.jpg,.png"
				@change="loadImage()"
				ref="image-file"/>

	</div>
	<transition name="slide-fade">
		<div class="stm-lms-message error" v-if="image['message']" v-html="image['message']"></div>
	</transition>
	<?php if ( class_exists( 'STM_LMS_Media_Library' ) ) : ?>
		<?php require STM_LMS_PATH . '/settings/media_library/main.php'; ?>
	<?php endif; ?>
</div>
