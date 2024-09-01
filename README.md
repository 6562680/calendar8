# Calendar

## Что это

В PHP работать с датами это сущий ад.
Каждое действие требует преобразовывать к дате в три-четыре строки, каждое может выбросить исключение, каждое светится в PHPStorm как предупреждение...

Этот пакет сделан, чтобы сделать работу с датами более удобной. Плюс в нем можно посмотреть, как сделать грамотное наследование имеющищегося в коробке PHP ужаса, чтобы этим управлять или добавить своё. 

## Old good times

Сначала ты просто пользуешься классами Date и уверен, что нужно просто "правильно готовить".

Потом ты знакомишься с Immutable, понимаешь, что сложность удваивается, пока не поймешь когда какое нужно выбирать. Начинаешь приводить одно в другое, и натыкаться на заботливо расставленные мины в виде исключений и обратной совместимости версий PHP.

Потом знакомишься с фреймворком Laravel, где используется пакет Carbon на уровне ядра, который на инициализацию со своими регулярками тратит 60 мс. Хочешь избавиться от него, и не можешь. Потом сможешь)

Когда наконец избавился, хочешь, чтобы в API твоя дата выглядела так, чтобы Frontend её мог без проблем парсить, и чтобы при этом не пришлось настраивать целый `symfony/serializer`.
Начинаешь наследоваться от имеющихся классов и понимаешь, что половина функций по прежнему возвращает встроенные объекты и сериализацию твою игнорирует.

К сожалению, даты совмещают как работу над ними, так и хранение состояния внутри, и постоянно косячат по микросекундам. Нужно быть трижды осторожным, чтобы написать стабильный код.

Это история про то, что ООП это та еще беда... Иногда нужно просто делать как в Javascript - наращивать объекты функционалом, использовать композицию... И тут же понимать, что композиция отвязывает тебя от Структур данных, и привязывает к Поведению. В общем танцы с бубном каждый день.

## Установка

```
composer require gzhegow/calendar;
```

## Пример

```php
<?php

use Gzhegow\Calendar\Lib;
use Gzhegow\Calendar\Calendar;


require_once __DIR__ . '/vendor/autoload.php';


// > настраиваем PHP
ini_set('memory_limit', '32M');

// > настраиваем обработку ошибок
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (error_reporting() & $errno) {
        throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
    }
});
set_exception_handler(function ($e) {
    var_dump(Lib::php_dump($e));
    var_dump($e->getMessage());
    var_dump(($e->getFile() ?? '{file}') . ': ' . ($e->getLine() ?? '{line}'));

    die();
});


// > создаем календарь
$calendar = new Calendar();

// > можно изменить классы дат на свои собственные реализации
// \Gzhegow\Calendar\Type::setInstance(new \Gzhegow\Calendar\Type());

// > создаем дату
$tests[ '_calendar_date' ] = $calendar->parseDateTime($datetime = 'now', $formats = null, $timezoneIfParsed = null);
Lib::assert_true('is_a', [ $tests[ '_calendar_date' ], DateTime::class ]);

// > создаем/распознаем дату
$tests[ '_calendar_date_immutable' ] = $calendar->parseDateTimeImmutable($datetime = 'now', $formats = null, $timezoneIfParsed = null);
Lib::assert_true('is_a', [ $tests[ '_calendar_date_immutable' ], DateTimeImmutable::class ]);

// > проводим действия над датой, чтобы убедится что Immutable работает
$tests[ '_calendar_date_immutable_add' ] = $tests[ '_calendar_date_immutable' ]->add(new \DateInterval('P1D'));
$tests[ '_calendar_date_immutable_sub' ] = $tests[ '_calendar_date_immutable' ]->sub(new \DateInterval('P1D'));
$tests[ '_calendar_date_immutable_modify' ] = $tests[ '_calendar_date_immutable' ]->modify('+ 10 hours');
Lib::assert_true('is_a', [ $tests[ '_calendar_date_immutable_add' ], DateTimeImmutable::class ]);
Lib::assert_true('is_a', [ $tests[ '_calendar_date_immutable_sub' ], DateTimeImmutable::class ]);
Lib::assert_true('is_a', [ $tests[ '_calendar_date_immutable_modify' ], DateTimeImmutable::class ]);
if ($tests[ '_calendar_date_immutable_add' ] === $tests[ '_calendar_date_immutable_sub' ]) throw new \RuntimeException();
if ($tests[ '_calendar_date_immutable_sub' ] === $tests[ '_calendar_date_immutable_modify' ]) throw new \RuntimeException();
if ($tests[ '_calendar_date_immutable_add' ] === $tests[ '_calendar_date_immutable_modify' ]) throw new \RuntimeException();

// > создает дату "сейчас", просто alias для _calendar_date($date)
$tests[ '_calendar_now' ] = $calendar->now($timezone = null);
$tests[ '_calendar_now_immutable' ] = $calendar->nowImmutable($timezone = null);
Lib::assert_true('is_a', [ $tests[ '_calendar_now' ], DateTime::class ]);
Lib::assert_true('is_a', [ $tests[ '_calendar_now_immutable' ], DateTimeImmutable::class ]);

// > создает/распознает временную зону
$tests[ '_calendar_timezone' ] = $calendar->parseDateTimeZone($timezone = 'UTC');
Lib::assert_true('is_a', [ $tests[ '_calendar_timezone' ], DateTimeZone::class ]);
Lib::assert_true(function () use ($tests) {
    return 'UTC' === $tests[ '_calendar_timezone' ]->getName();
});

// > создает/распознает интервал
$tests[ '_calendar_interval' ] = $calendar->parseDateInterval($interval = 'P0D', $formats = null);
Lib::assert_true('is_a', [ $tests[ '_calendar_interval' ], DateInterval::class ]);
Lib::assert_true(function () use ($tests) {
    return 'P0D' === $tests[ '_calendar_interval' ]->jsonSerialize();
});

// > возвращает разницу между датами
$now = $calendar->nowImmutable();
$past = $now->modify('- 10 hours');
$tests[ '_calendar_diff' ] = $calendar->diff($now, $past, $absolute = false); // : ?DateInterval;
Lib::assert_true('is_a', [ $tests[ '_calendar_diff' ], DateInterval::class ]);
Lib::assert_true(function () use ($tests) {
    return 'PT10H' === $tests[ '_calendar_diff' ]->jsonSerialize();
});

var_dump(json_encode($tests, JSON_PRETTY_PRINT));

// string(730) "{
//     "_calendar_date": "2024-05-09T19:47:39.074+03:00",
//     "_calendar_date_immutable": "2024-05-09T19:47:39.074+03:00",
//     "_calendar_date_immutable_add": "2024-05-10T19:47:39.074+03:00",
//     "_calendar_date_immutable_sub": "2024-05-08T19:47:39.074+03:00",
//     "_calendar_date_immutable_modify": "2024-05-10T05:47:39.074+03:00",
//     "_calendar_now": "2024-05-09T19:47:39.074+03:00",
//     "_calendar_now_immutable": "2024-05-09T19:47:39.074+03:00",
//     "_calendar_timezone": "UTC",
//     "_calendar_interval": "P0D",
//     "_calendar_diff": "PT10H"
// }"

$dump = [];
foreach ( $tests as $i => $test ) {
    $dump[ $i ] = Lib::php_dump($test);
}
var_dump($dump);

// array(13) {
//   ["_calendar_date"]=>
//   string(48) "{ object(Gzhegow\Calendar\Struct\DateTime # 8) }"
//   ["_calendar_date_immutable"]=>
//   string(57) "{ object(Gzhegow\Calendar\Struct\DateTimeImmutable # 9) }"
//   ["_calendar_date_immutable_add"]=>
//   string(58) "{ object(Gzhegow\Calendar\Struct\DateTimeImmutable # 10) }"
//   ["_calendar_date_immutable_sub"]=>
//   string(58) "{ object(Gzhegow\Calendar\Struct\DateTimeImmutable # 11) }"
//   ["_calendar_date_immutable_modify"]=>
//   string(57) "{ object(Gzhegow\Calendar\Struct\DateTimeImmutable # 7) }"
//   ["_calendar_now"]=>
//   string(49) "{ object(Gzhegow\Calendar\Struct\DateTime # 12) }"
//   ["_calendar_now_immutable"]=>
//   string(58) "{ object(Gzhegow\Calendar\Struct\DateTimeImmutable # 13) }"
//   string(58) "{ object(Gzhegow\Calendar\Struct\DateTimeImmutable # 15) }"
//   ["_calendar_timezone"]=>
//   string(53) "{ object(Gzhegow\Calendar\Struct\DateTimeZone # 16) }"
//   ["_calendar_interval"]=>
//   string(53) "{ object(Gzhegow\Calendar\Struct\DateInterval # 17) }"
//   ["_calendar_diff"]=>
//   string(53) "{ object(Gzhegow\Calendar\Struct\DateInterval # 20) }"
// }
```