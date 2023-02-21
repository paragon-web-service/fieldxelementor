<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class MissingNameIdException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\115\x49\x53\x53\111\x4e\107\x5f\x4e\101\x4d\105\111\104");
        $Oo = 126;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\133{$this->code}\x5d\72\x20{$this->message}\12";
    }
}
