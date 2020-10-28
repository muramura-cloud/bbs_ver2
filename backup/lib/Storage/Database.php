<?php

// 抽象クラス
// 何が保証されているのかというと、データベースの接続設定を入手して、データベースに接続（データベースハンドラを用意）すること。
abstract class Storage_Database
{
    // 俺の掲示板でいう$dbhがセットされる。
    protected $conn = null;

    protected $config = [
        'host'     => '127.0.0.1',
        'port'     => '',
        'name'     => '',
        'user'     => '',
        'password' => '',
    ];

    // データベースの接続設定をプロパティにセットしてデータベースに接続している。
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);

        if (function_exists('get_db_config')) {
            // get_db_config()はlibのfunctions。array_mergeはキーが被ったら上書きされる。
            // ここではデータベースの名前とかユーザー名とかデータベースの接続に必要な情報（必要な情報はconfig/database.phpに書き込まれている）をセットしている。
            $this->config = array_merge($this->config, get_db_config());
        }

        // Storage_Database_MySQLクラスのメソッドを叩いている。データベースハンドラをセットしている。。
        $this->conn = $this->connect();
    }
}
