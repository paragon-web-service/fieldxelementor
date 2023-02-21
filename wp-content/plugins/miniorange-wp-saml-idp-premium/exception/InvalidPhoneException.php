<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidPhoneException extends \Exception
{
    public function __construct($mA)
    {
        $hW = MoIDPMessages::showMessage("\105\x52\122\x4f\122\x5f\x50\x48\x4f\x4e\105\137\x46\x4f\122\x4d\x41\124", array("\160\x68\x6f\x6e\145" => $mA));
        $Oo = 112;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\133{$this->code}\135\x3a\x20{$this->message}\12";
    }
}
