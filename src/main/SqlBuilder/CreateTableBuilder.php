<?php

namespace Dreamcat\Components\Db\Mysql\SqlBuilder;

/**
 * 建表语句
 * @author vijay
 */
class CreateTableBuilder
{
    private $dbName;
    private $table = "";
    private $checkExists = true;
    private $fields = [];
    private $indexs = [];
    private $engine = "innodb";
    private $charset = "utf-8";

    public function table(string $table): CreateTableBuilder
    {
        $this->table = $table;
        return $this;
    }

    public function checkExists(bool $checkExists): CreateTableBuilder
    {
        $this->checkExists = $checkExists;
        return $this;
    }

    public function dbName(string $dbName): CreateTableBuilder
    {
        $this->dbName = $dbName;
        return $this;
    }

    public function build(): string
    {
        $sql = "create table ";
        if ($this->checkExists) {
            $sql .= "if not exists ";
        }
        if ($this->dbName) {
            $sql .= "`{$this->dbName}`.";
        }
        if (strlen($this->table)) {
            $sql .= "`{$this->table}` (";
        } else {
            # todo 抛出异常
        }
        return $sql;
    }
}

# end of file
