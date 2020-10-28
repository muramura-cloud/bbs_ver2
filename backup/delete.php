<?php
// 基本的にこのファイルはindex.phpからの遷移から到達する。

require_once('config/init.php');


$controller = new Controller_Bulletin();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setFiles($_FILES);
$controller->setEnvs($_SERVER);
// まず最初にset_upメソッド叩かれてユーザーエラー設定して、Loggerインスタンスをプロパティにセットして、
// delete()メソッドを叩く。
// index.phpから送られてきた、投稿IDとパスワードとページ番号を取得して、
// IDが不正だったら400 Bad Requestをhttpステータスを送信して、400ページを表示してログに書き込む。
// データベースセットを取得して、
// IDをもとに削除対象となる投稿を取得して、
// うまく取得できなかったら、404 Not Foundをhttpステータスを送信して、404ページを表示してログに書き込む。
// 投稿にパスワードがあるかどうかチェックして、なかったらbulletin/delete.phpをロードして、「この投稿にはパスワードがセットされていないので削除できません。」と表示する。
// 入力されたパスワードがあっているかどうか確認してあってたらbulletin/delete.phpをロードして、「本当に削除しますか？」と確認する。
// 「YES」が押されたらまたこのファイルが再読み込みされて、投稿を削除したことにする。（画像がアップされていたらその画像を消す。）
// 現在のページがトータルページ数を飛び出ないように設定して、
// index.phpへリダイレクト。（この際に現在のページ番号をパラメータにセット）
$controller->execute('delete');
