<?php

// ユニークなセッションIDが生成される。 
session_start();

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Tokyo');

define('BASE_URI_PATH',   '/bbs_ver2');
define('PROJECT_ROOT',    '/var/www/html' . BASE_URI_PATH);

define('CLASS_FILES_DIR', PROJECT_ROOT . '/classes');
define('LIB_FILES_DIR',   PROJECT_ROOT . '/lib');
define('HTML_FILES_DIR',  PROJECT_ROOT . '/html');
define('LOG_FILES_DIR',   PROJECT_ROOT . '/logs');

require_once(PROJECT_ROOT  . '/config/database.php');
require_once(PROJECT_ROOT  . '/lib/functions.php');
require_once(LIB_FILES_DIR . '/ClassLoader.php');

// PROJECT_ROOTが/var/www/html/bbs/ebine_bbs6なので
// CLASS_FILES_DIRは/var/www/html/bbs/ebine_bbs6/classesとなる。
// requireなどでファイルを探す時の出発地点を設定している。
add_include_path(CLASS_FILES_DIR);
add_include_path(LIB_FILES_DIR);

// この関数は未定義のクラスが呼ばれた時に自動で呼び出す関数を指定できる
// クラスが増えれば増えるほどファイルで多くのrequire()を書かなきゃいけなくてめんどい。
// 多分だけど、引数が配列になっているのはクラスのメソッドを自動で呼び出すことにしてるから。普通は関数名を引数に入れる。。
spl_autoload_register(['ClassLoader', 'autoload']);
