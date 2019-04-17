<?php

namespace Dreamcat\Components\Db\Mysql;

use Dreamcat\Components\Db\Mysql\Enum\BindTypes;
use mysqli_stmt;
use Psr\Log\LoggerInterface;

/**
 * Class DbPrepare
 * @package Dreamcat\Components\Db\Mysql
 * 代表一个prepared语句
 * @author SolitudeSword
 */
class DbPrepare
{
    /**
     * @var mysqli_stmt $preResult prepared语句的对象
     * @access private
     */
    private $preResult;
    /**
     * @var string $sql 创建时使用的SQL
     * @access private
     */
    private $sql;

    /**
     * @var LoggerInterface $logRecord 日志记录对象
     * @access private
     */
    private $logRecord;

    /**
     * 构造函数
     * @param mysqli_stmt $preResult 代表一个prepared语句的对象
     * @param string $sql 创建时使用的SQL
     * @param LoggerInterface $logRecord
     */
    public function __construct(mysqli_stmt $preResult, string $sql, LoggerInterface $logRecord = null)
    {
        $this->preResult = $preResult;
        $this->sql = $sql;
        $this->logRecord = $logRecord;
    }

    /**
     * bindParam
     * 绑定参数
     * @param array $args 参数列表，元素如果是数组，第一个值BIND_TYPE_*常量，表示数据类型，第二个值为数据，如果不是数组，则值为数据
     * @return bool 是否成功
     * @access public
     * @note 绑定的变量是引用形式，所以最终使用的值取决于执行时变量的值
     */
    public function bindParam(&...$args): bool
    {
        $vars = [""];
        foreach ($args as &$arg) {
            if (is_array($arg)) {
                if (count($arg) < 2) {
                    return false;
                }
                $vars[0] .= $arg[0];
                $vars[] = &$arg[1];
            } else {
                $vars[0] .= BindTypes::BIND_TYPE_S;
                $vars[] = &$arg;
            }
        }
        return call_user_func_array([
            $this->preResult,
            "bind_param",
        ], $vars);
    }

    /** @noinspection PhpDocSignatureInspection */
    /**
     * bindValue
     * 把值绑定到参数
     * @param array $args 参数列表，元素如果是数组，第一个值BIND_TYPE_*常量，表示数据类型，第二个值为数据，如果不是数组，则值为数据
     * @return bool 是否成功
     * @access public
     * @note 非引用绑定
     */
    public function bindValue(): bool
    {
        return $this->bindValueAry(func_get_args());
    }

    /**
     * bindValueAry
     * 把值绑定到参数
     * @param array $args 参数列表，元素如果是数组，第一个值BIND_TYPE_*常量，表示数据类型，第二个值为数据，如果不是数组，则值为数据
     * @return bool 是否成功
     * @access public
     * @note 非引用绑定
     */
    public function bindValueAry(array $args): bool
    {
        if (!$args) {
            return true;
        }
        $vars = [""];
        foreach ($args as &$arg) {
            if (is_array($arg)) {
                if (count($arg) < 2) {
                    return false;
                }
                $vars[0] .= $arg[0];
                $vars[] = &$arg[1];
            } else {
                $vars[0] .= BindTypes::BIND_TYPE_S;
                $vars[] = &$arg;
            }
        }
        return call_user_func_array([
            $this->preResult,
            "bind_param",
        ], $vars);
    }

    /**
     * errorCode
     * 上次的错误码，无错误返回0
     * @return int 上次错误码
     * @access public
     */
    public function errorCode(): int
    {
        return intval($this->preResult->errno);
    }

    /**
     * lastError
     * 获取上次的错误信息，无信息返回空字符串
     * @return string 错误信息
     * @access public
     */
    public function lastError(): string
    {
        return $this->preResult->error;
    }

    /**
     * select
     * 执行select类SQL
     * @return false|DbResult 成功返回结果集，失败或者非select类sql返回false
     * @access public
     */
    public function select()
    {
        if ($this->preResult->execute()) {
            /** @noinspection PhpAssignmentInConditionInspection */
            if ($result = $this->preResult->get_result()) {
                return new DbResult($result);
            } else {
                # 取不到结果集对象，即非select语句
                return false;
            }
        } else {
            # 执行失败
            if ($this->logRecord) {
                $this->logRecord->error("SQL执行错误", [
                    "sql" => $this->sql,
                    "error" => $this->lastError(),
                ]);
            }
            return false;
        }
    }

    /**
     * exec
     * 执行sql
     * @return int 成功返回影响条数，失败返回-1
     * @access public
     */
    public function exec()
    {
        $ret = $this->preResult->execute();
        if ($ret) {
            if ($this->preResult->affected_rows < 0) {
                $this->preResult->get_result();
            }
            return $this->preResult->affected_rows;
        } else {
            # 执行失败
            if ($this->logRecord) {
                $this->logRecord->error("SQL执行错误", [
                    "sql" => $this->sql,
                    "error" => $this->lastError(),
                ]);
            }
            return -1;
        }
    }

    /**
     * getAffectedRows
     * 获得上条sql影响条数
     * @return int 影响条数
     * @access public
     */
    public function getAffectedRows()
    {
        return $this->preResult->affected_rows;
    }

    /**
     * lastInsertId
     * 返回最后插入行的ID
     * @return string 最后插入行的ID
     * @access public
     */
    public function lastInsertId()
    {
        return strval($this->preResult->insert_id);
    }

    /**
     * selectrow
     * 执行select类SQL并返回第一行数据
     * @return array 第一行数据，失败返回空数组
     * @access public
     */
    public function selectrow()
    {
        return ($ret = $this->select()) ? $ret->current() : [];
    }
}

# end of file
