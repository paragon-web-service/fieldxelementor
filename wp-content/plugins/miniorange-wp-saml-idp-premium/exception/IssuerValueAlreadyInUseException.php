<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class IssuerValueAlreadyInUseException extends \Exception
{
    public function __construct($di)
    {
        $hW = MoIDPMessages::showMessage("\111\x53\x53\125\105\122\x5f\105\x58\111\123\124\x53", array("\156\x61\155\145" => $di->mo_idp_sp_name));
        $Oo = 106;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\x5b{$this->code}\x5d\x3a\40{$this->message}\12";
    }
}
