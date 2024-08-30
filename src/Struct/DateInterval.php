<?php

namespace Gzhegow\Calendar\Struct;

use Gzhegow\Calendar\Lib;


class DateInterval extends \DateInterval implements
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

        Lib::assert_true('is_a', [ $object, \DateInterval::class ]);

        (function ($state) {
            foreach ( $state as $key => $value ) {
                $this->{$key} = $value;
            }
        })->call($interval = new static('P0D'), (array) $object);

        return $interval;
    }

    /**
     * @return static
     */
    public static function createFromDateString($datetime)
    {
        Lib::assert([ Lib::class, 'filter_string' ], [ $datetime ]);

        $object = parent::createFromDateString($datetime);

        (function ($state) {
            foreach ( $state as $key => $value ) {
                $this->{$key} = $value;
            }
        })->call($interval = new static('P0D'), (array) $object);

        return $interval;
    }


    public function jsonSerialize() : mixed
    {
        // $dt = new \DateInterval();
        // var_dump($dt, $var = json_encode($dt), json_decode($var));
        //
        // > string(88) "{"y":0,"m":0,"d":0,"h":0,"i":10,"s":0,"f":0,"invert":0,"days":false,"from_string":false}"
        // > object(stdClass)#2 (10) {
        // >   ["y"]=>
        // >   int(0)
        // >   ["m"]=>
        // >   int(0)
        // >   ["d"]=>
        // >   int(0)
        // >   ["h"]=>
        // >   int(0)
        // >   ["i"]=>
        // >   int(10)
        // >   ["s"]=>
        // >   int(0)
        // >   ["f"]=>
        // >   int(0)
        // >   ["invert"]=>
        // >   int(0)
        // >   ["days"]=>
        // >   bool(false)
        // >   ["from_string"]=>
        // >   bool(false)
        // > }
        //
        // vs
        //
        // > string(5) "PT10M"

        $search = [ 'S0F', 'M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M' ];
        $replace = [ 'S', 'M', 'H', 'DT', 'M', 'P', 'Y', 'P' ];

        $result = $this->format('P%yY%mM%dDT%hH%iM%sS%fF');
        $result = str_replace($search, $replace, $result);
        $result = rtrim($result, 'PT') ?: 'P0D';

        return $result ?: null;
    }
}
