<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class PasswordResetFailedException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\105\x52\x52\x4f\x52\137\x4f\x43\x43\x55\122\122\105\x44");
        $Oo = 116;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\133{$this->code}\135\x3a\40{$this->message}\12";
    }
}
