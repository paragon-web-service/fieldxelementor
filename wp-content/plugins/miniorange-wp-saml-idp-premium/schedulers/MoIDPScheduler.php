<?php


namespace IDP\Schedulers;

use IDP\Helper\Utilities\MoIDPUtility;
class MoIDPScheduler extends BaseScheduler
{
    function __construct()
    {
        add_filter("\x63\162\x6f\x6e\x5f\163\143\x68\x65\144\x75\154\x65\x73", array($this, "\143\165\x73\164\x6f\x6d\x5f\x63\162\157\156\137\x73\143\150\x65\144\165\154\x65"));
        foreach ($this->eventActionPair as $xO => $l7) {
            add_action($xO, $l7);
            cs:
        }
        TT:
    }
    public function custom_cron_schedule($u5)
    {
        $u5["\x65\166\x65\162\171\137\x31\65\x5f\144\141\171\x73"] = array("\151\x6e\164\145\162\x76\x61\154" => 1296000, "\x64\151\x73\160\x6c\x61\x79" => __("\117\x6e\143\x65\40\145\166\x65\162\x79\40\x31\x35\x20\x64\x61\171\x73"));
        $u5["\145\x76\x65\162\171\x5f\x31\60\x5f\x64\x61\171\x73"] = array("\151\156\x74\x65\162\x76\141\154" => 864000, "\144\151\163\160\x6c\x61\171" => __("\x4f\x6e\x63\x65\x20\145\x76\145\x72\x79\40\61\x30\40\x64\x61\x79\163"));
        $u5["\167\145\145\153\154\171"] = array("\x69\156\164\145\x72\x76\x61\x6c" => 604800, "\x64\151\163\x70\154\141\171" => __("\117\x6e\x63\x65\x20\x57\145\x65\153\154\171"));
        $u5["\155\157\156\164\150\154\171"] = array("\151\156\x74\x65\162\x76\x61\154" => 2635200, "\x64\x69\163\x70\x6c\x61\171" => __("\x4f\156\x63\x65\x20\x4d\157\156\x74\x68\154\x79"));
        $u5["\155\x69\x6e\165\x74\145\154\x79"] = array("\151\156\x74\x65\x72\x76\x61\x6c" => 60, "\144\151\163\141\x70\x6c\x79" => __("\x4f\156\x63\145\40\x65\166\145\162\x79\40\155\x69\x6e\165\164\x65"));
        $u5["\x65\166\145\x72\171\137\63\x5f\155\151\x6e\165\x74\x65\x73"] = array("\x69\x6e\x74\x65\162\166\141\154" => 180, "\144\x69\163\141\x70\x6c\x79" => __("\x4f\156\x63\145\x20\145\x76\x65\x72\x79\40\63\40\x6d\x69\x6e\165\x74\145"));
        $u5["\145\166\x65\162\171\x5f\x35\x5f\x6d\x69\x6e\165\164\x65\x73"] = array("\x69\156\x74\x65\x72\x76\141\x6c" => 300, "\x64\x69\x73\141\x70\154\171" => __("\117\156\143\x65\40\x65\x76\145\162\171\40\x35\40\x6d\151\x6e\x75\164\x65"));
        return $u5;
    }
}
