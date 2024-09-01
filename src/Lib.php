<?php

namespace Gzhegow\Calendar;

class Lib
{
    /**
     * > gzhegow, выводит короткую и наглядную форму содержимого переменной в виде строки
     */
    public static function php_dump($value, int $maxlen = null) : string
    {
        if (! is_iterable($value)) {
            if (is_object($value)) {
                if (! method_exists($value, '__debugInfo')) {
                    $_value = '{ object(' . get_class($value) . ' # ' . spl_object_id($value) . ') }';

                } else {
                    ob_start();
                    var_dump($value);
                    $_value = ob_get_clean();

                    $_value = '{ object(' . get_class($value) . ' # ' . spl_object_id($value) . '): ' . $_value . ' }';
                }

            } elseif (is_string($value)) {
                $_value = ''
                    . '{ '
                    . 'string(' . strlen($value) . ')'
                    . ' "'
                    . ($maxlen
                        ? (substr($value, 0, $maxlen) . '...')
                        : $value
                    )
                    . '"'
                    . ' }';

            } else {
                $_value = null
                    ?? (($value === null) ? '{ NULL }' : null)
                    ?? (($value === false) ? '{ FALSE }' : null)
                    ?? (($value === true) ? '{ TRUE }' : null)
                    ?? (is_resource($value) ? ('{ resource(' . gettype($value) . ' # ' . ((int) $value) . ') }') : null)
                    //
                    ?? (is_int($value) ? (var_export($value, 1)) : null) // INF
                    ?? (is_float($value) ? (var_export($value, 1)) : null) // NAN
                    //
                    ?? null;
            }

        } else {
            $_value = [];
            foreach ( $value as $k => $v ) {
                $_value[ $k ] = null
                    ?? (is_array($v) ? '{ array(' . count($v) . ') }' : null)
                    // ! recursion
                    ?? static::php_dump($v, $maxlen);
            }

            ob_start();
            var_dump($_value);
            $_value = ob_get_clean();

            if (is_object($value)) {
                $_value = '{ iterable(' . get_class($value) . ' # ' . spl_object_id($value) . '): ' . $_value . ' }';
            }

            $_value = trim($_value);
            $_value = preg_replace('/\s+/', ' ', $_value);
        }

        if (null === $_value) {
            throw static::php_throwable([ 'Unable to ' . __FUNCTION__ . '()', 'var' => $value ]);
        }

        return $_value;
    }

    /**
     * > gzhegow, перебрасывает исключение на "тихое", если из библиотеки внутреннее постоянно подсвечивается в PHPStorm
     *
     * @return \LogicException|\RuntimeException|null
     */
    public static function php_throwable($error = null, ...$errors) : ?object
    {
        if (is_a($error, \Closure::class)) {
            $error = $error(...$errors);
        }

        if (
            is_a($error, \LogicException::class)
            || is_a($error, \RuntimeException::class)
        ) {
            return $error;
        }

        $throwErrors = static::php_throwable_args($error, ...$errors);

        $message = $throwErrors[ 'message' ] ?? __FUNCTION__;
        $code = $throwErrors[ 'code' ] ?? -1;
        $previous = $throwErrors[ 'previous' ] ?? null;

        return $previous
            ? new \RuntimeException($message, $code, $previous)
            : new \LogicException($message, $code);
    }

    /**
     * > gzhegow, парсит ошибки для передачи результата в конструктор исключения
     *
     * @return array{
     *     messageList: string[],
     *     codeList: int[],
     *     previousList: string[],
     *     messageCodeList: array[],
     *     messageDataList: array[],
     *     message: ?string,
     *     code: ?int,
     *     previous: ?string,
     *     messageCode: ?string,
     *     messageData: ?array,
     *     messageObject: ?object,
     *     __unresolved: array,
     * }
     */
    public static function php_throwable_args($arg = null, ...$args) : array
    {
        array_unshift($args, $arg);

        $len = count($args);

        $messageList = null;
        $codeList = null;
        $previousList = null;
        $messageCodeList = null;
        $messageDataList = null;

        $__unresolved = [];

        for ( $i = 0; $i < $len; $i++ ) {
            $a = $args[ $i ];

            if (is_a($a, \Throwable::class)) {
                $previousList[ $i ] = $a;

                continue;
            }

            if (
                is_array($a)
                || is_a($a, \stdClass::class)
            ) {
                $messageDataList[ $i ] = (array) $a;

                if ('' !== ($messageString = (string) $messageDataList[ $i ][ 0 ])) {
                    $messageList[ $i ] = $messageString;

                    unset($messageDataList[ $i ][ 0 ]);

                    if (! $messageDataList[ $i ]) {
                        unset($messageDataList[ $i ]);
                    }
                }

                continue;
            }

            if (is_int($a)) {
                $codeList[ $i ] = $a;

                continue;
            }

            if ('' !== ($vString = (string) $a)) {
                $messageList[ $i ] = $vString;

                continue;
            }

            $__unresolved[ $i ] = $a;
        }

        for ( $i = 0; $i < $len; $i++ ) {
            if (isset($messageList[ $i ])) {
                if (preg_match('/^[a-z](?!.*\s)/i', $messageList[ $i ])) {
                    $messageCodeList[ $i ] = strtoupper($messageList[ $i ]);
                }
            }
        }

        $result = [];

        $result[ 'messageList' ] = $messageList;
        $result[ 'codeList' ] = $codeList;
        $result[ 'previousList' ] = $previousList;
        $result[ 'messageCodeList' ] = $messageCodeList;
        $result[ 'messageDataList' ] = $messageDataList;

        $messageDataList = $messageDataList ?? [];

        $message = $messageList ? end($messageList) : null;
        $code = $codeList ? end($codeList) : null;
        $previous = $previousList ? end($previousList) : null;
        $messageCode = $messageCodeList ? end($messageCodeList) : null;

        $messageData = $messageDataList
            ? array_replace(...$messageDataList)
            : [];

        $messageObject = (object) ([ $message ] + $messageData);

        $result[ 'message' ] = $message;
        $result[ 'code' ] = $code;
        $result[ 'previous' ] = $previous;
        $result[ 'messageCode' ] = $messageCode;
        $result[ 'messageData' ] = $messageData;

        $result[ 'messageObject' ] = $messageObject;

        $result[ '__unresolved' ] = $__unresolved;

        return $result;
    }


    /**
     * > gzhegow, вызывает произвольный колбэк с аргументами, если результат NULL - бросает исключение
     * > _assert('_filter_strlen', [ $input ], 'Переменная `input` должна быть не-пустой строкой')
     *
     * @param callable|null $fn
     */
    public static function assert($fn, array $args = [], $error = '', ...$errors)
    {
        $result = ($fn === null)
            ? $fn
            : call_user_func_array($fn, $args);

        if (null === $result) {
            if (('' === $error) || (null === $error)) {
                if (! $errors) {
                    $error = [ '[ ' . __FUNCTION__ . ' ] ' . static::php_dump($fn), $args ];
                }
            }

            throw static::php_throwable($error, ...$errors);
        }

        return $result;
    }

    /**
     * > gzhegow, вызывает произвольный колбэк с аргументами, если колбэк вернул не TRUE, бросает исключение, иначе $args[0]
     * > _assert_true('is_int', [ $input ], 'Переменная `input` должна быть числом')
     *
     * @param callable|bool $fn
     */
    public static function assert_true($fn, array $args = [], $error = '', ...$errors) // : mixed
    {
        $bool = is_bool($fn)
            ? $fn
            : (bool) call_user_func_array($fn, $args);

        if (true !== $bool) {
            if (('' === $error) || (null === $error)) {
                if (! $errors) {
                    $error = [ '[ ' . __FUNCTION__ . ' ] ' . static::php_dump($fn), $args ];
                }
            }

            throw static::php_throwable($error, ...$errors);
        }

        return $args[ 0 ] ?? null;
    }


    public static function parse_int($value) : ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            if (! is_numeric($value)) {
                return null;
            }
        }

        $valueOriginal = $value;

        if (! is_scalar($valueOriginal)) {
            if (null === ($_valueOriginal = static::parse_string($valueOriginal))) {
                return null;
            }

            if (! is_numeric($_valueOriginal)) {
                return null;
            }

            $valueOriginal = $_valueOriginal;
        }

        $_value = $valueOriginal;
        $status = @settype($_value, 'integer');

        if ($status) {
            if ((float) $valueOriginal !== (float) $_value) {
                return null;
            }

            return $_value;
        }

        return null;
    }

    public static function parse_num($value) // : ?int|float
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                return null;

            } else {
                return $value;
            }
        }

        if (is_string($value)) {
            if (! is_numeric($value)) {
                return null;
            }
        }

        $valueOriginal = $value;

        if (! is_scalar($valueOriginal)) {
            if (null === ($_valueOriginal = static::parse_string($valueOriginal))) {
                return null;
            }

            if (! is_numeric($_valueOriginal)) {
                return null;
            }

            $valueOriginal = $_valueOriginal;
        }

        $_value = $valueOriginal;

        $_valueInt = $_value;
        $statusInt = @settype($_valueInt, 'integer');

        $_valueFloat = $_value;
        $statusFloat = @settype($_valueFloat, 'float');

        if ($statusInt) {
            if ($_valueFloat === (float) $_valueInt) {
                return $_valueInt;
            }
        }

        if ($statusFloat) {
            return $_valueFloat;
        }

        return null;
    }


    public static function parse_string($value) : ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (
            is_null($value)
            || is_array($value)
            || is_resource($value)
        ) {
            return null;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $_value = (string) $value;

                return $_value;
            }

            return null;
        }

        $_value = $value;
        $status = @settype($_value, 'string');

        if ($status) {
            return $_value;
        }

        return null;
    }

    public static function parse_astring($value) : ?string
    {
        if (null === ($_value = static::parse_string($value))) {
            return null;
        }

        if ('' === $_value) {
            return null;
        }

        return $_value;
    }
}
