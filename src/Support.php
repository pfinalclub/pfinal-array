<?php

declare(strict_types=1);

namespace pf\arr;

use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;

final class Support
{
    public static function isList(array $data): bool
    {
        $index = 0;
        foreach ($data as $key => $_) {
            if ($key !== $index) {
                return false;
            }
            $index++;
        }

        return true;
    }

    public static function segments(string $path): array
    {
        if ($path === '') {
            return [];
        }

        return explode('.', $path);
    }

    public static function getPath(array $data, array $segments, mixed $default = null): mixed
    {
        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    public static function hasPath(array $data, array $segments): bool
    {
        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return false;
            }
            $data = $data[$segment];
        }

        return true;
    }

    public static function setPath(array $data, array $segments, mixed $value): array
    {
        if ($segments === []) {
            if (!is_array($value)) {
                throw new InvalidArgumentException('The root value must be an array.');
            }

            return $value;
        }

        $key = array_shift($segments);
        if ($segments === []) {
            $data[$key] = $value;

            return $data;
        }

        $child = $data[$key] ?? [];
        if (!is_array($child)) {
            $child = [];
        }
        $data[$key] = self::setPath($child, $segments, $value);

        return $data;
    }

    public static function forgetPath(array $data, array $segments): array
    {
        if ($segments === []) {
            return [];
        }

        $key = array_shift($segments);
        if (!array_key_exists($key, $data)) {
            return $data;
        }
        if ($segments === []) {
            unset($data[$key]);

            return $data;
        }
        if (!is_array($data[$key])) {
            return $data;
        }
        $data[$key] = self::forgetPath($data[$key], $segments);

        return $data;
    }

    public static function resolveKey(mixed $item, string|callable $key): mixed
    {
        if (is_string($key)) {
            if (is_array($item)) {
                if (!array_key_exists($key, $item)) {
                    throw new InvalidArgumentException(sprintf('Missing key [%s].', $key));
                }

                return $item[$key];
            }
            if (is_object($item)) {
                return $item->{$key} ?? null;
            }

            return null;
        }

        return $key($item);
    }

    public static function identity(mixed $value): string
    {
        if (is_array($value)) {
            $encoded = '';
            foreach ($value as $key => $item) {
                $encoded .= self::identity($key) . self::identity($item);
            }

            return 'a' . strlen($encoded) . ':' . $encoded;
        }
        if (is_object($value)) {
            return 'o' . spl_object_id($value) . ';';
        }
        if (is_float($value)) {
            if (is_nan($value)) {
                return 'fnan;';
            }
            if (is_infinite($value)) {
                return $value > 0 ? 'finf;' : 'f-inf;';
            }
            $encoded = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);

            return 'f' . (is_string($encoded) ? $encoded : sprintf('%.17G', $value)) . ';';
        }
        if (is_int($value)) {
            return 'i' . $value . ';';
        }
        if (is_string($value)) {
            return 's' . strlen($value) . ':' . $value;
        }
        if (is_bool($value)) {
            return $value ? 'b1;' : 'b0;';
        }
        if ($value === null) {
            return 'n;';
        }

        return 'u' . gettype($value) . ';';
    }

    public static function arrayKey(mixed $key): int|string
    {
        if (is_int($key) || is_string($key)) {
            return $key;
        }
        if (is_bool($key)) {
            return $key ? 'true' : 'false';
        }
        if ($key === null) {
            return '';
        }
        if (is_float($key)) {
            return is_nan($key) ? 'NAN' : (string) $key;
        }

        throw new InvalidArgumentException('Key must be a scalar.');
    }

    public static function flatten(array $data, ?int $depth, int $level = 0): array
    {
        $result = [];
        foreach ($data as $value) {
            if (is_array($value) && ($depth === null || $level < $depth)) {
                foreach (self::flatten($value, $depth, $level + 1) as $item) {
                    $result[] = $item;
                }
                continue;
            }
            $result[] = $value;
        }

        return $result;
    }

    public static function combinations(array $items, int $size): array
    {
        $count = count($items);
        if ($size < 0 || $size > $count) {
            return [];
        }
        if ($size === 0) {
            return [[]];
        }

        $result = [];
        $walk = function (int $start, array $chosen) use (&$walk, $items, $size, $count, &$result): void {
            if (count($chosen) === $size) {
                $result[] = $chosen;

                return;
            }
            $need = $size - count($chosen);
            for ($i = $start; $i <= $count - $need; $i++) {
                $next = $chosen;
                $next[] = $items[$i];
                $walk($i + 1, $next);
            }
        };
        $walk(0, []);

        return $result;
    }

    public static function tree(
        array $list,
        string $idKey,
        string $parentKey,
        string $childrenKey,
        mixed $rootParent
    ): array {
        $indexed = [];
        foreach ($list as $item) {
            if (!is_array($item) || !array_key_exists($idKey, $item)) {
                continue;
            }
            $indexed[$item[$idKey]] = $item;
        }

        $childrenOf = [];
        foreach ($indexed as $id => $item) {
            $parent = array_key_exists($parentKey, $item) ? $item[$parentKey] : null;
            $childrenOf[self::treeKey($parent)][] = $id;
        }

        $build = function (mixed $parentId, array $stack) use (&$build, $indexed, $childrenOf, $childrenKey): array {
            $nodes = [];
            foreach ($childrenOf[self::treeKey($parentId)] ?? [] as $id) {
                $marker = self::treeKey($id);
                if (isset($stack[$marker])) {
                    continue;
                }
                $node = $indexed[$id];
                $next = $stack;
                $next[$marker] = true;
                $children = $build($id, $next);
                if ($children !== []) {
                    $node[$childrenKey] = $children;
                }
                $nodes[] = $node;
            }

            return $nodes;
        };

        return $build($rootParent, []);
    }

    private static function treeKey(mixed $value): string
    {
        if (is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value) === 1)) {
            return 'n:' . (int) $value;
        }

        return self::identity($value);
    }

    public static function flattenTree(array $nodes, string $childrenKey): array
    {
        $result = [];
        foreach ($nodes as $node) {
            if (!is_array($node)) {
                continue;
            }
            $children = $node[$childrenKey] ?? [];
            unset($node[$childrenKey]);
            $result[] = $node;
            if (is_array($children) && $children !== []) {
                foreach (self::flattenTree($children, $childrenKey) as $child) {
                    $result[] = $child;
                }
            }
        }

        return $result;
    }

    public static function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === false || $value === '' || $value === [];
    }

    public static function compact(array $data, bool $deep): array
    {
        foreach ($data as $key => $value) {
            if ($deep && is_array($value)) {
                $value = self::compact($value, true);
                $data[$key] = $value;
            }
            if (self::isEmptyValue($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    public static function toObject(array $data): object
    {
        $object = new \stdClass();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::isList($value)
                    ? array_map(fn ($item) => is_array($item) ? self::toObjectOrList($item) : $item, $value)
                    : self::toObject($value);
            }
            $object->{(string) $key} = $value;
        }

        return $object;
    }

    public static function toObjectOrList(array $data): mixed
    {
        if (self::isList($data)) {
            return array_map(fn ($item) => is_array($item) ? self::toObjectOrList($item) : $item, $data);
        }

        return self::toObject($data);
    }

    public static function toXml(array $data, string $root): string
    {
        self::assertXmlName($root);
        $xml = new SimpleXMLElement(sprintf('<?xml version="1.0" encoding="UTF-8"?><%s/>', $root));
        self::appendXml($xml, $data);
        $output = $xml->asXML();
        if ($output === false) {
            throw new RuntimeException('Failed to encode XML.');
        }

        return $output;
    }

    public static function toCsv(array $data, string $separator, string $enclosure, string $newline): string
    {
        if ($data === []) {
            return '';
        }

        $rows = array_values($data);
        $first = $rows[0];
        $lines = [];
        if (is_array($first) && !self::isList($first)) {
            $headers = array_keys($first);
            $lines[] = self::csvLine($headers, $separator, $enclosure);
            foreach ($rows as $row) {
                $line = [];
                foreach ($headers as $header) {
                    $line[] = is_array($row) ? ($row[$header] ?? '') : '';
                }
                $lines[] = self::csvLine($line, $separator, $enclosure);
            }
        } else {
            foreach ($rows as $row) {
                $lines[] = self::csvLine(is_array($row) ? array_values($row) : [$row], $separator, $enclosure);
            }
        }

        return implode($newline, $lines);
    }

    private static function appendXml(SimpleXMLElement $xml, array $data): void
    {
        foreach ($data as $key => $value) {
            $name = is_int($key) ? 'item' : (string) $key;
            self::assertXmlName($name);
            if (!is_array($value) && is_object($value)) {
                throw new InvalidArgumentException('XML values must be scalars or arrays.');
            }
            $child = $xml->addChild($name);
            if (!$child instanceof SimpleXMLElement) {
                throw new RuntimeException(sprintf('Failed to add XML element [%s].', $name));
            }
            if (is_array($value)) {
                self::appendXml($child, $value);
                continue;
            }
            $child[0] = self::scalarToString($value);
        }
    }

    private static function assertXmlName(string $name): void
    {
        if (!preg_match('/^[A-Za-z_][\w.-]*$/', $name)) {
            throw new InvalidArgumentException(sprintf('Invalid XML name [%s].', $name));
        }
    }

    private static function scalarToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private static function csvLine(array $fields, string $separator, string $enclosure): string
    {
        $encoded = [];
        foreach ($fields as $field) {
            if (is_array($field) || is_object($field)) {
                $field = json_encode($field, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $text = self::scalarToString($field);
            $text = str_replace($enclosure, $enclosure . $enclosure, $text);
            $encoded[] = $enclosure . $text . $enclosure;
        }

        return implode($separator, $encoded);
    }

    public static function listIndex(int $index, int $count): ?int
    {
        if ($count === 0) {
            return null;
        }
        if ($index < 0) {
            $index += $count;
        }
        if ($index < 0 || $index >= $count) {
            return null;
        }

        return $index;
    }

    public static function deepMerge(array $left, array $right): array
    {
        foreach ($right as $key => $value) {
            if (
                is_string($key)
                && is_array($value)
                && array_key_exists($key, $left)
                && is_array($left[$key])
                && !self::isList($value)
                && !self::isList($left[$key])
            ) {
                $left[$key] = self::deepMerge($left[$key], $value);
                continue;
            }
            $left[$key] = $value;
        }

        return $left;
    }

    public static function dot(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value) && $value !== []) {
                foreach (self::dot($value, $path) as $childPath => $child) {
                    $result[$childPath] = $child;
                }
                continue;
            }
            $result[$path] = $value;
        }

        return $result;
    }
}
