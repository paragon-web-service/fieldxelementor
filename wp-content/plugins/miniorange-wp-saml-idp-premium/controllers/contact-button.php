<?php


use IDP\Helper\Constants\MoIDPConstants;
global $_wp_admin_css_colors;
$au = get_user_option("\x61\144\155\x69\156\137\143\x6f\154\157\x72");
$KP = $_wp_admin_css_colors[$au]->colors;
$current_user = wp_get_current_user();
$QV = get_site_option("\155\157\x5f\151\144\x70\137\141\x64\155\x69\156\x5f\x65\x6d\x61\151\x6c");
$mA = get_site_option("\155\157\137\151\x64\x70\x5f\x61\x64\x6d\151\x6e\137\x70\150\157\x6e\145");
$mA = $mA ? $mA : '';
$PF = MoIDPConstants::FEEDBACK_EMAIL;
include MSI_DIR . "\x76\x69\145\167\x73\57\x63\x6f\x6e\x74\x61\143\164\55\142\165\164\x74\x6f\156\x2e\x70\x68\160";
