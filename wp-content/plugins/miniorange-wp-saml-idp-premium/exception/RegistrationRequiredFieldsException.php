<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class RegistrationRequiredFieldsException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\122\x45\121\x55\111\x52\105\104\137\122\x45\x47\x49\x53\x54\x52\x41\124\x49\117\x4e\137\x46\111\105\114\x44\123");
        $Oo = 111;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\x20\x5b{$this->code}\135\72\x20{$this->message}\xa";
    }
}
