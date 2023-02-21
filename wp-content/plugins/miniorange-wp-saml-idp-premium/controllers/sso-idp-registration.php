<?php


use IDP\Helper\Constants\MoIDPConstants;
use IDP\Handler\RegistrationHandler;
$hy = MoIDPConstants::HOSTNAME;
$Pu = RegistrationHandler::instance();
$L3 = $hy . "\x2f\x6d\x6f\x61\x73\57\x6c\x6f\x67\x69\x6e" . "\x3f\x72\x65\144\x69\162\145\143\x74\125\162\154\75" . $hy . "\57\x6d\157\141\x73\57\166\151\145\167\154\151\x63\x65\156\163\x65\x6b\x65\x79\163";
$QV = get_site_option("\155\x6f\x5f\x69\x64\160\x5f\141\144\155\x69\x6e\x5f\x65\x6d\141\x69\x6c");
$D4 = MSI_DIR . "\x76\x69\145\167\x73\x2f\162\x65\147\x69\163\164\x72\x61\164\151\157\156\x2f";
$YP = $Pu->_nonce;
if (get_site_option("\155\157\x5f\x69\144\x70\x5f\166\145\162\151\x66\x79\x5f\x63\x75\x73\164\157\x6d\x65\x72")) {
    goto c3;
}
if (trim(get_site_option("\x6d\157\x5f\x69\144\x70\137\141\144\x6d\x69\x6e\x5f\x65\155\141\x69\154")) != '' && trim(get_site_option("\155\157\x5f\151\x64\160\x5f\141\x64\x6d\x69\156\137\141\160\x69\x5f\153\145\x79")) == '' && get_site_option("\155\x6f\137\x69\144\160\x5f\x6e\145\x77\x5f\162\x65\x67\151\x73\164\162\x61\164\151\157\156") != "\x74\162\165\x65") {
    goto sh;
}
if (get_site_option("\x6d\x6f\x5f\151\x64\160\x5f\x72\x65\147\x69\163\x74\162\141\x74\x69\157\156\x5f\x73\x74\141\x74\x75\x73") == "\115\x4f\137\117\x54\x50\137\x44\105\114\111\126\x45\x52\x45\x44\137\123\x55\x43\x43\x45\x53\123" || get_site_option("\155\157\x5f\x69\144\x70\x5f\162\145\x67\x69\163\x74\162\x61\164\x69\157\x6e\x5f\x73\164\141\164\x75\x73") == "\x4d\117\x5f\117\124\x50\x5f\126\101\x4c\x49\104\x41\x54\x49\x4f\x4e\x5f\x46\x41\x49\114\125\x52\105" || get_site_option("\x6d\157\x5f\151\144\x70\x5f\162\145\x67\151\x73\164\x72\141\x74\x69\157\x6e\137\163\x74\141\164\x75\163") == "\x4d\x4f\x5f\117\x54\120\137\x44\105\x4c\111\x56\x45\122\x45\x44\x5f\x46\x41\x49\114\x55\122\x45") {
    goto KJ;
}
if (!$mW) {
    goto sf;
}
if ($mW && !$JM) {
    goto oS;
}
include MSI_DIR . "\x63\157\156\164\x72\x6f\154\154\x65\x72\x73\x2f\x73\163\157\x2d\151\x64\x70\55\x73\x65\x74\x74\151\x6e\147\x73\x2e\160\x68\x70";
goto j1;
oS:
include $D4 . "\166\145\x72\x69\x66\171\55\154\x6b\x2e\x70\150\x70";
j1:
goto ft;
sf:
delete_site_option("\160\x61\x73\163\167\157\x72\144\137\x6d\x69\163\155\x61\164\x63\150");
update_site_option("\155\x6f\137\x69\144\160\137\x6e\145\x77\x5f\x72\145\x67\x69\163\164\x72\x61\164\x69\157\156", "\x74\162\165\145");
$current_user = wp_get_current_user();
include $D4 . "\x6e\145\167\x2d\162\145\147\151\x73\164\x72\x61\x74\151\157\x6e\x2e\160\x68\160";
ft:
goto Ng;
KJ:
include $D4 . "\166\145\162\151\x66\171\55\157\164\x70\56\x70\x68\160";
Ng:
goto pi;
sh:
include $D4 . "\x76\x65\x72\151\x66\x79\55\143\x75\x73\164\157\x6d\x65\162\x2e\160\x68\x70";
pi:
goto bB;
c3:
include $D4 . "\x76\x65\162\151\x66\x79\x2d\143\x75\163\x74\x6f\155\145\x72\x2e\x70\150\x70";
bB:
