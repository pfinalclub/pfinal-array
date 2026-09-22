<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function dateRange(string $from, string $to, string $step = '+1 day', string $format = 'Y-m-d'): array
{
    $current = strtotime($from);
    $last = strtotime($to);
    if ($current === false || $last === false) {
        throw new InvalidArgumentException('Invalid date.');
    }

    $dates = [];
    $guard = 0;
    while ($current <= $last) {
        $dates[] = date($format, $current);
        $next = strtotime($step, $current);
        if ($next === false || $next <= $current) {
            throw new InvalidArgumentException('Date step must move forward.');
        }
        $current = $next;
        if (++$guard > 100000) {
            throw new InvalidArgumentException('Date range is too large.');
        }
    }

    return $dates;
}

function dateMap(
    string $from,
    string $to,
    mixed $default = null,
    string $step = '+1 day',
    string $format = 'Y-m-d'
): array {
    return array_fill_keys(dateRange($from, $to, $step, $format), $default);
}
