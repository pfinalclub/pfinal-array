<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;

function compact(bool $deep = false): callable
{
    return fn (array $data): array => Support::compact($data, $deep);
}

function defaults(array $defaults): callable
{
    return fn (array $data): array => array_replace($defaults, $data);
}

function renameKeys(array $map): callable
{
    return function (array $data) use ($map): array {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = array_key_exists($key, $map) ? $map[$key] : $key;
            if (!is_int($newKey) && !is_string($newKey)) {
                throw new InvalidArgumentException('Rename target must be a string or int.');
            }
            if (array_key_exists($newKey, $result)) {
                throw new InvalidArgumentException(sprintf('Duplicate key [%s] after rename.', (string) $newKey));
            }
            $result[$newKey] = $value;
        }

        return $result;
    };
}
