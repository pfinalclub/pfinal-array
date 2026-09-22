<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function sample(int $count = 1): callable
{
    return function (array $data) use ($count): array {
        if ($count <= 0 || $data === []) {
            return [];
        }
        $count = min($count, count($data));
        $keys = array_rand($data, $count);
        if ($count === 1) {
            $keys = [$keys];
        }
        $result = [];
        foreach ($keys as $key) {
            $result[] = $data[$key];
        }

        return $result;
    };
}

function sampleWeighted(callable $weightFn): callable
{
    return function (array $data) use ($weightFn): mixed {
        $total = 0.0;
        $weights = [];
        foreach ($data as $key => $item) {
            $weight = $weightFn($item, $key);
            if (!is_int($weight) && !is_float($weight) && !(is_string($weight) && is_numeric($weight))) {
                throw new InvalidArgumentException('Weight must be a non-negative number.');
            }
            $weight = (float) $weight;
            if ($weight < 0) {
                throw new InvalidArgumentException('Weight must be a non-negative number.');
            }
            $weights[$key] = $weight;
            $total += $weight;
        }
        if ($total <= 0.0) {
            return null;
        }

        $target = (random_int(0, 1_000_000_000) / 1_000_000_000) * $total;
        $cursor = 0.0;
        $last = null;
        foreach ($data as $key => $item) {
            if ($weights[$key] === 0.0) {
                continue;
            }
            $cursor += $weights[$key];
            $last = $item;
            if ($target < $cursor) {
                return $item;
            }
        }

        return $last;
    };
}
