# pfinal-array

函数式 PHP 数组工具。变换函数不修改传入的数组，每次都返回新值，并用 `pipe` 串成管道。

要求 **PHP >= 8.0**。这是 2.0，调用方式与 1.x 的 `PFarr::pf_*` 不兼容。

## 安装

```bash
composer require nancheng/pfinal-array
```

## 调用方式

高阶函数先接收键名或回调，返回一个「数组进、结果出」的函数。数据放在最后一次调用里。`pipe` 从左到右把上一步的结果交给下一步。`compose` 从右到左组合成一个函数。

```php
require './vendor/autoload.php';

use function pf\arr\filter;
use function pf\arr\pipe;
use function pf\arr\pluck;
use function pf\arr\sortBy;
use function pf\arr\unique;

$names = pipe(
    $rows,
    filter(fn (array $row): bool => $row['active']),
    pluck('name'),
    unique(),
    sortBy(fn (string $name): string => $name),
);
```

单独调用时写成 `unique()($rows)`。`range`、`repeat`、`wrap`、`dateRange`、`dateMap` 不接收上游数组，直接返回结果。

约定：

- 回调参数一般是 `($value, $key)`。`mapKeys` 是 `($key, $value)`。`reduce` / `reduceRight` 是 `($carry, $value, $key)`。
- 字符串参数始终表示字段名。即便存在同名函数，也不会被当成回调。回调请传闭包。
- 比较使用 `===`。`1` 和 `'1'` 是不同的值。
- `map`、`filter`、`reject`、`unique`、`sortBy`、`orderBy`、`partition`、`reverse` 保留原键。`take`、`drop`、`slice`、`chunk`、`window`、`insert`、`move`、`removeAt`、`updateAt`、`rotate`、`pad` 按列表处理，结果从 0 重新编号。
- PHP 数组会把数字字符串键收成整数。`groupBy`、`countBy`、`keyBy` 里，整数 `1` 和字符串 `"1"` 是同一组，`null` 和 `''` 也是同一组。
- 分组或重命名产生重复键时，`renameKeys`、`mapKeys`、`invert` 抛出 `InvalidArgumentException`。`keyBy` 保留最后一条。

可运行示例见 [example/demo.php](example/demo.php)。

## 组合

| 函数 | 说明 |
| --- | --- |
| `pipe($value, ...$steps)` | 从左到右执行。某一步如果不再返回数组，后面的数组函数会收到这个值。 |
| `compose(...$steps)` | 返回一个函数，调用时从右到左执行。 |

## 变换

| 函数 | 说明 |
| --- | --- |
| `map($mapper)` | 逐项映射，保留键。 |
| `flatMap($mapper)` | 映射后把返回的数组展开一层，结果重新编号。返回的非数组值原样放入。 |
| `mapKeys($mapper)` | 用 `($key, $value)` 生成新键。新键必须是 `string` 或 `int`，重复则抛错。 |
| `filter($predicate)` | 保留回调返回真的项，保留键。 |
| `reject($predicate)` | 去掉回调返回真的项，保留键。 |
| `reduce($reducer, $initial = null)` | 从左向右折叠。 |
| `reduceRight($reducer, $initial = null)` | 从右向左折叠，键仍是原键。 |

## 判断

这些函数返回布尔值或整数，适合放在管道末尾。

| 函数 | 说明 |
| --- | --- |
| `every($predicate)` | 全部满足。空数组为 `true`。 |
| `some($predicate)` | 至少一项满足。空数组为 `false`。 |
| `none($predicate)` | 没有一项满足。空数组为 `true`。 |
| `has($path)` | 点路径上的键是否存在。值为 `null` 也算存在。空路径返回 `true`。 |
| `contains($needle, $strict = true)` | 当前这一层是否包含该值，不进入子数组。 |
| `isList()` | 是否为从 0 开始的连续列表。空数组是列表。 |
| `isAssoc()` | 是否为非空、且不是列表的数组。 |
| `isEmpty()` | 是否为 `[]`。 |
| `equals($other)` | 是否与另一份数组 `===`。键的顺序也参与比较。 |
| `depth()` | 嵌套层数。空数组和一维数组都是 `1`。 |

## 路径

路径用点语法，例如 `user.name`。

| 函数 | 说明 |
| --- | --- |
| `get($path, $default = null)` | 取值。缺键时返回 `$default`；键存在且值为 `null` 时返回 `null`。空路径返回整个数组。 |
| `set($path, $value)` | 写入并返回新数组。中间缺的层级会建成数组。空路径时 `$value` 必须是数组，并整个替换。 |
| `update($path, $updater, $default = null)` | 把路径上的当前值交给回调再写回。缺键时回调收到 `$default`。 |
| `forget($path)` | 删掉该路径。路径不存在时原样返回。空路径返回 `[]`。 |
| `pull($path, $default = null)` | 返回 `['value' => 取出的值, 'array' => 删掉该路径后的数组]`。 |
| `only($keys, ...$more)` | 只保留这些顶层键，顺序按参数。可写 `only('id', 'name')` 或 `only(['id'], 'name')`。 |
| `except($keys, ...$more)` | 去掉这些顶层键。 |
| `dot()` | 展开成点路径。列表下标会写成 `tags.0`。空数组作为叶子保留。键名本身不要包含 `.`。 |
| `undot()` | 把点路径还原成嵌套数组。 |

```php
use function pf\arr\dot;
use function pf\arr\get;
use function pf\arr\pull;

get('user.name', '未知')($data);

pull('user.name')($data);
// ['value' => '南丞', 'array' => [...]]

dot()(['user' => ['name' => '南丞'], 'tags' => ['php']]);
// ['user.name' => '南丞', 'tags.0' => 'php']
```

## 序列

下标支持负数，`-1` 表示最后一项。`take` 的负数表示从末尾取。`drop` 小于等于 0 时返回重新编号后的全部元素。

| 函数 | 说明 |
| --- | --- |
| `first($predicate = null, $default = null)` | 第一项。传入回调时返回第一个匹配项，没有则返回 `$default`。 |
| `last($predicate = null, $default = null)` | 最后一项，规则同 `first`。 |
| `nth($index, $default = null)` | 按下标取值。越界返回 `$default`。 |
| `find($predicate, $default = null)` | 第一个匹配的值。 |
| `findLast($predicate, $default = null)` | 最后一个匹配的值。 |
| `findIndex($predicate)` | 第一个匹配的原键，没有则 `null`。 |
| `findLastIndex($predicate)` | 最后一个匹配的原键，没有则 `null`。 |
| `take($count)` | 取前若干项。负数从末尾取。 |
| `drop($count)` | 丢掉前若干项。 |
| `slice($offset, $length = null)` | 切片，语义同 `array_slice`。 |
| `chunk($size)` | 按固定长度分块。`$size < 1` 时立刻抛错。 |
| `window($size, $step = 1)` | 滑动窗口，只保留完整窗口。`$size` 和 `$step` 都要 `>= 1`。 |
| `partition($predicate)` | 返回 `[$passed, $rejected]`，两边都保留原键。 |
| `reverse()` | 反转并保留键。 |
| `rotate($steps)` | 向左旋转 `$steps` 位。负数向右。 |
| `append($value)` | 在末尾追加一个元素。 |
| `prepend($value)` | 在开头插入一个元素。 |
| `concat($other)` | 连接。使用 `array_merge`，右侧的字符串键会覆盖左侧。 |
| `insert($position, $value)` | 在下标处插入一个元素。数组也会作为单个元素插入。越界会夹到两端。 |
| `move($from, $to)` | 把 `$from` 的元素移到最终下标 `$to`。`$from` 越界时只做重新编号。 |
| `removeAt($index)` | 删掉下标处的元素。越界时只做重新编号。 |
| `updateAt($index, $updater)` | 更新下标处的元素。越界时原样返回重新编号的列表。 |
| `without(...$values)` | 按 `===` 删掉这些值，保留其余键。 |
| `pad($size, $value)` | 补到指定长度。`$size` 为负时从左侧补，语义同 `array_pad`。 |
| `intersperse($separator)` | 在相邻元素之间插入分隔值。 |
| `keys()` | 全部键，重新编号。 |
| `values()` | 全部值，重新编号。 |
| `unique()` | 按 `===` 去重，保留第一次出现的键。 |
| `uniqueBy($keyFn)` | 按回调结果去重，保留第一次出现的项。 |
| `sortBy($keyFn, $direction = 'asc')` | 按回调排序，保留键。方向只能是 `asc` 或 `desc`。 |
| `sortKeys($direction = 'asc')` | 按键排序。 |
| `orderBy(...$criteria)` | 多条件排序，保留键。条件是字段名、闭包，或 `[字段或闭包, 'desc']`。 |
| `shuffle()` | 打乱。列表会重新编号，关联数组保留键。 |
| `combinations($size)` | 按原顺序生成组合。`$size` 为 0 时得到 `[[]]`；超出长度或小于 0 时得到 `[]`。 |

```php
use function pf\arr\orderBy;
use function pf\arr\pipe;
use function pf\arr\pluck;

$names = pipe(
    $rows,
    orderBy('city', ['age', 'desc']),
    pluck('name'),
);
```

## 聚合

`sumBy`、`avgBy`、`medianBy` 只接受数字。空数组时 `sumBy` 返回 `0`，`avgBy` 和 `medianBy` 返回 `null`。`minBy` / `maxBy` 返回原元素，空数组返回 `null`。偶数个元素时，`medianBy` 取中间两个数的平均值。

| 函数 | 说明 |
| --- | --- |
| `length()` | 元素个数。 |
| `countBy($keyFn)` | 按回调结果计数。 |
| `sumBy($valueFn)` | 求和。 |
| `minBy($valueFn)` | 回调值最小的那一项。 |
| `maxBy($valueFn)` | 回调值最大的那一项。 |
| `avgBy($valueFn)` | 平均值，类型是 `float`。 |
| `medianBy($valueFn)` | 中位数。 |

## 结构

`groupBy` 和 `keyBy` 的字符串参数是字段名。记录上缺这个键时抛错。`groupBy('city', 'age')` 会先按城市、再按年龄分组。

| 函数 | 说明 |
| --- | --- |
| `groupBy(...$keys)` | 分组。同一组里保留原记录。 |
| `keyBy($key)` | 用字段或回调做键。重复键保留最后一条。 |
| `where($matches)` | 保留字段与给定值全部 `===` 的记录。 |
| `whereIn($key, $values)` | 字段值在列表中。缺这个字段的记录会被排除。 |
| `whereNotIn($key, $values)` | 字段值不在列表中。缺这个字段的记录会被排除。 |
| `pluck($valueKey, $indexKey = null)` | 取一列，语义同 `array_column`。 |
| `flatten($depth = null)` | 展平。`$depth` 为 `null` 时全部展开，为 `1` 时只展开一层。 |
| `fromPairs()` | `[[键, 值], ...]` 转成关联数组。元素不是二元组时抛错。 |
| `toPairs()` | 关联数组转成 `[[键, 值], ...]`。 |
| `zip(...$others)` | 与其他列表按位置配对，长度取最短的那个。 |
| `unzip()` | 把配对列表拆回各列。 |
| `invert()` | 键和值对调。值必须是 `string` 或 `int`，重复则抛错。 |
| `merge($other)` | 两边都是关联数组时按键递归合并。遇到列表时，右侧按下标覆盖，不会拼接。 |
| `transpose()` | 行列互换。缺的单元格不会补 `null`。 |
| `tree($idKey = 'id', $parentKey = 'parent_id', $childrenKey = 'items', $rootParent = 0)` | 组装树。字符串 `"1"` 和整数 `1` 视为同一个主键。重复 `id` 保留后一条。成环时断开回路，不再继续下挂。 |
| `flattenTree($childrenKey = 'items')` | 把树展开成列表，并去掉子节点字段。 |

```php
use function pf\arr\groupBy;
use function pf\arr\merge;
use function pf\arr\tree;

groupBy('city', 'age')($rows);

merge(['a' => ['b' => 2, 'c' => 3]])(['a' => ['b' => 1], 'e' => 5]);
// ['a' => ['b' => 2, 'c' => 3], 'e' => 5]

tree()($nodes);
```

## 集合

`union`、`intersect`、`diff`、`diffBoth` 按 `===` 比较，结果去重并重新编号。`diffBoth` 先给出左侧独有的值，再给出右侧独有的值。

| 函数 | 说明 |
| --- | --- |
| `union($other)` | 并集，保留左侧里先出现的值。 |
| `intersect($other)` | 交集，顺序跟左侧走。 |
| `diff($other)` | 只在左侧出现的值。 |
| `diffBoth($other)` | 两侧各自独有的值。 |
| `diffBy($keyFn, $other)` | 丢掉左侧里、回调结果已在右侧出现过的项。左侧重复项会保留。 |
| `unionBy($keyFn, $other)` | 按回调结果取并集。先保留左侧，再补上右侧没有出现过的键。 |
| `intersectBy($keyFn, $other)` | 按回调结果取交集，保留左侧原记录。 |

## 规范化

| 函数 | 说明 |
| --- | --- |
| `compact($deep = false)` | 去掉 `null`、`false`、`''` 和 `[]`，保留 `0` 和 `'0'`。`$deep` 为 `true` 时递归，递归后变空的数组也会去掉。 |
| `defaults($defaults)` | 只填左侧没有的键，已有的值（包括 `null`）保持不变。 |
| `renameKeys($map)` | 按 `['旧键' => '新键']` 重命名。新键必须是 `string` 或 `int`，撞键则抛错。 |

## 随机

| 函数 | 说明 |
| --- | --- |
| `sample($count = 1)` | 不放回抽取。始终返回数组。`$count` 小于等于 0 时返回 `[]`，大于长度时取全部。 |
| `sampleWeighted($weightFn)` | 按权重抽一个元素。权重必须是非负数。总权重为 0 时返回 `null`。 |

## 编码

| 函数 | 说明 |
| --- | --- |
| `toJson($flags = JSON_UNESCAPED_UNICODE \| JSON_UNESCAPED_SLASHES)` | 编码为 JSON。失败时抛出 `RuntimeException`。 |
| `toXml($root = 'root')` | 编码为 XML。列表元素使用标签 `item`。非法标签名、以及对象值会抛错。 |
| `toCsv($separator = ',', $enclosure = '"', $newline = "\n")` | 关联数组行会用第一行的键做表头。纯列表则每行直接输出，不加表头。 |
| `toObject()` | 关联数组转成 `stdClass`，列表保持为数组。 |

## 直接生成

这些函数直接返回数组。`range` 和 `dateRange` 都包含结束值。步长不能停在原地或往回走，单次最多生成 100000 项。

| 函数 | 说明 |
| --- | --- |
| `range($from, $to, $step = 1)` | 整数序列。方向和步长相反时返回 `[]`。 |
| `repeat($value, $times)` | 重复一个值。`$times` 为 0 时返回 `[]`。 |
| `wrap($value)` | 非数组包成单项列表；已经是数组则原样返回。 |
| `dateRange($from, $to, $step = '+1 day', $format = 'Y-m-d')` | 日期列表。非法日期，或步长没有向前推进时抛错。 |
| `dateMap($from, $to, $default = null, $step = '+1 day', $format = 'Y-m-d')` | 以日期为键、`$default` 为值的关联数组。 |

```php
use function pf\arr\dateMap;
use function pf\arr\range;

range(1, 5, 2);                 // [1, 3, 5]
dateMap('2026-01-01', '2026-01-03', 0);
```

## 测试

```bash
composer install
composer test
```

库本身要求 PHP >= 8.0。测试使用 PHPUnit 11，运行测试需要 PHP >= 8.2。
