<?php


namespace IDP\Handler;

use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
final class SupportHandler extends BaseHandler
{
    use Instance;
    private function __construct()
    {
    }
    public function _mo_idp_support_query($y8)
    {
        $this->checkIfSupportQueryFieldsEmpty(array("\155\157\137\151\144\x70\x5f\143\x6f\x6e\x74\x61\x63\x74\137\165\163\137\145\155\x61\x69\154" => $y8, "\x6d\x6f\x5f\x69\x64\160\137\x63\x6f\x6e\x74\141\x63\164\137\165\163\x5f\x71\x75\x65\162\x79" => $y8));
        $QV = sanitize_text_field($y8["\x6d\157\137\x69\144\160\137\143\x6f\156\x74\x61\143\x74\x5f\x75\x73\137\x65\x6d\141\151\154"]);
        $mA = sanitize_text_field($y8["\x6d\157\137\151\x64\160\137\143\x6f\156\164\141\x63\x74\x5f\165\x73\137\x70\x68\157\x6e\x65"]);
        $lB = sanitize_text_field($y8["\x6d\x6f\137\x69\x64\160\x5f\143\157\156\x74\x61\143\x74\x5f\x75\163\137\x71\165\x65\x72\171"]);
        $oG = MoIDPUtility::submitContactUs($QV, $mA, $lB);
        if ($oG == FALSE) {
            goto vU;
        }
        do_action("\155\157\137\x69\144\160\137\x73\150\x6f\x77\x5f\155\x65\163\x73\141\x67\x65", MoIDPMessages::showMessage("\x51\125\105\122\x59\137\123\105\x4e\x54"), "\123\x55\x43\103\x45\x53\x53");
        goto Kn;
        vU:
        do_action("\x6d\157\x5f\x69\144\x70\137\163\150\157\x77\137\x6d\x65\163\163\141\147\x65", MoIDPMessages::showMessage("\x45\x52\122\117\x52\137\x51\125\x45\122\x59"), "\105\122\122\x4f\x52");
        Kn:
    }
}
