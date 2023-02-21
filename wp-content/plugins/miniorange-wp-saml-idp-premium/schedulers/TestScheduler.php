<?php


namespace IDP\Schedulers;

use IDP\Helper\Utilities\MoIDPUtility;
class TestScheduler extends BaseScheduler
{
    public function setYearlySchedule($Gq)
    {
        if (!MSI_DEBUG) {
            goto nq;
        }
        MoIDPUtility::mo_debug("\x53\145\x74\164\151\156\147\40\64\x35\40\x4d\x69\156\165\x74\145\x20\123\143\x68\145\144\165\x6c\145\x20\146\157\162\x20\x4c\151\143\x65\x6e\x73\x65\x20\x43\150\145\143\153");
        nq:
        wp_schedule_single_event(time() + 2700, $this->events[0], array($Gq));
    }
    public function unsetYearlySchedule()
    {
        if (!MSI_DEBUG) {
            goto AL;
        }
        MoIDPUtility::mo_debug("\125\156\x73\143\150\145\x64\165\x6c\151\x6e\x67\40\x34\x35\x20\115\151\x6e\165\x74\145\40\x53\x63\x68\145\x64\165\154\x65\x20\146\x6f\x72\x20\114\x69\143\x65\x6e\163\x65\x20\x43\x68\145\x63\153");
        AL:
        wp_unschedule_event(wp_next_scheduled($this->events[0]), $this->events[0]);
    }
    public function set15DaySchedule($Gq)
    {
        if (!MSI_DEBUG) {
            goto S7;
        }
        MoIDPUtility::mo_debug("\123\x65\164\164\x69\156\147\40\65\x20\115\x69\x6e\165\x74\x65\x20\x53\x63\150\145\x64\165\x6c\145\40\146\157\162\x20\x4c\151\143\x65\x6e\x73\x65\x20\103\150\145\143\x6b");
        S7:
        wp_schedule_single_event(time() + 300, $this->events[1], array($Gq));
    }
    public function unset15DaySchedule()
    {
        if (!MSI_DEBUG) {
            goto B1;
        }
        MoIDPUtility::mo_debug("\x55\156\163\x63\x68\145\x64\165\154\x69\156\147\40\65\x20\115\151\x6e\165\164\x65\x20\x53\x63\x68\x65\x64\165\154\x65");
        B1:
        wp_unschedule_event(wp_next_scheduled($this->events[1]), $this->events[1]);
    }
    public function set10DaySchedule($Gq)
    {
        if (!MSI_DEBUG) {
            goto tN;
        }
        MoIDPUtility::mo_debug("\123\x65\164\164\x69\x6e\x67\x20\63\x20\115\151\x6e\x75\x74\x65\x20\123\143\x68\145\x64\165\154\x65\x20\x66\x6f\162\40\114\x69\x63\x65\x6e\x73\x65\40\103\150\145\x63\153");
        tN:
        wp_schedule_single_event(time() + 180, $this->events[2], array($Gq));
    }
    public function unset10DaySchedule()
    {
        if (!MSI_DEBUG) {
            goto hl;
        }
        MoIDPUtility::mo_debug("\125\156\x73\x63\150\x65\x64\x75\x6c\x69\x6e\x67\x20\63\x20\115\151\x6e\x75\x74\x65\x20\123\143\150\x65\x64\x75\154\x65\40\146\157\162\x20\114\x69\x63\145\156\x73\145\x20\103\150\145\x63\x6b");
        hl:
        wp_unschedule_event(wp_next_scheduled($this->events[2]), $this->events[2]);
    }
    public function set5DaySchedule($Gq)
    {
        if (!MSI_DEBUG) {
            goto zX;
        }
        MoIDPUtility::mo_debug("\x53\x65\x74\164\x69\x6e\147\x20\61\40\115\x69\156\x75\x74\145\40\123\x63\150\145\144\165\154\145\x20\x66\x6f\x72\x20\114\151\x63\x65\156\x73\x65\40\x43\x68\x65\143\x6b");
        zX:
        wp_schedule_single_event(time() + 60, $this->events[2], array($Gq));
    }
    public function unset5DaySchedule()
    {
        if (!MSI_DEBUG) {
            goto Jf;
        }
        MoIDPUtility::mo_debug("\125\x6e\163\x63\x68\145\x64\165\154\x69\x6e\x67\40\x31\40\x4d\151\x6e\x75\x74\145\x20\x53\x63\x68\145\x64\x75\154\145\40\146\157\162\x20\x4c\x69\x63\x65\x6e\163\145\40\x43\x68\145\143\153");
        Jf:
        wp_unschedule_event(wp_next_scheduled($this->events[2]), $this->events[2]);
    }
    public function setFinalCheckSchedule()
    {
        if (!MSI_DEBUG) {
            goto pr;
        }
        MoIDPUtility::mo_debug("\123\145\x74\164\x69\x6e\147\x20\x33\x20\x4d\151\x6e\165\164\145\40\123\143\x68\x65\x64\x75\154\145\x20\146\157\x72\x20\144\x65\141\143\164\151\x76\141\164\x69\x6e\x67\x20\164\150\x65\x20\160\154\165\147\x69\x6e");
        pr:
        wp_schedule_single_event(time() + 180, $this->events[3]);
    }
    public function unsetFinalCheckSchedule()
    {
        if (!MSI_DEBUG) {
            goto Zj;
        }
        MoIDPUtility::mo_debug("\x55\156\163\x63\x68\145\144\165\x6c\151\x6e\147\x20\x33\40\115\151\156\x75\x74\145\x20\123\x63\x68\x65\x64\165\154\x65\x20\x66\157\162\40\144\145\x61\x63\164\151\x76\141\x74\151\x6e\147\40\x74\150\145\x20\160\154\x75\147\x69\156");
        Zj:
        wp_unschedule_event(wp_next_scheduled($this->events[3]), $this->events[3]);
    }
}
