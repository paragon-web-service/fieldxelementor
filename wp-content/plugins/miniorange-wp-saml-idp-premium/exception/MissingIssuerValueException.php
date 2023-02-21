<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class MissingIssuerValueException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\115\x49\123\x53\x49\x4e\x47\x5f\111\x53\123\x55\105\x52\137\x56\101\x4c\125\x45");
        $Oo = 123;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\x5b{$this->code}\135\x3a\x20{$this->message}\xa";
    }
}
