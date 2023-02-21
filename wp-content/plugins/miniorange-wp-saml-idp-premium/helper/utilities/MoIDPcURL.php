<?php


namespace IDP\Helper\Utilities;

use IDP\Helper\Constants\MoIDPConstants;
use IDP\Helper\Utilities\MoIDPUtility;
class MoIDPcURL
{
    public static function create_customer($QV, $ze, $xq, $mA = '', $sl = '', $T2 = '')
    {
        $L3 = MoIDPConstants::HOSTNAME . "\x2f\x6d\157\x61\163\57\162\x65\163\x74\57\x63\x75\163\x74\x6f\x6d\145\162\x2f\x61\144\x64";
        $Fn = MoIDPConstants::DEFAULT_CUSTOMER_KEY;
        $o5 = MoIDPConstants::DEFAULT_API_KEY;
        $yz = array("\x63\x6f\155\160\x61\x6e\x79\116\x61\155\145" => $ze, "\141\x72\x65\x61\x4f\146\x49\156\164\145\x72\145\x73\164" => MoIDPConstants::AREA_OF_INTEREST, "\x66\x69\162\x73\164\x6e\141\x6d\145" => $sl, "\x6c\141\x73\x74\156\141\x6d\x65" => $T2, "\145\x6d\x61\151\154" => $QV, "\x70\150\x6f\x6e\145" => $mA, "\x70\141\163\x73\167\157\162\144" => $xq);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function get_customer_key($QV, $xq)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\57\x6d\157\141\x73\x2f\162\x65\x73\x74\57\x63\x75\163\x74\x6f\x6d\x65\162\x2f\153\145\171";
        $Fn = MoIDPConstants::DEFAULT_CUSTOMER_KEY;
        $o5 = MoIDPConstants::DEFAULT_API_KEY;
        $yz = array("\145\155\x61\151\x6c" => $QV, "\x70\x61\163\163\167\x6f\x72\x64" => $xq);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function submit_contact_us($fy, $xx, $lB)
    {
        $current_user = wp_get_current_user();
        $L3 = MoIDPConstants::HOSTNAME . "\57\x6d\157\x61\x73\57\162\x65\163\164\57\143\165\163\164\157\155\145\162\x2f\x63\x6f\x6e\164\x61\x63\x74\55\x75\x73";
        $lB = "\x5b\x57\x50\x20\111\x44\x50\x20\x50\154\x75\x67\151\x6e\135\72\x20" . $lB;
        $Fn = !MoIDPUtility::isBlank(get_option("\155\x6f\x5f\143\x75\163\164\x6f\x6d\145\162\137\166\141\154\x69\144\141\x74\151\x6f\156\x5f\x61\144\155\151\156\137\143\165\x73\164\157\155\x65\162\x5f\x6b\145\x79")) ? get_option("\155\157\137\x63\165\163\x74\x6f\x6d\x65\x72\137\166\x61\154\151\x64\141\x74\x69\157\156\137\x61\x64\x6d\151\x6e\137\x63\165\163\164\x6f\x6d\x65\162\x5f\153\145\x79") : MoIDPConstants::DEFAULT_CUSTOMER_KEY;
        $o5 = !MoIDPUtility::isBlank(get_option("\155\157\x5f\143\165\163\164\157\155\145\162\137\166\141\x6c\151\x64\x61\x74\151\x6f\156\x5f\x61\x64\x6d\151\156\137\141\x70\151\x5f\x6b\x65\171")) ? get_option("\155\x6f\x5f\143\x75\163\164\157\155\145\x72\x5f\166\141\x6c\x69\x64\141\x74\x69\x6f\x6e\x5f\141\144\x6d\151\156\137\141\160\151\x5f\153\145\171") : MoIDPConstants::DEFAULT_API_KEY;
        $yz = array("\146\x69\x72\163\x74\x4e\x61\x6d\145" => $current_user->user_firstname, "\154\141\x73\x74\x4e\141\155\x65" => $current_user->user_lastname, "\x63\157\155\x70\141\x6e\171" => $_SERVER["\123\105\122\x56\105\x52\x5f\116\x41\115\x45"], "\143\143\105\x6d\141\151\x6c" => MoIDPConstants::FEEDBACK_EMAIL, "\x65\x6d\141\x69\154" => $fy, "\x70\150\157\156\145" => $xx, "\x71\165\145\162\171" => $lB);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return true;
    }
    public static function mius($Fn, $o5, $Oo)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\57\155\x6f\x61\163\57\x61\160\x69\57\x62\141\143\x6b\x75\160\x63\x6f\144\145\57\x75\x70\x64\x61\164\145\x73\164\x61\x74\x75\x73";
        $yz = array("\x63\157\x64\145" => $Oo, "\143\165\163\x74\157\x6d\145\x72\113\145\171" => $Fn);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function notify($Fn, $o5, $Jb, $Rp, $x4)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\57\x6d\x6f\141\x73\x2f\141\160\x69\x2f\x6e\x6f\x74\151\x66\171\x2f\163\x65\156\x64";
        $yz = array("\x63\165\163\x74\x6f\x6d\145\162\x4b\145\171" => $Fn, "\163\145\x6e\x64\105\155\141\x69\154" => true, "\x65\x6d\x61\x69\154" => array("\143\165\163\164\x6f\x6d\x65\x72\113\145\x79" => $Fn, "\146\x72\157\155\x45\x6d\x61\x69\x6c" => "\151\x6e\146\157\x40\x78\x65\143\165\x72\151\146\x79\x2e\x63\157\x6d", "\x62\x63\143\x45\x6d\141\151\154" => "\163\x61\155\154\x73\x75\x70\160\157\162\164\x40\x78\145\143\165\x72\x69\146\171\56\x63\157\155", "\146\162\x6f\155\x4e\141\x6d\145" => "\x6d\151\156\151\x4f\x72\x61\x6e\x67\145", "\x74\x6f\105\x6d\141\151\x6c" => $Jb, "\164\x6f\116\x61\x6d\145" => $Jb, "\x73\165\x62\152\x65\143\x74" => $x4, "\143\157\156\x74\x65\x6e\x74" => $Rp));
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
    }
    public static function ccl($Fn, $o5)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\x2f\x6d\157\x61\x73\x2f\162\x65\163\164\57\143\165\x73\164\157\x6d\x65\x72\57\154\151\x63\x65\x6e\x73\x65";
        $yz = array("\143\x75\x73\164\157\155\x65\162\111\x64" => $Fn, "\141\x70\x70\154\151\x63\141\x74\151\x6f\x6e\x4e\x61\155\x65" => "\x77\x70\x5f\x73\x61\x6d\154\137\151\144\x70");
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function vml($Fn, $o5, $Oo, $Pz, $yW = false)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\x2f\155\x6f\141\x73\57\141\160\151\57\x62\x61\143\153\x75\160\143\157\144\x65\x2f\166\145\162\151\x66\x79";
        if (!$yW) {
            goto vk;
        }
        $L3 = MoIDPConstants::HOSTNAME . "\57\x6d\x6f\141\163\57\141\x70\x69\57\142\141\143\153\x75\160\x63\157\x64\x65\57\x63\x68\x65\143\153";
        vk:
        $yz = array("\x63\157\x64\x65" => $Oo, "\x63\x75\163\x74\157\155\x65\x72\113\x65\x79" => $Fn, "\x61\144\x64\x69\x74\151\x6f\156\141\154\x46\151\x65\x6c\x64\163" => array("\x66\151\x65\x6c\x64\x31" => $Pz));
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function send_otp_token($yn, $mA, $QV)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\57\155\x6f\x61\163\57\141\160\151\57\141\x75\164\150\x2f\x63\x68\141\154\x6c\x65\x6e\x67\x65";
        $Fn = MoIDPConstants::DEFAULT_CUSTOMER_KEY;
        $o5 = MoIDPConstants::DEFAULT_API_KEY;
        $yz = array("\x63\165\163\164\157\x6d\145\162\x4b\145\171" => $Fn, "\145\155\x61\x69\154" => $QV, "\160\x68\x6f\156\145" => $mA, "\x61\x75\164\x68\x54\171\160\x65" => $yn, "\x74\x72\x61\156\163\x61\x63\164\x69\x6f\156\116\141\x6d\145" => MoIDPConstants::AREA_OF_INTEREST);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function validate_otp_token($sm, $Cp)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\57\155\157\141\x73\x2f\141\160\151\57\141\x75\164\150\x2f\x76\141\154\151\x64\x61\x74\x65";
        $Fn = MoIDPConstants::DEFAULT_CUSTOMER_KEY;
        $o5 = MoIDPConstants::DEFAULT_API_KEY;
        $yz = array("\164\170\111\144" => $sm, "\164\x6f\x6b\145\x6e" => $Cp);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function check_customer($QV)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\x2f\155\157\x61\x73\57\x72\x65\x73\164\57\143\x75\x73\x74\157\x6d\145\x72\x2f\143\x68\x65\143\x6b\55\x69\x66\x2d\x65\170\x69\163\x74\163";
        $Fn = MoIDPConstants::DEFAULT_CUSTOMER_KEY;
        $o5 = MoIDPConstants::DEFAULT_API_KEY;
        $yz = array("\x65\x6d\x61\151\154" => $QV);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    public static function forgot_password($QV, $Fn, $o5)
    {
        $L3 = MoIDPConstants::HOSTNAME . "\x2f\155\x6f\x61\163\x2f\x72\145\x73\x74\x2f\143\x75\x73\x74\x6f\155\x65\162\x2f\160\x61\x73\163\x77\157\162\144\55\162\x65\163\145\164";
        $yz = array("\145\x6d\x61\x69\x6c" => $QV);
        $PD = json_encode($yz);
        $XA = self::createAuthHeader($Fn, $o5);
        $wG = self::callAPI($L3, $PD, $XA);
        return $wG;
    }
    private static function createAuthHeader($Fn, $o5)
    {
        $IU = round(microtime(true) * 1000);
        $IU = number_format($IU, 0, '', '');
        $kB = $Fn . $IU . $o5;
        $XA = hash("\x73\150\x61\65\61\62", $kB);
        $pg = ["\103\157\156\x74\x65\x6e\x74\x2d\x54\x79\x70\145" => "\x61\160\160\154\151\143\141\x74\x69\x6f\156\57\x6a\x73\157\156", "\x43\165\163\x74\x6f\x6d\145\162\55\x4b\x65\x79" => "{$Fn}", "\x54\x69\155\x65\x73\x74\141\x6d\x70" => "{$IU}", "\101\x75\164\150\157\x72\151\172\141\164\151\x6f\156" => "{$XA}"];
        return $pg;
    }
    private static function callAPI($L3, $Rk, $RT = array("\103\x6f\x6e\164\145\x6e\x74\55\124\x79\160\x65" => "\x61\x70\160\x6c\151\143\x61\x74\x69\x6f\156\x2f\152\x73\x6f\x6e"))
    {
        $VW = ["\155\x65\164\x68\157\x64" => "\x50\x4f\123\124", "\142\x6f\144\x79" => $Rk, "\x74\x69\x6d\145\x6f\165\x74" => "\x31\x30\60\x30\60", "\162\145\x64\151\x72\145\x63\164\x69\157\156" => "\61\60", "\x68\164\x74\160\x76\145\162\x73\x69\x6f\x6e" => "\61\56\60", "\142\x6c\157\143\x6b\x69\156\x67" => true, "\x68\x65\x61\144\145\x72\163" => $RT, "\163\163\x6c\166\x65\162\x69\x66\171" => MSI_TEST ? false : true];
        $wG = wp_remote_post($L3, $VW);
        if (!is_wp_error($wG)) {
            goto i4;
        }
        wp_die("\123\157\155\x65\164\x68\151\x6e\x67\40\167\145\156\164\40\167\x72\157\156\x67\72\40\x3c\142\162\57\x3e\40{$wG->get_error_message()}");
        i4:
        return wp_remote_retrieve_body($wG);
    }
}
