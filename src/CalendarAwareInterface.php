<?php

namespace Gzhegow\Calendar;


interface CalendarAwareInterface
{
    /**
     * @param null|CalendarInterface $calendar
     *
     * @return void
     */
    public function setCalendar(?CalendarInterface $calendar) : void;
}
