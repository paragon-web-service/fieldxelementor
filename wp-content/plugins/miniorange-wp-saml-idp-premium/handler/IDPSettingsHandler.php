<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
final class IDPSettingsHandler extends BaseHandler
{
    use Instance;
    private function __construct()
    {
    }
    public function mo_change_idp_entity_id($y8)
    {
        global $dbIDPQueries;
        $this->checkIfValidPlugin();
        if (array_key_exists("\155\x6f\x5f\163\x61\155\x6c\137\x69\x64\x70\137\145\x6e\x74\x69\164\171\x5f\x69\x64", $y8) && !empty($y8["\x6d\x6f\x5f\x73\x61\155\x6c\137\x69\144\160\x5f\x65\156\x74\151\x74\171\x5f\151\x64"])) {
            goto uo;
        }
        do_action("\x6d\x6f\137\x69\144\160\x5f\x73\x68\x6f\167\x5f\155\145\163\x73\141\x67\x65", MoIDPMessages::showMessage("\x49\x44\x50\137\x45\x4e\124\x49\124\x59\x5f\x49\104\137\x4e\125\x4c\x4c"), "\x45\x52\x52\117\122");
        goto JB;
        uo:
        update_site_option("\x6d\x6f\137\x69\144\160\137\x65\156\164\x69\x74\x79\137\151\x64", sanitize_text_field($y8["\x6d\x6f\137\x73\x61\155\x6c\137\x69\144\x70\137\x65\x6e\x74\x69\164\171\x5f\151\144"]));
        MoIDPUtility::createMetadataFile();
        do_action("\x6d\157\x5f\x69\x64\x70\137\x73\150\157\x77\x5f\155\145\x73\x73\141\147\145", MoIDPMessages::showMessage("\x49\x44\x50\x5f\x45\116\x54\x49\124\x59\137\111\x44\137\103\110\101\x4e\x47\x45\x44"), "\x53\125\x43\103\x45\x53\123");
        JB:
    }
}
