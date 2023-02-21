<?php


namespace IDP\Handler;

use IDP\Helper\Traits\Instance;
use IDP\Helper\Constants\MoIDPMessages;
use IDP\Helper\Utilities\MoIDPUtility;
class RoleBasedSSOHandler extends BaseHandler
{
    use Instance;
    public function handle_role_based_sso($y8)
    {
        $this->checkIfValidPlugin();
        $YO = isset($y8["\141\x6c\154\x6f\167\145\144\x5f\162\x6f\x6c\x65\x73"]) ? explode("\54", $y8["\141\154\x6c\157\x77\x65\144\137\x72\157\x6c\x65\x73"]) : array();
        $Q9 = isset($y8["\155\x6f\x5f\x69\144\160\137\x72\157\x6c\145\137\162\x65\163\x74\x72\x69\x63\164\x69\x6f\156"]) ? TRUE : FALSE;
        foreach ($YO as $bG) {
            $oq[$bG] = true;
            P8:
        }
        pj:
        $oq = MoIDPUtility::sanitizeAssociativeArray($oq);
        update_site_option("\155\x6f\137\151\x64\160\137\163\x73\x6f\137\141\154\154\x6f\x77\x65\144\x5f\x72\157\154\x65\163", $oq);
        update_site_option("\x6d\157\x5f\x69\x64\160\x5f\x72\x6f\154\145\x5f\142\x61\163\x65\x64\x5f\162\x65\163\x74\x72\x69\x63\x74\151\x6f\156", $Q9);
        do_action("\155\x6f\137\x69\x64\160\x5f\x73\150\x6f\x77\x5f\x6d\145\163\x73\141\147\x65", MoIDPMessages::showMessage("\123\105\x54\124\111\x4e\x47\123\x5f\123\x41\126\x45\104"), "\123\x55\103\x43\105\123\x53");
    }
}
