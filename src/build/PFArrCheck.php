<?php

namespace pf\arr\build;

trait PFArrCheck
{
    public  function pf_exists($key, $array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            return is_array($array) && array_key_exists((string)$key, $array);
        }
        return self::parseAndValidateKeys($key, $array)['isExists'];
    }

    public  function pf_save($key, &$array, $value, $replace = true)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            $array[$key] = $value;
            return true;
        }
        if ($key === '[]') {
            $array[] = $value;
            return true;
        }
        $parseInfo = self::parseAndValidateKeys($key, $array, 'save');
        if ($parseInfo['completed']) {
            $currEl = &$array;
            foreach ($parseInfo['keys'] as $key) {
                if (!array_key_exists((string)$key, (array)$currEl)) {
                    if (!$parseInfo['append'] && !is_array($currEl) && $currEl !== null) {
                        $parseInfo['completed'] = false;
                        break;
                    }
                    $mCurSource[$key] = [];
                } else {
                    if (!$parseInfo['append'] && !is_array($currEl)) {
                        $parseInfo['completed'] = false;
                        break;
                    }
                }
                $currEl = &$currEl[$key];
            }
            if ($parseInfo['completed']) {
                if (!$replace && $parseInfo['isExists']) {
                    return false;
                }
                if ($parseInfo['append']) {
                    $currEl[] = $value;
                } else {
                    $currEl = $value;
                }
            }
        }
        return $parseInfo['completed'];
    }

    public  function pf_delete($key, &$array)
    {
        if (!is_array($array)) {
            return false;
        }
        if ($key == '') {
            unset($array[$key]);
            return true;
        }
        if ($key === '[]') {
            return false;
        }
        return self::parseAndValidateKeys($key, $array, 'delete')['completed'];
    }

    public  function pf_check_get($key, $array, $default = null, $ignoreString = true)
    {
        if (!is_array($array)) {
            return $default;
        }
        if ($key == '') {
            if (!array_key_exists((string)$key, $array) || !is_array($array)) {
                return $default;
            }
            return $array[$key];
        }
        if ($key === '[]') {
            return $default;
        }
        $parseInfo = self::parseAndValidateKeys($key, $array, 'get');
        if ($ignoreString) {
            return (!$parseInfo['isString'] && $parseInfo['completed']) ? $parseInfo['value'] : $default;
        }
        return ($parseInfo['completed'] && ($parseInfo['isExists'] || $parseInfo['isString'])) ? $parseInfo['value'] : $default;
    }

    public  function pf_shuffle_assoc($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $keys = array_keys($array);
        shuffle($keys);
        $random = [];
        foreach ($keys as $key) {
            $random[$key] = $array[$key];
        }
        return $random;
    }

    private  function parseAndValidateKeys($key, &$array, $mode = '')
    {
        $parseInfo = [
            'keys' => [],
            'lastKey' => '',
            'prevEl' => &$array,
            'currEl' => &$array,
            'isExists' => null,
            'cntEBrackets' => 0,
            'isString' => false,
            'completed' => false,
            'first' => true,
            'append' => false,
            'value' => null
        ];
        $parseInfo['isBroken'] = (bool)preg_replace_callback(array('/(?J:\[([\'"])(?<el>.*?)\1\]|(?<el>\]?[^\[]+)|\[(?<el>(?:[^\[\]]+|(?R))*)\])/'),
            function ($m) use (&$parseInfo, &$array) {
                if ($m[0] == '[]') {
                    $parseInfo['isExists'] = false;
                    $parseInfo['cntEBrackets']++;
                    $parseInfo['append'] = $parseInfo['cntEBrackets'] == 1;
                    return '';
                }
                $parseInfo['append'] = false;
                $parseInfo['keys'][] = $m['el'];
                if ($parseInfo['isExists'] !== false) {
                    if (!is_array($parseInfo['currEl'])) {
                        $parseInfo['isExists'] = false;
                        $parseInfo['lastKey'] = $m['el'];
                        return '';
                    }
                    if (($parseInfo['isExists'] = array_key_exists((string)$m['el'],
                            $parseInfo['currEl']) && is_array($parseInfo['currEl']))
                    ) {
                        if (!$parseInfo['first']) {
                            $parseInfo['prevEl'] = &$parseInfo['currEl'];
                        }
                        $parseInfo['currEl'] = &$parseInfo['currEl'][$m['el']];
                        $parseInfo['lastKey'] = $m['el'];
                        $parseInfo['first'] = false;
                    }
                }
                return '';
            }, $key);
        if ($parseInfo['isExists'] === false && is_array($parseInfo['prevEl']) && is_string($parseInfo['currEl'])) {
            $parseInfo['isString'] = true;
            if ($mode == 'get' && isset($parseInfo['currEl'][$parseInfo['lastKey']])) {
                $parseInfo['completed'] = true;
                $parseInfo['value'] = $parseInfo['currEl'][$parseInfo['lastKey']];
            }
        }
        if ($mode == 'get' && $parseInfo['isExists']) {
            $parseInfo['completed'] = true;
            $parseInfo['value'] = $parseInfo['prevEl'][$parseInfo['lastKey']];
        }
        if ($mode == 'delete' && $parseInfo['isExists']) {
            unset($parseInfo['prevEl'][$parseInfo['lastKey']]);
            $parseInfo['completed'] = true;
        }
        if ($mode == 'save') {
            if ($parseInfo['append']) {
                if ($parseInfo['cntEBrackets'] == 1) {
                    $parseInfo['completed'] = true;
                }
            } else {
                if ($parseInfo['cntEBrackets'] == 0) {
                    $parseInfo['completed'] = true;
                }
            }
        }
        if ($parseInfo['isBroken']) {
            $parseInfo['completed'] = false;
        }
        return $parseInfo;
    }
}