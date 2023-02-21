<?php


namespace IDP\Helper\Utilities;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\SAML2\MetadataGenerator;
use IDP\Exception\InvalidSSOUserException;
use IDP\Exception\InvalidOperationException;
class MoIDPUtility
{
    public static function getHiddenPhone($mA)
    {
        $Ts = "\x78\170\170\x78\170\170\x78" . substr($mA, strlen($mA) - 3);
        return $Ts;
    }
    public static function isBlank($l7)
    {
        if (!(!isset($l7) || empty($l7))) {
            goto Rw;
        }
        return TRUE;
        Rw:
        return FALSE;
    }
    public static function isCurlInstalled()
    {
        return in_array("\x63\165\162\x6c", get_loaded_extensions());
    }
    public static function startSession()
    {
        if (!(!session_id() || session_id() == '' || !isset($_SESSION))) {
            goto nI;
        }
        session_start();
        nI:
    }
    public static function validatePhoneNumber($mA)
    {
        return preg_match(MoIDPConstants::PATTERN_PHONE, $mA, $BT);
    }
    public static function getCurrPageUrl()
    {
        $T9 = "\150\x74\x74\x70";
        if (!(isset($_SERVER["\110\124\124\x50\x53"]) && $_SERVER["\110\124\x54\120\x53"] == "\x6f\156")) {
            goto ZM;
        }
        $T9 .= "\x73";
        ZM:
        $T9 .= "\x3a\57\57";
        if ($_SERVER["\123\x45\x52\x56\x45\x52\137\120\x4f\x52\124"] != "\x38\x30") {
            goto DJ;
        }
        $T9 .= $_SERVER["\123\x45\x52\x56\x45\122\137\x4e\x41\115\105"] . $_SERVER["\x52\x45\x51\125\105\123\x54\x5f\125\x52\111"];
        goto v4;
        DJ:
        $T9 .= $_SERVER["\123\105\122\x56\105\x52\137\116\101\115\x45"] . "\72" . $_SERVER["\123\x45\122\126\105\x52\x5f\x50\x4f\x52\124"] . $_SERVER["\x52\x45\121\x55\105\123\124\137\x55\122\111"];
        v4:
        if (!function_exists("\x61\160\160\x6c\171\137\146\151\154\x74\x65\162\x73")) {
            goto kB;
        }
        apply_filters("\167\160\160\x62\x5f\x63\x75\162\160\141\147\x65\165\x72\x6c", $T9);
        kB:
        return $T9;
    }
    public static function addSPCookie($QB, $FW = '')
    {
        if (!isset($_COOKIE["\155\157\137\163\160\137\143\x6f\165\156\164"])) {
            goto k6;
        }
        $rM = 1;
        e6:
        if (!($rM <= $_COOKIE["\155\x6f\x5f\163\x70\137\x63\157\x75\156\164"])) {
            goto jl;
        }
        if (!($_COOKIE["\155\157\137\x73\160\x5f" . $rM . "\137\151\163\163\165\145\162"] == $QB)) {
            goto NJ;
        }
        return;
        NJ:
        vY:
        $rM++;
        goto e6;
        jl:
        k6:
        $C4 = isset($_COOKIE["\x6d\x6f\x5f\163\x70\x5f\x63\157\165\156\164"]) ? $_COOKIE["\x6d\157\137\163\x70\137\x63\157\x75\x6e\x74"] + 1 : 1;
        setcookie("\155\x6f\137\x73\160\137\x63\x6f\x75\x6e\x74", $C4, time() + 21600, "\57");
        setcookie("\x6d\157\x5f\163\160\137" . $C4 . "\x5f\151\163\x73\165\x65\162", $QB, time() + 21600, "\57");
        setcookie("\x6d\157\137\x73\x70\x5f" . $C4 . "\137\x73\x65\163\x73\x69\x6f\x6e\x49\156\x64\145\170", $FW, time() + 21600, "\57");
    }
    public static function getHiddenEmail($QV)
    {
        if (!(!isset($QV) || trim($QV) === '')) {
            goto qQ;
        }
        return '';
        qQ:
        $Dj = strlen($QV);
        $Kp = substr($QV, 0, 1);
        $AR = strrpos($QV, "\x40");
        $iS = substr($QV, $AR - 1, $Dj);
        $rM = 1;
        kl:
        if (!($rM < $AR)) {
            goto ve;
        }
        $Kp = $Kp . "\170";
        JD:
        $rM++;
        goto kl;
        ve:
        $Wn = $Kp . $iS;
        return $Wn;
    }
    public static function micr()
    {
        $QV = get_site_option("\155\157\x5f\x69\144\x70\x5f\x61\144\155\151\156\x5f\x65\155\141\151\154");
        $Fn = get_site_option("\155\x6f\x5f\151\x64\x70\x5f\141\x64\155\151\x6e\137\143\x75\163\164\x6f\x6d\145\162\137\x6b\145\171");
        return !$QV || !$Fn || !is_numeric(trim($Fn)) ? false : true;
    }
    public static function gssc()
    {
        global $dbIDPQueries;
        return $dbIDPQueries->get_sp_count();
    }
    public static function createCustomer()
    {
        $QV = get_site_option("\155\x6f\137\151\144\x70\x5f\x61\x64\155\151\x6e\x5f\145\x6d\x61\x69\x6c");
        $mA = get_site_option("\155\x6f\x5f\151\144\x70\x5f\141\x64\155\x69\x6e\x5f\160\150\x6f\x6e\x65");
        $xq = get_site_option("\x6d\x6f\137\151\x64\x70\x5f\141\x64\155\151\156\137\x70\x61\x73\163\x77\157\162\144");
        $Dc = get_site_option("\x6d\157\x5f\x69\144\160\137\x63\157\155\x70\141\x6e\171\137\x6e\x61\155\x65");
        $VH = get_site_option("\155\x6f\x5f\151\x64\160\x5f\x66\x69\x72\163\x74\137\156\141\155\x65");
        $Dp = get_site_option("\155\157\137\x69\144\160\137\154\141\163\164\137\156\141\155\x65");
        $Rp = MoIDPcURL::create_customer($QV, $Dc, $xq, $mA, $VH, $Dp);
        return $Rp;
    }
    public static function getCustomerKey($QV, $xq)
    {
        $Rp = MoIDPcURL::get_customer_key($QV, $xq);
        return $Rp;
    }
    public static function checkCustomer()
    {
        $QV = get_site_option("\155\x6f\137\x69\x64\160\x5f\x61\x64\155\x69\156\137\145\x6d\141\x69\x6c");
        $Rp = MoIDPcURL::check_customer($QV);
        return $Rp;
    }
    public static function sendOtpToken($Gu, $QV = '', $mA = '')
    {
        $Rp = MoIDPcURL::send_otp_token($Gu, $mA, $QV);
        return $Rp;
    }
    public static function validateOtpToken($sm, $Cp)
    {
        $Rp = MoIDPcURL::validate_otp_token($sm, $Cp);
        return $Rp;
    }
    public static function submitContactUs($QV, $mA, $lB)
    {
        MoIDPcURL::submit_contact_us($QV, $mA, $lB);
        return true;
    }
    public static function forgotPassword($QV)
    {
        $QV = get_site_option("\155\x6f\x5f\151\144\160\x5f\141\x64\x6d\x69\156\137\x65\x6d\141\x69\154");
        $Fn = get_site_option("\155\157\137\x69\144\160\x5f\141\x64\155\151\156\x5f\143\165\163\164\x6f\x6d\145\162\x5f\x6b\x65\171");
        $o5 = get_site_option("\155\x6f\x5f\x69\x64\160\x5f\141\144\x6d\151\x6e\137\x61\x70\151\x5f\153\145\171");
        $Rp = MoIDPcURL::forgot_password($QV, $Fn, $o5);
        return $Rp;
    }
    public static function ccl()
    {
        $Fn = get_site_option("\155\x6f\x5f\x69\x64\160\137\141\144\155\151\x6e\137\x63\165\x73\164\157\x6d\x65\x72\137\x6b\145\x79");
        $o5 = get_site_option("\x6d\157\x5f\151\x64\x70\137\x61\x64\x6d\x69\x6e\137\141\x70\x69\137\153\x65\171");
        $Rp = MoIDPcURL::ccl($Fn, $o5);
        return $Rp;
    }
    public static function unsetCookieVariables($Tu)
    {
        foreach ($Tu as $T_) {
            unset($_COOKIE[$T_]);
            setcookie($T_, '', time() - 86400, "\x2f");
            tI:
        }
        xz:
    }
    public static function getPublicCertPath()
    {
        return MSI_DIR . "\151\156\143\154\165\x64\145\163" . DIRECTORY_SEPARATOR . "\162\145\163\157\165\x72\143\145\x73" . DIRECTORY_SEPARATOR . "\151\x64\160\x2d\x73\x69\x67\x6e\x69\x6e\147\x2e\x63\x72\164";
    }
    public static function getPrivateKeyPath()
    {
        return MSI_DIR . "\x69\x6e\x63\x6c\x75\x64\x65\x73" . DIRECTORY_SEPARATOR . "\x72\x65\x73\x6f\x75\x72\143\145\x73" . DIRECTORY_SEPARATOR . "\151\144\x70\55\x73\x69\147\156\x69\x6e\x67\x2e\x6b\x65\171";
    }
    public static function getPublicCert()
    {
        return file_get_contents(MSI_DIR . "\151\x6e\x63\x6c\x75\x64\145\163" . DIRECTORY_SEPARATOR . "\x72\145\x73\157\165\x72\143\145\163" . DIRECTORY_SEPARATOR . "\x69\x64\x70\x2d\163\x69\x67\x6e\x69\x6e\x67\56\143\162\x74");
    }
    public static function getPrivateKey()
    {
        return file_get_contents(MSI_DIR . "\151\x6e\x63\154\x75\x64\x65\163" . DIRECTORY_SEPARATOR . "\162\x65\163\x6f\x75\x72\x63\x65\x73" . DIRECTORY_SEPARATOR . "\x69\144\x70\55\163\151\147\x6e\x69\156\147\x2e\153\x65\171");
    }
    public static function getPublicCertURL()
    {
        return MSI_URL . "\151\x6e\x63\x6c\x75\x64\145\x73" . DIRECTORY_SEPARATOR . "\x72\x65\163\157\165\x72\143\x65\163" . DIRECTORY_SEPARATOR . "\x69\144\160\55\163\151\x67\x6e\151\156\x67\x2e\143\162\x74";
    }
    public static function mo_debug($hW)
    {
        error_log("\133\x4d\x4f\55\x4d\x53\x49\55\x4c\117\x47\x5d\x5b" . date("\x6d\x2d\x64\55\x59", time()) . "\135\72\40" . $hW);
    }
    public static function createMetadataFile()
    {
        $xj = is_multisite() ? get_sites() : null;
        $ER = is_null($xj) ? site_url("\57") : get_site_url($xj[0]->blog_id, "\57");
        $Pr = is_null($xj) ? site_url("\57") : get_site_url($xj[0]->blog_id, "\57");
        $ED = get_site_option("\x6d\157\x5f\151\144\x70\x5f\145\156\x74\151\164\x79\137\151\x64") ? get_site_option("\x6d\157\x5f\x69\x64\x70\x5f\145\x6e\x74\151\164\x79\137\x69\x64") : MSI_URL;
        $Ln = self::getPublicCert();
        $UP = new MetadataGenerator($ED, TRUE, $Ln, $ER, $ER, $Pr, $Pr);
        $s1 = $UP->generateMetadata();
        if (!MSI_DEBUG) {
            goto rN;
        }
        MoIDPUtility::mo_debug("\x4d\145\x74\141\144\x61\164\141\40\107\145\156\x65\x72\141\x74\x65\144\72\40" . $s1);
        rN:
        $oh = fopen(MSI_DIR . "\155\x65\x74\141\144\x61\164\141\56\x78\155\x6c", "\x77");
        fwrite($oh, $s1);
        fclose($oh);
    }
    public static function showMetadata()
    {
        $xj = is_multisite() ? get_sites() : null;
        $ER = is_null($xj) ? site_url("\x2f") : get_site_url($xj[0]->blog_id, "\x2f");
        $Pr = is_null($xj) ? site_url("\x2f") : get_site_url($xj[0]->blog_id, "\x2f");
        $ED = get_site_option("\x6d\x6f\x5f\151\x64\x70\137\x65\156\x74\x69\x74\171\x5f\151\144") ? get_site_option("\155\157\x5f\151\144\160\137\145\x6e\x74\x69\x74\x79\137\151\x64") : MSI_URL;
        $Ln = self::getPublicCert();
        $UP = new MetadataGenerator($ED, TRUE, $Ln, $ER, $ER, $Pr, $Pr);
        $s1 = $UP->generateMetadata();
        if (!ob_get_contents()) {
            goto tM;
        }
        ob_clean();
        tM:
        header("\103\x6f\156\164\145\156\x74\55\x54\171\160\x65\72\40\164\x65\170\164\57\170\155\154");
        echo $s1;
        exit;
    }
    public static function generateRandomAlphanumericValue($kd)
    {
        $lW = "\141\x62\x63\144\145\x66\x30\x31\x32\x33\x34\65\x36\67\x38\71";
        $SS = strlen($lW);
        $ZS = '';
        $rM = 0;
        zJ:
        if (!($rM < $kd)) {
            goto Ti;
        }
        $ZS .= substr($lW, rand(0, 15), 1);
        YU:
        $rM++;
        goto zJ;
        Ti:
        return "\x61" . $ZS;
    }
    public static function iclv()
    {
        $xO = get_site_option("\x6d\x6f\137\151\144\x70\x5f\143\x75\x73\x74\157\x6d\x65\x72\137\164\x6f\153\x65\x6e");
        $cI = \AESEncryption::decrypt_data(get_site_option("\x73\151\164\x65\x5f\x69\144\160\137\143\153\154"), $xO);
        $rm = get_site_option("\x73\x6d\154\x5f\x69\144\x70\137\x6c\x6b");
        $QV = get_site_option("\155\x6f\137\151\144\x70\137\x61\144\x6d\x69\156\137\145\x6d\141\x69\x6c");
        $Fn = get_site_option("\155\x6f\x5f\x69\144\x70\137\x61\x64\x6d\x69\156\137\x63\165\163\164\x6f\155\145\162\137\x6b\145\171");
        return !($cI != "\164\162\165\145" || !$rm || !$QV || !$Fn || !is_numeric(trim($Fn)));
    }
    public static function cled()
    {
        $xO = get_site_option("\155\157\x5f\x69\144\160\137\143\x75\x73\x74\157\155\145\x72\137\x74\157\153\145\x6e");
        $wy = get_site_option("\163\155\x6c\137\x69\x64\160\137\x6c\145\144");
        $HP = null;
        if ($wy == null) {
            goto a4;
        }
        $HP = \AESEncryption::decrypt_data($wy, $xO);
        goto FG;
        a4:
        $Rp = json_decode(self::ccl(), true);
        update_site_option("\x6d\x6f\137\151\144\160\137\x73\x70\137\143\x6f\x75\x6e\x74", $Rp["\x6e\157\117\146\123\120"]);
        $e5 = array_key_exists("\x6e\157\117\x66\125\163\x65\162\163", $Rp) ? $Rp["\x6e\x6f\x4f\146\x55\x73\145\x72\163"] : null;
        $HP = array_key_exists("\x6c\x69\x63\145\x6e\x73\x65\105\x78\160\151\x72\171", $Rp) ? strtotime($Rp["\154\151\x63\x65\x6e\163\145\x45\x78\160\x69\162\x79"]) === false ? null : strtotime($Rp["\154\x69\x63\145\x6e\x73\x65\105\x78\x70\x69\162\171"]) : null;
        if (self::isBlank($e5)) {
            goto az;
        }
        update_site_option("\155\x6f\x5f\151\x64\160\137\165\163\162\137\154\155\164", \AESEncryption::encrypt_data($e5, $xO));
        az:
        if (self::isBlank($HP)) {
            goto wt;
        }
        update_site_option("\x73\155\154\137\x69\x64\x70\137\x6c\145\x64", \AESEncryption::encrypt_data($HP, $xO));
        wt:
        FG:
        $wn = new \DateTime("\x40{$HP}");
        $Wx = new \DateTime();
        $HK = $Wx->diff($wn)->format("\45\x72\45\141");
        if (!($HK <= 30)) {
            goto B_;
        }
        $AF = get_site_option("\151\x64\160\x5f\154\151\x63\145\156\163\x65\137\141\154\145\x72\164\x5f\x73\x65\156\164");
        if ($HK > 7) {
            goto Yc;
        }
        if ($HK <= 7 && $HK > 0) {
            goto sL;
        }
        if ($HK <= 0 && $HK > -15) {
            goto Lf;
        }
        if (!($HK <= -15)) {
            goto u4;
        }
        if (!($AF == null || $AF <= 0 && $AF > -15)) {
            goto Ms;
        }
        self::spdae();
        update_site_option("\x69\x64\x70\137\x6c\x69\143\x65\156\163\145\137\x61\154\145\162\x74\x5f\163\145\x6e\x74", $HK);
        Ms:
        return true;
        u4:
        goto kp;
        Lf:
        if (!($AF == null || $AF <= 7 && $AF > 0)) {
            goto gG;
        }
        self::slrfae();
        update_site_option("\x69\x64\160\x5f\154\151\x63\x65\156\163\x65\x5f\x61\x6c\x65\162\x74\137\x73\x65\156\164", $HK);
        gG:
        kp:
        goto XD;
        sL:
        if (!($AF == null || $AF <= 30 && $AF > 7)) {
            goto CY;
        }
        self::slrae($HK);
        update_site_option("\x69\x64\x70\x5f\x6c\151\x63\x65\x6e\x73\x65\137\141\154\145\162\164\137\x73\x65\x6e\164", $HK);
        CY:
        XD:
        goto PX;
        Yc:
        if (!($AF == null)) {
            goto DH;
        }
        self::slrae($HK);
        update_site_option("\x69\144\x70\x5f\154\151\143\x65\x6e\x73\x65\137\141\154\145\162\164\137\163\145\156\164", $HK);
        DH:
        PX:
        B_:
        return false;
    }
    public static function cvd()
    {
        $Kg = get_site_option("\151\x64\160\137\x76\x6c\x5f\x63\x68\x65\x63\x6b\x5f\x74");
        if (empty($Kg)) {
            goto Jo;
        }
        $Kg = intval($Kg);
        if (!(time() - $Kg < 3600 * 24 * 3)) {
            goto Nb;
        }
        return false;
        Nb:
        Jo:
        $Oo = get_site_option("\163\x6d\154\137\x69\x64\x70\137\x6c\153");
        $xO = get_site_option("\155\157\137\x69\144\160\x5f\143\x75\163\164\x6f\155\145\x72\137\x74\x6f\x6b\x65\156");
        if (!self::mo_idp_lk_multi_host()) {
            goto Nk;
        }
        $Oo = \AESEncryption::decrypt_data($Oo, $xO);
        $Rp = json_decode(MoIDPUtility::vml($Oo, true), true);
        if (strcasecmp($Rp["\x73\x74\141\x74\x75\x73"], "\123\125\103\103\x45\x53\123") == 0) {
            goto Jq;
        }
        return true;
        goto AT;
        Jq:
        delete_site_option("\x69\x64\160\x5f\x76\154\x5f\143\x68\145\x63\x6b\137\163");
        update_site_option("\151\x64\x70\x5f\x76\154\137\x63\x68\145\x63\153\x5f\164", time());
        return false;
        AT:
        Nk:
        if (empty($Oo)) {
            goto dn;
        }
        $Oo = \AESEncryption::decrypt_data($Oo, $xO);
        $Rp = json_decode(MoIDPUtility::vml($Oo, true), true);
        if (!(strcasecmp($Rp["\163\x74\x61\x74\165\163"], "\x53\x55\103\x43\x45\x53\x53") != 0)) {
            goto HD;
        }
        update_site_option("\151\144\x70\137\166\x6c\137\143\150\x65\143\x6b\x5f\163", \AESEncryption::encrypt_data("\x66\141\154\x73\145", $xO));
        HD:
        dn:
        update_site_option("\151\144\x70\x5f\x76\154\137\x63\x68\145\x63\153\137\x74", time());
        return false;
    }
    public static function mo_idp_lk_multi_host()
    {
        $xO = get_site_option("\155\x6f\x5f\151\x64\x70\x5f\x63\165\x73\x74\x6f\155\x65\x72\x5f\x74\157\x6b\x65\156");
        $qj = get_site_option("\151\144\x70\137\x76\154\x5f\143\150\145\x63\153\x5f\163");
        if (empty($qj)) {
            goto ca;
        }
        $qj = \AESEncryption::decrypt_data($qj, $xO);
        if (!($qj == "\146\x61\x6c\163\x65")) {
            goto RK;
        }
        return true;
        RK:
        ca:
        return false;
    }
    public static function vml($Oo, $yW = false)
    {
        $Fn = get_site_option("\155\x6f\137\151\x64\160\x5f\x61\144\155\x69\156\137\x63\165\x73\x74\157\155\x65\162\x5f\x6b\145\171");
        $o5 = get_site_option("\155\157\x5f\x69\144\x70\x5f\x61\144\155\x69\x6e\x5f\141\160\x69\x5f\x6b\145\x79");
        $Rp = MoIDPcURL::vml($Fn, $o5, $Oo, site_url(), $yW);
        return $Rp;
    }
    public static function mius()
    {
        $Fn = get_site_option("\x6d\x6f\x5f\151\x64\160\137\x61\144\x6d\x69\x6e\x5f\143\x75\x73\164\x6f\155\145\162\137\x6b\x65\x79");
        $o5 = get_site_option("\x6d\x6f\x5f\x69\x64\160\137\141\x64\x6d\x69\156\x5f\141\160\x69\137\x6b\145\x79");
        $xO = get_site_option("\155\157\x5f\151\144\x70\137\143\x75\x73\164\x6f\x6d\x65\x72\137\164\157\x6b\x65\156");
        $Oo = \AESEncryption::decrypt_data(get_site_option("\x73\155\x6c\137\151\144\160\x5f\x6c\x6b"), $xO);
        $Rp = MoIDPcURL::mius($Fn, $o5, $Oo);
        return $Rp;
    }
    public static function suedae($Tv)
    {
        if (!MSI_DEBUG) {
            goto sH;
        }
        MoIDPUtility::mo_debug("\123\145\x6e\144\x69\x6e\x67\x20\x75\x73\145\162\40\145\x78\143\145\x65\x64\x65\x64\x20\144\x65\x6c\141\x79\145\144\x20\x61\x6c\x65\x72\x74\40\145\x6d\141\151\154");
        sH:
        $Fn = get_site_option("\x6d\x6f\x5f\x69\x64\160\137\x61\144\x6d\151\156\x5f\x63\165\163\164\x6f\x6d\x65\x72\137\x6b\x65\x79");
        $o5 = get_site_option("\155\x6f\137\x69\144\x70\137\x61\144\x6d\x69\x6e\x5f\141\160\151\x5f\153\145\x79");
        $Jb = get_site_option("\x6d\157\137\151\x64\x70\137\141\x64\x6d\x69\156\137\x65\155\141\151\154");
        $Rp = "\110\145\154\x6c\157\54\74\142\162\76\74\x62\x72\76\131\157\x75\x20\x68\141\x76\145\40\x70\x75\162\143\150\141\163\x65\144\x20\154\151\143\x65\156\x73\145\x20\146\x6f\162\40\x57\157\x72\x64\120\x72\x65\163\x73\x20\123\101\115\x4c\40\62\56\60\x20\111\x44\x50\40\120\x6c\165\147\151\156\40\146\157\x72\x20\74\142\x3e" . $Tv . "\40\x75\163\145\162\163\x3c\57\142\76\56\x20\xd\xa\x9\x9\11\x9\11\x9\101\x73\40\156\x75\155\x62\x65\162\40\x6f\146\40\165\x73\145\162\x73\40\x6f\x6e\40\x79\x6f\165\162\x20\x73\151\x74\145\40\150\141\166\x65\x20\x67\x72\x6f\167\156\x20\x74\157\40\155\157\162\x65\x20\x74\x68\x61\x6e\40" . $Tv . "\40\165\163\145\162\x73\x20\x6e\x6f\x77\54\x20\171\x6f\165\x20\163\150\157\x75\x6c\144\40\165\x70\147\x72\141\x64\145\40\x79\157\x75\162\x20\165\163\145\162\x20\15\xa\x9\x9\11\11\x9\x9\x6c\x69\x63\x65\156\x73\145\x20\167\x69\x74\x68\x69\156\40\x33\x30\40\x64\141\x79\163\40\x66\x6f\x72\40\x61\40\x73\155\157\157\x74\x68\40\x53\x53\x4f\x20\x65\x78\160\x65\162\151\145\x6e\143\x65\x20\146\157\162\40\x79\x6f\x75\162\40\x75\163\x65\162\163\40\157\x6e\40\x79\x6f\x75\x72\x20\x73\x69\x74\145\x20\x3c\x62\x3e" . get_bloginfo() . "\x3c\57\142\76\56\74\142\x72\76\x3c\142\162\x3e\15\xa\11\x9\x9\11\11\11\x50\154\145\141\x73\145\40\x72\x65\141\x63\x68\x20\x6f\165\164\40\164\x6f\40\165\163\x20\x61\164\x20\x3c\x61\40\150\x72\x65\146\75\x27\155\x61\151\x6c\164\157\x3a\151\156\146\x6f\100\x78\145\143\x75\162\x69\146\171\x2e\143\157\x6d\x27\76\x69\x6e\146\x6f\x40\170\x65\x63\165\x72\151\146\171\56\x63\157\x6d\x3c\x2f\141\x3e\40\x6f\x72\x20\165\163\145\40\x74\150\x65\x20\123\165\x70\x70\157\162\164\x20\x46\x6f\162\155\40\x69\x6e\x20\x74\x68\x65\40\160\x6c\x75\x67\151\x6e\x20\164\x6f\x20\165\x70\147\162\141\x64\x65\x20\x74\x68\145\40\x6c\151\x63\145\x6e\163\145\x20\x74\157\40\x63\157\x6e\164\x69\156\165\x65\40\165\163\151\156\147\40\157\165\x72\x20\x70\154\x75\147\x69\x6e\56\15\12\x9\11\11\x9\11\x9\74\142\162\x3e\x3c\x62\162\x3e\124\150\141\156\153\x73\54\x3c\142\162\76\x6d\151\x6e\151\117\162\141\x6e\147\145";
        $x4 = "\105\170\x63\145\145\144\x65\x64\40\x4c\x69\143\145\156\x73\x65\40\114\x69\x6d\151\164\x20\x46\157\162\x20\116\x6f\x20\x4f\146\40\125\163\145\x72\163\40\55\40\x57\157\162\144\x50\162\145\163\x73\x20\x53\x41\115\x4c\40\x32\56\x30\40\111\x44\120\40\120\154\x75\x67\x69\156\x20\174\40" . get_bloginfo();
        update_site_option("\x75\x73\x65\162\x5f\x65\x78\x63\145\x65\x64\x65\x64\137\x64\145\x6c\141\171\x65\x64\137\141\x6c\145\x72\164\x5f\145\155\x61\151\x6c\x5f\x73\145\x6e\x74", 1);
        $n3 = MSI_LK_DEBUG ? time() + 1800 : time() + 2678400;
        if (!MSI_DEBUG) {
            goto K1;
        }
        MoIDPUtility::mo_debug("\123\145\x74\x74\151\x6e\x67\40\104\145\154\141\171\145\x64\40\124\x69\x6d\x65\x20\x3a\x20" . $n3 . "\x20\x43\x75\x72\162\145\x6e\164\x20\x54\x69\155\x65\x20\72\x20" . time());
        K1:
        update_site_option("\x64\145\x6c\141\171\x5f\165\163\x65\x72\x5f\162\x65\x73\164\162\151\143\164\151\157\x6e", $n3);
        MoIDPcURL::notify($Fn, $o5, $Jb, $Rp, $x4);
    }
    public static function sueae($Tv)
    {
        if (!MSI_DEBUG) {
            goto mi;
        }
        MoIDPUtility::mo_debug("\x53\x65\x6e\x64\151\x6e\x67\40\x75\x73\145\x72\x20\x65\170\143\x65\x65\x64\x65\144\40\141\154\145\162\164\40\x65\x6d\x61\x69\154");
        mi:
        $Fn = get_site_option("\155\157\137\x69\x64\160\x5f\x61\x64\155\151\x6e\137\x63\165\x73\164\157\x6d\x65\162\137\153\145\171");
        $o5 = get_site_option("\x6d\x6f\137\151\x64\160\x5f\x61\x64\155\151\156\x5f\x61\x70\151\137\x6b\x65\171");
        $Jb = get_site_option("\155\157\137\x69\144\x70\x5f\141\144\155\151\156\137\x65\155\x61\151\x6c");
        $Rp = "\x48\145\x6c\154\157\x2c\74\142\x72\x3e\74\142\x72\x3e\x59\157\165\40\x68\141\166\145\40\160\165\162\143\150\x61\163\x65\144\x20\154\x69\x63\145\x6e\163\145\x20\146\x6f\x72\x20\127\x6f\162\144\120\162\x65\x73\x73\x20\123\x41\115\x4c\x20\x32\56\x30\x20\111\104\x50\x20\x50\x6c\165\x67\151\x6e\40\146\x6f\x72\x20\x3c\142\x3e" . $Tv . "\40\x75\x73\x65\x72\163\x3c\57\142\76\56\x20\xd\12\x9\11\11\x9\11\x9\x41\163\x20\x6e\x75\x6d\x62\x65\x72\x20\157\146\40\165\x73\x65\162\163\x20\x6f\x6e\40\171\x6f\165\162\40\x73\151\x74\145\x20\150\141\166\145\x20\x67\162\157\167\x6e\x20\x74\157\40\x6d\x6f\x72\x65\x20\164\x68\141\156\x20" . $Tv . "\x20\165\163\145\162\x73\40\156\157\x77\40\141\156\x64\x20\x74\x68\145\x20\x33\x30\x20\144\141\171\x20\147\162\141\143\145\x20\x70\x65\162\x69\x6f\x64\x20\xd\xa\x9\11\x9\11\11\x9\x69\163\x20\x6f\166\145\162\x2c\40\156\x65\167\40\165\x73\x65\162\163\x20\167\151\x6c\x6c\40\x6e\157\164\40\x62\145\40\x61\142\x6c\145\x20\x74\x6f\x20\x75\x73\145\x20\123\123\x4f\40\143\x61\160\141\142\x69\x6c\151\x74\151\145\163\x20\x66\157\162\40\x79\157\x75\x72\x20\x73\x69\x74\x65\40\x3c\142\76" . get_bloginfo() . "\x3c\x2f\x62\x3e\56\74\x62\x72\76\x3c\142\x72\x3e\xd\xa\11\11\x9\x9\x9\11\120\154\145\x61\x73\x65\40\x72\x65\x61\x63\x68\x20\x6f\x75\164\x20\x74\157\x20\165\163\x20\x61\164\x20\74\x61\40\150\162\145\146\75\47\x6d\x61\151\x6c\164\157\x3a\151\x6e\x66\x6f\x40\x78\145\143\x75\x72\x69\146\x79\56\x63\157\x6d\47\76\151\x6e\x66\x6f\100\x78\x65\x63\165\162\x69\x66\x79\56\x63\x6f\x6d\74\57\141\76\40\157\x72\40\x75\x73\x65\40\x74\150\x65\40\123\165\x70\x70\x6f\162\164\x20\x46\157\x72\155\x20\x69\x6e\40\164\x68\145\40\160\x6c\165\x67\151\156\40\x74\x6f\x20\165\160\147\x72\x61\144\x65\40\164\150\145\x20\x6c\151\143\x65\156\163\x65\40\x74\157\40\143\157\156\164\151\x6e\165\145\x20\x75\x73\151\156\147\x20\157\x75\x72\40\160\x6c\165\x67\x69\156\x2e\xd\12\11\11\x9\x9\x9\x9\74\x62\x72\x3e\74\142\x72\76\x54\150\141\x6e\153\163\54\74\x62\162\76\x6d\x69\156\151\117\162\141\156\x67\145";
        $x4 = "\105\170\143\145\x65\x64\145\x64\40\x4c\x69\143\145\x6e\163\145\x20\114\x69\155\151\164\40\106\157\162\x20\116\x6f\x20\117\146\40\125\x73\145\x72\163\x20\55\40\x57\x6f\x72\144\x50\x72\x65\163\x73\x20\123\x41\115\114\40\62\56\60\40\x49\x44\120\x20\x50\154\165\x67\151\156\40\x7c\40" . get_bloginfo();
        update_site_option("\x75\163\x65\x72\x5f\145\x78\x63\x65\145\x64\x65\x64\137\x61\154\x65\162\164\137\x65\x6d\x61\151\x6c\x5f\x73\x65\156\x74", 1);
        MoIDPcURL::notify($Fn, $o5, $Jb, $Rp, $x4);
    }
    public static function slrae($Gq)
    {
        if (!MSI_DEBUG) {
            goto HH;
        }
        MoIDPUtility::mo_debug("\x53\x65\156\144\x69\x6e\x67\40\154\151\143\x65\156\163\145\x20\162\145\x6e\x65\x77\x20\141\x6c\x65\162\x74\x20\145\x6d\x61\151\x6c");
        HH:
        $Fn = get_site_option("\155\157\x5f\x69\x64\160\x5f\141\x64\x6d\x69\156\x5f\143\x75\x73\x74\157\x6d\x65\162\x5f\x6b\x65\x79");
        $o5 = get_site_option("\155\157\137\x69\144\160\137\141\x64\x6d\151\x6e\137\141\x70\151\x5f\153\145\171");
        $Jb = get_site_option("\x6d\157\x5f\x69\x64\x70\x5f\141\144\155\x69\x6e\x5f\145\155\x61\x69\x6c");
        $Rp = "\110\x65\x6c\x6c\157\x2c\x3c\142\162\76\74\x62\x72\76\x54\x68\151\x73\x20\145\155\141\x69\x6c\x20\x69\x73\40\x74\x6f\40\156\157\x74\x69\146\171\x20\171\157\165\40\164\x68\141\164\x20\171\157\165\162\40\x31\x20\171\x65\x61\162\40\154\151\x63\145\x6e\x73\x65\x20\146\157\x72\x20\x57\x6f\162\x64\x50\x72\x65\x73\x73\40\123\101\115\x4c\40\62\56\60\40\x49\x44\120\x20\x50\x6c\165\147\x69\x6e\40\x77\x69\x6c\154\40\145\x78\160\x69\x72\145\x20\15\12\11\x9\11\11\x9\x9\151\x6e\40" . $Gq . "\x20\x64\x61\x79\x73\56\x20\124\150\145\40\160\x6c\x75\147\x69\x6e\x20\167\x69\154\x6c\x20\163\x74\157\x70\40\x77\x6f\x72\x6b\151\x6e\147\40\141\x66\x74\145\x72\x20\x74\x68\x65\x20\154\x69\x63\145\156\x73\x65\x64\x20\160\145\162\151\157\144\x20\145\x78\160\x69\x72\x65\x73\x2e\x3c\x62\162\x3e\x3c\142\x72\76\15\xa\11\11\11\x9\11\11\x59\x6f\165\40\x77\151\x6c\154\x20\x6e\145\145\x64\40\164\157\x20\162\x65\156\x65\x77\x20\x79\x6f\165\162\x20\x6c\151\x63\x65\x6e\163\145\x20\x74\x6f\40\x63\x6f\156\164\151\156\x75\x65\x20\x75\x73\151\x6e\x67\40\x74\x68\145\x20\160\154\165\147\x69\x6e\40\157\156\40\171\x6f\165\x72\40\x77\x65\142\163\151\x74\x65\x20\x3c\x62\x3e" . get_bloginfo() . "\x3c\57\x62\x3e\56\xd\12\11\11\11\11\x9\x9\74\x62\162\x3e\x3c\142\162\x3e\103\157\x6e\164\x61\x63\x74\40\165\163\x20\x61\x74\x20\x3c\x61\40\x68\x72\145\x66\x3d\47\x6d\x61\x69\154\x74\x6f\72\x69\156\x66\157\100\170\x65\x63\165\162\x69\146\171\56\x63\157\155\47\x3e\x69\x6e\146\x6f\x40\170\x65\143\x75\162\x69\x66\x79\56\x63\x6f\x6d\x3c\57\141\x3e\x20\151\146\40\171\x6f\x75\40\x77\x69\163\x68\x20\x74\x6f\40\x72\x65\156\145\x77\x20\x79\157\x75\162\x20\x6c\x69\143\x65\156\x73\145\56\74\x62\x72\76\74\x62\162\x3e\x54\150\x61\156\153\x73\54\x3c\x62\162\76\x6d\x69\x6e\151\x4f\162\x61\x6e\x67\x65";
        $x4 = "\114\x69\143\x65\x6e\163\145\x20\105\x78\x70\x69\162\x79\x20\55\x20\x57\x6f\162\144\120\162\x65\x73\163\40\123\101\115\x4c\x20\62\56\60\x20\111\x44\x50\40\x50\154\165\147\x69\x6e\x20\174\x20" . get_bloginfo();
        MoIDPcURL::notify($Fn, $o5, $Jb, $Rp, $x4);
    }
    public static function slrfae()
    {
        if (!MSI_DEBUG) {
            goto I4;
        }
        MoIDPUtility::mo_debug("\123\145\156\144\x69\156\x67\40\x6c\x69\143\145\156\x73\145\40\162\x65\x6e\145\x77\40\146\x69\x6e\x61\x6c\40\x61\154\x65\162\x74\x20\x65\155\141\x69\154");
        I4:
        $Fn = get_site_option("\x6d\x6f\137\151\144\x70\137\x61\144\155\x69\x6e\137\143\x75\x73\x74\x6f\x6d\145\162\137\153\x65\171");
        $o5 = get_site_option("\155\x6f\137\x69\144\160\137\x61\x64\x6d\x69\156\137\x61\160\x69\x5f\153\145\171");
        $Jb = get_site_option("\155\x6f\137\x69\144\x70\137\141\x64\155\151\156\x5f\145\155\141\x69\x6c");
        $Rp = "\110\145\154\x6c\157\x2c\74\142\162\x3e\74\142\x72\76\x59\x6f\165\x72\x20\61\x20\x79\145\141\x72\x20\x6c\151\143\x65\156\163\x65\40\x66\x6f\x72\40\x57\157\x72\x64\x50\x72\x65\x73\163\40\123\101\115\x4c\40\x32\56\x30\x20\x49\x44\120\x20\120\x6c\x75\x67\151\x6e\40\x68\141\163\40\x65\x78\160\x69\x72\x65\144\x20\146\x6f\162\x20\171\157\x75\x72\x20\x77\x65\x62\163\x69\x74\x65\x20\x3c\x62\x3e" . get_bloginfo() . "\x3c\57\x62\x3e\56\40\xd\12\11\11\11\x9\11\x9\124\150\145\40\x70\x6c\x75\147\151\x6e\x20\167\x69\x6c\x6c\40\163\164\x6f\160\40\x77\157\162\153\x69\x6e\147\40\163\157\157\x6e\56\74\x62\162\76\74\142\162\x3e\131\157\165\40\x77\151\x6c\x6c\x20\x6e\x65\x65\x64\x20\164\157\x20\162\x65\156\x65\167\x20\x79\157\165\162\x20\x6c\x69\x63\x65\156\163\145\x20\x74\157\40\143\157\156\164\x69\x6e\165\145\40\165\x73\151\x6e\147\x20\x74\150\x65\x20\x70\x6c\x75\147\x69\156\56\15\xa\x9\x9\x9\11\x9\11\103\157\156\164\141\143\164\40\165\x73\x20\x61\x74\40\74\141\x20\x68\x72\x65\x66\75\47\x6d\x61\151\x6c\x74\157\72\x69\x6e\146\x6f\100\170\145\x63\165\162\151\x66\x79\x2e\143\x6f\x6d\47\76\151\x6e\x66\x6f\100\170\x65\143\165\x72\151\x66\171\56\143\157\155\74\x2f\141\76\x20\x69\146\x20\171\x6f\165\40\x77\151\x73\150\x20\164\157\x20\x72\145\156\x65\x77\x20\171\x6f\165\x72\40\x6c\x69\x63\145\x6e\163\x65\x2e\74\142\162\x3e\74\142\x72\x3e\x54\150\x61\x6e\x6b\x73\x2c\x3c\142\x72\76\x6d\151\x6e\x69\x4f\162\141\156\147\x65";
        $x4 = "\x4c\151\143\145\x6e\x73\145\40\105\x78\x70\x69\162\145\144\x20\55\40\x57\x6f\x72\144\120\162\x65\163\163\40\123\101\115\x4c\40\62\x2e\x30\40\111\104\120\40\x50\154\165\147\x69\156\x20\174\x20" . get_bloginfo();
        MoIDPcURL::notify($Fn, $o5, $Jb, $Rp, $x4);
    }
    public static function spdae()
    {
        if (!MSI_DEBUG) {
            goto Ux;
        }
        MoIDPUtility::mo_debug("\123\145\x6e\x64\x69\x6e\147\x20\160\154\165\147\x69\156\x20\144\x65\x61\143\164\x69\166\141\x74\x65\144\40\145\x6d\x61\151\x6c");
        Ux:
        $Fn = get_site_option("\155\x6f\x5f\x69\144\160\137\141\144\x6d\151\156\x5f\x63\x75\x73\164\x6f\x6d\x65\x72\x5f\x6b\145\171");
        $o5 = get_site_option("\155\157\x5f\151\x64\x70\137\x61\144\155\x69\x6e\x5f\x61\x70\151\137\x6b\x65\x79");
        $Jb = get_site_option("\x6d\157\137\x69\x64\160\137\x61\144\155\x69\x6e\137\145\155\141\151\x6c");
        $Rp = "\110\145\154\154\157\x2c\x3c\142\x72\x3e\74\142\162\76\x59\x6f\165\162\40\x31\40\171\x65\141\x72\x20\154\x69\143\x65\x6e\163\145\40\146\157\x72\x20\x57\x6f\162\144\120\x72\x65\163\163\40\x53\x41\x4d\114\40\62\x2e\x30\x20\x49\104\x50\40\x50\154\x75\x67\x69\x6e\x20\x68\x61\x73\x20\x65\170\x70\x69\x72\x65\144\x2e\40\131\x6f\x75\x20\x68\141\x76\x65\40\x6e\x6f\x74\40\162\145\156\x65\x77\x65\x64\x20\x79\157\x75\162\x20\x6c\151\x63\145\x6e\x73\145\40\167\151\x74\x68\151\156\40\xd\xa\11\x9\x9\11\x9\x9\164\150\x65\40\61\65\x20\144\x61\x79\x73\40\x67\x72\141\143\x65\40\x70\x65\x72\151\157\144\x20\x67\151\166\x65\156\x20\164\157\x20\x79\157\165\56\x20\124\x68\x65\x20\x53\123\x4f\40\150\141\163\40\142\145\x65\156\x20\144\x69\163\141\142\154\145\x64\x20\157\x6e\x20\x79\x6f\x75\162\x20\x77\x65\142\163\x69\x74\x65\40\x3c\x62\x3e" . get_bloginfo() . "\x3c\x2f\142\76\x2e\x3c\142\162\76\x3c\x62\162\x3e\xd\xa\11\11\x9\11\11\x9\103\157\x6e\x74\141\143\164\x20\x75\163\40\141\164\x20\74\141\40\150\162\145\146\75\x27\x6d\x61\151\154\164\157\x3a\151\156\x66\157\x40\170\x65\143\165\x72\151\146\171\56\143\x6f\155\47\x3e\151\x6e\x66\157\x40\x78\145\x63\165\x72\151\x66\171\56\x63\x6f\x6d\74\x2f\141\x3e\40\x69\x66\40\x79\x6f\165\x20\x77\x69\x73\150\x20\x74\157\40\162\145\x6e\x65\x77\40\x79\x6f\165\x72\x20\x6c\151\x63\x65\x6e\163\145\x2e\x3c\142\x72\76\74\x62\162\x3e\124\x68\141\x6e\153\x73\x2c\x3c\x62\162\76\155\x69\156\151\x4f\162\x61\156\x67\145";
        $x4 = "\x4c\151\x63\x65\x6e\163\145\x20\105\170\x70\x69\x72\x65\x64\40\55\x20\x57\x6f\162\x64\120\x72\x65\x73\163\40\x53\101\x4d\x4c\x20\x32\x2e\60\40\111\104\x50\x20\120\x6c\165\147\151\x6e\x20\174\x20" . get_bloginfo();
        MoIDPcURL::notify($Fn, $o5, $Jb, $Rp, $x4);
    }
    public static function suwae($Tv, $Hj)
    {
        if (!MSI_DEBUG) {
            goto KG;
        }
        MoIDPUtility::mo_debug("\x53\x65\x6e\x64\151\x6e\x67\40\x75\163\145\162\x20\167\141\162\x6e\x69\x6e\147\x20\141\x6c\x65\162\164\40\x65\x6d\x61\x69\154");
        KG:
        $Fn = get_site_option("\x6d\157\137\x69\144\160\137\141\x64\155\151\x6e\x5f\143\165\x73\164\157\x6d\145\162\137\153\145\x79");
        $o5 = get_site_option("\x6d\x6f\137\x69\144\160\x5f\141\144\155\151\x6e\137\x61\x70\x69\x5f\153\x65\171");
        $Jb = get_site_option("\x6d\x6f\x5f\x69\144\x70\x5f\141\x64\155\151\x6e\137\145\155\141\151\x6c");
        $Rp = "\110\145\x6c\154\x6f\54\74\x62\162\x3e\x3c\x62\162\76\131\x6f\165\40\x68\141\166\145\40\160\165\162\x63\x68\141\x73\x65\144\x20\154\x69\143\145\156\163\145\40\x66\x6f\x72\x20\127\157\x72\x64\x50\162\x65\163\x73\40\x53\101\115\114\x20\62\56\60\40\x49\104\120\x20\x50\154\x75\x67\x69\x6e\x20\x66\157\x72\40\74\142\76" . $Tv . "\40\165\x73\x65\x72\163\x3c\x2f\x62\76\x2e\40\15\12\11\11\11\11\11\11\124\x68\x65\40\156\x75\155\x62\x65\x72\40\157\x66\40\165\163\x65\162\163\x20\157\x6e\40\171\x6f\x75\x72\40\x73\151\x74\145\40\150\141\166\x65\x20\x67\162\157\167\x6e\x20\x74\157\40\x6d\157\162\145\40\164\150\141\156\40\x38\60\x25\40\x28\x3c\142\x3e" . $Hj . "\x20\165\163\x65\162\163\74\57\142\76\x29\40\x6f\x66\40\x74\x6f\164\141\x6c\40\165\x73\x65\x72\x73\x20\156\157\167\56\40\15\12\11\11\x9\x9\11\x9\x59\x6f\165\x20\163\x68\157\x75\x6c\144\40\x75\x70\x67\x72\x61\144\x65\x20\171\x6f\x75\x72\40\154\151\x63\145\x6e\x73\x65\x20\146\157\x72\x20\155\x69\156\151\117\x72\x61\156\x67\x65\x20\127\x6f\162\x64\x50\x72\145\x73\163\x20\123\x41\115\114\40\x32\56\60\x20\x49\x44\120\x20\160\154\165\x67\151\156\x20\157\x6e\x20\x79\x6f\x75\162\40\x77\145\x62\163\151\x74\145\40\74\x62\76" . get_bloginfo() . "\74\x2f\x62\x3e\56\74\142\x72\76\74\142\x72\76\xd\xa\x9\x9\11\11\11\11\x50\x6c\x65\141\x73\x65\40\x72\x65\141\143\x68\x20\x6f\x75\164\x20\164\x6f\x20\165\x73\x20\x61\x74\x20\x3c\x61\x20\150\162\x65\x66\x3d\47\x6d\x61\151\154\x74\157\72\151\156\146\157\x40\170\x65\143\x75\x72\151\146\x79\56\143\157\x6d\47\76\151\x6e\x66\157\x40\x78\x65\x63\165\x72\151\146\x79\x2e\x63\x6f\155\74\x2f\x61\x3e\40\x6f\162\40\x75\163\145\x20\164\150\x65\40\123\x75\x70\x70\x6f\x72\x74\x20\x46\157\x72\x6d\40\x69\156\40\164\150\x65\40\160\154\165\147\151\x6e\x20\x74\x6f\x20\x75\x70\x67\x72\141\144\145\40\164\150\x65\x20\x6c\x69\x63\x65\x6e\163\x65\x20\164\157\40\x63\157\156\164\x69\x6e\x75\x65\x20\165\163\151\156\x67\x20\x6f\165\x72\40\160\x6c\165\147\151\x6e\56\xd\xa\11\x9\11\x9\11\x9\74\x62\x72\x3e\74\142\x72\76\124\x68\x61\x6e\x6b\163\x2c\74\x62\x72\76\155\x69\x6e\151\117\x72\x61\x6e\147\145";
        $x4 = "\x52\145\141\x63\150\145\x64\x20\x38\x30\45\x20\114\x69\143\145\156\163\145\x20\114\x69\155\151\x74\40\x46\x6f\x72\40\x4e\157\40\x4f\x66\40\x55\x73\145\162\x73\40\55\40\x57\x6f\162\144\x50\162\145\x73\163\40\123\x41\115\x4c\40\x32\x2e\60\40\x49\x44\x50\x20\x50\154\x75\147\x69\x6e\40\174\40" . get_bloginfo();
        update_site_option("\165\x73\x65\x72\137\165\x73\x65\162\137\x77\141\162\156\x69\156\x67\x5f\141\x6c\145\x72\x74\x5f\x65\x6d\141\x69\x6c\x5f\x73\x65\156\164", 1);
        MoIDPcURL::notify($Fn, $o5, $Jb, $Rp, $x4);
    }
    public static function cutol($user)
    {
        global $dbIDPQueries;
        if (!get_user_meta($user->ID, "\x6d\157\137\x69\x64\160\137\165\x73\145\162\x5f\x74\x79\x70\145", true)) {
            goto Ys;
        }
        if (!MSI_DEBUG) {
            goto v2;
        }
        MoIDPUtility::mo_debug("\x52\x65\160\x65\141\x74\x20\125\163\x65\x72");
        v2:
        update_user_meta($user->ID, "\x6c\141\163\164\x5f\154\x6f\147\147\145\x64\x5f\151\x6e", date("\x6d\55\171"));
        return;
        Ys:
        if (!MoIDPUtility::isBlank(get_site_option("\x6d\157\x5f\x69\144\160\x5f\165\163\x72\x5f\x6c\155\164"))) {
            goto N_;
        }
        throw new InvalidOperationException();
        N_:
        if (!MSI_DEBUG) {
            goto is;
        }
        MoIDPUtility::mo_debug("\116\145\x77\40\x53\x53\117\40\x55\163\x65\x72");
        is:
        $xO = get_site_option("\155\x6f\x5f\151\x64\x70\x5f\143\165\x73\x74\x6f\x6d\x65\x72\x5f\x74\157\x6b\x65\156");
        $yM = \AESEncryption::decrypt_data(get_site_option("\x6d\157\x5f\x69\x64\160\x5f\x75\x73\162\x5f\154\x6d\x74"), $xO);
        $fX = $dbIDPQueries->get_users();
        $zl = get_site_option("\x75\163\145\162\137\145\170\x63\145\145\x64\145\x64\x5f\x61\154\145\x72\164\137\145\x6d\141\151\x6c\137\x73\145\x6e\164");
        $AC = get_site_option("\165\x73\145\x72\x5f\145\x78\143\x65\x65\x64\x65\x64\x5f\x64\x65\154\x61\x79\145\144\x5f\141\154\145\x72\x74\x5f\x65\x6d\x61\x69\x6c\x5f\x73\145\156\x74");
        $KO = get_site_option("\144\x65\x6c\x61\x79\137\x75\x73\145\162\137\x72\145\163\x74\x72\151\x63\x74\151\x6f\156");
        if (!MSI_DEBUG) {
            goto gU;
        }
        MoIDPUtility::mo_debug("\x55\x73\145\162\72\x20" . $fX . "\40\x41\x6c\x6c\x6f\x77\x65\x64\x3a\40" . $yM . "\x20\x44\145\x6c\141\171\x65\x64\x20\105\155\x61\x69\154\x20\123\145\156\x74\x3a\x20" . $AC . "\x20\105\x78\x63\145\145\144\145\x64\x20\105\x6d\x61\151\x6c\40\123\x65\156\x74\x3a" . $zl);
        gU:
        if (!MSI_DEBUG) {
            goto PF;
        }
        MoIDPUtility::mo_debug("\x44\x65\x6c\141\171\145\x64\x20\124\151\x6c\x6c\72" . $KO . "\40\103\165\162\162\x65\156\164\x20\x54\x69\x6d\145\72" . time());
        PF:
        if (!(!MoIDPUtility::isBlank($KO) && $KO >= time())) {
            goto Ow;
        }
        return;
        Ow:
        if ($fX > $yM - 1 && !self::isValidNewSSOUser($yM)) {
            goto Mq;
        }
        $AC = get_site_option("\x75\163\x65\x72\137\x75\x73\145\x72\x5f\167\x61\x72\156\x69\x6e\147\x5f\141\x6c\x65\162\164\x5f\145\155\x61\151\154\137\163\145\156\164");
        $KO = get_site_option("\x64\x65\x6c\141\x79\x5f\165\x73\145\x72\x5f\x72\x65\x73\x74\162\151\x63\164\x69\x6f\156");
        if (!($AC != 1)) {
            goto RF;
        }
        $ys = false;
        $vr = $fX + 1;
        if (!MSI_DEBUG) {
            goto aZ;
        }
        MoIDPUtility::mo_debug("\125\163\x65\x72\72\x20" . $vr . "\x20\x41\154\x6c\157\x77\x65\144\x3a\x20" . $yM . "\x20\127\x61\x72\156\151\156\147\x20\105\155\141\151\x6c\x20\123\x65\x6e\x74\72\x20" . $AC);
        aZ:
        if (!($yM < 5 && $vr == $yM - 1)) {
            goto kz;
        }
        $ys = true;
        kz:
        $bv = $vr * 100 / $yM;
        if (!($bv >= 80 || $ys)) {
            goto hg;
        }
        self::suwae($yM, $vr);
        hg:
        RF:
        if (!self::isBlank($KO)) {
            goto nM;
        }
        update_user_meta($user->ID, "\x6d\157\x5f\x69\144\x70\137\x75\163\x65\x72\x5f\164\x79\160\145", "\x73\x73\x6f\137\165\163\145\x72");
        update_user_meta($user->ID, "\x6c\141\163\x74\137\x6c\157\x67\x67\x65\144\137\x69\x6e", date("\155\55\x79"));
        nM:
        goto MK;
        Mq:
        if (!($AC != 1)) {
            goto Rs;
        }
        self::suedae($yM);
        return;
        Rs:
        if (!($AC == 1 && $zl != 1)) {
            goto pX;
        }
        self::sueae($yM);
        pX:
        throw new InvalidSSOUserException();
        MK:
    }
    public static function isValidNewSSOUser($yM)
    {
        if (!MSI_DEBUG) {
            goto u_;
        }
        MoIDPUtility::mo_debug("\103\x68\x65\x63\153\x69\x6e\x67\x20\x69\x66\40\x63\165\x73\164\x6f\x6d\x65\x72\x20\150\141\163\40\165\160\147\162\x61\144\145\x64\40\x68\x69\163\x20\x6c\151\x63\145\156\x73\x65");
        u_:
        $xO = get_site_option("\155\157\137\151\x64\x70\x5f\x63\x75\163\x74\157\155\x65\x72\x5f\164\x6f\x6b\145\156");
        $T7 = json_decode(MoIDPUtility::ccl(), true);
        $e5 = array_key_exists("\x6e\157\x4f\146\x55\163\x65\x72\163", $T7) ? $T7["\x6e\157\117\146\x55\x73\145\x72\x73"] : null;
        $HP = array_key_exists("\154\x69\143\x65\156\163\x65\105\x78\x70\151\162\x79", $T7) ? strtotime($T7["\154\151\143\x65\156\163\145\x45\x78\x70\x69\162\x79"]) === false ? null : strtotime($T7["\154\151\143\145\x6e\x73\x65\105\170\160\x69\162\171"]) : null;
        if (!(!MoIDPUtility::isBlank($e5) && $yM < $e5)) {
            goto On;
        }
        if (!MSI_DEBUG) {
            goto Z8;
        }
        MoIDPUtility::mo_debug("\125\x70\144\141\x74\151\156\x67\40\165\x73\145\x72\x20\154\151\143\145\x6e\163\x65");
        Z8:
        update_site_option("\155\157\137\x69\144\160\137\x75\x73\x72\137\x6c\155\x74", \AESEncryption::encrypt_data($e5, $xO));
        delete_site_option("\165\163\145\162\137\145\x78\143\145\145\x64\145\x64\x5f\x61\x6c\145\162\x74\x5f\145\x6d\x61\x69\154\137\163\145\x6e\x74");
        delete_site_option("\165\163\145\162\x5f\145\170\x63\145\x65\144\x65\x64\x5f\x64\145\x6c\141\x79\145\144\x5f\x61\154\x65\x72\x74\137\x65\x6d\141\151\154\137\x73\x65\156\164");
        delete_site_option("\144\145\154\141\171\x5f\x75\163\x65\162\x5f\x72\145\x73\x74\162\x69\x63\164\x69\157\156");
        return TRUE;
        On:
        return FALSE;
    }
    public static function sanitizeAssociativeArray($dN)
    {
        $Nx = array();
        foreach ($dN as $xO => $l7) {
            $xO = htmlspecialchars($xO);
            $l7 = htmlspecialchars($l7);
            $Nx[$xO] = $l7;
            hJ:
        }
        IN:
        return $Nx;
    }
}
