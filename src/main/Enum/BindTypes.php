<?php

namespace Dreamcat\Components\Db\Mysql\Enum;

/**
 * Class BindTypes
 * @package Dreamcat\Components\Db\Mysql\Enum
 * 绑定参数类型
 * @author SolitudeSword
 */
class BindTypes
{
    /**
     * @const string BIND_TYPE_I 绑定参数类型-整型
     * @access public
     * @static
     */
    const BIND_TYPE_I = 'i';
    /**
     * @const string BIND_TYPE_D 绑定参数类型-浮点数
     * @access public
     * @static
     */
    const BIND_TYPE_D = 'd';
    /**
     * @const string BIND_TYPE_S 绑定参数类型-字符串，默认类型
     * @access public
     * @static
     */
    const BIND_TYPE_S = 's';
    /**
     * @const string BIND_TYPE_B 绑定参数类型-二进制
     * @access public
     * @static
     */
    const BIND_TYPE_B = 'b';
}

# end of file
