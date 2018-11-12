<?php
/**
 * Created by PhpStorm.
 * User: 运营部
 * Date: 2018/11/12
 * Time: 12:59
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

namespace tests;


use pf\arr\PFarr;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function testArrayExists()
    {
        $data = [
            'k0' => 'v0',
            'k1' => [
                'k1-1' => 'v1-1'
            ],
            'complex_[name]_!@#$&%*^' => 'complex',
            'k2' => 'string'
        ];
        $this->assertEquals(true,PFarr::pf_exists('[k1][k1-1]',$data),'元素不在数组中');
        $this->assertEquals(true,PFarr::pf_exists('k1',$data),'元素不在数组中');
        $this->assertEquals(true,PFarr::pf_exists('k2',$data),'元素不在数组中');
        $this->assertEquals(true,PFarr::pf_exists('[k2][2]',$data),'元素不在数组中');
    }
}
