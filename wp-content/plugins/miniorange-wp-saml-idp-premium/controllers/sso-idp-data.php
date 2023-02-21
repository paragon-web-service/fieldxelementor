<?php


use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\SAMLUtilities;
$J1 = site_url("\x2f\77\x6f\160\x74\x69\157\x6e\75\x6d\157\137\x69\x64\160\x5f\x6d\145\164\141\x64\141\164\141");
$uT = MSI_DIR . "\155\145\x74\141\144\x61\x74\141\x2e\x78\x6d\154";
$de = get_site_option("\x6d\157\x5f\151\144\x70\x5f\x70\162\x6f\x74\x6f\143\x6f\x6c");
$Ud = MSI_URL;
$xj = is_multisite() ? get_sites() : null;
$GX = is_null($xj) ? site_url("\x2f") : get_site_url($xj[0]->blog_id, "\x2f");
$yr = MoIDPUtility::getPublicCertURL();
$Ln = SAMLUtilities::desanitize_certificate(MoIDPUtility::getPublicCert());
$wt = openssl_x509_parse(MoIDPUtility::getPublicCert());
$P1 = date(DATE_RFC2822, $wt["\166\x61\x6c\151\x64\x54\157\x5f\164\151\x6d\145\137\164"]);
$wo = add_query_arg(array("\160\x61\147\x65" => $i_->_menuSlug), $_SERVER["\122\x45\121\125\x45\123\124\x5f\x55\122\x49"]);
$Ga = get_site_option("\155\157\137\151\x64\x70\137\145\x6e\x74\x69\x74\x79\137\151\144") ? get_site_option("\155\157\137\x69\x64\160\137\145\x6e\164\x69\x74\x79\137\x69\144") : $Ud;
$lC = "\123\x65\164\x2d\115\163\x6f\154\104\157\x6d\141\151\x6e\x41\x75\x74\x68\x65\x6e\164\151\143\x61\x74\x69\x6f\156\x20\x2d\x41\165\164\x68\x65\x6e\164\x69\143\141\164\x69\157\156\x20\106\x65\x64\145\x72\141\164\x65\x64\x20\x2d\x44\x6f\155\x61\151\x6e\x4e\141\x6d\x65\x20" . "\40\x3c\x62\x3e\x26\x6c\164\73\x79\157\x75\162\137\x64\x6f\x6d\141\151\x6e\x26\147\164\x3b\x3c\57\142\76\40" . "\55\x49\163\x73\165\x65\162\x55\x72\151\x20\x22" . $Ga . "\42\x20\55\x4c\157\147\117\x66\x66\x55\x72\x69\40\42" . $GX . "\x22\x20\x2d\x50\141\x73\163\151\166\145\x4c\157\147\x4f\156\125\162\x69\x20\42" . $GX . "\x22\40\55\x53\x69\x67\156\x69\x6e\x67\x43\145\x72\164\151\146\x69\143\x61\x74\145\40\x22" . $Ln . "\42\40\x2d\x50\162\x65\146\145\162\x72\145\144\101\165\x74\x68\145\156\164\x69\143\x61\164\151\x6f\156\x50\162\x6f\164\x6f\143\157\x6c\40\127\x53\x46\105\x44";
if (!(!file_exists($uT) || filesize($uT) == 0)) {
    goto q8;
}
MoIDPUtility::createMetadataFile();
q8:
include MSI_DIR . "\166\151\145\167\163\57\151\x64\160\x2d\x64\x61\x74\141\56\160\x68\x70";
