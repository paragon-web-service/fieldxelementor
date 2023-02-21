<?php
/*
Plugin Name: Login using WordPress Users
Plugin URI: https://miniorange.com/
Description: (Premium) Convert your WordPress into an IDP.
Version: 13.1.1
Author: miniOrange
Author URI: https://miniorange.com/
*/


if (defined("\101\x42\x53\120\101\124\x48")) {
    goto l_;
}
exit;
l_:
define("\115\x53\x49\137\120\x4c\125\107\111\116\137\x4e\x41\115\105", plugin_basename(__FILE__));
$E6 = substr(MSI_PLUGIN_NAME, 0, strpos(MSI_PLUGIN_NAME, "\57"));
define("\x4d\123\x49\x5f\x4e\101\x4d\105", $E6);
include "\x61\165\164\157\x6c\157\x61\144\56\x70\x68\160";
\IDP\MoIDP::instance();
