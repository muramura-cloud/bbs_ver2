<?php

// このクラスが担当する基本的な機能は
// 外部の入力をプロパティで管理、外部のサーバー情報をプロパティで管理、
// エラーの設定とエラーの記録
// 処理のスタートとページ遷移
abstract class Controller_Base
{
    // 序盤のsetEnvsでセットされる。
    // ページがリロードされるごとに'GET', 'POST', 'PUT', 'DELETE'のいずれかが入る。しかし、これなぜ取得する必要がある？
    protected $method = 'GET';

    // ページ番号とかタイトルとか外部からの入力値がくる
    protected $params = [];

    // 画像が入る。
    protected $files  = [];
    // loggerクラスのインスタンスが入る。loggerクラスをアイテムとして入手している感じがした。

    protected $logger = null;

    // 自分のサーバーの情報(ヘッダ、パス、スクリプトの位置のような 情報)がここのプロパティとしてセットされる。めっちゃ多い。
    // ページ遷移とかする時にこのプロパティにurlとかが使われる。
    protected $envs = [
        // [UNIQUE_ID] => X4PU8ItpwyhV2ouezUuTeQAAAAM
        // [HTTP_HOST] => murata-bbs.test
        // [HTTP_CONNECTION] => keep-alive
        // [HTTP_CACHE_CONTROL] => max-age=0
        // [HTTP_UPGRADE_INSECURE_REQUESTS] => 1
        // [HTTP_USER_AGENT] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36
        // [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9
        // [HTTP_REFERER] => http://murata-bbs.test/bbs/ebine_bbs6/index.php
        // [HTTP_ACCEPT_ENCODING] => gzip, deflate
        // [HTTP_ACCEPT_LANGUAGE] => ja,en-US;q=0.9,en;q=0.8
        // [HTTP_COOKIE] => PHPSESSID=nt6j3j30cbn5prek0mlk0eqldu
        // [PATH] => /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin
        // [SERVER_SIGNATURE] => 
        // [SERVER_SOFTWARE] => Apache
        // [SERVER_NAME] => murata-bbs.test
        // [SERVER_ADDR] => 172.28.128.3
        // [SERVER_PORT] => 80
        // [REMOTE_ADDR] => 172.28.128.1
        // [DOCUMENT_ROOT] => /var/www/html
        // [REQUEST_SCHEME] => http
        // [CONTEXT_PREFIX] => 
        // [CONTEXT_DOCUMENT_ROOT] => /var/www/html
        // [SERVER_ADMIN] => system@crewbit.co.jp
        // [SCRIPT_FILENAME] => /var/www/html/bbs/ebine_bbs6/index.php
        // [REMOTE_PORT] => 55816
        // [GATEWAY_INTERFACE] => CGI/1.1
        // [SERVER_PROTOCOL] => HTTP/1.1
        // [REQUEST_METHOD] => GET
        // [QUERY_STRING] => 
        // [REQUEST_URI] => /bbs/ebine_bbs6/index.php
        // [SCRIPT_NAME] => /bbs/ebine_bbs6/index.php
        // [PHP_SELF] => /bbs/ebine_bbs6/index.php
        // [REQUEST_TIME_FLOAT] => 1602475248.894
        // [REQUEST_TIME] => 1602475248
        'http-host'       => 'localhost',
        'server-name'     => 'localhost',
        'server-port'     => '80',
        'server-protocol' => 'HTTP/1.0',
        'remote-addr'     => '127.0.0.1',
        'request-uri'     => '/',
    ];

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        $params = $this->params;
        if (isset($params[$key]) && $params[$key] !== '') {
            return $params[$key];
        }
    }

    // $_SERVERに内容をプロパティにセットして送信方式（メソッドもプロパティにセットする）
    public function setEnvs(array $envs)
    {
        foreach ($envs as $key => $value) {
            $this->setEnv($key, $value);
        }

        if ($_method = $this->getEnv('Request-Method')) {
            $this->setMethod($_method);
        }
    }

    // $envsプロパティにhttpなどのサーバー情報を追加する
    public function setEnv($key, $value)
    {
        // normalizeEnvKey($key)はハイフンをアンダーバーに置き換えるメソッド
        $this->envs[$this->normalizeEnvKey($key)] = $value;
    }

    public function getEnvs()
    {
        return $this->envs;
    }

    public function getEnv($key)
    {
        $_key = $this->normalizeEnvKey($key);

        if (isset($this->envs[$_key])) {
            return $this->envs[$_key];
        }
    }

    public function setMethod($method)
    {
        $_method = strtoupper($method);

        if (in_array($_method, array('GET', 'POST', 'PUT', 'DELETE'))) {
            $this->method = $_method;
        } else {
            trigger_error(__METHOD__ . '() Invalid method: ' . $method, E_USER_ERROR);
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    // 簡単に説明すると$_FILE['img']を返す。dataというキーを追加して画像データを保存している。
    public function getFile($key, $removeTmpFile = true)
    {
        $files = $this->files;
        if (isset($files[$key]) && !empty($files[$key])) {
            $file = $files[$key];
            if (!empty($file['tmp_name']) && $file['size'] >= 1) {
                // これ画像ファイルのマジックナンバー（画像データ）を返している。
                $file['data'] = file_get_contents($file['tmp_name']);
                if ($removeTmpFile) {
                    unlink($file['tmp_name']);
                }

                return $file;
            }
        }
    }

    // Loggerインスタンスをプロパティにセットして独自エラー設定をする
    public function setUp()
    {
        // ログファイルの保存先をプロパティにセットする
        $this->logger = new Logger();

        // 実行時エラーをユーザーが定義するための関数。trigger_error()内部で使用しているから設定しているっぽい。
        set_error_handler([$this, 'errorHandler']);
    }

    // ロガーインスタンスを生成し、実行時エラーを設定（set_error_handler）している。
    // このメソッドの中で全てが完結している。よってここで例外をキャッチしている。
    public function execute($action)
    {
        try {
            $this->setUp();

            // このクラスを継承したBulletinクラスでindex()とかがある。
            if (!method_exists($this, $action)) {
                throw new Exception(__METHOD__ . "() Action not found. '{$action}'");
            }
            $this->$action();
        } catch (Exception $e) {
            $this->err500($e->getMessage());
        }
    }

    // $uriはindex.php$paramsは['page' => $page]
    public function redirect($uri, $params = [], $exit = true)
    {
        if (!empty($params)) {
            // $uriのなかで'?'がないんだったら？をつけるしあるんだった&を入れる。
            $glue = (strpos($uri, '?') === false) ? '?' : '&';
            // http_build_query()は連想配列のキーと値の間に＝を入れて文字列にするだけ。接続子はパラメータで＆を指定している。
            $uri .= $glue . http_build_query($params, '', '&');
        }

        header('Location: ' .  BASE_URI_PATH . '/' . $uri);

        if ($exit) {
            exit;
        }
    }

    // trigger_error()で引っかかったエラーのメッセージをここで編集してログファイルに書き込みする。あと、普通のプログラム上のエラーもここに集まる。プログラマーのミスによるエラーがここに吸収される。
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $message = $errstr;

        // エラーが発生したファイルの名前
        if (!empty($errfile)) {
            $message .= ' file: '  . $errfile;
        }

        if (!empty($errline)) {
            $message .= ' line: '  . $errline;
        }

        $this->log($message, $errno);

        return false;
    }

    // エラーメッセージに具体的な情報を加えロガークラスのメソッドを叩いている。
    // これってLoggerクラスじゃないんだね
    public function log($message, $errType = E_ALL)
    {
        if ($this->logger) {
            // 172.28.128.1REMOTE_ADDRはIPアドレスが入っている。
            // 172.28.128. 400 Bad Request 
            $message = $this->getEnv('Remote-Addr') . ' '
                // REQUEST_URIは/bbs/ebine_bbs6/index.phpリクエストしてきたファイルパスが入っている。
                // 172.28.128. /bbs/ebine_bbs6/index.php 400 Bad Request 
                . $this->getEnv('Request-Uri') . ' '
                . $message;

            // ファイル名は指定していないのでdefault
            $this->logger->write($message, $errType);
        }
    }

    // err系のメソッドはhttpステータス情報を送信してレンダーしてログファイルに書き込みを行う
    public function err400($message = "", $exit = true)
    {
        // SERVER_PROTOCOLはHTTP/1.1
        $protocol = $this->getEnv('Server-Protocol');
        // httpステータスを送信する
        header("{$protocol} 400 Bad Request");

        $this->render('error/400.php', [
            'message'    => $message,
            'requestUri' => $this->getEnv('Request-Uri'),
        ]);

        $this->log('400 Bad Request ' . $message, E_WARNING);

        if ($exit) {
            exit;
        }
    }

    public function err404($message = "", $exit = true)
    {
        $protocol = $this->getEnv('Server-Protocol');
        header("{$protocol} 404 Not Found");

        $this->render('error/404.php', [
            'message'    => $message,
            'requestUri' => $this->getEnv('Request-Uri'),
        ]);

        $this->log('404 Not Found ' . $message, E_NOTICE);

        if ($exit) {
            exit;
        }
    }

    // $messageには例外のメッセージが入る。
    public function err500($message = "", $exit = true)
    {
        $protocol = $this->getEnv('Server-Protocol');
        header("{$protocol} 500 Internal Server Error");

        $this->render('error/500.php', [
            'message'    => $message,
            'requestUri' => $this->getEnv('Request-Uri'),
        ]);

        $this->log('500 Internal Server Error ' . $message, E_ERROR);

        if ($exit) {
            exit;
        }
    }

    // $nameにはhtmlディレクトリに存在する表示したいファイル名とが入る。'bulletin/index.php'とかも。htmlフォルダのファイル名が入ってくる。
    protected function render($name, $data = [])
    {
        // $templateにはHTML_FILES_DIR/bulletin/index.phpが入る。要するにこれはパスを生成している。
        if ($template = $this->getTemplate($name)) {
            // get_object_vars($this)ではこのクラスのプロパティが連想配列で格納される。
            // 例えば、httpやrequestなどの情報（$envs）やtitleやbodyなどの外部からの入力（$paramsと$files）、Loggerクラスあとは、画像の保存先や通信方式（getなのかpostなのか）
            // extractは配列からシンボルテーブルに変数をインポートする。EXTR_OVERWRITEは変数の衝突があった際に新しい変数の内容に上書きする。$_GET や $_FILES のようなユーザーの入力による値をextract()の引数に使用してはいけません。
            // 具体的には関数を呼び出した後のスクリプトで、ここで読み込んだ配列のキーを変数名として使うことができる。いきなり、$titleと指定すると連想配列のtitleをキーとする値が参照される。
            extract(array_merge(get_object_vars($this), $data), EXTR_OVERWRITE);
            // pre_print(array_merge(get_object_vars($this), $data));

            include($template);
        } else {
            trigger_error(__METHOD__ . '() Template not found: ' . $name, E_USER_ERROR);
        }
    }

    // HTMLディレクトリに存在するテンプレートファイルののパスを作るメソッド。
    protected function getTemplate($name)
    {
        // $pathには/var/www/html/bbs/ebine_bbs6/html/error/500.phpが入る。
        $path = HTML_FILES_DIR . '/' . $name;

        if (file_exists($path)) {
            return $path;
        }
    }

    // '_'を'-'へ置き換える。
    protected function normalizeEnvKey($key)
    {
        return strtolower(str_replace('_', '-', $key));
    }
}
