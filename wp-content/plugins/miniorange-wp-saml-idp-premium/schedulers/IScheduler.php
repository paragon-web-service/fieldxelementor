<?php


namespace IDP\Schedulers;

interface IScheduler
{
    public function setYearlySchedule($Gq);
    public function unsetYearlySchedule();
    public function set15DaySchedule($Gq);
    public function unset15DaySchedule();
    public function set10DaySchedule($Gq);
    public function unset10DaySchedule();
    public function set5DaySchedule($Gq);
    public function unset5DaySchedule();
    public function setFinalCheckSchedule();
    public function unsetFinalCheckSchedule();
    public function unscheduleAllEvents();
}
