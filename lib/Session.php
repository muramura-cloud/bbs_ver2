<?php

// セッション管理クラス
// このクラスを経由して$_SESSIONにユーザーを入れる。ネットと切り分ける必要がある。
class Session {

    protected $timeout; // セッションタイムアウト時間


    // コンストラクタ
    // @param string $sessname セッション名
    // @param array $params オプション
    public function __construct($sessname = null, array $params = []){
        if(is_string($sessname) && !empty($sessname)){
            session_name($sessname);
        }

        # sessname関数を使うと、セッション名を設定できる。セッション名は、セッションIDを保持するクッキーの名前として使われる。
        # 同じドメイン配下に複数との異なるアプリケーションを動かす場合、セッションが干渉しないようにする必要がある。
        # セッションをアプリケーションごとに分けるために、任意のセッション名を指定できるようにしている。

        // タイムアウト時間が指定されていないときは
        // セッションガーベジコレクタの時間をセッションタイムアウト時間とする
        $gc_maxlifetime = ini_get('session.gc_maxlifetime');
        $this->timeout = $gc_maxlifetime;
        if(is_array($params) && count($params) > 0){
            if(isset($params['timeout']) && ($params['timeout'] > 0)){
                if($gc_maxlifetime < $params['timeout']){
                    ini_set('session.gc_maxlifetime', $params['timeout']);
                }
                $this->timeout = $params['timeout'];
            }
        }

        # セッションタイムアウトの時間を取得。セッションタイムアウトはこのSessionクラスで管理している。
        # オプションでタイムアウト時間が指定されない場合、PHP設定のsession.gc_maxlifetimeの時間を
        # セッションタイムアウトの時間として使う。
    }


    // セッション存在チェック
    // @return boolean true: セッション開始中
    public function sessionExists(){
        if(isset($_COOKIE[session_name()])){
            return true;
        }
        return false;
    }

    # WebブラウザからセッションIDが送信されているかどうかをチェックする。


    // セッション開始とタイムアウトをチェック
    // @return boolean true     : セッションタイムアウトしていない
    //                 false    : セッションタイムアウトした
    public function start(){
        session_start();
        $now = time();
        $lastreq = $this->get('lastreq', $now); // 前回アクセス時刻を取得
        $this->set('lastreq', $now); //アクセス時刻を保存
        if(($lastreq + $this->timeout) <= $now){ // タイムアウト？
            return false;
        }
        return true;
    }

    # セッション管理機能を使うには、session_start関数を呼び出す。
    # session_start関数を呼び出した後、$_SESSION変数にアクセスすることで
    # セッションの読み出し、書き込みができるようになる。


    public function regenerate(){
        session_regenerate_id(true);
    }

    # session_regenerate_id関数を呼び出すと、セッションIDを生成しなおす。
    # ログイン認証機能を実装する場合、ログインの成功後にセッションIDを変更する必要がある。


    // セッションの有効期限と有効なディレクトリを設定
    public function setRange($lifetime = 0, $path = $_SERVER['DOCUMENT_ROOT']){
        session_set_cookie_params($lifetime, $path);
    }

    # セッションの有効期限と有効なディレクトリを設定する。
    # ブラウザを閉じたときに破棄する場合は$lifetimeを0にする。
    # startメソッドを実行する前にsetRangeメソッドを実行する必要がある。


    // クッキー削除要求処理
    public function delCookie(){
        if($this->sessionExists()){
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() -4200,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']);
        }
    }

    # セッションIDを保持しているクッキーを削除するようWebブラウザに要求する。
    # クッキーの削除を要求するには、setcookie関数でクッキーの有効期限を過去に設定する。

    // セッション終了処理
    public function endProc(){
        $this->clear();
        $this->delCookie();
        session_destroy();
    }

    # セッション終了処理をまとめて行う関数。自身のクラスのclearメソッドでセッション変数（$_SESSION）のデータをすべて破棄する。
    # セッションIDを保持しているクッキーを削除するようWebブラウザに要求し、
    # session_destroy関数でサーバー側に保存しているセッションデータを削除。


    //　session変数設定
    public function set($key, $value){
        $_SESSION[$key] = $value;
    }

    // セッション変数取得
    public function get($key, $default = null){
        if(isset($_SESSION[$key])){
            return $_SESSION[$key];
        }
        return $default;
    }

    // セッション変数削除
    public function remove($key){
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    // セッション変数クリア
    public function clear(){
        $_SESSION = [];
    }
}
