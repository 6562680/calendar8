<?php

namespace Gzhegow\Calendar\Struct;

use Gzhegow\Calendar\Lib;
use Gzhegow\Calendar\Type;
use Gzhegow\Calendar\Calendar;


class DateTime extends \DateTime implements DateTimeInterface,
    \JsonSerializable
{
    /**
     * @return static
     */
    public static function createFromInterface($object) : \DateTime
    {
        if (is_a($object, static::class)) {
            return clone $object;
        }

        if (! is_a($object, \DateTimeInterface::class)) {
            throw new \LogicException('The `object` should be instance of: '
                . \DateTimeInterface::class
                . ' / ' . Lib::php_dump($object)
            );
        }

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
        if (null === Lib::parse_astring($format)) {
            throw  new \LogicException(
                'The `format` should be a non-empty string: ' . Lib::php_dump($format)
            );
        }

        if (null === Lib::parse_astring($datetime)) {
            throw  new \LogicException(
                'The `datetime` should be a non-empty string: ' . Lib::php_dump($datetime)
            );
        }

        if (null !== $timezone) {
            if (! is_a($timezone, \DateTimeZone::class)) {
                throw new \LogicException('The `object` should be instance of: '
                    . \DateTimeZone::class
                    . ' / ' . Lib::php_dump($timezone)
                );
            }
        }

        $dt = parent::createFromFormat($format, $datetime, $timezone);

        $microseconds = str_pad($dt->format('u'), 6, '0');

        try {
            $dt = (new static('now', $dt->getTimezone()))
                ->setTimestamp($dt->getTimestamp())
                ->modify("+ {$microseconds} microseconds")
            ;
        }
        catch ( \Throwable $e ) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $dt;
    }


    public function diff($targetObject, $absolute = false) : \DateInterval
    {
        $interval = parent::diff($targetObject, $absolute);

        $dtiClass = Type::dateInterval();
        $interval = $dtiClass::{'createFromInstance'}($interval);

        return $interval;
    }


    public function jsonSerialize() : mixed
    {
        // var_dump($date, $var = json_encode($date));
        //
        // > string(72) "{"date":"1970-01-01 00:00:00.000000","timezone_type":3,"timezone":"UTC"}"
        //
        // vs
        //
        // > string(29) "2024-04-08T08:42:04.037+00:00"

        return $this->format(Calendar::FORMAT_JAVASCRIPT_MILLISECONDS);
    }
}
