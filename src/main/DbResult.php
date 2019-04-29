<?php

namespace Dreamcat\Components\Db\Mysql;

use mysqli_result;

/**
 * Class DbResult
 * @package Dreamcat\Components\Db\Mysql
 * db结果集
 * @author SolitudeSword
 */
class DbResult implements \Iterator, \ArrayAccess, \Countable
{
    /**
     * @var mysqli_result $result
     * @access private
     */
    private $result;
    /**
     * @var array $current 当前元素
     * @access private
     */
    private $current;
    /**
     * @var int $idx 当前下标
     * @access private
     */
    private $idx;

    /**
     * @var int $dataSeek 当前指针的下标
     * @access private
     */
    private $dataSeek;

    /**
     * 构造函数
     * @param mysqli_result $result 结果集对象
     */
    public function __construct(mysqli_result $result)
    {
        $this->result = $result;
        $this->rewind();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->result->free();
    }

    /**
     * fetch
     * 返回当前数据并将指针后移
     * @return array 当前数据，无数据返回空数组
     * @access public
     */
    public function fetch(): array
    {
        $data = $this->current;
        $this->next();
        return $data;
    }

    /**
     * rowCount
     * 返回当前结果行数
     * @return int 当前结果行数
     * @access public
     */
    public function rowCount(): int
    {
        return $this->result->num_rows;
    }

    /**
     * fetchAll
     * 获取结果集中所有数据
     * @return array
     * @access public
     */
    public function fetchAll(): array
    {
        if ($this->result->num_rows) {
            $this->result->data_seek(0);
            $this->dataSeek = $this->result->num_rows;
            return $this->result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    # Iterator接口内容

    /**
     * current
     * 返回当前元素
     * @return array 当前元素
     * @access public
     */
    public function current(): array
    {
        return $this->current;
    }

    /**
     * key
     * 返回当前元素的键
     * @return int 当前元素的键
     * @access public
     */
    public function key(): int
    {
        return $this->idx;
    }

    /**
     * next
     * 向前移动到下一个元素
     * @return void
     * @access public
     */
    public function next(): void
    {
        if ($this->idx >= $this->result->num_rows) {
            return;
        }
        if ($this->idx != $this->dataSeek) {
            $this->result->data_seek($this->idx);
        }
        ++$this->idx;
        $this->dataSeek = $this->idx;
        $this->current = $this->result->fetch_assoc() ?: [];
    }

    /**
     * rewind
     * 返回到迭代器的第一个元素
     * @return void
     * @access public
     */
    public function rewind(): void
    {
        if ($this->result->num_rows) {
            $this->result->data_seek(0);
            $this->idx = 0;
            $this->dataSeek = 0;
            $this->current = $this->result->fetch_assoc();
        } else {
            $this->idx = 0;
            $this->dataSeek = 0;
            $this->current = [];
        }
    }

    /**
     * valid
     * 检查当前位置是否有效
     * @return bool 当前位置是否有效
     * @access public
     */
    public function valid(): bool
    {
        return $this->idx < $this->result->num_rows;
    }

    # ArrayAccess接口内容

    /**
     * offsetExists
     * 检查一个偏移位置是否存在
     * @param int $offset 需要检查的偏移位置
     * @return bool 是否存在
     * @access public
     */
    public function offsetExists($offset): bool
    {
        $offset = intval($offset);
        return $offset >= 0 && $offset < $this->result->num_rows;
    }

    /**
     * offsetGet
     * 获取一个偏移位置的值
     * @param int $offset 需要获取的偏移位置
     * @return array 指定位置的数据
     * @access public
     */
    public function offsetGet($offset): array
    {
        $offset = intval($offset);
        if ($offset >= 0 && $offset < $this->result->num_rows) {
            $this->result->data_seek($offset);
            $this->dataSeek = $offset + 1;
            return $this->result->fetch_assoc();
        } else {
            return [];
        }
    }

    /**
     * offsetSet
     * 设置一个偏移位置的值，此函数不实现
     * @param int $offset 偏移量
     * @param mixed $value 要设定的值
     * @return void
     * @access public
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * offsetUnset
     * 复位一个偏移位置的值，此函数不实现
     * @param int $offset 偏移量
     * @return void
     * @access public
     */
    public function offsetUnset($offset): void
    {
    }

    /**
     * count
     * 返回条数
     * @return int
     * @access public
     */
    public function count(): int
    {
        return $this->rowCount();
    }
}

# end of file
