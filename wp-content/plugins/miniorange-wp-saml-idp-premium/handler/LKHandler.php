<?php


namespace IDP\Handler;

use IDP\Handler\BaseHandler;
use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Traits\Instance;
use IDP\Helper\Utilities\MoIDPUtility;
use IDP\Schedulers\SchedulerFactory;
final class LKHandler extends BaseHandler
{
    use Instance;
    private function __construct()
    {
    }
    public function _mo_verify_license($y8)
    {
        $this->checkIfRequiredFieldsEmpty(array("\151\144\x70\x5f\154\153" => $y8));
        $Oo = trim($y8["\x69\144\x70\x5f\x6c\153"]);
        $T7 = json_decode(MoIDPUtility::ccl(), true);
        $e5 = array_key_exists("\156\157\117\146\125\163\145\x72\x73", $T7) ? $T7["\156\x6f\117\146\125\163\145\162\163"] : null;
        $HP = array_key_exists("\x6c\151\x63\x65\x6e\163\145\105\170\160\151\x72\x79", $T7) ? strtotime($T7["\x6c\x69\143\145\156\x73\145\105\x78\x70\151\162\x79"]) === false ? null : strtotime($T7["\x6c\x69\x63\145\x6e\x73\x65\x45\170\160\x69\x72\x79"]) : null;
        switch ($T7["\163\x74\x61\164\165\x73"]) {
            case "\x53\x55\103\x43\x45\123\123":
                $this->_vlk_success($Oo, $e5, $HP);
                goto Ba;
            default:
                $this->_vlk_fail();
                goto Ba;
        }
        cG:
        Ba:
    }
    public function _vlk_success($Oo, $e5, $HP)
    {
        $Rp = json_decode(MoIDPUtility::vml($Oo), true);
        if (array_key_exists("\163\x74\x61\x74\165\163", $Rp)) {
            goto G7;
        }
        do_action("\x6d\x6f\x5f\151\144\x70\x5f\x73\x68\157\x77\137\x6d\145\163\x73\x61\147\x65", MoIDPMessages::showMessage("\x45\122\122\x4f\122\137\x4f\103\x43\125\122\122\105\x44"), "\105\x52\122\x4f\x52");
        goto Zk;
        G7:
        if (strcasecmp($Rp["\x73\x74\x61\x74\x75\x73"], "\123\x55\103\103\x45\123\x53") == 0) {
            goto Kt;
        }
        if (!(strcasecmp($Rp["\x73\x74\x61\164\165\x73"], "\x46\101\x49\114\x45\x44") == 0)) {
            goto HG;
        }
        if (strcasecmp($Rp["\155\145\x73\163\141\x67\x65"], "\x43\157\x64\145\40\150\x61\x73\x20\x45\170\160\x69\162\x65\x64") == 0) {
            goto QT;
        }
        do_action("\155\x6f\x5f\x69\144\160\137\163\x68\x6f\167\137\x6d\145\163\x73\x61\147\145", MoIDPMessages::showMessage("\x45\116\124\105\x52\105\x44\x5f\x49\x4e\126\x41\x4c\111\x44\137\113\105\x59"), "\x45\x52\122\117\x52");
        goto Va;
        QT:
        do_action("\155\157\x5f\x69\x64\160\x5f\x73\150\157\167\137\x6d\x65\163\x73\141\x67\145", MoIDPMessages::showMessage("\x4c\111\x43\x45\116\123\x45\x5f\113\x45\x59\137\111\116\x5f\125\x53\x45"), "\x45\x52\x52\x4f\x52");
        Va:
        HG:
        goto hR;
        Kt:
        $xO = get_site_option("\155\157\x5f\151\144\x70\137\x63\x75\x73\164\157\155\x65\162\x5f\x74\x6f\x6b\x65\156");
        update_site_option("\x73\155\x6c\x5f\x69\x64\x70\x5f\x6c\x6b", \AESEncryption::encrypt_data($Oo, $xO));
        update_site_option("\163\x69\164\x65\137\151\x64\160\137\x63\x6b\154", \AESEncryption::encrypt_data("\x74\x72\165\145", $xO));
        update_site_option("\164\137\163\x69\164\x65\137\163\164\141\164\165\x73", \AESEncryption::encrypt_data("\x66\x61\x6c\x73\x65", $xO));
        if (MoIdpUtility::isBlank($e5)) {
            goto Ra;
        }
        update_site_option("\155\x6f\137\151\x64\160\137\165\x73\162\x5f\154\x6d\x74", \AESEncryption::encrypt_data($e5, $xO));
        Ra:
        if (MoIdpUtility::isBlank($HP)) {
            goto tS;
        }
        update_site_option("\163\x6d\154\137\x69\144\160\x5f\154\145\144", \AESEncryption::encrypt_data($HP, $xO));
        tS:
        do_action("\155\x6f\x5f\x69\144\x70\x5f\x73\150\x6f\x77\x5f\x6d\x65\x73\x73\x61\x67\x65", MoIDPMessages::showMessage("\114\111\x43\x45\116\x53\105\137\x56\x45\122\111\x46\x49\x45\104"), "\123\x55\103\103\105\123\x53");
        hR:
        Zk:
    }
    public function _vlk_fail()
    {
        $xO = get_site_option("\155\x6f\x5f\151\144\160\137\x63\x75\163\x74\x6f\x6d\x65\162\x5f\164\x6f\x6b\x65\x6e");
        update_site_option("\x73\x69\x74\x65\137\151\144\x70\137\143\153\x6c", \AESEncryption::encrypt_data("\x66\x61\x6c\163\x65", $xO));
        do_action("\x6d\157\x5f\151\144\160\137\163\150\x6f\x77\137\155\x65\163\163\x61\147\x65", MoIDPMessages::showMessage("\116\x4f\124\x5f\x55\x50\107\x52\101\x44\105\104\x5f\x59\105\124", array("\x75\162\x6c" => "\x68\164\x74\160\163\x3a\x2f\x2f\160\x6c\x75\x67\151\x6e\x73\56\x6d\x69\156\151\x6f\162\x61\156\x67\145\x2e\x63\x6f\x6d\x2f\167\157\162\144\x70\162\x65\163\x73\x2d\x73\141\x6d\154\x2d\151\144\160\x23\x70\x72\x69\143\151\x6e\x67")), "\x45\x52\x52\117\x52");
    }
    public static function checkLForR($L1)
    {
        if (!MSI_DEBUG) {
            goto UY;
        }
        MoIDPUtility::mo_debug("\101\154\x65\x72\164\40\x75\x73\145\x72\40\164\150\141\x74\x20\150\145\40\156\x65\x65\x64\163\40\164\x6f\40\x72\145\156\145\167\x20\150\151\x73\40\x6c\x69\x63\145\x6e\x73\145");
        UY:
        $fq = SchedulerFactory::getInstance();
        if ($L1 == "\x33\60") {
            goto YG;
        }
        if ($L1 == "\x31\65") {
            goto lX;
        }
        if ($L1 == "\x35") {
            goto Q9;
        }
        MoIDPUtility::slrfae();
        $fq->unset5DaySchedule();
        $fq->setFinalCheckSchedule();
        goto k1;
        Q9:
        MoIDPUtility::slrae($L1);
        $fq->unset10DaySchedule();
        $fq->set5DaySchedule("\x30");
        k1:
        goto gj;
        lX:
        MoIDPUtility::slrae($L1);
        $fq->unset15DaySchedule();
        $fq->set10DaySchedule("\x35");
        gj:
        goto J1;
        YG:
        MoIDPUtility::slrae($L1);
        $fq->unsetYearlySchedule();
        $fq->set15DaySchedule("\61\65");
        J1:
    }
    public static function CheckIfUserHasRHisL()
    {
        if (!MSI_DEBUG) {
            goto k8;
        }
        MoIDPUtility::mo_debug("\103\150\x65\x63\153\151\156\x67\40\x69\x66\40\165\x73\145\162\x20\150\141\163\40\162\x65\x6e\x65\167\145\x64\40\x68\151\163\40\154\x69\x63\145\x6e\163\145");
        k8:
        SchedulerFactory::getInstance()->unsetFinalCheckSchedule();
        MoIDPUtility::spdae();
        do_action("\x73\164\141\x72\164\x64\x70\x72\x6f\x63\145\x73\163");
    }
    public function refresh_sp_users_count()
    {
        $Rp = json_decode(MoIDPUtility::ccl(), true);
        if ($Rp) {
            goto Gb;
        }
        do_action("\155\x6f\137\x69\144\x70\137\163\x68\x6f\x77\x5f\155\x65\163\x73\x61\147\145", MoIDPMessages::showMessage("\105\122\x52\117\x52\137\x4f\103\x43\x55\x52\x52\x45\104"), "\105\x52\x52\117\x52");
        goto Xr;
        Gb:
        update_site_option("\155\157\137\x69\144\x70\137\x73\160\137\x63\x6f\x75\156\164", $Rp["\156\x6f\x4f\x66\123\120"]);
        $e5 = array_key_exists("\x6e\x6f\117\146\125\163\145\x72\x73", $Rp) ? $Rp["\156\157\x4f\x66\x55\x73\145\x72\x73"] : null;
        $HP = array_key_exists("\x6c\151\x63\x65\156\163\145\x45\170\x70\x69\162\171", $Rp) ? strtotime($Rp["\x6c\151\143\145\156\x73\145\105\x78\160\x69\162\x79"]) === false ? null : strtotime($Rp["\154\151\143\145\x6e\163\x65\105\x78\160\151\162\171"]) : null;
        $xO = get_site_option("\155\157\137\x69\x64\160\137\143\165\x73\164\x6f\155\145\162\x5f\x74\x6f\x6b\x65\156");
        if (!($HP > time() + 31 * 24 * 3600)) {
            goto bw;
        }
        delete_site_option("\x69\x64\x70\137\x6c\151\x63\145\x6e\163\x65\x5f\x61\x6c\x65\x72\x74\x5f\163\145\156\x74");
        bw:
        if (MoIdpUtility::isBlank($e5)) {
            goto Oz;
        }
        update_site_option("\155\x6f\137\151\x64\160\x5f\165\163\x72\137\154\155\x74", \AESEncryption::encrypt_data($e5, $xO));
        Oz:
        if (MoIdpUtility::isBlank($HP)) {
            goto hz;
        }
        update_site_option("\x73\x6d\x6c\137\151\x64\x70\137\x6c\x65\x64", \AESEncryption::encrypt_data($HP, $xO));
        hz:
        Xr:
    }
}
