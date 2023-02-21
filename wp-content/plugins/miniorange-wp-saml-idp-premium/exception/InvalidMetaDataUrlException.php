<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidMetaDataUrlException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\111\x4e\x56\x41\114\111\x44\x5f\x55\122\114");
        $Oo = 130;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\x5b{$this->code}\x5d\72\40{$this->message}\xa";
    }
}
