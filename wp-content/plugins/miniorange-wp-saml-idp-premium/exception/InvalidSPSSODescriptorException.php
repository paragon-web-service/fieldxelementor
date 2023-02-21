<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidSPSSODescriptorException extends \Exception
{
    public function __construct($hW)
    {
        $hW = MoIDPMessages::showMessage($hW);
        $Oo = 131;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\133{$this->code}\x5d\x3a\x20{$this->message}\xa";
    }
}
