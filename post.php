<?php

require_once('config/init.php');

$controller = new Controller_Bulletin();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setFiles($_FILES);
$controller->setEnvs($_SERVER);
$controller->setSessions($_SESSION);
// Loggerインスタンスをプロパティにセットしてユーザーエラー設定をして
// 外部の入力を受け取って、
// データベースの編集セットを受け取って
// 入力値のバリデーションして、
// 画像のバリデーションして、
// バリデーションエラーがあったら再リロードしてエラ〜メッセージを表示（実質form.phpで表示）。エラーがなかったらindex.phpにリダイレクト
$controller->execute('post');
