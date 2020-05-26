<?php
/**
 * Created by PhpStorm.
 * User: PFinal南丞
 * Email: Lampxiezi@163.com
 * Date: 2020/5/26
 * Time: 14:45
 */
require __DIR__ . '/../vendor/autoload.php';

use pf\arr\PFarr;

$array = [100, 200, 300, ['300', '400']];

$value = PFarr::pf_array_first($array, function ($value, $key) {
    return $value >= 350;
});
PFarr::dd($value);

