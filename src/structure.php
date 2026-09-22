<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function groupBy(string|callable ...$keys): callable
{
    if ($keys === []) {
        throw new InvalidArgumentException('groupBy expects at least one key.');
    }

    return function (array $data) use ($keys): array {
        $key = array_shift($keys);
        $grouped = [];
        foreach ($data as $value) {
            $grouped[Support::arrayKey(Support::resolveKey($value, $key))][] = $value;
        }
        if ($keys === []) {
            return $grouped;
        }
        $next = groupBy(...$keys);
        foreach ($grouped as $groupKey => $items) {
            $grouped[$groupKey] = $next($items);
        }

        return $grouped;
    };
}

function keyBy(string|callable $key): callable
{
    return function (array $data) use ($key): array {
        $result = [];
        foreach ($data as $value) {
            $result[Support::arrayKey(Support::resolveKey($value, $key))] = $value;
        }

        return $result;
    };
}

function pluck(string $valueKey, ?string $indexKey = null): callable
{
    return fn (array $data): array => array_column($data, $valueKey, $indexKey);
}

function flatten(?int $depth = null): callable
{
    return fn (array $data): array => Support::flatten($data, $depth);
}

function fromPairs(): callable
{
    return function (array $data): array {
        $result = [];
        foreach ($data as $pair) {
            if (!is_array($pair) || !array_key_exists(0, $pair) || !array_key_exists(1, $pair)) {
                throw new InvalidArgumentException('Each pair must be a two-element list.');
            }
            $result[Support::arrayKey($pair[0])] = $pair[1];
        }

        return $result;
    };
}

function toPairs(): callable
{
    return function (array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = [$key, $value];
        }

        return $result;
    };
}

function transpose(): callable
{
    return function (array $data): array {
        $result = [];
        foreach ($data as $rowKey => $row) {
            if (!is_array($row)) {
                $row = [$row];
            }
            foreach ($row as $column => $value) {
                $result[$column][$rowKey] = $value;
            }
        }

        return $result;
    };
}

function tree(
    string $idKey = 'id',
    string $parentKey = 'parent_id',
    string $childrenKey = 'items',
    mixed $rootParent = 0
): callable {
    return fn (array $data): array => Support::tree($data, $idKey, $parentKey, $childrenKey, $rootParent);
}

function flattenTree(string $childrenKey = 'items'): callable
{
    return fn (array $data): array => Support::flattenTree($data, $childrenKey);
}

function where(array $matches): callable
{
    return filter(function (mixed $row) use ($matches): bool {
        if (!is_array($row)) {
            return false;
        }
        foreach ($matches as $key => $expected) {
            if (!array_key_exists($key, $row) || $row[$key] !== $expected) {
                return false;
            }
        }

        return true;
    });
}

function whereIn(string $key, array $values): callable
{
    return filter(function (mixed $row) use ($key, $values): bool {
        return is_array($row) && array_key_exists($key, $row) && in_array($row[$key], $values, true);
    });
}

function whereNotIn(string $key, array $values): callable
{
    return filter(function (mixed $row) use ($key, $values): bool {
        return is_array($row) && array_key_exists($key, $row) && !in_array($row[$key], $values, true);
    });
}

function zip(array ...$others): callable
{
    return function (array $data) use ($others): array {
        $columns = [array_values($data)];
        foreach ($others as $other) {
            $columns[] = array_values($other);
        }
        $length = min(array_map('count', $columns));
        $result = [];
        for ($index = 0; $index < $length; $index++) {
            $row = [];
            foreach ($columns as $column) {
                $row[] = $column[$index];
            }
            $result[] = $row;
        }

        return $result;
    };
}

function unzip(): callable
{
    return function (array $data): array {
        $columns = transpose()($data);

        return array_map(
            fn (array $column): array => array_values($column),
            array_values($columns)
        );
    };
}

function invert(): callable
{
    return function (array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            if (!is_int($value) && !is_string($value)) {
                throw new InvalidArgumentException('invert values must be strings or ints.');
            }
            if (array_key_exists($value, $result)) {
                throw new InvalidArgumentException(sprintf('Duplicate value [%s].', (string) $value));
            }
            $result[$value] = $key;
        }

        return $result;
    };
}

function merge(array $other): callable
{
    return fn (array $data): array => Support::deepMerge($data, $other);
}
