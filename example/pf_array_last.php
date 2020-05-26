<?php
/**
 * Created by PhpStorm.
 * User: PFinal南丞
 * Email: Lampxiezi@163.com
 * Date: 2020/5/26
 * Time: 14:48
 */
require __DIR__ . '/../vendor/autoload.php';

use pf\arr\PFarr;

$array = [100, 200, 300];

$value = PFarr::pf_array_last($array, function ($value, $key) {
    return $value >= 300;
});
PFarr::dd($value);
