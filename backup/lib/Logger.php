<?php

class Logger
{
    // ログファイルが格納されるディレクトリへのパス
    // LOG_FILES_DIR
    protected $dir   = '';
    
    // ログファイルの名前をキーとして値にはファイルポイントが入る連想配列になっている。。
    // これってなんでプロパティとしてセットしているのだろうか？
    protected $files = [];

    public function __construct($dir = null)
    {
        $this->setDir($dir);

        // この関数はスクリプトが完了した時、あるいはexitされた時に実行される関数を登録できる。
        // $thisはこのクラス自体を参照するので、このクラスのshutdownメソッドを呼び出すことにしている。
        register_shutdown_function([$this, 'shutdown']);
    }

    // 多分アプリケーションによってログの場所を変えたい時とかに臨機応変に対応できるんだ思う。
    public function setDir($dir, $append = true)
    {
        if (empty($dir)) {
            if (defined('LOG_FILES_DIR')) {
                // /var/www/html/bbs/ebine_bbs6/logs
                $dir = LOG_FILES_DIR;
            } else {
                throw new Exception(__METHOD__ . '() You must specify the directory.');
            }
        }

        // file_exists($dir)はファイルまたはディレクトリが存在するか調べ、is_file($dir)はそれが通常ファイルかどうかを確認する。ファイルだった例外とする。
        // ファイルじゃなくてフォルダ
        if (file_exists($dir) && is_file($dir)) {
            throw new Exception(__METHOD__ . "() '{$dir}' is a file.");
        }

        // ディレクトリが存在しなかったらディレクトリを作成する
        if (!file_exists($dir)) {
            if ($append) {
                if (!mkdir($dir, 0777, true)) {
                    throw new Exception(__METHOD__ . "() Failed to create directory '{$dir}'.");
                }
            } else {
                throw new Exception(__METHOD__ . "() Directory not found. '{$dir}'");
            }
        }

        $this->dir = $dir;
    }

    // ログファイルを書き込んでいる$messageには172.28.128. /bbs/ebine_bbs6/index.php 400 Bad Requestこんな感じ
    // 既に同じログファイル名のものが存在する確認して日付とエラータイプをエラーメッセージに足して書き込む
    public function write($message, $errType = 'E_ALL', $name = 'default')
    {
        $fileName = $name . '.log';

        if (!isset($this->files[$fileName])) {
            // ファイルポインタが格納される
            // /var/www/html/bbs/ebine_bbs6/logs/default.log
            $this->files[$fileName] = fopen($this->dir . '/' . $fileName, 'a+');
        }

        // Y/m/d H:i:s E_Warning 172.28.128. /bbs/ebine_bbs6/index.php 400 Bad Request
        $log = '[' . date('Y/m/d H:i:s') . '] ' . $this->errTypeToString($errType) . ' ' . $message;
        fwrite($this->files[$fileName], $log . PHP_EOL);
    }

    public function shutdown()
    {
        foreach ($this->files as $fp) {
            fclose($fp);
        }

        // なんでこれ$filesを空に戻すのだろう？
        $this->files = [];
    }

    protected function errTypeToString($type)
    {
        switch ($type) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_CORE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_CORE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            default:
                if (defined('E_RECOVERABLE_ERROR') && $type === E_RECOVERABLE_ERROR) {
                    return 'E_RECOVERABLE_ERROR';
                } elseif (defined('E_DEPRECATED') && $type === E_DEPRECATED) {
                    return 'E_DEPRECATED';
                } elseif (defined('E_USER_DEPRECATED') && $type === E_USER_DEPRECATED) {
                    return 'E_USER_DEPRECATED';
                } else {
                    return $type;
                }
        }
    }
}
