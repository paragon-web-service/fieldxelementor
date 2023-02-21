<?php


namespace IDP\Helper\Utilities;

use IDP\Helper\Traits\Instance;
final class RewriteRules
{
    use Instance;
    function __construct()
    {
        add_filter("\155\x6f\144\137\162\x65\x77\x72\x69\164\x65\x5f\162\x75\154\x65\x73", array($this, "\x6f\165\x74\x70\165\x74\137\150\x74\141\x63\x63\x65\163\163"));
    }
    function output_htaccess($Xu)
    {
        $E6 = MSI_NAME;
        $zv = "\111\x6e\144\x65\170\x49\x67\x6e\x6f\162\145\40{$E6}\52\40\141\x63\164\151\x6f\x6e\163\x20\x63\157\x6e\164\162\x6f\154\x6c\x65\162\x73\40\x65\170\x63\145\x70\x74\x69\157\x6e\40\x68\145\x6c\x70\145\162\x20\x69\156\143\154\x75\144\145\x73\40\x73\143\x68\x65\144\165\154\x65\162\163\40\x76\151\x65\x77\x73\x20\52\56\160\150\x70" . "\12" . "\x3c\x46\151\x6c\145\x73\x4d\x61\x74\143\150\40\x22\x5c\x2e\50\153\145\x79\51\44\42\76" . "\xa" . "\117\162\144\145\162\x20\x61\154\x6c\157\x77\x2c\144\145\156\x79" . "\12" . "\104\x65\156\x79\x20\x66\x72\157\x6d\x20\x61\154\x6c" . "\xa" . "\x3c\57\106\151\x6c\x65\x73\x4d\x61\x74\143\x68\x3e";
        return $Xu . $zv;
    }
}
