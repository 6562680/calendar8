<?php

namespace Gzhegow\Calendar;

class Calendar implements CalendarInterface
{
    const INTERVAL_MINUTE = 60;
    const INTERVAL_HOUR   = 3600;
    const INTERVAL_DAY    = 86400;
    const INTERVAL_WEEK   = 604800;
    const INTERVAL_MONTH  = 2592000;
    const INTERVAL_YEAR   = 31536000;

    const LIST_INTERVAL = [
        self::INTERVAL_MINUTE => true,
        self::INTERVAL_HOUR   => true,
        self::INTERVAL_DAY    => true,
        self::INTERVAL_WEEK   => true,
        self::INTERVAL_MONTH  => true,
        self::INTERVAL_YEAR   => true,
    ];


    const FORMAT_FILENAME_DATE                  = 'ymd';
    const FORMAT_FILENAME_DATETIME              = 'ymd_His';
    const FORMAT_FILENAME_DATETIME_MICROSECONDS = 'ymd_His_u';
    const FORMAT_FILENAME_DATETIME_MILLISECONDS = 'ymd_His_v';
    const FORMAT_FILENAME_DAY                   = 'ymd_000000';
    const FORMAT_FILENAME_HOUR                  = 'ymd_H0000';
    const FORMAT_FILENAME_MINUTE                = 'ymd_Hi00';

    const FORMAT_JAVASCRIPT              = DATE_ATOM;
    const FORMAT_JAVASCRIPT_MILLISECONDS = "Y-m-d\TH:i:s.vP";

    const FORMAT_SQL                       = self::FORMAT_SQL_DATETIME;
    const FORMAT_SQL_DATE                  = 'Y-m-d';
    const FORMAT_SQL_DATETIME              = 'Y-m-d H:i:s';
    const FORMAT_SQL_DATETIME_MICROSECONDS = 'Y-m-d H:i:s.u';
    const FORMAT_SQL_DATETIME_MILLISECONDS = 'Y-m-d H:i:s.v';
    const FORMAT_SQL_DAY                   = 'Y-m-d 00:00:00';
    const FORMAT_SQL_HOUR                  = 'Y-m-d H:00:00';
    const FORMAT_SQL_MINUTE                = 'Y-m-d H:i:00';
    const FORMAT_SQL_MICROSECONDS          = self::FORMAT_SQL_DATETIME_MICROSECONDS;
    const FORMAT_SQL_MILLISECONDS          = self::FORMAT_SQL_DATETIME_MILLISECONDS;
    const FORMAT_SQL_TIME                  = 'H:i:s';
    const FORMAT_SQL_TIME_MICROSECONDS     = 'H:i:s.u';
    const FORMAT_SQL_TIME_MILLISECONDS     = 'H:i:s.v';

    const LIST_FORMAT = [
        // self::FORMAT_SQL                            => true,
        // self::FORMAT_SQL_MICROSECONDS               => true,
        // self::FORMAT_SQL_MILLISECONDS               => true,

        self::FORMAT_FILENAME_DATE                  => true,
        self::FORMAT_FILENAME_DATETIME              => true,
        self::FORMAT_FILENAME_DATETIME_MICROSECONDS => true,
        self::FORMAT_FILENAME_DATETIME_MILLISECONDS => true,
        self::FORMAT_FILENAME_DAY                   => true,
        self::FORMAT_FILENAME_HOUR                  => true,

        self::FORMAT_JAVASCRIPT              => true,
        self::FORMAT_JAVASCRIPT_MILLISECONDS => true,

        self::FORMAT_SQL_DATE                  => true,
        self::FORMAT_SQL_DATETIME              => true,
        self::FORMAT_SQL_DATETIME_MICROSECONDS => true,
        self::FORMAT_SQL_DATETIME_MILLISECONDS => true,
        self::FORMAT_SQL_DAY                   => true,
        self::FORMAT_SQL_HOUR                  => true,
        self::FORMAT_SQL_TIME                  => true,
    ];


    /**
     * @var string
     */
    protected $timezoneDefault = 'UTC';

    /**
     * @var string[]
     */
    protected $parseDateTimeFormatsDefault = [
        self::FORMAT_SQL,
        self::FORMAT_JAVASCRIPT,
    ];
    /**
     * @var string[]
     */
    protected $parseDateIntervalFormatsDefault = [
        self::FORMAT_SQL_TIME,
    ];

    /**
     * @var callable|null
     */
    protected $fnDateFormatter;
    /**
     * @var callable|null
     */
    protected $fnDateIntervalFormatter;

    /**
     * @var \DateTimeImmutable
     */
    protected $nowFixed;


    public function __construct()
    {
        $this->timezoneDefault = $this->parseDateTimeZone($this->timezoneDefault);
    }


    public function newDateTime($datetime = 'now', $timezone = null) : \DateTime
    {
        try {
            $dtClass = Type::dateTime();
            $dt = new $dtClass($datetime, $timezone);
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $datetime, $timezone ]),
                -1, $e
            );
        }

        return $dt;
    }

    public function newDateTimeFromInterface($object) : \DateTime
    {
        try {
            $dtClass = Type::dateTime();
            $dt = $dtClass::{'createFromInterface'}($object);

            $dt = $dt ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid `object`: ' . Lib::php_dump($object),
                -1, $e
            );
        }

        if (null === $dt) {
            throw new \LogicException(
                'Invalid `object`: ' . Lib::php_dump($object),
                -1
            );
        }

        return $dt;
    }

    public function newDateTimeFromFormat($format, $datetime = 'now', $timezone = null) : \DateTime
    {
        try {
            $dtClass = Type::dateTime();
            $dt = $dtClass::{'createFromFormat'}($format, $datetime, $timezone);

            $dt = $dt ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $format, $datetime, $timezone ]),
                -1, $e
            );
        }

        if (null === $dt) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $format, $datetime, $timezone ]),
                -1
            );
        }

        return $dt;
    }


    public function newDateTimeImmutable($datetime = 'now', $timezone = null) : \DateTimeImmutable
    {
        try {
            $dtClass = Type::dateTimeImmutable();
            $dt = new $dtClass($datetime, $timezone);
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $datetime, $timezone ]),
                -1, $e
            );
        }

        return $dt;
    }

    public function newDateTimeImmutableFromInterface($instanceInterface) : \DateTimeImmutable
    {
        try {
            $dtClass = Type::dateTimeImmutable();
            $dt = $dtClass::{'createFromInterface'}($instanceInterface);

            $dt = $dt ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid `instance`: ' . Lib::php_dump($instanceInterface),
                -1, $e
            );
        }

        if (null === $dt) {
            throw new \LogicException(
                'Invalid `instance`: ' . Lib::php_dump($instanceInterface),
                -1
            );
        }

        return $dt;
    }

    public function newDateTimeImmutableFromFormat($format, $dtFormattedString, $timezoneIfParsed = null) : \DateTimeImmutable
    {
        try {
            $dtClass = Type::dateTimeImmutable();
            $dt = $dtClass::{'createFromFormat'}($format, $dtFormattedString, $timezoneIfParsed);

            $dt = $dt ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $format, $dtFormattedString, $timezoneIfParsed ]),
                -1, $e
            );
        }

        if (null === $dt) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $format, $dtFormattedString, $timezoneIfParsed ]),
                -1
            );
        }

        return $dt;
    }


    public function newDateTimeZone($timezone = 'UTC') : \DateTimeZone
    {
        try {
            $tzClass = Type::dateTimeZone();
            $tz = new $tzClass($timezone);
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $timezone ]),
                -1, $e
            );
        }

        return $tz;
    }

    public function newDateTimeZoneFromInstance($instance) : \DateTimeZone
    {
        try {
            $tzClass = Type::dateTimeZone();
            $tz = $tzClass::{'createFromInstance'}($instance);

            $tz = $tz ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid `instance`: ' . Lib::php_dump($instance),
                -1, $e
            );
        }

        if (null === $tz) {
            throw new \LogicException(
                'Invalid `instance`: ' . Lib::php_dump($instance),
                -1
            );
        }

        return $tz;
    }


    public function newDateInterval($duration = 'P0D') : \DateInterval
    {
        try {
            $dtiClass = Type::dateInterval();
            $dti = new $dtiClass($duration);
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid input: ' . Lib::php_dump([ $duration ]),
                -1, $e
            );
        }

        return $dti;
    }

    public function newDateIntervalFromInstance($instance)
    {
        try {
            $dtiClass = Type::dateInterval();
            $dti = $dtiClass::{'createFromInstance'}($instance);

            // > gzhegow, convert false to null
            $dti = $dti ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid `instance`: ' . Lib::php_dump($instance),
                -1, $e
            );
        }

        if (null === $dti) {
            throw new \LogicException(
                'Invalid `instance`: ' . Lib::php_dump($instance),
                -1
            );
        }

        return $dti;
    }

    public function newDateIntervalFromDateString($dtiString)
    {
        try {
            $dtiClass = Type::dateInterval();
            $dti = $dtiClass::{'createFromDateString'}($dtiString);

            // > gzhegow, convert false to null
            $dti = $dti ?: null;
        }
        catch ( \Throwable $e ) {
            throw new \LogicException(
                'Invalid `dtiString`: ' . Lib::php_dump($dtiString),
                -1, $e
            );
        }

        if (null === $dti) {
            throw new \LogicException(
                'Invalid `dtiString`: ' . Lib::php_dump($dtiString),
                -1
            );
        }

        return $dti;
    }


    /**
     * @param string|\DateTimeZone $timezoneDefault
     */
    public function setTimezoneDefault($timezoneDefault) : void
    {
        $timezoneDefault = $this->parseDateTimeZone($timezoneDefault);

        $this->timezoneDefault = $timezoneDefault;
    }


    /**
     * @param array|string[] $parseFormatsDefault
     */
    public function setParseFormatsDefault(array $parseFormatsDefault) : void
    {
        $this->parseDateTimeFormatsDefault = $parseFormatsDefault;
    }


    /**
     * @param callable|null $fnDateFormatter
     */
    public function setFnDateFormatter(?callable $fnDateFormatter) : void
    {
        $this->fnDateFormatter = $fnDateFormatter;
    }

    /**
     * @param callable|null $fnDateIntervalFormatter
     */
    public function setFnDateIntervalFormatter(?callable $fnDateIntervalFormatter) : void
    {
        $this->fnDateIntervalFormatter = $fnDateIntervalFormatter;
    }


    public function dateTime($datetime = 'now', $timezone = null) : \DateTime
    {
        if ('' === $timezone) {
            $timezone = $this->timezoneDefault;

        } elseif ($timezone) {
            $timezone = $this->parseDateTimeZone($timezone);
        }

        $dt = $this->newDateTime($datetime, $timezone);

        return $dt;
    }

    public function dateTimeImmutable($datetime = 'now', $timezone = null) : \DateTimeImmutable
    {
        if ('' === $timezone) {
            $timezone = $this->timezoneDefault;

        } elseif ($timezone) {
            $timezone = $this->parseDateTimeZone($timezone);
        }

        $dt = $this->newDateTimeImmutable($datetime, $timezone);

        return $dt;
    }

    public function dateTimeZone($timezone = 'UTC') : \DateTimeZone
    {
        if ('' === $timezone) {
            $timezone = $this->timezoneDefault;

        } elseif ($timezone) {
            $timezone = $this->parseDateTimeZone($timezone);
        }

        $tz = $this->newDateTimeZone($timezone);

        return $tz;
    }

    public function dateInterval($duration = 'P0D') : \DateInterval
    {
        $interval = $this->newDateInterval($duration);

        return $interval;
    }


    public function now($timezone = null) : \DateTime
    {
        return $this->dateTime('now', $timezone);
    }

    public function nowImmutable($timezone = null) : \DateTimeImmutable
    {
        return $this->dateTimeImmutable('now', $timezone);
    }


    public function parseDateTime($datetime, array $formats = null, $timezoneIfParsed = null) : ?\DateTime
    {
        if (null === $datetime) {
            return null;
        }

        if (is_a($datetime, \DateTimeInterface::class)) {
            $dt = $this->newDateTimeFromInterface($datetime);

            return $dt;
        }

        if (null !== ($_num = Lib::parse_num($datetime))) {
            if ($dt = $this->parseDateTimeFromNum($_num, $timezoneIfParsed)) {
                $dt = $this->newDateTimeFromInterface($dt);

                return $dt;
            }
        }

        if (is_string($datetime)) {
            if ('' === $datetime) {
                return null;
            }

            if ($dt = $this->parseDateTimeFromString($datetime, $timezoneIfParsed)) {
                $dt = $this->newDateTimeFromInterface($dt);

                return $dt;
            }

            $formats = $formats ?? $this->parseDateTimeFormatsDefault;

            foreach ( $formats as $format ) {
                if ($dt = $this->parseDateTimeFromStringByFormat(
                    $format,
                    $datetime, $timezoneIfParsed
                )) {
                    $dt = $this->newDateTimeFromInterface($dt);

                    return $dt;
                }
            }
        }

        throw new \LogicException(
            'UNSUPPORTED_DATE_TIME: ' . Lib::php_dump($datetime),
            -1
        );
    }

    public function parseDateTimeImmutable($datetime, array $formats = null, $timezoneIfParsed = null) : ?\DateTimeImmutable
    {
        if (null === $datetime) {
            return null;
        }

        if (is_a($datetime, \DateTimeInterface::class)) {
            $dt = $this->newDateTimeImmutableFromInterface($datetime);

            return $dt;
        }

        if (null !== ($_num = Lib::parse_num($datetime))) {
            if ($dt = $this->parseDateTimeFromNum($_num, $timezoneIfParsed)) {
                $dt = $this->newDateTimeImmutableFromInterface($dt);

                return $dt;
            }
        }

        if (is_string($datetime)) {
            if ('' === $datetime) {
                return null;
            }

            if ($dt = $this->parseDateTimeFromString($datetime, $timezoneIfParsed)) {
                $dt = $this->newDateTimeImmutableFromInterface($dt);

                return $dt;
            }

            $formats = $formats ?? $this->parseDateTimeFormatsDefault;

            foreach ( $formats as $format ) {
                if ($dt = $this->parseDateTimeFromStringByFormat(
                    $format,
                    $datetime, $timezoneIfParsed
                )) {
                    $dt = $this->newDateTimeImmutableFromInterface($dt);

                    return $dt;
                }
            }
        }

        throw new \LogicException(
            'UNSUPPORTED_DATE_TIME: ' . Lib::php_dump($datetime)
        );
    }


    public function parseDateTimeFromNum($num, $timezoneIfParsed = null) : ?\DateTime
    {
        if (null === ($_num = Lib::parse_num($num))) {
            return null;
        }

        if ($_num < 0) {
            return null;
        }

        $int = (int) $_num;
        $frac = $_num - $int;

        $dt = $this->dateTime('now', $timezoneIfParsed);

        $dt->setTimestamp((int) $_num);

        if ($frac) {
            $frac *= 1000000;
            $frac = (int) $frac;
            $frac = str_pad($frac, 6, 0);

            $dt->modify("+ {$frac} microseconds");
        }

        return $dt;
    }

    public function parseDateTimeFromString($datetime, $timezoneIfParsed = null) : ?\DateTime
    {
        if (null === ($_string = Lib::parse_astring($datetime))) {
            return null;
        }

        try {
            // > gzhegow, timezone will be ignored if format contains one
            // https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
            $dt = $this->dateTime($_string, $timezoneIfParsed);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        return $dt;
    }

    public function parseDateTimeFromStringByFormat(string $format, $datetime, $timezoneIfParsed = null) : ?\DateTime
    {
        if (null === ($_string = Lib::parse_astring($datetime))) {
            return null;
        }

        try {
            // > gzhegow, timezone will be ignored if format contains one
            // https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
            $dt = $this->newDateTimeFromFormat($format, $_string, $timezoneIfParsed);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        return $dt;
    }


    public function parseDateTimeZone($timezone) : ?\DateTimeZone
    {
        if (null === $timezone) {
            return null;
        }

        if ($timezone instanceof \DateTimeZone) {
            $tz = $this->newDateTimeZoneFromInstance($timezone);

            return $tz;
        }

        if (is_string($timezone)) {
            if ('' === $timezone) {
                return null;
            }

            if ($tz = $this->parseDateTimeZoneFromStringTimezone($timezone)) {
                return $tz;
            }
        }

        throw new \LogicException(
            'UNSUPPORTED_DATE_TIME_ZONE: ' . Lib::php_dump($timezone),
            -1
        );
    }

    public function parseDateTimeZoneFromStringTimezone($timezone) : ?\DateTimeZone
    {
        if (null === ($_timezone = Lib::parse_astring($timezone))) {
            return null;
        }

        try {
            $tz = $this->newDateTimeZone($_timezone);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        return $tz;
    }


    public function parseDateInterval($interval, array $formats = null) : ?\DateInterval
    {
        if (null === $interval) {
            return null;
        }

        if ($interval instanceof \DateInterval) {
            $_interval = $this->newDateIntervalFromInstance($interval);

            return $_interval;
        }

        if (null !== ($_int = Lib::parse_int($interval))) {
            if ($_interval = $this->parseDateIntervalFromInt($_int)) {
                return $_interval;
            }
        }

        if (is_string($interval)) {
            if ('' === $interval) {
                return null;
            }

            if ($_interval = $this->parseDateIntervalFromStringDuration($interval)) {
                return $_interval;
            }

            $formats = $formats ?? $this->parseDateIntervalFormatsDefault;

            foreach ( $formats as $format ) {
                if ($_interval = $this->parseDateIntervalFromStringByFormat($format,
                    $interval
                )) {
                    return $_interval;
                }
            }

            if ($_interval = $this->parseDateIntervalFromStringDatetime($interval)) {
                return $_interval;
            }
        }

        throw new \LogicException(
            'UNSUPPORTED_DATE_INTERVAL: ' . Lib::php_dump($interval),
            -1
        );
    }

    public function parseDateIntervalFromInt($int) : ?\DateInterval
    {
        if (null === ($_int = Lib::parse_int($int))) {
            return null;
        }

        if ($_int < 0) {
            return null;
        }

        $seconds = $_int;

        $days = floor($seconds / static::INTERVAL_DAY);
        $seconds = $seconds % static::INTERVAL_DAY;

        $hours = floor($seconds / static::INTERVAL_HOUR);
        $seconds = $seconds % static::INTERVAL_HOUR;

        $minutes = floor($seconds / static::INTERVAL_MINUTE);
        $seconds = $seconds % static::INTERVAL_MINUTE;

        $duration = sprintf('P%dDT%dH%dM%dS', $days, $hours, $minutes, $seconds);

        try {
            $interval = $this->newDateInterval($duration);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        return $interval;
    }

    public function parseDateIntervalFromStringDuration($duration) : ?\DateInterval
    {
        if (null === ($_string = Lib::parse_astring($duration))) {
            return null;
        }

        try {
            $interval = $this->newDateInterval($_string);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        return $interval;
    }

    public function parseDateIntervalFromStringByFormat(string $format, $datetime) : ?\DateInterval
    {
        if (null === ($_string = Lib::parse_astring($datetime))) {
            return null;
        }

        try {
            // > gzhegow, timezone will be ignored if format contains one
            // https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
            $dt = $this->newDateTimeFromFormat($format, $_string, null);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        $now = $this->newDateTime('now midnight', null);

        $interval = $this->diff($dt, $now, true);

        return $interval;
    }

    public function parseDateIntervalFromStringDatetime($datetime) : ?\DateInterval
    {
        if (null === ($_string = Lib::parse_astring($datetime))) {
            return null;
        }

        try {
            $interval = $this->newDateIntervalFromDateString($_string);
        }
        catch ( \Throwable $e ) {
            return null;
        }

        return $interval;
    }


    public function diff(\DateTimeInterface $from, \DateTimeInterface $to, bool $absolute = null) : \DateInterval
    {
        $absolute = $absolute ?? false;

        $diff = $from->diff($to, $absolute);

        $interval = $this->newDateIntervalFromInstance($diff);

        return $interval;
    }


    public function formatHuman(?\DateTimeInterface $dateTime) : ?string
    {
        if (null === $dateTime) {
            return null;
        }

        if ($fn = $this->fnDateFormatter) {
            return $fn($dateTime);
        }

        if (! extension_loaded('intl')) {
            $dateTimeFormatted = $dateTime->format(\DateTimeInterface::RSS);

        } else {
            $intlDateFormatter = new \IntlDateFormatter(
                'ru_RU',
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                null,
                \IntlDateFormatter::GREGORIAN,
                'dd MMMM yyyy HH:mm:SS'
            );

            $dateTimeFormatted = $intlDateFormatter->format($dateTime);
        }

        return $dateTimeFormatted;
    }

    public function formatDay(?\DateTimeInterface $dateTime) : ?string
    {
        if (null === $dateTime) {
            return null;
        }

        if ($fn = $this->fnDateFormatter) {
            return $fn($dateTime);
        }

        if (! extension_loaded('intl')) {
            $dateTimeFormatted = $dateTime->format(\DateTimeInterface::RSS);

        } else {
            $intlDateFormatter = new \IntlDateFormatter(
                'ru_RU',
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                null,
                \IntlDateFormatter::GREGORIAN,
                'dd MMMM yyyy'
            );

            $dateTimeFormatted = $intlDateFormatter->format($dateTime);
        }

        return $dateTimeFormatted;
    }

    public function formatAgo(?\DateTimeInterface $dateTime, \DateTimeInterface $from = null) : ?string
    {
        if (null === $dateTime) {
            return null;
        }

        $from = $from ?? $this->newDateTimeImmutable('now');

        $dateInterval = $from->diff($dateTime);

        if ($fn = $this->fnDateIntervalFormatter) {
            return $fn($dateInterval);
        }

        $dateIntervalFormatted = null
            ?? ($dateInterval->y ? $dateInterval->format('%y г.') : null)
            ?? ($dateInterval->m ? $dateInterval->format('%m мес.') : null)
            ?? ($dateInterval->d ? $dateInterval->format('%d дн.') : null)
            ?? ($dateInterval->h ? $dateInterval->format('%h час.') : null)
            ?? ($dateInterval->i ? $dateInterval->format('%i мин.') : null)
            ?? ($dateInterval->s ? $dateInterval->format('%s сек.') : null)
            ?? $dateInterval->format('%s сек.');

        return $dateIntervalFormatted;
    }


    public function formatTimestamp(?\DateTimeInterface $dateTime) : ?string
    {
        return $dateTime
            ? (string) $dateTime->getTimestamp()
            : null;
    }

    public function formatTimestampUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $clone = (clone $dateTime)->setTimezone(
                $this->newDateTimeZone('UTC')
            );

            return (string) $clone->getTimestamp();
        }

        return null;
    }


    public function formatMilliseconds(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $timestamp = $dateTime->getTimestamp();
            $milliseconds = substr($dateTime->format('u'), 0, -3);

            return "{$timestamp}.{$milliseconds}";
        }

        return null;
    }

    public function formatMillisecondsUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $clone = (clone $dateTime)->setTimezone(
                $this->newDateTimeZone('UTC')
            );

            $timestamp = $clone->getTimestamp();
            $milliseconds = substr($clone->format('u'), 0, -3);

            return "{$timestamp}.{$milliseconds}";
        }

        return null;
    }


    public function formatMicroseconds(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            return $dateTime->format('U.u');
        }

        return null;
    }

    public function formatMicrosecondsUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $clone = (clone $dateTime)->setTimezone(
                $this->newDateTimeZone('UTC')
            );

            return $clone->format('U.u');
        }

        return null;
    }


    public function formatSql(?\DateTimeInterface $dateTime) : ?string
    {
        return $dateTime
            ? $dateTime->format(static::FORMAT_SQL)
            : null;
    }

    public function formatSqlUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $format = (clone $dateTime)
                ->setTimezone(
                    $this->newDateTimeZone('UTC')
                )
                ->format(static::FORMAT_SQL)
            ;

            return $format;
        }

        return null;
    }


    public function formatSqlMicroseconds(?\DateTimeInterface $dateTime) : ?string
    {
        return $dateTime
            ? $dateTime->format(static::FORMAT_SQL_MICROSECONDS)
            : null;
    }

    public function formatSqlMicrosecondsUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $format = (clone $dateTime)
                ->setTimezone(
                    $this->newDateTimeZone('UTC')
                )
                ->format(static::FORMAT_SQL_MICROSECONDS)
            ;

            return $format;
        }

        return null;
    }


    public function formatSqlMilliseconds(?\DateTimeInterface $dateTime) : ?string
    {
        return $dateTime
            ? substr($dateTime->format(static::FORMAT_SQL_MICROSECONDS), 0, -3)
            : null;
    }

    public function formatSqlMillisecondsUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $format = substr(
                (clone $dateTime)
                    ->setTimezone(
                        $this->newDateTimeZone('UTC')
                    )
                    ->format(static::FORMAT_SQL_MICROSECONDS),
                0, -3
            );

            return $format;
        }

        return null;
    }


    public function formatJavascript(?\DateTimeInterface $dateTime) : ?string
    {
        return $dateTime
            ? $dateTime->format(static::FORMAT_JAVASCRIPT)
            : null;
    }

    public function formatJavascriptUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $format = (clone $dateTime)
                ->setTimezone(
                    $this->newDateTimeZone('UTC')
                )
                ->format(static::FORMAT_JAVASCRIPT)
            ;

            return $format;
        }

        return null;
    }


    public function formatJavascriptMilliseconds(?\DateTimeInterface $dateTime) : ?string
    {
        return $dateTime
            ? $dateTime->format(static::FORMAT_JAVASCRIPT_MILLISECONDS)
            : null;
    }

    public function formatJavascriptMillisecondsUTC(?\DateTimeInterface $dateTime) : ?string
    {
        if ($dateTime) {
            $format = (clone $dateTime)
                ->setTimezone($this->newDateTimeZone('UTC'))
                ->format(static::FORMAT_JAVASCRIPT_MILLISECONDS)
            ;

            return $format;
        }

        return null;
    }


    public function formatIntervalISO(?\DateInterval $dateInterval) : ?string
    {
        if (! $dateInterval) {
            return null;
        }

        $search = [ 'S0F', 'M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M' ];
        $replace = [ 'S', 'M', 'H', 'DT', 'M', 'P', 'Y', 'P' ];

        $result = $dateInterval->format('P%yY%mM%dDT%hH%iM%sS%fF');
        $result = str_replace($search, $replace, $result);
        $result = rtrim($result, 'PT') ?: 'P0D';

        return $result ?: null;
    }
}
