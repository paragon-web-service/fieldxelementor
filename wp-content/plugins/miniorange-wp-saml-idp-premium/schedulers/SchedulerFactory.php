<?php


namespace IDP\Schedulers;

class SchedulerFactory
{
    private static $_instance;
    public static function getInstance()
    {
        if (!is_null(self::$_instance)) {
            goto rU;
        }
        if (MSI_DEBUG && MSI_LK_DEBUG) {
            goto sb;
        }
        self::$_instance = new CustomSchedulers();
        goto wu;
        sb:
        self::$_instance = new TestScheduler();
        wu:
        rU:
        return self::$_instance;
    }
}
