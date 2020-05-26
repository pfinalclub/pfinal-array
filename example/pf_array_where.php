<?php
/**
 * Created by PhpStorm.
 * User: PFinal南丞
 * Email: Lampxiezi@163.com
 * Date: 2020/5/26
 * Time: 14:36
 */
require __DIR__ . '/../vendor/autoload.php';

use pf\arr\PFarr;

$array = [100, '200', 300, '400', 500];

$array = PFarr::pf_array_where($array, function ($value, $key) {
    return is_string($value);
});
PFarr::dd($array);
