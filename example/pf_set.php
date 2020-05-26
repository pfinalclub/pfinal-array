<?php
/**
 * Created by PhpStorm.
 * User: PFinal南丞
 * Email: Lampxiezi@163.com
 * Date: 2020/5/26
 * Time: 14:11
 */
require __DIR__ . '/../vendor/autoload.php';

use pf\arr\PFarr;

$arr = [
    ['name' => ['ddf', 'emd', 'dd' => ['test']]],
    'sex' => '女'
];
PFarr::dd(PFarr::pf_set($arr, '0.name.dd', '大爷'));
