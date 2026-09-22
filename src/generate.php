<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function range(int $from, int $to, int $step = 1): array
{
    if ($step === 0) {
        throw new InvalidArgumentException('Step must not be zero.');
    }
    $span = $to - $from;
    if (($span > 0 && $step < 0) || ($span < 0 && $step > 0)) {
        return [];
    }
    $count = intdiv(abs($span), abs($step)) + 1;
    if ($count > 100000) {
        throw new InvalidArgumentException('Range is too large.');
    }

    $result = [];
    if ($step > 0) {
        for ($current = $from; $current <= $to; $current += $step) {
            $result[] = $current;
        }
    } else {
        for ($current = $from; $current >= $to; $current += $step) {
            $result[] = $current;
        }
    }

    return $result;
}

function repeat(mixed $value, int $times): array
{
    if ($times < 0) {
        throw new InvalidArgumentException('Repeat count must be at least 0.');
    }
    if ($times > 100000) {
        throw new InvalidArgumentException('Repeat count is too large.');
    }

    return array_fill(0, $times, $value);
}

function wrap(mixed $value): array
{
    return is_array($value) ? $value : [$value];
}
