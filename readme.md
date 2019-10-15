# 项目简介

Db 中间件

## 安装

```
composer require "dreamcat/dbmysql"
```

## 使用说明
### 简单示例
```php
<?php

use Dreamcat\Components\Db\Mysql\DbMysql;
$db = new DbMysql("127.0.0.1", "root", "123456", "mysql");
foreach ($db->queryResult("select * from db") as $row) {
    print_r($row);
}
```
