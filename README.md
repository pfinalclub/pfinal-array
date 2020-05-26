# pfinal-array

[![](https://img.shields.io/github/issues/pfinalclub/pfinal-array?style=flat-square)](https://github.com/pfinalclub/pfinal-array)
[![](https://img.shields.io/github/forks/pfinalclub/pfinal-array?style=flat-square)](https://github.com/pfinalclub/pfinal-array)
[![](https://img.shields.io/github/stars/pfinalclub/pfinal-array?style=flat-square)](https://github.com/pfinalclub/pfinal-array)
[![](https://img.shields.io/github/license/pfinalclub/pfinal-array?style=flat-square)](https://github.com/pfinalclub/pfinal-array)

**Note:** ```PHP``` ```PHPArray``` ```Validator```

这是一个PHP数组操作增强组件,对 PHP 数组的常用操作进行封装

目前包括以下方法：

|  函数名   | 函数描述 |
|  ----  | ---- |
| pf_del_val()  | 删除数组中的某个值 |
| pf_del_val() |删除数组中的某个值|
| pf_key_exists() |   判断数组中是否有这个键|
| pf_get() |         根据键名获取数组中的某个值,支持点语法|
| pf_set() |         设置数组元素值支持点语法|
| pf_arr_sort() |   数组排序|
| pf_tree() |        二级数组树结构化(不递归)|
| pf_get_tree() |     多级数组结构化(不递归)|
| pf_array_unique() |   多维数组去重 |
| pf_array_depth() |       检测数组的维度|
| pf_encode() |         数据格式转换支持 数组转 'json','xml','csv','serialize'|
| pf_array_flatten() |        将多维折叠数组变为一维|
| pf_is_list() |              判断PHP数组是否索引数组|
| pf_array_rand_by_weight() | 根据权重获取随机区间返回ID|
| pf_rand_val() |      随机获取数组中的元素|
| pf_rand_weighted() | 按权重 随机返回数组的值|
| pf_array_shuffle() | 随机打乱数组(支持多维数组)|
| pf_array_insert() |  在数组中的给定位置插入元素|
| pf_array_diff_both() |    返回两个数组中不同的元素|
| pf_array_group_by() | 按指定的键对数组依次分组|
| pf_array_null() |    把数组中的null转换成空字符串|
| pf_count_element() |   统计数组中元素出现的次数|
| pf_map() |   重组数组|
| pf_exists() |  判断数组中某个键有木有值|
| pf_arr_group_by() |  按指定值给数组分组|
| pf_arr_sort_by_key() |  按指定键给数组排序|
| pf_arr_remove_empty() |  递归过滤多维数组中 空白字符，负值，false，null|
| pf_date_indexed() | 生成一个日期数组|
| pf_date_assoc() | 产生一个关联数组|
| pf_array_where() | 使用给定闭包对数组进行过滤|
| pf_array_first() | 获取数组的第一个元素|
| pf_array_last() |  获取数组的最后一个元素|

## 安装

通过 Composer 安装：

```composer
  composer require nancheng/pfinal-array
```
---

## 使用

```php

    require './vendor/autoload.php';
    use pf\arr\PFarr;
    // 调用方法
```

## 例子



*多维数组去重*

```php
    $arr = [1,54,'a',45,12,'c',1,1,12,[1,1,'a',['a','b','a']]];
    $arr = PFarr::pf_array_unique($arr);
    echo '<pre>';
    print_r($arr);
        
    
    // 结果
    Array
    (
        [0] => 1
        [1] => 54
        [2] => a
        [3] => 45
        [4] => 12
        [5] => c
        [9] => Array
            (
                [0] => 1
                [2] => a
                [3] => Array
                    (
                        [0] => a
                        [1] => b
                    )
    
            )
    
    )
```

*获取指定列的数据*

```php
$result = PFarr::pf_array_col($records, 'first_name', 'id');
    print_r($result);
```

*按指定的键对数组依次分组*

```php
$records = [
    [
        'city'  => '上海',
        'age'   => 18,
        'name'  => '马二'
    ],
    [
        'city'  => '上海',
        'age'   => 20,
        'name'  => '翠花'
    ]
];

//按照 city 分组 
$arr = PFarr::pf_array_group_by($records,'city');

//按照 city 分组 完成 之后 再按照  age 分组
   
$arr1 = PFarr::pf_array_group_by($records,'city','age');

```
*组词算法*
```php
<?php
//组词算法  
$arr=array('裤子','牛仔','低腰','加肥');  
$count=count($arr);  
for($i=1;$i<=$count;$i++){  
    $temp[$i]=PFarr::pf_diy_words($arr,$i);  
}  
PFarr::dd($temp);
  

```

*统计数组元素在数组中出现的次数*

```php
<?php
$arr_one = ['a','b','c','d'];
$arr_two = ['a','b','a','c','b','d'];

PFarr::dd(PFarr::pf_count_element($arr_one));

/*
  返回
  Array
   (
       [a] => 1
       [b] => 1
       [c] => 1
       [d] => 1
   )
 */

PFarr::dd(PFarr::pf_count_element($arr_two));
 /*
   返回
 Array
 (
     [a] => 2
     [b] => 2
     [c] => 1
     [d] => 1
 )
  
 */


```
*从多维数组或对象数组构建一个映射(键-值对)。*

```php
<?php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
];

PFarr::dd(PFarr::pf_map($array,'id','name'));

/*
  返回:
 Array
 (
     [123] => aaa
     [124] => bbb
     [345] => ccc
 )
 */


PFarr::dd(PFarr::pf_map($array,'id','name','class'));
/*
返回
Array
(
    [x] => Array
        (
            [123] => aaa
            [124] => bbb
        )

    [y] => Array
        (
            [345] => ccc
        )

)
*/

```
查看更多例子:[更多](./example/README.md)

### 其他

继续完善
