<?php

namespace Dreamcat\Components\Db\Mysql\SqlBuilder;

/**
 * SQL语句生成器
 * @author vijay
 */
class SqlBuilder
{
    public static function createTable(): CreateTableBuilder
    {
        return new CreateTableBuilder();
    }
}

# end of file
