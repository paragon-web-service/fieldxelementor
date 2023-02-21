<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPcURL;
final class FeedbackHandler extends BaseHandler
{
    use Instance;
    private function __construct()
    {
        $this->_nonce = "\x6d\x6f\x5f\151\144\x70\x5f\x66\x65\x65\x64\x62\x61\x63\153";
    }
    public function _mo_send_feedback($y8)
    {
        $this->isValidRequest();
        $ZH = $_POST["\x6d\151\x6e\151\157\162\141\x6e\147\x65\137\146\x65\x65\144\142\141\x63\x6b\x5f\x73\165\x62\155\151\164"];
        $Gs = sanitize_textarea_field($_POST["\x71\x75\x65\x72\171\137\146\x65\x65\x64\x62\141\143\x6b"]);
        $V3 = array_key_exists("\155\x6f\x5f\151\x64\x70\137\x6b\x65\145\160\137\x73\x65\x74\164\151\x6e\x67\x73\x5f\x69\156\x74\141\x63\164", $y8);
        if ($V3) {
            goto T5;
        }
        update_site_option("\x6d\x6f\137\x69\x64\160\x5f\x6b\145\145\x70\x5f\163\145\x74\x74\151\156\147\x73\x5f\x69\x6e\x74\x61\143\164", FALSE);
        goto MN;
        T5:
        update_site_option("\155\x6f\x5f\x69\144\160\137\153\145\145\160\137\x73\145\164\164\x69\x6e\147\x73\137\x69\156\x74\x61\143\164", TRUE);
        MN:
        if (!($ZH !== "\x53\x6b\151\x70\x20\46\40\x44\145\141\x63\164\151\166\141\164\145")) {
            goto oh;
        }
        $this->_sendEmail($this->_renderEmail($Gs));
        oh:
        deactivate_plugins([MSI_PLUGIN_NAME]);
    }
    private function _renderEmail($hW)
    {
        $p0 = file_get_contents(MSI_DIR . "\x69\156\x63\154\x75\x64\x65\x73\57\x68\164\x6d\x6c\x2f\146\145\x65\x64\x62\141\x63\153\x2e\155\151\x6e\56\x68\x74\x6d\x6c");
        $QV = get_site_option("\x6d\x6f\x5f\151\144\160\x5f\141\144\x6d\151\x6e\x5f\x65\x6d\x61\151\x6c");
        $p0 = str_replace("\x7b\173\x53\x45\122\x56\105\x52\x7d\x7d", $_SERVER["\x53\x45\x52\x56\x45\122\x5f\x4e\101\x4d\x45"], $p0);
        $p0 = str_replace("\173\x7b\105\x4d\101\111\114\175\175", $QV, $p0);
        $p0 = str_replace("\x7b\x7b\x50\114\x55\x47\111\116\x7d\175", MoIDPConstants::AREA_OF_INTEREST, $p0);
        $p0 = str_replace("\x7b\173\x56\105\x52\x53\x49\x4f\x4e\x7d\175", MSI_VERSION, $p0);
        $p0 = str_replace("\173\x7b\x54\x59\120\x45\175\175", "\133\x50\154\x75\x67\151\156\x20\104\145\141\143\164\x69\166\x61\164\145\144\135", $p0);
        $p0 = str_replace("\x7b\x7b\106\105\x45\104\102\x41\103\x4b\x7d\175", $hW, $p0);
        return $p0;
    }
    private function _sendEmail($Rp)
    {
        $Fn = get_site_option("\155\157\x5f\151\x64\x70\x5f\141\x64\155\x69\156\x5f\x63\165\163\x74\157\x6d\x65\162\137\153\x65\171");
        $o5 = get_site_option("\155\157\137\151\x64\160\137\141\x64\x6d\151\156\137\x61\160\151\137\153\145\x79");
        MoIDPcURL::notify(!$Fn ? MoIDPConstants::DEFAULT_CUSTOMER_KEY : $Fn, !$o5 ? MoIDPConstants::DEFAULT_API_KEY : $o5, MoIDPConstants::FEEDBACK_EMAIL, $Rp, "\x57\x6f\162\x64\120\162\145\x73\163\40\x49\104\120\x20\120\154\165\147\151\x6e\x20\104\145\x61\143\164\151\x76\x61\164\145\x64");
    }
}
