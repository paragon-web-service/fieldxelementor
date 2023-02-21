<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class MissingWaAttributeException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\115\x49\123\x53\x49\x4e\x47\x5f\x57\x41\x5f\101\124\124\122");
        $Oo = 127;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\x20\133{$this->code}\x5d\72\x20{$this->message}\12";
    }
}
