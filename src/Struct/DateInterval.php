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

        if (! is_a($object, \DateInterval::class)) {
            throw new \LogicException('The `object` should be instance of: '
                . \DateInterval::class
                . ' / ' . Lib::php_dump($object)
            );
        }

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
        if (null === Lib::parse_astring($datetime)) {
            throw  new \LogicException(
                'The `datetime` should be a non-empty string: ' . Lib::php_dump($datetime)
            );
        }

        $dti = parent::createFromDateString($datetime);

        (function ($state) {
            foreach ( $state as $key => $value ) {
                $this->{$key} = $value;
            }
        })->call($interval = new static('P0D'), (array) $dti);

        return $interval;
    }


    public function jsonSerialize() : mixed
    {
        // $dti = new \DateInterval();
        // var_dump($dti, $var = json_encode($dti));
        //
        // > string(88) "{"y":0,"m":0,"d":0,"h":0,"i":10,"s":0,"f":0,"invert":0,"days":false,"from_string":false}"
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
