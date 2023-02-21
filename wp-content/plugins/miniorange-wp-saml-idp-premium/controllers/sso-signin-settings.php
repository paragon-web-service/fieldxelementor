<?php


global $dbIDPQueries, $wp_roles;
use IDP\Helper\Utilities\MoIDPUtility;
$di = $dbIDPQueries->get_sp_list();
$Pr = wp_logout_url(site_url());
$vh = $mW && $JM ? '' : "\x64\x69\x73\x61\x62\154\145\x64\x20\x74\x69\x74\x6c\x65\75\42\104\x69\163\141\x62\154\145\144\x2e\40\103\157\156\x66\151\x67\x75\x72\x65\40\171\x6f\x75\x72\40\x53\x65\x72\166\151\x63\x65\x20\120\162\x6f\x76\x69\144\x65\x72\42";
$bA = add_query_arg(array("\x70\141\147\145" => $iQ->_menuSlug), $_SERVER["\122\105\x51\125\x45\x53\x54\137\x55\x52\111"]);
$zF = esc_url(get_site_option("\155\x6f\x5f\x69\144\160\x5f\143\165\x73\x74\157\155\137\x6c\157\147\x69\x6e\137\165\162\x6c"));
$av = $wp_roles->role_names;
$Qi = array_keys($wp_roles->role_names);
$gA = array_values($wp_roles->role_names);
$fC = '';
$Z0 = get_site_option("\155\157\x5f\x69\x64\160\x5f\163\x73\157\x5f\141\x6c\x6c\157\167\x65\144\137\x72\x6f\x6c\145\x73");
$am = !is_array($Z0) ? array() : MoIDPUtility::sanitizeAssociativeArray($Z0);
$Q9 = get_site_option("\155\157\x5f\151\x64\160\137\x72\x6f\154\x65\x5f\x62\141\163\x65\x64\x5f\x72\x65\x73\x74\x72\151\143\x74\x69\x6f\156");
$Nr = empty($Q9) ? '' : "\x63\150\145\x63\153\x65\x64";
$HS = empty($Q9) ? "\150\151\x64\x64\145\156" : '';
$sv = end($Qi);
foreach ($Qi as $FJ) {
    if (isset($am[$FJ])) {
        goto vs;
    }
    goto jL;
    goto bR;
    vs:
    if (!($sv === $FJ)) {
        goto b3;
    }
    $fC = "\143\150\x65\x63\x6b\145\x64";
    b3:
    bR:
    By:
}
jL:
include MSI_DIR . "\x76\151\145\x77\x73\57\163\x69\147\x6e\x69\x6e\x2d\163\x65\x74\164\151\156\x67\163\56\x70\x68\160";
