<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function map(callable $mapper): callable
{
    return function (array $data) use ($mapper): array {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = $mapper($value, $key);
        }

        return $result;
    };
}

function flatMap(callable $mapper): callable
{
    return function (array $data) use ($mapper): array {
        $result = [];
        foreach ($data as $key => $value) {
            $mapped = $mapper($value, $key);
            if (is_array($mapped)) {
                foreach ($mapped as $item) {
                    $result[] = $item;
                }
                continue;
            }
            $result[] = $mapped;
        }

        return $result;
    };
}

function filter(callable $predicate): callable
{
    return fn (array $data): array => array_filter($data, $predicate, ARRAY_FILTER_USE_BOTH);
}

function reject(callable $predicate): callable
{
    return filter(fn (mixed $value, int|string $key): bool => !$predicate($value, $key));
}

function reduce(callable $reducer, mixed $initial = null): callable
{
    return function (array $data) use ($reducer, $initial): mixed {
        $carry = $initial;
        foreach ($data as $key => $value) {
            $carry = $reducer($carry, $value, $key);
        }

        return $carry;
    };
}

function reduceRight(callable $reducer, mixed $initial = null): callable
{
    return function (array $data) use ($reducer, $initial): mixed {
        $carry = $initial;
        foreach (array_reverse($data, true) as $key => $value) {
            $carry = $reducer($carry, $value, $key);
        }

        return $carry;
    };
}

function mapKeys(callable $mapper): callable
{
    return function (array $data) use ($mapper): array {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = $mapper($key, $value);
            if (!is_int($newKey) && !is_string($newKey)) {
                throw new InvalidArgumentException('mapKeys must return a string or int.');
            }
            if (array_key_exists($newKey, $result)) {
                throw new InvalidArgumentException(sprintf('Duplicate key [%s].', (string) $newKey));
            }
            $result[$newKey] = $value;
        }

        return $result;
    };
}
