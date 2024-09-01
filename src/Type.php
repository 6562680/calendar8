<?php

namespace Gzhegow\Calendar;

use Gzhegow\Calendar\Struct\DateTime;
use Gzhegow\Calendar\Struct\DateInterval;
use Gzhegow\Calendar\Struct\DateTimeZone;
use Gzhegow\Calendar\Struct\DateTimeImmutable;


class Type
{
    /**
     * @return class-string<\DateInterval>
     */
    public static function dateInterval() : string
    {
        return static::getInstance()->_dateInterval();
    }

    /**
     * @return class-string<\DateTime>
     */
    public static function dateTime() : string
    {
        return static::getInstance()->_dateTime();
    }

    /**
     * @return class-string<\DateTimeImmutable>
     */
    public static function dateTimeImmutable() : string
    {
        return static::getInstance()->_dateTimeImmutable();
    }

    /**
     * @return class-string<\DateTimeZone>
     */
    public static function dateTimeZone() : string
    {
        return static::getInstance()->_dateTimeZone();
    }


    /**
     * @return class-string<\DateInterval>
     */
    protected function _dateInterval() : string
    {
        return DateInterval::class;
    }

    /**
     * @return class-string<\DateTime>
     */
    protected function _dateTime() : string
    {
        return DateTime::class;
    }

    /**
     * @return class-string<\DateTimeImmutable>
     */
    protected function _dateTimeImmutable() : string
    {
        return DateTimeImmutable::class;
    }

    /**
     * @return class-string<\DateTimeZone>
     */
    protected function _dateTimeZone() : string
    {
        return DateTimeZone::class;
    }


    /**
     * @return static
     */
    public static function getInstance() : self
    {
        return static::$instance = static::$instance ?? new static();
    }

    public static function setInstance(self $instance) : void
    {
        static::$instance = static::$instance = $instance;
    }

    protected static $instance;
}
