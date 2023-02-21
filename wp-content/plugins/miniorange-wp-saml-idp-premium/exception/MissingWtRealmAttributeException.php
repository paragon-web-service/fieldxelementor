<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class MissingWtRealmAttributeException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x4d\x49\x53\123\x49\116\107\137\127\124\122\x45\101\x4c\x4d\137\101\x54\x54\122");
        $Oo = 128;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\x5b{$this->code}\135\72\x20{$this->message}\12";
    }
}
