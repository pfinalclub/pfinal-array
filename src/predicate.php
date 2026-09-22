<?php

declare(strict_types=1);

namespace pf\arr;

function every(callable $predicate): callable
{
    return function (array $data) use ($predicate): bool {
        foreach ($data as $key => $value) {
            if (!$predicate($value, $key)) {
                return false;
            }
        }

        return true;
    };
}

function some(callable $predicate): callable
{
    return function (array $data) use ($predicate): bool {
        foreach ($data as $key => $value) {
            if ($predicate($value, $key)) {
                return true;
            }
        }

        return false;
    };
}

function none(callable $predicate): callable
{
    return fn (array $data): bool => !some($predicate)($data);
}

function has(string $path): callable
{
    return fn (array $data): bool => Support::hasPath($data, Support::segments($path));
}

function contains(mixed $needle, bool $strict = true): callable
{
    return fn (array $data): bool => in_array($needle, $data, $strict);
}

function isList(): callable
{
    return fn (array $data): bool => Support::isList($data);
}

function isAssoc(): callable
{
    return fn (array $data): bool => $data !== [] && !Support::isList($data);
}

function depth(): callable
{
    return function (array $data): int {
        $max = 1;
        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }
            $current = depth()($value) + 1;
            if ($current > $max) {
                $max = $current;
            }
        }

        return $max;
    };
}

function isEmpty(): callable
{
    return fn (array $data): bool => $data === [];
}

function equals(array $other): callable
{
    return fn (array $data): bool => $data === $other;
}
