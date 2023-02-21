<?php


namespace RobRichards\XMLSecLibs\Utils;

class XPath
{
    const ALPHANUMERIC = "\x5c\167\x5c\144";
    const NUMERIC = "\x5c\x64";
    const LETTERS = "\134\x77";
    const EXTENDED_ALPHANUMERIC = "\134\167\x5c\x64\x5c\163\134\x2d\137\x3a\134\x2e";
    const SINGLE_QUOTE = "\47";
    const DOUBLE_QUOTE = "\42";
    const ALL_QUOTES = "\x5b\x27\42\135";
    public static function filterAttrValue($l7, $UQ = self::ALL_QUOTES)
    {
        return preg_replace("\x23" . $UQ . "\43", '', $l7);
    }
    public static function filterAttrName($Pk, $hl = self::EXTENDED_ALPHANUMERIC)
    {
        return preg_replace("\x23\133\x5e" . $hl . "\135\x23", '', $Pk);
    }
}
