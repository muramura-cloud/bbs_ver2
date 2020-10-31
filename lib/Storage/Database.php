<?php

// 何が保証されているのかというと、データベースの接続設定を入手して、データベースに接続（データベースハンドラを用意）すること。
// 言い換えると、データベースを操作するクラスは必ず、データベースの接続情報を取得してデータベースに接続するという仕事を必ず所有している。だから、ここの定義する。
abstract class Storage_Database
{
    protected $dbh = null;

    protected $config = [
        'host'     => '127.0.0.1',
        'port'     => '',
        'name'     => '',
        'user'     => '',
        'password' => '',
    ];

    // データベースの接続設定をプロパティにセットしてデータベースに接続している。
    // $config['charset'] = 'utf8';引数には文字エンコードがデフォルトではいる。
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);

        if (function_exists('get_db_config')) {
            // get_db_config()はlibのfunctions。array_mergeはキーが被ったら上書きされる。
            // ここではデータベースの名前とかユーザー名とかデータベースの接続に必要な情報（必要な情報はconfig/database.phpに書き込まれている）をセットしている。
            $this->config = array_merge($this->config, get_db_config());
        }

        // Storage_Database_MySQLクラスのメソッドを叩いている。データベースハンドラをセットしている。
        $this->dbh = $this->connect();
    }
}
