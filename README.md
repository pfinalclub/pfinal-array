# pfinal-array

![](https://img.shields.io/apm/l/vim-mode.svg)
[![](https://img.shields.io/badge/Downloads-4k-red.svg)](https://packagist.org/packages/nancheng/pfinal-array)


**Note:** ```PHP``` ```PHPArray``` ```Validator```

这是一个PHP数组操作中间件,对 PHP 数组的常用操作进行封装
目前包括以下方法：

- del_val()     删除数组中的某个值
- keyExists()   判断数组中是否有这个键
- get()         根据键名获取数组中的某个值,支持点语法
- pf_arr_sort() 数组冒泡排序
- tree()        二级数组树结构化(不递归)
- getTree()     多级数组结构化(不递归)
- pf_array_unique()  多维数组去重 
- array_depth()  检测数组的维度

- pf_encode()  数据格式转换

- pf_array_flatten() 将多维折叠数组变为一维
- pf_is_list() 判断PHP数组是否索引数组

- pf_array_rand_by_weight() 根据权重获取随机区间返回ID


## 安装

通过 Composer 安装：

    php composer.phar require nancheng/pfinal-array
---

## 使用

```php

    require './vendor/autoload.php';
    use pf\arr\PFarr;
    
    PFarr::pf_array_unique($arr);
    PFarr::pf_array_col
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

### 其他

继续完善
