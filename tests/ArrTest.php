<?php

declare(strict_types=1);

namespace tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

use function pf\arr\append;
use function pf\arr\avgBy;
use function pf\arr\chunk;
use function pf\arr\combinations;
use function pf\arr\compact;
use function pf\arr\compose;
use function pf\arr\concat;
use function pf\arr\contains;
use function pf\arr\countBy;
use function pf\arr\dateMap;
use function pf\arr\dateRange;
use function pf\arr\defaults;
use function pf\arr\depth;
use function pf\arr\diff;
use function pf\arr\diffBoth;
use function pf\arr\diffBy;
use function pf\arr\drop;
use function pf\arr\every;
use function pf\arr\except;
use function pf\arr\filter;
use function pf\arr\find;
use function pf\arr\findLast;
use function pf\arr\first;
use function pf\arr\flatMap;
use function pf\arr\flatten;
use function pf\arr\flattenTree;
use function pf\arr\forget;
use function pf\arr\fromPairs;
use function pf\arr\get;
use function pf\arr\groupBy;
use function pf\arr\has;
use function pf\arr\insert;
use function pf\arr\intersect;
use function pf\arr\isAssoc;
use function pf\arr\isList;
use function pf\arr\keyBy;
use function pf\arr\last;
use function pf\arr\map;
use function pf\arr\maxBy;
use function pf\arr\minBy;
use function pf\arr\none;
use function pf\arr\only;
use function pf\arr\partition;
use function pf\arr\pipe;
use function pf\arr\pluck;
use function pf\arr\prepend;
use function pf\arr\pull;
use function pf\arr\reduce;
use function pf\arr\reject;
use function pf\arr\renameKeys;
use function pf\arr\reverse;
use function pf\arr\sample;
use function pf\arr\sampleWeighted;
use function pf\arr\set;
use function pf\arr\shuffle;
use function pf\arr\slice;
use function pf\arr\some;
use function pf\arr\sortBy;
use function pf\arr\sumBy;
use function pf\arr\take;
use function pf\arr\toCsv;
use function pf\arr\toJson;
use function pf\arr\toObject;
use function pf\arr\toPairs;
use function pf\arr\toXml;
use function pf\arr\transpose;
use function pf\arr\tree;
use function pf\arr\union;
use function pf\arr\unique;
use function pf\arr\uniqueBy;
use function pf\arr\update;
use function pf\arr\dot;
use function pf\arr\equals;
use function pf\arr\findIndex;
use function pf\arr\findLastIndex;
use function pf\arr\intersperse;
use function pf\arr\intersectBy;
use function pf\arr\invert;
use function pf\arr\isEmpty;
use function pf\arr\keys;
use function pf\arr\length;
use function pf\arr\mapKeys;
use function pf\arr\medianBy;
use function pf\arr\merge;
use function pf\arr\move;
use function pf\arr\nth;
use function pf\arr\orderBy;
use function pf\arr\pad;
use function pf\arr\range;
use function pf\arr\reduceRight;
use function pf\arr\removeAt;
use function pf\arr\repeat;
use function pf\arr\rotate;
use function pf\arr\sortKeys;
use function pf\arr\undot;
use function pf\arr\unionBy;
use function pf\arr\updateAt;
use function pf\arr\values;
use function pf\arr\where;
use function pf\arr\whereIn;
use function pf\arr\whereNotIn;
use function pf\arr\window;
use function pf\arr\without;
use function pf\arr\wrap;
use function pf\arr\zip;
use function pf\arr\unzip;

final class ArrTest extends TestCase
{
    public function testPipeAndCompose(): void
    {
        $rows = [
            ['name' => 'a', 'active' => false],
            ['name' => 'b', 'active' => true],
            ['name' => 'b', 'active' => true],
        ];

        $piped = pipe(
            $rows,
            filter(fn (array $row): bool => $row['active']),
            pluck('name'),
            unique(),
            sortBy(fn (string $name): string => $name)
        );
        self::assertSame(['b'], array_values($piped));

        $composed = compose(
            map(fn (int $n): int => $n + 1),
            filter(fn (int $n): bool => $n > 1)
        )([1, 2, 3]);
        self::assertSame([1 => 3, 2 => 4], $composed);
    }

    public function testTransformAndPredicates(): void
    {
        $data = ['a' => 1, 'b' => 2, 'c' => 3];
        self::assertSame(['a' => 2, 'b' => 3, 'c' => 4], map(fn (int $n): int => $n + 1)($data));
        self::assertSame([1, 10, 2, 20], flatMap(fn (int $n): array => [$n, $n * 10])([1, 2]));
        self::assertSame(['b' => 2], filter(fn (int $n): bool => $n === 2)($data));
        self::assertSame(['a' => 1, 'c' => 3], reject(fn (int $n): bool => $n === 2)($data));
        self::assertSame(6, reduce(fn (int $carry, int $n): int => $carry + $n, 0)($data));
        self::assertTrue(every(fn (int $n): bool => $n > 0)($data));
        self::assertFalse(every(fn (int $n): bool => $n > 1)($data));
        self::assertTrue(some(fn (int $n): bool => $n === 3)($data));
        self::assertTrue(none(fn (int $n): bool => $n < 0)($data));
        self::assertFalse(none(fn (int $n): bool => $n === 1)($data));
        self::assertTrue(contains(2)($data));
        self::assertFalse(contains('2')([1, 2]));
        self::assertTrue(contains('2', false)([1, 2]));
        self::assertTrue(isList()([1, 2]));
        self::assertTrue(isList()([]));
        self::assertFalse(isList()(['a' => 1]));
        self::assertFalse(isAssoc()([]));
        self::assertTrue(isAssoc()(['a' => 1]));
        self::assertSame(1, depth()([]));
        self::assertSame(3, depth()([1, [2, [3]]]));
    }

    public function testPathDoesNotMutateSource(): void
    {
        $data = ['user' => ['name' => '南丞', 'city' => '上海']];
        self::assertTrue(has('user.name')($data));
        self::assertFalse(has('user.age')($data));
        self::assertSame('南丞', get('user.name')($data));
        self::assertSame('未知', get('user.age', '未知')($data));
        self::assertNull(get('user.extra')(['user' => ['extra' => null]]));

        $next = set('user.city', '杭州')($data);
        self::assertSame('上海', $data['user']['city']);
        self::assertSame('杭州', $next['user']['city']);
        self::assertSame('南丞', $next['user']['name']);

        $updated = update('user.age', fn (int $age): int => $age + 1, 17)($data);
        self::assertSame(18, $updated['user']['age']);
        self::assertArrayNotHasKey('age', $data['user']);

        $forgotten = forget('user.city')($data);
        self::assertArrayHasKey('city', $data['user']);
        self::assertSame(['name' => '南丞'], $forgotten['user']);

        $pulled = pull('user.name')($data);
        self::assertSame('南丞', $pulled['value']);
        self::assertSame(['user' => ['city' => '上海']], $pulled['array']);
        self::assertSame('南丞', $data['user']['name']);

        self::assertSame(['name' => '南丞'], only('name')($data['user']));
        self::assertSame(['city' => '上海'], except(['name'])($data['user']));
    }

    public function testSequence(): void
    {
        $data = ['x' => 1, 'y' => 2, 'z' => 3, 'w' => 2];
        self::assertSame(1, first()($data));
        self::assertSame(2, last()($data));
        self::assertSame('empty', first(null, 'empty')([]));
        self::assertSame(2, find(fn (int $n): bool => $n === 2)($data));
        self::assertSame(2, findLast(fn (int $n): bool => $n === 2)($data));
        self::assertSame([1, 2], take(2)($data));
        self::assertSame([2], take(-1)($data));
        self::assertSame([2, 3, 2], drop(1)($data));
        self::assertSame([2, 3], slice(1, 2)($data));
        self::assertSame([[1, 2], [3, 2]], chunk(2)($data));
        [$passed, $rejected] = partition(fn (int $n): bool => $n > 1)($data);
        self::assertSame(['y' => 2, 'z' => 3, 'w' => 2], $passed);
        self::assertSame(['x' => 1], $rejected);
        self::assertSame(['w' => 2, 'z' => 3, 'y' => 2, 'x' => 1], reverse()($data));
        self::assertSame([1, 2, 9], append(9)([1, 2]));
        self::assertSame([0, 1, 2], prepend(0)([1, 2]));
        self::assertSame([1, 2, 3, 4], concat([3, 4])([1, 2]));
        self::assertSame([1, [2, 3], 4], insert(1, [2, 3])([1, 4]));
        self::assertSame([1, 2, 4, 3], insert(-1, 4)([1, 2, 3]));
        self::assertSame([0 => 1, 2 => '1'], unique()([1, 1, '1']));
        self::assertSame(
            [0 => ['id' => 1], 2 => ['id' => 2]],
            uniqueBy(fn (array $row): int => $row['id'])([['id' => 1], ['id' => 1, 'extra' => true], ['id' => 2]])
        );
        self::assertSame(['b' => 1, 'a' => 2], sortBy(fn (int $n): int => $n)(['a' => 2, 'b' => 1]));
        self::assertSame(['a' => 2, 'b' => 1], sortBy(fn (int $n): int => $n, 'desc')(['a' => 2, 'b' => 1]));

        $source = [1, 2, 3];
        $shuffled = shuffle()($source);
        $sorted = $shuffled;
        \sort($sorted);
        self::assertSame([1, 2, 3], $source);
        self::assertSame([1, 2, 3], $sorted);
        self::assertSame(
            [['裤子', '牛仔'], ['裤子', '低腰'], ['牛仔', '低腰']],
            combinations(2)(['裤子', '牛仔', '低腰'])
        );
    }

    public function testAggregateStructureAndSet(): void
    {
        $rows = [
            ['city' => '上海', 'age' => 18, 'name' => '马二', 'score' => 1],
            ['city' => '上海', 'age' => 20, 'name' => '翠花', 'score' => 3],
            ['city' => '杭州', 'age' => 18, 'name' => '南丞', 'score' => 2],
        ];
        self::assertSame(['上海' => 2, '杭州' => 1], countBy(fn (array $row): string => $row['city'])($rows));
        self::assertSame(6, sumBy(fn (array $row): int => $row['score'])($rows));
        self::assertSame(2.0, avgBy(fn (array $row): int => $row['score'])($rows));
        self::assertNull(avgBy(fn (int $n): int => $n)([]));
        self::assertSame($rows[0], minBy(fn (array $row): int => $row['score'])($rows));
        self::assertSame($rows[1], maxBy(fn (array $row): int => $row['score'])($rows));

        $grouped = groupBy('city', 'age')($rows);
        self::assertSame('马二', $grouped['上海'][18][0]['name']);
        self::assertSame('杭州', keyBy('name')($rows)['南丞']['city']);
        self::assertSame([1 => '马二', 3 => '翠花'], pluck('name', 'score')(array_slice($rows, 0, 2)));
        self::assertSame([1, 2, 3], flatten()([[1, [2]], 3]));
        self::assertSame([1, [2], 3], flatten(1)([[1, [2]], 3]));
        self::assertSame(['a' => 1, 'b' => 2], fromPairs()([['a', 1], ['b', 2]]));
        self::assertSame([['a', 1], ['b', 2]], toPairs()(['a' => 1, 'b' => 2]));
        self::assertSame(
            [0 => [0 => 1, 1 => 3], 1 => [0 => 2, 1 => 4]],
            transpose()([[1, 2], [3, 4]])
        );

        $nodes = [
            ['id' => 1, 'parent_id' => 0, 'name' => '根'],
            ['id' => 2, 'parent_id' => 1, 'name' => '子'],
            ['id' => 3, 'parent_id' => '1', 'name' => '次子'],
        ];
        $built = tree()($nodes);
        self::assertSame('根', $built[0]['name']);
        self::assertSame(['子', '次子'], pluck('name')($built[0]['items']));
        self::assertSame([1, 2, 3], pluck('id')(flattenTree()($built)));

        self::assertSame([1, 2, 3, 4], union([2, 3, 4])([1, 2]));
        self::assertSame([2], intersect([2, '2'])([1, 2, 2]));
        self::assertSame([1], diff([2])([1, 2, 2]));
        self::assertSame([1, 3], diffBoth([2, 3])([1, 2]));
        self::assertSame(
            [['id' => 2]],
            diffBy(fn (array $row): int => $row['id'], [['id' => 1]])([['id' => 1], ['id' => 2]])
        );
    }

    public function testNormalizeEncodeDateAndRandom(): void
    {
        $source = ['a' => '', 'b' => 0, 'c' => ['d' => null, 'e' => 'x', 'f' => []], 'g' => false];
        self::assertSame(['b' => 0, 'c' => ['d' => null, 'e' => 'x', 'f' => []]], compact()($source));
        self::assertSame(['b' => 0, 'c' => ['e' => 'x']], compact(true)($source));
        self::assertSame($source, $source);
        self::assertSame(['a' => 0, 'b' => 2], defaults(['a' => 0, 'b' => 9])(['b' => 2]));
        self::assertSame(['id' => 1, 'title' => 'x'], renameKeys(['name' => 'title'])(['id' => 1, 'name' => 'x']));

        self::assertSame('{"name":"南丞"}', toJson()(['name' => '南丞']));
        $xml = toXml('user')(['name' => '南丞', 'tags' => ['php']]);
        self::assertStringContainsString('<name>南丞</name>', $xml);
        self::assertStringContainsString('<item>php</item>', $xml);
        self::assertSame("\"name\",\"city\"\n\"马二\",\"上海\"", toCsv()([['name' => '马二', 'city' => '上海']]));
        $object = toObject()(['user' => ['name' => '南丞'], 'tags' => ['php', 'array']]);
        self::assertInstanceOf(stdClass::class, $object->user);
        self::assertSame('南丞', $object->user->name);
        self::assertSame(['php', 'array'], $object->tags);

        self::assertSame(['2026-01-01', '2026-01-02'], dateRange('2026-01-01', '2026-01-02'));
        self::assertSame(['2026-01-01' => 0, '2026-01-02' => 0], dateMap('2026-01-01', '2026-01-02', 0));

        $items = ['a', 'b', 'c'];
        $picked = sample(2)($items);
        self::assertCount(2, $picked);
        self::assertSame($items, $items);
        self::assertContains($picked[0], $items);
        self::assertSame('only', sampleWeighted(fn (string $item): int => $item === 'only' ? 1 : 0)(['skip', 'only']));
        self::assertNull(sampleWeighted(fn (): int => 0)(['a']));
    }

    public function testInvalidArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        chunk(0);
    }

    public function testCheckedEdges(): void
    {
        $collapsed = ['k' => 'v,string:m=integer:1'];
        $distinct = ['k' => 'v', 'm' => 1];
        self::assertCount(2, unique()([$collapsed, $distinct]));
        self::assertCount(2, unique()([INF, -INF]));

        $xml = toXml()(['name' => 'Tom & Jerry', 'note' => 'a<b>']);
        self::assertStringContainsString('Tom &amp; Jerry', $xml);
        self::assertStringContainsString('a&lt;b&gt;', $xml);

        $self = tree()([['id' => 0, 'parent_id' => 0, 'name' => '自']]);
        self::assertSame('自', $self[0]['name']);
        self::assertArrayNotHasKey('items', $self[0]);

        $cycled = tree('id', 'parent_id', 'items', 1)([
            ['id' => 1, 'parent_id' => 2, 'name' => '甲'],
            ['id' => 2, 'parent_id' => 1, 'name' => '乙'],
        ]);
        self::assertSame('乙', $cycled[0]['name']);
        self::assertSame('甲', $cycled[0]['items'][0]['name']);
        self::assertArrayNotHasKey('items', $cycled[0]['items'][0]);

        self::assertSame(['z' => 1], set('', ['z' => 1])(['a' => 1]));
        self::assertSame([], forget('')(['a' => 1]));
        self::assertSame(['a' => 1, 'b' => 2], only(['a'], 'b')(['a' => 1, 'b' => 2, 'c' => 3]));
        self::assertSame(['c' => 3], except(['a'], 'b')(['a' => 1, 'b' => 2, 'c' => 3]));

        $this->expectException(InvalidArgumentException::class);
        renameKeys(['a' => 'b'])(['a' => 1, 'b' => 2]);
    }

    public function testCommonAdditions(): void
    {
        $rows = [
            ['city' => '上海', 'age' => 18, 'name' => '马二'],
            ['city' => '上海', 'age' => 20, 'name' => '翠花'],
            ['city' => '杭州', 'age' => 18, 'name' => '南丞'],
        ];

        self::assertTrue(isEmpty()([]));
        self::assertFalse(isEmpty()([0]));
        self::assertTrue(equals(['a' => [1]])(['a' => [1]]));
        self::assertFalse(equals(['a' => 1])(['a' => '1']));
        self::assertSame(3, length()($rows));
        self::assertSame(18, medianBy(fn (array $row): int => $row['age'])($rows));
        self::assertSame(2.5, medianBy(fn (int $n): int => $n)([1, 2, 3, 4]));
        self::assertNull(medianBy(fn (int $n): int => $n)([]));

        self::assertSame('南丞', nth(-1)(pluck('name')($rows)));
        self::assertSame('缺', nth(9, '缺')([1]));
        self::assertSame(1, findIndex(fn (array $row): bool => $row['age'] === 20)($rows));
        self::assertSame(2, findLastIndex(fn (array $row): bool => $row['age'] === 18)($rows));
        self::assertSame([2 => 2], without(1, '1')([1, '1', 2]));
        self::assertSame(['b', 'a'], keys()(['b' => 1, 'a' => 2]));
        self::assertSame([1, 2], values()(['b' => 1, 'a' => 2]));
        self::assertSame([1, 2, 0, 0], pad(4, 0)([1, 2]));
        self::assertSame([0, 0, 1, 2], pad(-4, 0)([1, 2]));
        self::assertSame([1, '-', 2, '-', 3], intersperse('-')([1, 2, 3]));
        self::assertSame([[1, 2], [2, 3], [3, 4]], window(2)([1, 2, 3, 4]));
        self::assertSame([[1, 2], [3, 4]], window(2, 2)([1, 2, 3, 4]));
        self::assertSame([2, 3, 1], rotate(1)([1, 2, 3]));
        self::assertSame([3, 1, 2], rotate(-1)([1, 2, 3]));
        self::assertSame([2, 3, 1, 4], move(0, 2)([1, 2, 3, 4]));
        self::assertSame([1, 3], removeAt(1)([1, 2, 3]));
        self::assertSame([2, 2], updateAt(0, fn (int $n): int => $n + 1)([1, 2]));
        self::assertSame(['a' => 2, 'b' => 1], sortKeys()(['b' => 1, 'a' => 2]));
        self::assertSame(
            ['翠花', '马二', '南丞'],
            array_values(pluck('name')(orderBy('city', ['age', 'desc'])($rows)))
        );

        self::assertSame('cba', reduceRight(fn (string $carry, string $item): string => $carry . $item, '')(['a', 'b', 'c']));
        self::assertSame(['p_a' => 1], mapKeys(fn (string $key): string => 'p_' . $key)(['a' => 1]));

        self::assertSame(
            [['city' => '上海', 'age' => 18, 'name' => '马二']],
            array_values(where(['city' => '上海', 'age' => 18])($rows))
        );
        self::assertSame(['南丞'], array_values(pluck('name')(whereIn('city', ['杭州'])($rows))));
        self::assertCount(2, whereNotIn('city', ['杭州'])($rows));
        self::assertSame([[1, 3], [2, 4]], zip([3, 4])([1, 2, 9]));
        self::assertSame([[1, 2], [3, 4]], unzip()([[1, 3], [2, 4]]));
        self::assertSame(['x' => 'a', 'y' => 'b'], invert()(['a' => 'x', 'b' => 'y']));
        self::assertSame(
            ['a' => ['b' => 2, 'c' => 3], 'e' => 5, 'd' => 4],
            merge(['a' => ['b' => 2, 'c' => 3], 'd' => 4])(['a' => ['b' => 1], 'e' => 5])
        );
        self::assertSame(
            ['user.name' => '南丞', 'tags.0' => 'php'],
            dot()(['user' => ['name' => '南丞'], 'tags' => ['php']])
        );
        self::assertSame(
            ['user' => ['name' => '南丞'], 'tags' => ['php']],
            undot()(['user.name' => '南丞', 'tags.0' => 'php'])
        );

        self::assertSame(
            [['id' => 1, 'name' => '左']],
            intersectBy(fn (array $row): int => $row['id'], [['id' => 1], ['id' => 3]])([
                ['id' => 1, 'name' => '左'],
                ['id' => 2, 'name' => '右'],
            ])
        );
        self::assertSame(
            [['id' => 1], ['id' => 2]],
            unionBy(fn (array $row): int => $row['id'], [['id' => 2], ['id' => 1, 'extra' => true]])([['id' => 1]])
        );

        self::assertSame([1, 3, 5], range(1, 5, 2));
        self::assertSame([3, 2, 1], range(3, 1, -1));
        self::assertSame(['a', 'a', 'a'], repeat('a', 3));
        self::assertSame(['a'], wrap('a'));
        self::assertSame([1, 2], wrap([1, 2]));
    }
}
