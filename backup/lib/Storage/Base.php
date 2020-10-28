<?php

// 仮に、Storage_Bulletin２みたいなのが必要になった時に、
// このファイルにStorage_BulletinとStorage_Bulletin２での共通処理を書いておけば、修正する時に楽。
abstract class Storage_Base
{
    // ここにはStorage_Database_MySQLインスタンスが入り、データベースを操作するメソッドと$conn($dbh)とデータベースの接続設定がプロパティとしてセットされている。
    protected $database  = null;
    
    protected $tableName = '';

    // これはMySQLと接続している。
    public function __construct()
    {
        $this->database = new Storage_Database_MySQL();
    }

    // ここで定義しているメソッドはStorage_BulletinとStorage_Bulletin２で共通して扱うメソッド
    public function fetch($column = null, $condition = null, $order = null, $offset = null, $limit = null)
    {
        return $this->database->fetch($this->tableName, $column, $condition, $order, $offset, $limit);
    }

    public function getCount($column = null, $condition = null)
    {
        return $this->database->getCount($this->tableName, $column, $condition);
    }

    // これ引数全く変わってないけど意味あるの？外部から送られてきた投稿IDとかをエスケープするためにpublicになっている。
    // Storage_Bulletinのメソッドとして用意している。これをしないと外部で$bulletin->database->escape()とすることになると思うけど、databaseってprotectedだから参照できない。
    public function escape($value, $withQuotes = true)
    {
        return $this->database->escape($value, $withQuotes);
    }
}
