<?php

namespace pf\arr\build;

class Base
{

    /**
     * 数组合并
     * @param $arr
     * @param $res
     * @return array
     */
    public function pf_merge($arr, $res)
    {
        $res = is_array($res) ? $res : [];
        foreach ($arr as $k => $v) {
            $res[$k] = isset($res[$k]) ? $res[$k] : $v;
            $res[$k] = is_array($res[$k]) ? $this->merge($v, $res[$k]) : $res[$k];
        }
        return $res;
    }

    /**
     * 移除数组中的某个值 获取新数组
     * @param array $data
     * @param array $values
     * @return array
     */
    public function del_val(array $data, array $values)
    {
        $news = [];
        foreach ($data as $key => $v) {
            if (!in_array($v, $values)) {
                $news[$key] = $v;
            }
        }
        return $news;
    }

    /**
     * 根据建明获取值
     * @param array $data
     * @param $key
     * @param null $value
     * @return array|mixed|null
     */
    public function get(array $data, $key, $value = null)
    {
        $exp = explode('.', $key);
        foreach ((array)$exp as $d) {
            if (isset($data[$d])) {
                $data = $data[$d];
            } else {
                return $value;
            }
        }
        return $data;
    }

    /**
     * 不区分大小写 检测数据数据键名
     * @param $key
     * @param $arr
     * @return bool
     */
    public function keyExists($key, $arr)
    {
        return array_key_exists(strtolower($key), $this->keyExists($arr));
    }

    /**
     * 根据下标过滤数据元素
     *
     * @param array $data 原数组数据
     * @param       $keys 参数的下标
     * @param int $type 1 存在在$keys时过滤  0 不在时过滤
     *
     * @return array
     */
    public function filterKeys(array $data, $keys, $type = 1)
    {
        $tmp = $data;
        foreach ($data as $k => $v) {
            if ($type == 1) {
                //存在时过滤
                if (in_array($k, $keys)) {
                    unset($tmp[$k]);
                }
            } else {
                //不在时过滤
                if (!in_array($k, $keys)) {
                    unset($tmp[$k]);
                }
            }
        }
        return $tmp;
    }

    /**
     * 数组排序
     * @param $arr
     * @return mixed
     */
    public function pf_arr_sort($arr)
    {
        $len = count($arr);
        for ($i = 1; $i < $len; $i++) {
            for ($k = 0; $k < $len - $i; $k++) {
                if ($arr[$k] > $arr[$k + 1]) {
                    $tmp = $arr[$k + 1];
                    $arr[$k + 1] = $arr[$k];
                    $arr[$k] = $tmp;
                }
            }
        }
        return $arr;
    }

    /**
     * 二级获取树形结构
     * @param $list
     * @param int $parent_id
     * @return array
     */
    public function tree($list,$parent_id=0) {
        $arr = [];
        $tree= [];
        foreach ($list as $value) {
            $arr[$value['parent_id']][]=$value;
        }

        foreach ($arr[$parent_id] as $key=>$val) {
            $tree[$key][] = $val;
            foreach ($arr[$val['id']] as $v) {
                $tree[$key]['son'][]=$v;
            }
        }
        return $tree;
    }

    /**
     * 多级获取树形结构
     * @param $list
     * @param int $parent_id
     * @return array
     */
    function getTree($list, $parent_id = 0) {
        $tree = [];
        if (!empty($list)) {
            //先修改为以id为下标的列表

            $newList = [];

            foreach ($list as $k => $v) {
                $newList[$v['id']] = $v;
            }
            //然后开始组装成特殊格式
            foreach ($newList as $value) {

                if ($parent_id == $value['parent_id']) {//先取出顶级
                    $tree[] = &$newList[$value['id']];
                } elseif (isset($newList[$value['parent_id']])) {
                    //再判定非顶级的pid是否存在，如果存在，则再pid所在的数组下面加入一个字段items，来将本身存进去
                    $newList[$value['parent_id']]['items'][] = &$newList[$value['id']];

                }
            }
        }    return $tree;
    }

    /**
     * 数组去重
     * @param $arr
     * @return array
     */
    public function pf_array_unique($arr) {
        $dime = $this->array_depth($arr);
        if($dime <= 1) {
            $data =array_unique($arr);
        } else {
            $temp=[];
            $new_data=[];
            foreach ($arr as $key=>$v) {
                if(is_array($v)) {
                    $new_data = pf_array_unique($v);
                } else {
                    $temp[$key]=$v;
                }
            }
            $data=array_unique($temp);
            array_push($data,$new_data);
        }
        return $data;
    }


    /**
     * 检测数组的维度
     * @param $array
     * @return int
     */
    public function array_depth($array) {
        if(!is_array($array)) return 0;
        $max_depth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->array_depth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }
        return $max_depth;
    }

    /**
     * 数组中指定的一列
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    public function pf_array_col($array, $columnKey, $indexKey = null)
    {
        $result = array();
        if(!empty($array)) {
            if (!function_exists('array_column')) {
                foreach ($array as $val) {
                    if (!is_array($val)) {
                        continue;
                    } elseif (is_null($indexKey) && array_key_exists($columnKey, $val)) {
                        $result[] = $val[$columnKey];
                    } elseif (array_key_exists($indexKey, $val)) {
                        if (is_null($columnKey)) {
                            $result[$val[$indexKey]] = $val;
                        } elseif (array_key_exists($columnKey, $val)) {
                            $result[$val[$indexKey]] = $val[$columnKey];
                        }
                    }
                }
            } else {
                $result = array_column($array, $columnKey, $indexKey);
            }
        }
        return $result;
    }

    /**
     * 对象转换成数组
     * @param $obj
     * @return array
     */
    public  function pf_obj_arr($obj)
    {
        $arr = is_object($obj) ? get_object_vars($obj) : $obj;
        if (is_array($arr)) {
            return array_map(array(
                __CLASS__,
                __FUNCTION__
            ), $arr);
        } else {
            return $arr;
        }
    }

    /**
     * 结构化打印数组
     * @param $arr
     * @param int $type
     */
    public function dd($arr,$type=1) {
        echo '<pre>';
        if($type==1) {
            print_r($arr);
        } else {
            var_dump($arr);
        }
        echo '</pre>';
        exit;
    }

}