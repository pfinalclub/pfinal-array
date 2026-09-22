<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function first(?callable $predicate = null, mixed $default = null): callable
{
    return function (array $data) use ($predicate, $default): mixed {
        if ($predicate === null) {
            foreach ($data as $item) {
                return $item;
            }

            return $default;
        }

        return find($predicate, $default)($data);
    };
}

function last(?callable $predicate = null, mixed $default = null): callable
{
    return function (array $data) use ($predicate, $default): mixed {
        if ($predicate === null) {
            if ($data === []) {
                return $default;
            }

            return $data[array_key_last($data)];
        }

        return find($predicate, $default)(array_reverse($data, true));
    };
}

function find(callable $predicate, mixed $default = null): callable
{
    return function (array $data) use ($predicate, $default): mixed {
        foreach ($data as $key => $value) {
            if ($predicate($value, $key)) {
                return $value;
            }
        }

        return $default;
    };
}

function findLast(callable $predicate, mixed $default = null): callable
{
    return fn (array $data): mixed => find($predicate, $default)(array_reverse($data, true));
}

function take(int $count): callable
{
    return function (array $data) use ($count): array {
        $items = array_values($data);
        if ($count < 0) {
            return array_slice($items, $count);
        }

        return array_slice($items, 0, $count);
    };
}

function drop(int $count): callable
{
    return function (array $data) use ($count): array {
        if ($count <= 0) {
            return array_values($data);
        }

        return array_slice(array_values($data), $count);
    };
}

function slice(int $offset, ?int $length = null): callable
{
    return fn (array $data): array => array_slice(array_values($data), $offset, $length);
}

function chunk(int $size): callable
{
    if ($size < 1) {
        throw new InvalidArgumentException('Chunk size must be at least 1.');
    }

    return fn (array $data): array => array_chunk(array_values($data), $size);
}

function partition(callable $predicate): callable
{
    return function (array $data) use ($predicate): array {
        $passed = [];
        $rejected = [];
        foreach ($data as $key => $value) {
            if ($predicate($value, $key)) {
                $passed[$key] = $value;
                continue;
            }
            $rejected[$key] = $value;
        }

        return [$passed, $rejected];
    };
}

function reverse(): callable
{
    return fn (array $data): array => array_reverse($data, true);
}

function append(mixed $value): callable
{
    return function (array $data) use ($value): array {
        $data[] = $value;

        return $data;
    };
}

function prepend(mixed $value): callable
{
    return fn (array $data): array => array_merge([$value], $data);
}

function concat(array $other): callable
{
    return fn (array $data): array => array_merge($data, $other);
}

function insert(int $position, mixed $value): callable
{
    return function (array $data) use ($position, $value): array {
        $items = array_values($data);
        $count = count($items);
        $index = $position < 0 ? $count + $position : $position;
        if ($index < 0) {
            $index = 0;
        }
        if ($index > $count) {
            $index = $count;
        }

        return array_merge(
            array_slice($items, 0, $index),
            [$value],
            array_slice($items, $index)
        );
    };
}

function unique(): callable
{
    return function (array $data): array {
        $result = [];
        $seen = [];
        foreach ($data as $key => $value) {
            $token = Support::identity($value);
            if (isset($seen[$token])) {
                continue;
            }
            $seen[$token] = true;
            $result[$key] = $value;
        }

        return $result;
    };
}

function uniqueBy(callable $keyFn): callable
{
    return function (array $data) use ($keyFn): array {
        $result = [];
        $seen = [];
        foreach ($data as $key => $value) {
            $token = Support::identity($keyFn($value, $key));
            if (isset($seen[$token])) {
                continue;
            }
            $seen[$token] = true;
            $result[$key] = $value;
        }

        return $result;
    };
}

function sortBy(callable $keyFn, string $direction = 'asc'): callable
{
    $direction = strtolower($direction);
    if (!in_array($direction, ['asc', 'desc'], true)) {
        throw new InvalidArgumentException('Direction must be asc or desc.');
    }

    return function (array $data) use ($keyFn, $direction): array {
        uasort($data, function (mixed $left, mixed $right) use ($keyFn, $direction): int {
            $compared = $keyFn($left) <=> $keyFn($right);

            return $direction === 'desc' ? -$compared : $compared;
        });

        return $data;
    };
}

function shuffle(): callable
{
    return function (array $data): array {
        $keys = array_keys($data);
        \shuffle($keys);
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $data[$key];
        }

        return Support::isList($data) ? array_values($result) : $result;
    };
}

function combinations(int $size): callable
{
    return fn (array $data): array => Support::combinations(array_values($data), $size);
}

function nth(int $index, mixed $default = null): callable
{
    return function (array $data) use ($index, $default): mixed {
        $items = array_values($data);
        $resolved = Support::listIndex($index, count($items));
        if ($resolved === null) {
            return $default;
        }

        return $items[$resolved];
    };
}

function findIndex(callable $predicate): callable
{
    return function (array $data) use ($predicate): int|string|null {
        foreach ($data as $key => $value) {
            if ($predicate($value, $key)) {
                return $key;
            }
        }

        return null;
    };
}

function findLastIndex(callable $predicate): callable
{
    return function (array $data) use ($predicate): int|string|null {
        foreach (array_reverse($data, true) as $key => $value) {
            if ($predicate($value, $key)) {
                return $key;
            }
        }

        return null;
    };
}

function without(mixed ...$values): callable
{
    return function (array $data) use ($values): array {
        $result = [];
        foreach ($data as $key => $value) {
            foreach ($values as $blocked) {
                if ($value === $blocked) {
                    continue 2;
                }
            }
            $result[$key] = $value;
        }

        return $result;
    };
}

function keys(): callable
{
    return fn (array $data): array => array_keys($data);
}

function values(): callable
{
    return fn (array $data): array => array_values($data);
}

function pad(int $size, mixed $value): callable
{
    return fn (array $data): array => array_pad(array_values($data), $size, $value);
}

function intersperse(mixed $separator): callable
{
    return function (array $data) use ($separator): array {
        $items = array_values($data);
        if (count($items) < 2) {
            return $items;
        }
        $result = [];
        foreach ($items as $index => $item) {
            if ($index > 0) {
                $result[] = $separator;
            }
            $result[] = $item;
        }

        return $result;
    };
}

function window(int $size, int $step = 1): callable
{
    if ($size < 1 || $step < 1) {
        throw new InvalidArgumentException('Window size and step must be at least 1.');
    }

    return function (array $data) use ($size, $step): array {
        $items = array_values($data);
        $count = count($items);
        $result = [];
        for ($offset = 0; $offset + $size <= $count; $offset += $step) {
            $result[] = array_slice($items, $offset, $size);
        }

        return $result;
    };
}

function rotate(int $steps): callable
{
    return function (array $data) use ($steps): array {
        $items = array_values($data);
        $count = count($items);
        if ($count === 0) {
            return [];
        }
        $offset = $steps % $count;
        if ($offset < 0) {
            $offset += $count;
        }
        if ($offset === 0) {
            return $items;
        }

        return array_merge(array_slice($items, $offset), array_slice($items, 0, $offset));
    };
}

function move(int $from, int $to): callable
{
    return function (array $data) use ($from, $to): array {
        $items = array_values($data);
        $count = count($items);
        $source = Support::listIndex($from, $count);
        if ($source === null) {
            return $items;
        }
        $target = $to < 0 ? $count + $to : $to;
        if ($target < 0) {
            $target = 0;
        }
        if ($target >= $count) {
            $target = $count - 1;
        }
        $item = $items[$source];
        $rest = array_merge(array_slice($items, 0, $source), array_slice($items, $source + 1));

        return array_merge(array_slice($rest, 0, $target), [$item], array_slice($rest, $target));
    };
}

function removeAt(int $index): callable
{
    return function (array $data) use ($index): array {
        $items = array_values($data);
        $resolved = Support::listIndex($index, count($items));
        if ($resolved === null) {
            return $items;
        }

        return array_merge(array_slice($items, 0, $resolved), array_slice($items, $resolved + 1));
    };
}

function updateAt(int $index, callable $updater): callable
{
    return function (array $data) use ($index, $updater): array {
        $items = array_values($data);
        $resolved = Support::listIndex($index, count($items));
        if ($resolved === null) {
            return $items;
        }
        $items[$resolved] = $updater($items[$resolved], $resolved);

        return $items;
    };
}

function sortKeys(string $direction = 'asc'): callable
{
    $direction = strtolower($direction);
    if (!in_array($direction, ['asc', 'desc'], true)) {
        throw new InvalidArgumentException('Direction must be asc or desc.');
    }

    return function (array $data) use ($direction): array {
        if ($direction === 'desc') {
            krsort($data);
        } else {
            ksort($data);
        }

        return $data;
    };
}

function orderBy(string|callable|array ...$criteria): callable
{
    $parsed = [];
    foreach ($criteria as $criterion) {
        if (is_array($criterion)) {
            $key = $criterion[0] ?? null;
            $direction = strtolower((string) ($criterion[1] ?? 'asc'));
        } else {
            $key = $criterion;
            $direction = 'asc';
        }
        if ((!is_string($key) && !is_callable($key)) || !in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('orderBy expects a key, a callable, or a [key, direction] pair.');
        }
        $parsed[] = [$key, $direction];
    }
    if ($parsed === []) {
        throw new InvalidArgumentException('orderBy expects at least one criterion.');
    }

    return function (array $data) use ($parsed): array {
        $valueOf = function (mixed $item, string|callable $key): mixed {
            if (is_string($key)) {
                return is_array($item) ? ($item[$key] ?? null) : null;
            }

            return $key($item);
        };
        uasort($data, function (mixed $left, mixed $right) use ($parsed, $valueOf): int {
            foreach ($parsed as [$key, $direction]) {
                $compared = $valueOf($left, $key) <=> $valueOf($right, $key);
                if ($compared === 0) {
                    continue;
                }

                return $direction === 'desc' ? -$compared : $compared;
            }

            return 0;
        });

        return $data;
    };
}
