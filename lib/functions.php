<?php

function html_escape($text, $flags = null, $encoding = null) {
    if (empty($flags)) {
        $flags = ENT_QUOTES;
    }

    if (empty($encoding)) {
        $encoding = 'UTF-8';
    }

    return htmlentities($text, $flags, $encoding);
}

function h($text, $flags = null, $encoding = null) {
    return html_escape($text, $flags, $encoding);
}

function mb_trim($string) {     
    return preg_replace('/\A[\x00\s]++|[\x00\s]++\z/u', '', $string);
}

function is_empty($value) {     
    return ($value === '' || $value === null || $value === []);
}

function get_uri($uri) {
    // BASE_URI_PATHは/bbs
    if (defined('BASE_URI_PATH')) {
        // この関数を使う人が$uriの先頭にスラッシュを入れても入れなくても大丈夫なようにltrim()している
        $uri = BASE_URI_PATH . '/' . ltrim($uri, '/');
    }

    return $uri;
}

// 自然数かどうかを確認する関数（自然数が０を含むかどうか設定できる）
function is_natural_number($num, $includeZero = false) {
    if (is_int($num)) {
        return ($includeZero) ? ($num >= 0) : ($num > 0);
    } elseif (is_string($num)) {
        if ($num === "0" && $includeZero) {
            return true;
        } elseif (preg_match('/^[1-9][0-9]*$/', $num) === 1) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// ランダムな文字列を生み出す。生み出す文字列の長さはデフォルトで１６。
function generate_random_string($length = 16) {
    $charas  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLen = strlen($charas);

    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $charas[mt_rand(0, $charLen - 1)];
    }

    return $string;
}

// データベースの設定を取得する関数
// $config = [
//     'host' => 'DATABASE_HOST',
//     'name' => 'DATABASE_NAME',
//     'user' => 'DATABASE_USER',
// ];
function get_db_config() {
    $config = [];

    $keys = ['HOST', 'NAME', 'USER', 'PASSWORD'];
    foreach ($keys as $key) {
        if (defined('DATABASE_' . $key)) {
            // constant()は定数の値を返す。型は数値か文字列
            $config[strtolower($key)] = constant('DATABASE_' . $key);
        } else {
            throw new Exception(__FUNCTION__ . "() DATABASE_{$key} is not defined.");
        }
    }

    return $config;
}

// include_pathはrequire()、include()、fopen()、file_get_contents()などのPHPのファイルを読み込む関数がファイルを検索するディレクトリのことで
// この関数はそのディレクトリを追加するための関数
function add_include_path($path, $prepend = false) {
    $current = get_include_path();

    if ($prepend) {
        // PATH_SEPARATORはコロン意味する
        set_include_path($path . PATH_SEPARATOR . $current);
    } else {
        set_include_path($current . PATH_SEPARATOR . $path);
    }
}

/**
 * Utility function for debug.
 */
function dump(/* plural args */) {
    echo '<pre style="background: #fff; color: #333; ' .
        'border: 1px solid #ccc; margin: 5px; padding: 10px;">';

    foreach (func_get_args() as $value) {
        print_r($value);
    }

    echo '</pre>';
}
