<?php

namespace Gzhegow\Calendar\Struct;

use Gzhegow\Calendar\Lib;
use Gzhegow\Calendar\Calendar;


class DateTimeImmutable extends \DateTimeImmutable implements DateTimeInterface,
    \JsonSerializable
{
    /**
     * @return static
     */
    public static function createFromInterface($object) : \DateTimeImmutable
    {
        if (is_a($object, static::class)) {
            return clone $object;
        }

        Lib::assert_true('is_a', [ $object, \DateTimeInterface::class ]);

        $microseconds = str_pad($object->format('u'), 6, '0');

        try {
            $dt = (new static('now', $object->getTimezone()))
                ->setTimestamp($object->getTimestamp())
                ->modify("+ {$microseconds} microseconds")
            ;
        }
        catch ( \Throwable $e ) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $dt;
    }


    /**
     * @return static
     */
    public static function createFromFormat($format, $datetime, $timezone = null)
    {
        Lib::assert([ Lib::class, 'filter_string' ], [ $format ]);
        Lib::assert([ Lib::class, 'filter_string' ], [ $datetime ]);
        Lib::assert_true('is_a', [ $timezone, \DateTimeZone::class ]);

        $object = parent::createFromFormat($format, $datetime, $timezone);

        $microseconds = str_pad($object->format('u'), 6, '0');

        try {
            $dt = (new static('now', $object->getTimezone()))
                ->setTimestamp($object->getTimestamp())
                ->modify("+ {$microseconds} microseconds")
            ;
        }
        catch ( \Throwable $e ) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $dt;
    }


    public function diff($targetObject, $absolute = false) : DateInterval
    {
        $interval = parent::diff($targetObject, $absolute);

        $interval = DateInterval::createFromInstance($interval);

        return $interval;
    }


    public function jsonSerialize() : mixed
    {
        // var_dump($date, $var = json_encode($date));
        //
        // > string(72) "{"date":"1970-01-01 00:00:00.000000","timezone_type":3,"timezone":"UTC"}"
        // > object(stdClass)#2 (3) {
        // >   ["date"]=>
        // >   string(26) "1970-01-01 00:00:00.000000"
        // >   ["timezone_type"]=>
        // >   int(3)
        // >   ["timezone"]=>
        // >   string(3) "UTC"
        // > }
        //
        // vs
        //
        // > string(29) "2024-04-08T08:42:04.037+00:00"

        return $this->format(Calendar::FORMAT_JAVASCRIPT_MILLISECONDS);
    }
}
