<div>
	<stm-image v-on:image-url="image_upload = $event" v-if="listener" label="<?php esc_html_e( 'Insert Image', 'masterstudy-lms-learning-management-system-pro' ); ?>" :key="'key_' + image_upload"></stm-image>

	<tinymce v-model="content_edited"
			:plugins="myPlugins"
			:toolbar1="myToolbar1"
			:toolbar2="myToolbar2"
			:other="myOtherOptions">
	</tinymce>

</div>
