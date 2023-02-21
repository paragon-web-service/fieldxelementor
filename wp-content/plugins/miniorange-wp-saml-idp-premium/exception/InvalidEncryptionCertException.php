<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidEncryptionCertException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\111\116\126\101\x4c\x49\x44\137\105\116\x43\122\131\120\124\x5f\103\105\122\124");
        $Oo = 108;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\x5b{$this->code}\x5d\72\x20{$this->message}\xa";
    }
}
