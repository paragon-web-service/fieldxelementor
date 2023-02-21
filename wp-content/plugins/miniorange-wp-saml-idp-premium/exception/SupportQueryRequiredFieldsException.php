<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class SupportQueryRequiredFieldsException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\x52\x45\121\x55\111\x52\105\104\x5f\121\x55\x45\x52\131\137\106\111\105\x4c\x44\123");
        $Oo = 109;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\133{$this->code}\135\72\40{$this->message}\xa";
    }
}
