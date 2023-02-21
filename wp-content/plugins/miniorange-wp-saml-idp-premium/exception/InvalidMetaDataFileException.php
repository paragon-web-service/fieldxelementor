<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class InvalidMetaDataFileException extends \Exception
{
    public function __construct($Aj)
    {
        $hW = $Aj;
        $Oo = 129;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\133{$this->code}\x5d\72\40{$this->message}\12";
    }
}
