<?php

declare(strict_types=1);

namespace pf\arr;

function get(string $path, mixed $default = null): callable
{
    return fn (array $data): mixed => Support::getPath($data, Support::segments($path), $default);
}

function set(string $path, mixed $value): callable
{
    return fn (array $data): array => Support::setPath($data, Support::segments($path), $value);
}

function update(string $path, callable $updater, mixed $default = null): callable
{
    return function (array $data) use ($path, $updater, $default): array {
        $current = has($path)($data) ? get($path)($data) : $default;

        return set($path, $updater($current))($data);
    };
}

function forget(string $path): callable
{
    return fn (array $data): array => Support::forgetPath($data, Support::segments($path));
}

function pull(string $path, mixed $default = null): callable
{
    return function (array $data) use ($path, $default): array {
        return [
            'value' => get($path, $default)($data),
            'array' => forget($path)($data),
        ];
    };
}

function only(array|string $keys, string ...$more): callable
{
    $list = is_array($keys) ? array_merge(array_values($keys), $more) : array_merge([$keys], $more);

    return function (array $data) use ($list): array {
        $result = [];
        foreach ($list as $key) {
            if (array_key_exists($key, $data)) {
                $result[$key] = $data[$key];
            }
        }

        return $result;
    };
}

function except(array|string $keys, string ...$more): callable
{
    $list = is_array($keys) ? array_merge(array_values($keys), $more) : array_merge([$keys], $more);

    return function (array $data) use ($list): array {
        foreach ($list as $key) {
            unset($data[$key]);
        }

        return $data;
    };
}

function dot(): callable
{
    return fn (array $data): array => Support::dot($data);
}

function undot(): callable
{
    return function (array $data): array {
        $result = [];
        foreach ($data as $path => $value) {
            $result = set((string) $path, $value)($result);
        }

        return $result;
    };
}
