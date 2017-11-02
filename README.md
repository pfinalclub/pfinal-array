# pfinal-array
这是一个PHP数组操作中间件

---

*多维数组去重*

```php
    $arr = [1,54,'a',45,12,'c',1,1,12,[1,1,'a',['a','b','a']]];
    $arr = PFarr::pf_array_unique($arr);
        echo '<pre>';
        print_r($arr);
```

*获取指定列的数据*

```php
$result = PFarr::pf_array_col($records, 'first_name', 'id');
    print_r($result);
```