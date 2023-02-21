<?php


namespace IDP\Exception;

class JSErrorException extends \Exception
{
    public function __construct($hW)
    {
        $hW = $hW;
        $Oo = 103;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\40\133{$this->code}\x5d\72\40{$this->message}\xa";
    }
}
