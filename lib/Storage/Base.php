<?php

// 仮に、Storage_Bulletin２みたいなのが必要になった時に、このファイルにStorage_BulletinとStorage_Bulletin２での共通処理を書いておけば、修正する時に楽。
abstract class Storage_Base
{
    // ここにはStorage_Database_MySQLインスタンスが入り、データベースを操作するメソッドと$dbhとデータベースの接続設定がプロパティとしてセットされている。
    protected $database  = null;

    protected $tableName = '';

    // 要はMySQLと接続して、データベースハンドラをプロパティにセット
    public function __construct()
    {
        $this->database = new Storage_Database_MySQL();
    }

    public function getRecords($wheres = [], $orders = [], $limit = [], $column = '*')
    {
        return $this->database->getRecords($this->tableName, $wheres, $orders, $limit, $column);
    }

    public function getRecordCount($wheres = [])
    {
        return $this->database->getRecordCount($this->tableName, $wheres);
    }
}
