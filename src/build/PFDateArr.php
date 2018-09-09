<?php
/**
 * Created by PhpStorm.
 * User: 运营部
 * Date: 2018/9/9
 * Time: 11:39
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

namespace pf\arr\build;


trait PFDateArr
{
    /**
     * 生成一个日期数组
     * @param $from
     * @param $to
     * @param string $step
     * @param string $outputFormat
     * @return array
     */
    public static function pf_date_indexed($from, $to, $step = '+1 day', $outputFormat = 'Y-m-d') {
        $dates = array();
        $current = strtotime($from);
        $last = strtotime($to);
        while($current <= $last) {
            $dates[] = date($outputFormat, $current);
            $current = strtotime($step, $current);
        }
        return $dates;
    }

    /**
     * 产生一个关联数组
     * @param $from
     * @param $to
     * @param null $default
     * @param string $step
     * @param string $outputFormat
     * @return array
     */
    public static function pf_date_assoc($from, $to, $default = null, $step = '+1 day', $outputFormat = 'Y-m-d')
    {
        $dates = self::pf_date_indexed($from, $to, $step, $outputFormat);
        return array_fill_keys($dates, $default);
    }
}