<?php

namespace Ctexthuang\AliSdk\Alipay\Tools;

use ArrayAccess;
use ReturnTypeWillChange;

class HandleArray implements ArrayAccess
{
    /**
     * 需要操作的数组
     * @var array
     */
    private array $dataArray;

    /**
     * 属性赋值
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->dataArray = $data;
    }

    /**
     * 确认数组是否存在
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->dataArray[$offset]);
    }

    /**
     * 返回数组的值
     * @param $offset
     * @return array|mixed|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset = null): mixed
    {
        if (is_null($offset)) {
            return $this->dataArray;
        }
        return $this->dataArray[$offset] ?? null;
    }

    /**
     * 设定数组的值
     * @param string $offset
     * @param array|int|null|string $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->dataArray[] = $value;
        } else {
            $this->dataArray[$offset] = $value;
        }
    }

    /**
     * 删除数组的值
     * @param $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if (is_null($offset)) {
            $this->dataArray = [];
        } else {
            unset($this->dataArray[$offset]);
        }
    }

    /**
     * 合并数据到数组
     * @param array $data 需要合并的数据
     * @param bool $append 是否追加数据
     * @return array
     */
    public function merge(array $data, bool $append = false): array
    {
        if ($append) {
            return $this->dataArray = array_merge($this->dataArray, $data);
        }

        return array_merge($this->dataArray, $data);
    }
}