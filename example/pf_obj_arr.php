<?php
/**
 * Created by PhpStorm.
 * User: 南丞
 * Date: 2019/1/24
 * Time: 13:51
 *
 *
 *                      _ooOoo_
 *                     o8888888o
 *                     88" . "88
 *                     (| ^_^ |)
 *                     O\  =  /O
 *                  ____/`---'\____
 *                .'  \\|     |//  `.
 *               /  \\|||  :  |||//  \
 *              /  _||||| -:- |||||-  \
 *              |   | \\\  -  /// |   |
 *              | \_|  ''\---/''  |   |
 *              \  .-\__  `-`  ___/-. /
 *            ___`. .'  /--.--\  `. . ___
 *          ."" '<  `.___\_<|>_/___.'  >'"".
 *        | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *        \  \ `-.   \_ __\ /__ _/   .-` /  /
 *  ========`-.____`-.___\_____/___.-`____.-'========
 *                       `=---='
 *  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *           佛祖保佑       永无BUG     永不修改
 *
 */

require __DIR__ . '/../vendor/autoload.php';

use pf\arr\PFarr;

class obj
{

}

$obj = new obj();
$obj->body           = 'another post';
$obj->id             = 21;
$obj->approved       = true;
$obj->favorite_count = 1;
$obj->status         = NULL;
//echo json_encode($obj);
PFarr::dd(PFarr::pf_obj_arr($obj));