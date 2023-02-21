<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidServiceProviderException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x49\x4e\x56\101\114\x49\104\x5f\x53\120");
        $Oo = 119;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\x5b{$this->code}\x5d\72\40{$this->message}\xa";
    }
}
