<?php

namespace Gzhegow\Calendar\Struct;

use Gzhegow\Calendar\Lib;


class DateTimeZone extends \DateTimeZone implements
    \JsonSerializable
{
    /**
     * @return static
     */
    public static function createFromInstance($object)
    {
        if (is_a($object, static::class)) {
            return clone $object;
        }

        Lib::assert_true('is_a', [ $object, \DateTimeZone::class ]);

        (function ($state) {
            foreach ( $state as $key => $value ) {
                $this->{$key} = $value;
            }
        })->call($tz = new static('UTC'), (array) $object);

        return $tz;
    }


    public function jsonSerialize() : mixed
    {
        // var_dump($tz, $var = json_encode($tz));
        //
        // > string(72) "{"timezone_type":3,"timezone":"UTC"}"
        // > object(stdClass)#2 (3) {
        // >   ["timezone_type"]=>
        // >   int(3)
        // >   ["timezone"]=>
        // >   string(3) "UTC"
        // > }
        //
        // vs
        //
        // > string(29) "UTC"

        return $this->getName();
    }
}
