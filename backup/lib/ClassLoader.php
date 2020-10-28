<?php

// 未定義とされたクラスを読み込むクラス
class ClassLoader
{
    // この引数には未定義として呼ばれたクラスが入る。
    public static function autoload($className)
    {
        //   第二引数がfalseになっているのは自動で__autoload()が呼ばれないようにしている。
        if (class_exists($className, false)) {
            return;
        }

        // クラスの名前で「_」を「/」に置き換えるUploader_FileがあったらUploader/File.phpになる。
        $filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        // include_pathとはインクルードするファイルのディレクトリにの設定を行う関数で
        // get_include_path()はその設定情報を取得する関数
        // .:/usr/share/pear:/usr/share/php:/var/www/html/bbs/ebine_bbs6/classes:/var/www/html/bbs/ebine_bbs6/lib
        // $includePaths = [
        //     [0] => '.',
        //     [1] => '/usr/share/pear',
        //     [2] => '/usr/share/php',
        //     [3] => '/var/www/html/bbs/ebine_bbs6/classes',
        //     [4] => '/var/www/html/bbs/ebine_bbs6/lib',
        // ];
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includePaths as $includePath) {
            $fullPath = $includePath . DIRECTORY_SEPARATOR . $filePath;

            // ファイルが存在して読み込み可能かどうかを確認して読み込めるなら読み込む
            if (is_readable($fullPath)) {
                require_once($fullPath);
                break;
            }
        }
    }
}
