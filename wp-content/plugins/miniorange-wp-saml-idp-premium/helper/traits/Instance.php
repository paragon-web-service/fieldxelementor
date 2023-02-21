<?php


namespace IDP\Helper\Traits;

trait Instance
{
    private static $_instance = null;
    public static function instance()
    {
        if (!is_null(self::$_instance)) {
            goto Pb;
        }
        self::$_instance = new self();
        Pb:
        return self::$_instance;
    }
}
