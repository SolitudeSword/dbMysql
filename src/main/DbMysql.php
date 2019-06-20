<?php

namespace Dreamcat\Components\Db\Mysql;

use Dreamcat\Components\Db\Mysql\Enum\DataTypes;
use Dreamcat\Components\Db\Mysql\Exception\DbError;
use mysqli;
use Psr\Log\LoggerInterface;

/**
 * Class DbMysql
 * @package Dreamcat\Components\Db\Mysql\Enum
 * mysql数据库连接，使用mysqli方法
 * @author SolitudeSword
 */
class DbMysql
{
    /**
     * @var mysqli $mysqli 连接对象
     * @access private
     */
    private $mysqli;

    /**
     * @var bool $inTransaction 当前db是否在事务中
     * @access private
     */
    private $inTransaction = false;

    /**
     * @var string $dbName 当前db名
     * @access private
     */
    private $dbName;

    /**
     * @var LoggerInterface $logRecord 日志记录对象
     * @access private
     */
    private $logRecord;

    /**
     * 构造函数
     * @param string $host 数据库地址
     * @param string $user 数据库用户名
     * @param string $pwd 数据库密码
     * @param string $db 数据库名
     * @param LoggerInterface $logRecord 日志记录器
     * @param string $charset 字符集
     * @throws DbError
     */
    public function __construct(
        string $host,
        string $user,
        string $pwd,
        string $db,
        LoggerInterface $logRecord = null,
        $charset = "utf-8"
    ) {
        $this->logRecord = $logRecord;
        $this->mysqli = new mysqli($host, $user, $pwd, $db);
        if ($this->mysqli->connect_error && $this->logRecord) {
            $this->logRecord->error("DB配置连接失败", [
                "user" => $user,
                "pwd" => $pwd,
                "host" => $host,
                "db" => $db,
            ]);
            throw new DbError("数据库连接失败");
        }
        $this->mysqli->set_charset($charset);
        $this->dbName = $db;
    }

    /**
     * getDbName
     * 获取当前db名，或指定的配置库名
     * @return string
     * @access
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * errorCode
     * 上次的错误码，无错误返回0
     * @return int 上次错误码
     * @access public
     */
    public function errorCode(): int
    {
        return intval($this->mysqli->errno);
    }

    /**
     * lastError
     * 获取上次的错误信息，无信息返回空字符串
     * @return string 错误信息
     * @access public
     */
    public function lastError(): string
    {
        return $this->mysqli->error;
    }

    /**
     * lastInsertId
     * 返回最后插入行的ID
     * @return string 最后插入行的ID
     * @access public
     */
    public function lastInsertId(): string
    {
        return strval($this->mysqli->insert_id);
    }

    /**
     * prepare
     * 预处理一个SQL
     * @param string $sql 要预处理的sql
     * @param bool $recordError 是否记录执行错误
     * @return DbPrepare|false 失败返回 false，成功返回 DbPrepare
     * @access public
     */
    public function prepare($sql, $recordError = true)
    {
        $ret = $this->mysqli->prepare($sql);
        if ($ret) {
            return new DbPrepare($ret, $sql, $recordError ? $this->logRecord : null);
        } else {
            if ($recordError && $this->logRecord) {
                $this->logRecord->error("SQL执行错误", [
                    "sql" => $sql,
                    "error" => $this->lastError(),
                ]);
            }
            return false;
        }
    }

    /**
     * queryResult
     * 执行一个查询SQL
     * @param string $sql SQL，必须使用预处理类语句
     * @param array $values 要绑定的值
     * @return false|DbResult 成功返回结果集，失败或者非select类sql返回false
     * @access public
     */
    public function queryResult($sql, array $values = [])
    {
        $stat = $this->prepare($sql);
        if (!$stat) {
            return false;
        }
        return $stat->bindValueAry($values) ? $stat->select() : false;
    }

    /**
     * escape
     * 字符串转义
     * @param string $str 要转义的字符串
     * @return string 转义后的字符串
     * @access public
     */
    public function escape($str): string
    {
        return $this->mysqli->real_escape_string($str);
    }

    /**
     * isInTrannsaction
     * 判断当前db是否在事务中
     * @return bool 是否在事务中
     * @access public
     */
    public function isInTrannsaction(): bool
    {
        return $this->inTransaction;
    }

    /**
     * beginTransaction
     * 开启事务
     * @return bool 是否成功开始
     * @note 如果之前已经在事务中，开启返回失败
     * @access public
     */
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = true;
            return $this->mysqli->begin_transaction();
        } else {
            return false;
        }
    }

    /**
     * commit
     * 提交事务
     * @return bool 是否成功提交事务
     * @access public
     */
    public function commit(): bool
    {
        if ($this->inTransaction) {
            $this->mysqli->commit();
            $this->inTransaction = false;
            return true;
        } else {
            return false;
        }
    }

    /**
     * rollBack
     * 回滚事务
     * @return void
     * @access public
     */
    public function rollBack(): void
    {
        $this->mysqli->rollback();
        $this->inTransaction = false;
    }

    /**
     * getAffectedRows
     * 获得上条sql影响条数
     * @return int 影响条数
     * @access public
     */
    public function getAffectedRows(): int
    {
        return $this->mysqli->affected_rows;
    }

    /**
     * createTable
     * 创建表
     * @param string $tableName 表名
     * @param array $fields 字段，数组元素结构{
     *      name : 字段名，必填
     *      type : DATA_TYPE_* 数据类型，必填
     *      autoInc : 是否自增字段，选填，默认为false
     *      len : 长度，某些类型使用，选填
     *      null : 是否可为null，选填，默认为false
     *      default : 字段默认值，选填，不存在则不指定
     *      comment : 注释，选填，不存在则不指定
     * }
     * @param array $indexs 索引列表，数组元素结构{
     *      name : 索引名，不存在为主键
     *      cols : 数组，必填，元素是字段名
     *      unique : 是否唯一索引，选填，默认false
     * }
     * @param bool $checkExists 是否加if not exists
     * @param string $engine 表引擎
     * @param string $charset 字符集
     * @param string $dbName 使用的数据库名，默认为当前库
     * @return bool 是否成功
     * @access public
     */
    public function createTable(
        string $tableName,
        array $fields,
        array $indexs = [],
        bool $checkExists = true,
        string $engine = "innodb",
        string $charset = "utf-8",
        string $dbName = ""
    ): bool {
        if (!$tableName || !$fields) {
            return false;
        }
        if ($dbName) {
            $dbName = "`{$dbName}`.";
        }
        $sql = 'create table ' . ($checkExists ? 'if not exists ' : '') . "{$dbName}`{$tableName}` (";

        $cols = [];
        foreach ($fields as $field) {
            if (!isset($field['name']) || !isset($field['type'])) {
                return false;
            }
            $desc = "`{$field['name']}` {$field['type']}";

            if ($field["type"] == DataTypes::DATA_TYPE_ENUM) {
                if (!isset($field["values"])) {
                    return false;
                }
                $values = [];
                foreach ($field["values"] as $val) {
                    $values[] = "'" . $this->escape($val) . "'";
                }
                if (!$values) {
                    return false;
                }
                $desc .= "(" . implode(", ", $values) . ")";
            } elseif (isset($field['len'])) {
                $desc .= "({$field['len']})";
            }
            if (!isset($field['null']) || !$field['null']) {
                $desc .= " not null";
            }

            if (isset($field['autoInc']) && $field['autoInc']) {
                $desc .= ' auto_increment';
            }

            if (isset($field['default'])) {
                $desc .= ' default \'' . $this->escape($field['default']) . '\'';
            }
            if (isset($field['comment'])) {
                $val = $this->escape($field['comment']);
                $desc .= " comment '{$val}'";
            }
            $cols[] = $desc;
        }

        foreach ($indexs as $index) {
            if (isset($index['name'])) {
                $desc = "key `{$index['name']}` (";
                if (isset($index['unique']) ? $index['unique'] : 0) {
                    $desc = "unique {$desc}";
                }
            } else {
                $desc = 'primary key (';
            }
            if (!isset($index['cols'])) {
                return false;
            }
            $indexCols = [];
            foreach ($index['cols'] as $col) {
                $indexCols[] = "`{$col}`";
            }
            $desc .= implode(', ', $indexCols) . ')';
            if (!empty($index['type']) && ($index['type'] == "HASH" || $index['type'] == "BTREE")) {
                $desc .= " using " . $index['type'];
            }
            $cols[] = $desc;
        }

        $sql .= implode(', ', $cols);
        $sql .= ") engine={$engine} default charset={$charset}";
        $result = $this->mysqli->query($sql);
        if (!$result && $this->logRecord) {
            $this->logRecord->error("SQL执行错误", [
                "sql" => $sql,
                "error" => $this->lastError(),
            ]);
        }
        return $result ? true : false;
    }

    /**
     * insert
     * 插入数据
     * @param string $tableName 要插入的表名
     * @param array $datas 数组，键是字段名，值是数据
     * @param bool $batch 是否批量
     * @param bool $replace 是否使用替换
     * @param bool $insIgnore 是否在insert时忽略错误
     * @param string $dbName 使用的数据库名，默认为当前库
     * @param string $onDuplicate 插入出现唯一键冲突时的sql，会附在on duplicate key update后面
     * @return false|int 批量返回影响行数，否则返回lastinsertid，失败都是返回false
     * @access public
     */
    public function insert($tableName, array $datas, $batch = false, $replace = false, $insIgnore = false, $dbName = '', $onDuplicate = "")
    {
        if (!$datas) {
            return 0;
        }
        if ($batch) {
            $datas = array_values($datas);
        } else {
            $datas = [$datas];
        }
        $fields = array_keys($datas[0]);
        if (!$fields) {
            return 0;
        }
        $values = [];
        foreach ($datas as $rowData) {
            if (!$rowData) {
                continue;
            }
            $data = [];
            foreach ($fields as $field) {
                if (isset($rowData[$field])) {
                    $data[] = '\'' . $this->escape($rowData[$field]) . '\'';
                } else {
                    $data[] = 'null';
                }
            }
            $values[] = '(' . implode(', ', $data) . ')';
        }
        if (!$values) {
            return 0;
        }
        $fields = '`' . implode('`, `', $fields) . '`';
        if ($dbName) {
            $dbName = "`{$dbName}`.";
        }
        $op = $replace ? 'replace' : 'insert';
        if (!$replace && $insIgnore) {
            $op .= ' ignore';
        }
        $sql = "{$op} into {$dbName}`{$tableName}` ({$fields}) values " . implode(', ', $values);
        if (!$replace && $onDuplicate) {
            $sql .= " on duplicate key update {$onDuplicate}";
        }
        if ($this->mysqli->query($sql)) {
            return $batch ? $this->getAffectedRows() : $this->lastInsertId();
        } else {
            if ($this->logRecord) {
                $this->logRecord->error("SQL[{$sql}]执行错误(" . $this->lastError() . ')');
            }
            return false;
        }
    }

}

# end of file
