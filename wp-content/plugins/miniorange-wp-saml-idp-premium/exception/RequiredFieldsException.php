<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class RequiredFieldsException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x52\x45\x51\125\111\122\105\x44\137\106\x49\105\114\x44\123");
        $Oo = 104;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\133{$this->code}\135\x3a\40{$this->message}\12";
    }
}
