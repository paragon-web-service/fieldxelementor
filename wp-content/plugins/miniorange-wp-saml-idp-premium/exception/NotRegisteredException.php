<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class NotRegisteredException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\116\117\x54\137\122\105\107\x5f\105\122\122\x4f\122");
        $Oo = 102;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\x5b{$this->code}\135\x3a\x20{$this->message}\12";
    }
}
