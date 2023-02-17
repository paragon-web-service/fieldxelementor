<?php
/**
 * @var $subject
 * @var $message
 * @var $email_manager
 */

$header_bg            = ! empty( $email_manager['stm_lms_email_template_hf_header_bg'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_hf_header_bg'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_header.png';
$logo                 = ! empty( $email_manager['stm_lms_email_template_hf_logo'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_hf_logo'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_logo.png';
$footer_bg            = ! empty( $email_manager['stm_lms_email_template_hf_footer_bg'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_hf_footer_bg'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_footer.png';
$reply_icon           = ! empty( $email_manager['stm_lms_email_template_reply_icon'] ) ? STM_LMS_Email_Manager::stm_lms_get_image_by_id( $email_manager['stm_lms_email_template_reply_icon'] ) ?? '' : STM_LMS_PRO_URL . '/addons/email_manager/email_reply.png';
$footer_copyrights    = $email_manager['stm_lms_email_template_reply_textarea'] ?? '';
$footer_reply         = $email_manager['stm_lms_email_template_reply_text'] ?? '';
$outside_bg           = $email_manager['stm_lms_email_template_hf_entire_bg'] ?? '';
$status_header_footer = $email_manager['stm_lms_email_template_hf'] ?? '';
$status_reply         = $email_manager['stm_lms_email_template_reply'] ?? '';


?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- Facebook sharing information tags -->
<meta property="og:title" content="Masterstudy LMS Email Template">

<title>Masterstudy LMS Email Template</title>
<style type="text/css">
	#backgroundTable {
		height: 100% !important;
		margin: 0;
		padding: 0;
		width: 100% !important;
	}

	body, #backgroundTable {
		background-color: <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? esc_html( $outside_bg ) : 'white'; ?>;
	}

	#backgroundTable #templateContainer {
		border: 1px solid <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? '#DDDDDD' : 'white'; ?>;
		margin-top: 40px;
	}

	#backgroundTable #templateHeader {
		background-color: #FFFFFF;
		border-bottom: 0;
	}

	#backgroundTable .headerContent {
		position: relative;
	}

	#backgroundTable #headerImage {
		height: auto;
		max-width: 700px;
		width: 100%;
	}

	#backgroundTable .columnOneContent {
		background-color: #FFFFFF;
	}

	/*LMS Styles*/
	#backgroundTable .headerContent img {
		min-height: 100px;
	}

	#backgroundTable .headerContent img.email-logo {
		min-height: auto;
		width: 200px;
		height: 35px;
		object-fit: contain;
	}

	#backgroundTable .email-logo {
		position: absolute;
		top: 25px;
		left: calc(50% - 100px);
	}

	#backgroundTable .headerContent-bottom.no-margin {
		margin-bottom: 0;
	}

	#backgroundTable .headerContent-bottom {
		width: 620px;
		height: 1px;
		background-color: <?php echo ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ? '#DBE0E9' : 'white'; ?>;
		display: block;
		margin: 0 auto;
		margin-bottom: 50px;
	}

	#backgroundTable .courseTitle h1, h2, h3, h4, h5, h6 {
		text-align: center;
		margin-bottom: 30px;
	}

	#backgroundTable .courseImage img {
		height: 220px;
		object-fit: cover;
		margin: 0 auto;
		display: flex;
		margin-bottom: 30px;
	}

	#backgroundTable .courseImage .email-image-parent {
		width: 460px;
	}

	#backgroundTable .email-image-parent-bg {
		position: relative;
	}

	#backgroundTable .courseImage .email-image-child {
		width: 400px;
		position: absolute;
		top: 0;
		left: calc(50% - 200px);
		border-radius: 5px;
	}

	#backgroundTable .courseBody {
		margin-bottom: 30px;
	}
	#backgroundTable .courseContentBody {
		margin-bottom: 30px !important;
	}

	#backgroundTable .courseContentBody, p {
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
		font-style: normal;
		font-weight: 500;
		font-size: 15px;
		line-height: 26px;
		/* or 173% */
		max-width: 460px;
		margin: 0 auto;
		text-align: center;

		/* Dark 50% */

		color: #808C98;
	}

	#backgroundTable .courseContentBody p strong {
		color: black;
	}

	#backgroundTable .courseButton {
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
		margin: 0 auto;

		width: 140px;
		height: 50px;
		text-decoration: none;

		/* Primary */

		background: #227AFF;
		color: white !important;
		border-radius: 5px;
		margin-bottom: 50px;
	}

	#backgroundTable .courseButton:hover {
		background: #0066ff;
	}

	#backgroundTable .courseFooter p {
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
		font-style: normal;
		font-weight: 500;
		font-size: 15px;
		line-height: 26px;
		/* or 173% */
		max-width: 460px;
		margin: 0 auto;
		text-align: center;
		margin-bottom: 30px;
		margin-top: 30px;

		/* Dark 50% */

		color: #808C98;
	}

	#backgroundTable .courseFooter p strong {
		color: black;
	}

	#backgroundTable .courseFooter.copyrights strong {
		display: block;
	}

	#backgroundTable .courseFooter.copyrights {
		position: relative;
	}
	#backgroundTable .copyright-content .content *{
		margin-bottom: 0;
		margin-top: 0;
	}
	#backgroundTable .copyright-content .content{
		margin-bottom: 30px;
	}
	#backgroundTable .courseFooter.copyrights .email-footer-bg {
		position: absolute;
		bottom: 0;
	}

	#backgroundTable .reply-email-link {
		display: flex;
		align-items: center;
		justify-content: center;
	}

	#backgroundTable .reply-email-link img {
		margin-right: 10px;
		width: 18px;
	}
</style>
<center>
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="backgroundTable">
		<tbody>
		<tr>
			<td align="center" valign="top">
				<table border="0" cellpadding="0" cellspacing="0" id="templateContainer">
					<tbody>
					<tr>
						<td align="center" valign="top">
							<!-- // Begin Template Header \\ -->
							<?php
							if ( $email_manager['stm_lms_email_template_branding'] ) {
								?>
								<table border="0" cellpadding="0" cellspacing="0" id="templateHeader">
									<tbody>
									<tr>
										<td class="headerContent">
											<?php
											if ( ! empty( $header_bg ) && ! empty( $status_header_footer ) ) {
												?>
												<!-- // Begin Module: Standard Header Image \\ -->
												<img
													src="<?php echo esc_attr( $header_bg ); ?>"
													style="max-width:700px; object-fit: cover;height: 95px; width: 700px; "
													id="headerImage campaign-icon"
													mc:label="header_image" mc:edit="header_image" mc:allowdesigner=""
													mc:allowtext="" alt="">
												<?php
											} else {
												?>
												<div
													style="max-width:700px; object-fit: cover;height: 95px; width: 700px;"
													id="headerImage campaign-icon"></div>
												<?php
											}
											if ( ! empty( $logo ) && ! empty( $status_header_footer ) ) {
												?>

												<img
													src="<?php echo esc_attr( $logo ); ?>"
													style="max-width:700px;" id="headerImage campaign-icon"
													mc:label="header_image" mc:edit="header_image" mc:allowdesigner=""
													mc:allowtext="" alt="" class="email-logo">
												<!-- // End Module: Standard Header Image \\ -->
												<?php
											}
											?>

										</td>
									</tr>
									<tr>
										<td class="headerContent-bottom">
										</td>
									</tr>
									</tbody>
								</table>
								<?php
							}
							?>
							<!-- // End Template Header \\ -->
						</td>
					</tr>
					<tr class="columnOneContent courseTitle">
						<td>
							<h2><?php echo esc_html( $subject ); ?></h2>
						</td>
					</tr>
					<tr class="columnOneContent courseBody">
						<td>
							<div class="courseContentBody">
							<?php echo $message; // phpcs:ignore?>
							</div>
						</td>
					</tr>
					<tr style="background-color: white;">
						<td class="headerContent-bottom no-margin">
						</td>
					</tr>
					<?php
					if ( ! empty( $footer_bg ) && ! empty( $status_header_footer ) && ! empty( $email_manager['stm_lms_email_template_branding'] ) ) {
						?>
					<tr class="columnOneContent courseFooter copyrights"
						style="background-image: url(<?php echo esc_attr( $footer_bg ); ?>); background-repeat: no-repeat; background-size: cover; max-width:700px; object-fit: cover;height: 155px; width: 700px;">
						<?php
					} else {
						?>
					<tr class="columnOneContent courseFooter copyrights"
						style=" max-width:700px; object-fit: cover;height: 155px; width: 700px;">
						<?php
					}
					if ( ! empty( $status_reply ) ) {
						?>

					<td class="copyright-content">
						<p class="reply-email-link">
							<?php
							if ( ! empty( $reply_icon ) && ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ) {
								?>
								<img src="<?php echo esc_attr( $reply_icon ); ?>"
									class="courseFooterIcon">
								<?php
							}
							if ( ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ) {
								echo esc_html( $footer_reply );
							}
							?>
						</p>

						<?php
						if ( ! empty( $footer_copyrights ) && ( ! empty( $email_manager['stm_lms_email_template_branding'] ) ) ) {
							?>
							<div class="content">
								<p>
								<?php
								echo $footer_copyrights; // phpcs:ignore
								?>
								</p>
							</div>
							<?php
						}
						?>
					</td>
						<?php
					} else if ( ! empty( $email_manager['stm_lms_email_template_branding'] ) && empty( $footer_bg ) ) {
						?>
						<tr class="columnOneContent courseFooter copyrights"
							style=" max-width:700px; object-fit: cover;height: 100px; width: 700px;">
							<td>
								<p class="reply-email-link"></p>
							</td>
							<?php
					}
					?>
						<td></td>
						</tr>
					</tbody>
				</table>
				<!-- // End Template Body \\ -->
			</td>
		</tr>
		</tbody>
	</table>
	<br>
</center>
