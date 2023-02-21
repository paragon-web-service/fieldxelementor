<?php


namespace IDP\Schedulers;

use IDP\Helper\Utilities\MoIDPUtility;
class CustomSchedulers extends BaseScheduler implements IScheduler
{
    public function setYearlySchedule($Gq)
    {
        wp_schedule_single_event(time() + 31536000 - 2592000, $this->events[0], array($Gq));
    }
    public function unsetYearlySchedule()
    {
        wp_unschedule_event(wp_next_scheduled($this->events[0]), $this->events[0]);
    }
    public function set15DaySchedule($Gq)
    {
        wp_schedule_single_event(time() + 1296000, $this->events[1], array($Gq));
    }
    public function unset15DaySchedule()
    {
        wp_unschedule_event(wp_next_scheduled($this->events[1]), $this->events[1]);
    }
    public function set10DaySchedule($Gq)
    {
        wp_schedule_single_event(time() + 864000, $this->events[2], array($Gq));
    }
    public function unset10DaySchedule()
    {
        wp_unschedule_event(wp_next_scheduled($this->events[2]), $this->events[2]);
    }
    public function set5DaySchedule($Gq)
    {
        wp_schedule_single_event(time() + 432000, $this->events[2], array($Gq));
    }
    public function unset5DaySchedule()
    {
        wp_unschedule_event(wp_next_scheduled($this->events[2]), $this->events[2]);
    }
    public function setFinalCheckSchedule()
    {
        wp_schedule_single_event(time() + 432000, $this->events[3]);
    }
    public function unsetFinalCheckSchedule()
    {
        wp_unschedule_event(wp_next_scheduled($this->events[3]), $this->events[3]);
    }
}
