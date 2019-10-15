<?php

namespace Dreamcat\Components\Db\Mysql\Enum;

/**
 * 数据类型
 * @author vijay
 */
final class DataTypes
{
    /**
     * @const string DATA_TYPE_SMALLINT 数据类型-smallint
     */
    const DATA_TYPE_SMALLINT = 'smallint';
    /**
     * @const string DATA_TYPE_SMALLUINT 数据类型-无符号smallint
     */
    const DATA_TYPE_SMALLUINT = 'smallint unsigned';
    /**
     * @const string DATA_TYPE_INT 数据类型-整型
     */
    const DATA_TYPE_INT = 'int';
    /**
     * @const string DATA_TYPE_UINT 数据类型-无符号整型
     */
    const DATA_TYPE_UINT = 'int unsigned';
    /**
     * @const string DATA_TYPE_BIGINT 数据类型-bigint
     */
    const DATA_TYPE_BIGINT = 'bigint';
    /**
     * @const string DATA_TYPE_BIGUINT 数据类型-无符号bigint
     */
    const DATA_TYPE_BIGUINT = 'bigint unsigned';
    /**
     * @const string DATA_TYPE_DOUBLE 数据类型-日期时间
     */
    const DATA_TYPE_DOUBLE = 'double';
    /**
     * @const string DATA_TYPE_VCHAR 数据类型-变长字符串
     */
    const DATA_TYPE_VCHAR = 'varchar';
    /**
     * @const string DATA_TYPE_CHAR 数据类型-字符串
     */
    const DATA_TYPE_CHAR = 'char';
    /**
     * @const string DATA_TYPE_TEXT 数据类型-文本
     */
    const DATA_TYPE_TEXT = 'text';
    /**
     * @const string DATA_TYPE_DATETIME 数据类型-日期时间
     */
    const DATA_TYPE_DATETIME = 'datetime';
    /**
     * @const string DATA_TYPE_TIMESTAMP 数据类型-时间
     */
    const DATA_TYPE_TIMESTAMP = "TimeStamp";
    /**
     * @const string DATA_TYPE_ENUM 数据类型-枚举
     */
    const DATA_TYPE_ENUM = "enum";
}

# end of file
