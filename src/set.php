<?php

declare(strict_types=1);

namespace pf\arr;

function union(array $other): callable
{
    return fn (array $data): array => array_values(unique()(array_merge(array_values($data), array_values($other))));
}

function intersect(array $other): callable
{
    return function (array $data) use ($other): array {
        $result = [];
        foreach ($data as $value) {
            foreach ($other as $candidate) {
                if ($value === $candidate) {
                    $result[] = $value;
                    break;
                }
            }
        }

        return array_values(unique()($result));
    };
}

function diff(array $other): callable
{
    return function (array $data) use ($other): array {
        $result = [];
        foreach ($data as $value) {
            $found = false;
            foreach ($other as $candidate) {
                if ($value === $candidate) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = $value;
            }
        }

        return array_values(unique()($result));
    };
}

function diffBoth(array $other): callable
{
    return fn (array $data): array => array_merge(diff($other)($data), diff($data)($other));
}

function diffBy(callable $keyFn, array $other): callable
{
    return function (array $data) use ($keyFn, $other): array {
        $blocked = [];
        foreach ($other as $key => $item) {
            $blocked[Support::identity($keyFn($item, $key))] = true;
        }
        $result = [];
        foreach ($data as $key => $item) {
            if (isset($blocked[Support::identity($keyFn($item, $key))])) {
                continue;
            }
            $result[] = $item;
        }

        return $result;
    };
}

function intersectBy(callable $keyFn, array $other): callable
{
    return function (array $data) use ($keyFn, $other): array {
        $allowed = [];
        foreach ($other as $key => $item) {
            $allowed[Support::identity($keyFn($item, $key))] = true;
        }
        $result = [];
        $seen = [];
        foreach ($data as $key => $item) {
            $token = Support::identity($keyFn($item, $key));
            if (!isset($allowed[$token]) || isset($seen[$token])) {
                continue;
            }
            $seen[$token] = true;
            $result[] = $item;
        }

        return $result;
    };
}

function unionBy(callable $keyFn, array $other): callable
{
    return function (array $data) use ($keyFn, $other): array {
        $result = [];
        $seen = [];
        foreach ([$data, $other] as $source) {
            foreach ($source as $key => $item) {
                $token = Support::identity($keyFn($item, $key));
                if (isset($seen[$token])) {
                    continue;
                }
                $seen[$token] = true;
                $result[] = $item;
            }
        }

        return $result;
    };
}
