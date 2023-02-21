<?php


use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Helper\Utilities\TabDetails;
use IDP\Helper\Utilities\Tabs;
$mW = MoIDPUtility::micr();
$JM = MoIDPUtility::iclv();
$yW = MoIDPUtility::mo_idp_lk_multi_host();
$kK = MSI_DIR . "\143\x6f\x6e\164\162\157\x6c\154\x65\x72\163\57";
$Y_ = TabDetails::instance();
$JB = $Y_->_tabDetails;
$Fl = $Y_->_parentSlug;
$iQ = $JB[Tabs::PROFILE];
$yF = $JB[Tabs::SIGN_IN_SETTINGS];
$kq = $JB[Tabs::LICENSE];
$JH = $JB[Tabs::METADATA];
$i_ = $JB[Tabs::IDP_CONFIG];
$z3 = $JB[Tabs::ATTR_SETTINGS];
$ij = $JB[Tabs::SUPPORT];
include MSI_DIR . "\166\151\x65\x77\163\x2f\x63\157\155\x6d\157\x6e\x2d\145\x6c\145\155\145\x6e\x74\x73\56\x70\x68\160";
include MSI_DIR . "\143\157\x6e\164\x72\x6f\154\x6c\145\162\163\57\163\163\157\55\x69\144\x70\55\x6e\x61\166\x62\141\x72\x2e\160\x68\x70";
if (!isset($_GET["\x70\141\x67\x65"])) {
    goto zd;
}
$jG = $mW && $JM ? "\163\163\157\55\151\x64\160\x2d\x70\x72\157\146\x69\154\x65\56\160\x68\x70" : "\163\x73\x6f\55\151\144\x70\55\x72\x65\x67\x69\x73\x74\162\x61\164\x69\157\156\x2e\160\x68\x70";
switch ($_GET["\x70\x61\x67\x65"]) {
    case $JH->_menuSlug:
        include $kK . "\x73\163\157\x2d\x69\144\160\x2d\144\x61\164\141\x2e\x70\150\x70";
        goto Nj;
    case $i_->_menuSlug:
        include $kK . "\x73\x73\x6f\x2d\x69\x64\160\x2d\x73\x65\164\164\x69\x6e\147\163\56\x70\150\x70";
        goto Nj;
    case $iQ->_menuSlug:
        include $kK . $jG;
        goto Nj;
    case $yF->_menuSlug:
        include $kK . "\x73\163\x6f\x2d\163\151\147\x6e\x69\x6e\55\163\145\164\164\151\x6e\x67\163\56\160\x68\x70";
        goto Nj;
    case $z3->_menuSlug:
        include $kK . "\163\x73\157\55\x61\x74\164\x72\x2d\x73\x65\x74\164\x69\x6e\147\x73\x2e\160\x68\160";
        goto Nj;
    case $kq->_menuSlug:
        include $kK . "\x73\x73\157\x2d\160\x72\x69\143\151\156\x67\x2e\x70\150\160";
        goto Nj;
    case $ij->_menuSlug:
        include $kK . "\163\163\x6f\x2d\151\144\x70\55\163\165\x70\160\157\162\x74\56\x70\x68\160";
        goto Nj;
    case $Fl:
        include $kK . "\160\x6c\x75\x67\151\156\55\x64\145\x74\x61\x69\x6c\163\56\160\150\x70";
        goto Nj;
}
aP:
Nj:
include $kK . "\143\x6f\x6e\164\x61\x63\x74\55\142\165\164\x74\x6f\x6e\56\160\x68\x70";
zd:
