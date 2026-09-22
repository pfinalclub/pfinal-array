<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function countBy(callable $keyFn): callable
{
    return function (array $data) use ($keyFn): array {
        $result = [];
        foreach ($data as $key => $value) {
            $group = Support::arrayKey($keyFn($value, $key));
            $result[$group] = ($result[$group] ?? 0) + 1;
        }

        return $result;
    };
}

function sumBy(callable $valueFn): callable
{
    return function (array $data) use ($valueFn): int|float {
        $sum = 0;
        foreach ($data as $key => $value) {
            $number = $valueFn($value, $key);
            if (!is_int($number) && !is_float($number) && !(is_string($number) && is_numeric($number))) {
                throw new InvalidArgumentException('sumBy expects numeric values.');
            }
            $sum += $number + 0;
        }

        return $sum;
    };
}

function minBy(callable $valueFn): callable
{
    return function (array $data) use ($valueFn): mixed {
        $found = false;
        $best = null;
        $bestValue = null;
        foreach ($data as $key => $value) {
            $current = $valueFn($value, $key);
            if (!$found || ($current <=> $bestValue) < 0) {
                $found = true;
                $best = $value;
                $bestValue = $current;
            }
        }

        return $best;
    };
}

function maxBy(callable $valueFn): callable
{
    return function (array $data) use ($valueFn): mixed {
        $found = false;
        $best = null;
        $bestValue = null;
        foreach ($data as $key => $value) {
            $current = $valueFn($value, $key);
            if (!$found || ($current <=> $bestValue) > 0) {
                $found = true;
                $best = $value;
                $bestValue = $current;
            }
        }

        return $best;
    };
}

function avgBy(callable $valueFn): callable
{
    return function (array $data) use ($valueFn): ?float {
        if ($data === []) {
            return null;
        }

        return ((float) sumBy($valueFn)($data)) / count($data);
    };
}

function length(): callable
{
    return fn (array $data): int => count($data);
}

function medianBy(callable $valueFn): callable
{
    return function (array $data) use ($valueFn): int|float|null {
        if ($data === []) {
            return null;
        }
        $numbers = [];
        foreach ($data as $key => $value) {
            $number = $valueFn($value, $key);
            if (!is_int($number) && !is_float($number) && !(is_string($number) && is_numeric($number))) {
                throw new InvalidArgumentException('medianBy expects numeric values.');
            }
            $numbers[] = $number + 0;
        }
        sort($numbers);
        $count = count($numbers);
        $middle = intdiv($count, 2);
        if ($count % 2 === 1) {
            return $numbers[$middle];
        }

        return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
    };
}
