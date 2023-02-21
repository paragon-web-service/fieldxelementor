<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class SPNameAlreadyInUseException extends \Exception
{
    public function __construct($di)
    {
        $hW = MoIDPMessages::showMessage("\123\120\x5f\x45\x58\111\x53\x54\x53");
        $Oo = 107;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\72\40\133{$this->code}\135\x3a\x20{$this->message}\xa";
    }
}
