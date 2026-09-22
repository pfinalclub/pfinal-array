<?php

declare(strict_types=1);

namespace pf\arr;

use RuntimeException;

function toJson(int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES): callable
{
    return function (array $data) use ($flags): string {
        $json = json_encode($data, $flags);
        if ($json === false) {
            throw new RuntimeException(json_last_error_msg());
        }

        return $json;
    };
}

function toXml(string $root = 'root'): callable
{
    return fn (array $data): string => Support::toXml($data, $root);
}

function toCsv(string $separator = ',', string $enclosure = '"', string $newline = "\n"): callable
{
    return fn (array $data): string => Support::toCsv($data, $separator, $enclosure, $newline);
}

function toObject(): callable
{
    return fn (array $data): object => Support::toObject($data);
}
