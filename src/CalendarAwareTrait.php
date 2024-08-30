<?php

namespace Gzhegow\Calendar;


trait CalendarAwareTrait
{
    /**
     * @var Calendar
     */
    protected $calendar;


    public function setCalendar(?CalendarInterface $calendar) : void
    {
        $this->calendar = $calendar;
    }
}
