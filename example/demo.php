<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use function pf\arr\filter;
use function pf\arr\groupBy;
use function pf\arr\pipe;
use function pf\arr\pluck;
use function pf\arr\sortBy;
use function pf\arr\unique;

$records = [
    ['city' => '上海', 'age' => 18, 'name' => '马二'],
    ['city' => '上海', 'age' => 20, 'name' => '翠花'],
    ['city' => '杭州', 'age' => 18, 'name' => '南丞'],
];

$result = pipe(
    $records,
    filter(fn (array $row): bool => $row['age'] >= 18),
    groupBy('city'),
    function (array $grouped): array {
        $names = [];
        foreach ($grouped as $city => $rows) {
            $names[$city] = pipe(
                $rows,
                pluck('name'),
                unique(),
                sortBy(fn (string $name): string => $name)
            );
        }

        return $names;
    }
);

print_r($result);
