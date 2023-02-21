<?php


namespace IDP\Exception;

use IDP\Helper\Constants\MoIDPMessages;
class MissingIDException extends \Exception
{
    public function __construct()
    {
        $hW = MoIDPMessages::showMessage("\115\111\x53\123\111\x4e\x47\137\x49\x44\x5f\106\x52\117\x4d\x5f\x52\105\x51\x55\x45\123\124");
        $Oo = 125;
        parent::__construct($hW, $Oo, NULL);
    }
    public function __toString()
    {
        return __CLASS__ . "\x3a\x20\133{$this->code}\135\x3a\x20{$this->message}\xa";
    }
}
