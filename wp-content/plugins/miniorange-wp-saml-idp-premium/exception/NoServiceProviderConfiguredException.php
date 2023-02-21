<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class NoServiceProviderConfiguredException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\116\117\x5f\x53\x50\x5f\x43\x4f\x4e\106\x49\107");
        $Oo = 101;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\133{$this->code}\135\x3a\40{$this->message}\xa";
    }
}
