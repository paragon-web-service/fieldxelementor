<div class="canvas-wrapper" dir="ltr">
	<div v-if="typeof certificates[currentCertificate] !== 'undefined'" v-bind:class="typeof certificates[currentCertificate] !== 'undefined' && typeof certificates[currentCertificate].data.orientation !== 'undefined' ? certificates[currentCertificate].data.orientation + ' canvas-wrap' : 'landscape canvas-wrap'">
		<div class="canvas-background-wrap" v-if="typeof certificates[currentCertificate] !== 'undefined' && typeof certificates[currentCertificate].image !== 'undefined' && certificates[currentCertificate].image">
			<img v-bind:src="certificates[currentCertificate].image" class="canvas-background" />
		</div>
		<?php require_once STM_LMS_PRO_ADDONS . '/certificate_builder/templates/parts/canvas/main.php'; ?>
	</div>
</div>

<div class="certificate_notice">
	<?php esc_html_e( 'For the proper display of the certificate, the background image must be 900x600px or 600x900px in portrait orientation.', 'masterstudy-lms-learning-management-system-pro' ); ?>
</div>
