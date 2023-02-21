<?php


namespace IDP\Handler;

use IDP\Helper\Traits\Instance;
use IDP\Helper\Constants\MoIDPMessages;
class CustomLoginURLHandler extends BaseHandler
{
    use Instance;
    function handle_custom_login_url($y8)
    {
        $this->checkIfValidPlugin();
        $yI = filter_var($y8["\x63\x75\x73\164\x6f\x6d\x5f\x6c\157\x67\151\x6e\137\x75\x72\x6c"], FILTER_SANITIZE_URL);
        $wK = filter_var($yI, FILTER_VALIDATE_URL);
        if ($y8["\143\x75\163\x74\x6f\x6d\x5f\154\x6f\147\151\156\x5f\165\162\154"] === '') {
            goto Gx;
        }
        if ($wK) {
            goto Ez;
        }
        do_action("\x6d\x6f\x5f\x69\x64\x70\x5f\163\150\157\167\x5f\155\x65\x73\163\x61\x67\145", MoIDPMessages::showMessage("\111\x4e\126\x41\114\x49\x44\x5f\x49\x4e\x50\125\124"), "\x45\x52\x52\x4f\x52");
        goto GF;
        Ez:
        update_site_option("\x6d\x6f\x5f\x69\x64\x70\x5f\x63\165\x73\164\157\x6d\x5f\x6c\x6f\x67\x69\156\x5f\x75\x72\154", $wK);
        do_action("\155\x6f\x5f\x69\144\160\137\163\150\x6f\167\137\155\145\x73\163\141\147\x65", MoIDPMessages::showMessage("\x53\x45\x54\x54\111\116\107\x53\137\123\101\x56\105\x44"), "\x53\125\x43\103\105\123\x53");
        GF:
        goto YJ;
        Gx:
        update_site_option("\x6d\157\x5f\x69\x64\x70\137\x63\165\163\x74\x6f\155\137\x6c\157\147\x69\x6e\x5f\x75\162\x6c", NULL);
        do_action("\x6d\x6f\x5f\x69\144\x70\x5f\x73\150\x6f\x77\137\x6d\x65\163\x73\x61\147\145", MoIDPMessages::showMessage("\x53\x45\124\x54\x49\x4e\x47\123\137\x53\101\126\105\104"), "\x53\125\x43\103\x45\x53\123");
        YJ:
    }
}
