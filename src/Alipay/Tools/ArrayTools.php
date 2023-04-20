<?php

namespace Ctexthuang\AliSdk\Alipay\Tools;

trait ArrayTools
{
    /**
     * 数组转字符串
     * @param array $array
     * @return string
     */
    private function arrayToString(array $array): string
    {
        $string = [];

        if ($array)
        {
            foreach ($array as $key => $value) {
                $string[] = $key . '=' . $value;
            }
        }

        return implode(',', $string);
    }
}