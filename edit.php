<?php
// このファイルも基本はindex.phpからの遷移で到達する

require_once('config/init.php');

$controller = new Controller_Bulletin();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setFiles($_FILES);
$controller->setEnvs($_SERVER);
$controller->setSessions($_SESSION);
// Loggerインスタンスをセットして、ユーザーエラー設定をして、
// editメソッドを叩いて、
// 投稿のIDとパスワードとページ番号を取得して、
// IDが不正だったら400ステータスコード送信して、400ページ表示して、ログに書き込む。
// 編集対象となる投稿を取得して、取得に失敗したら404ステータスコード送信して、404ページ表示して、ログに書き込む。
// 投稿に元々セットされていたデータを取得して、
// form.phpを編集版（送信先をこのedit.phpにする）にするために$isEditFormをtrueにして、
// 入力されたパスワードのチェックだけしてbulletin/edit.phpを表示する。
// パスワードがあってたら編集用フォームを表示する（画像とか元々セットされていた入力値を表示）
// 編集ボタン押したら、再度このファイルが読み込まれて（$isEditFormをtrueだから）、
// 編集入力値を受け取って、画像も削除するかどうか受け取って、
// データベースインスタンス取得して、
// 入力値バリデーションして、
// 画像アップローダーインスタンス化（画像の保存先をセット）して、
// 外部からの画像を取得してきて、
// もし、画像削除ボタンが押されていてなくて画像が取得されていたら画像をバリデーションして画像をアップロードする。
// もし、画像削除ボタンが押されたら投稿に画像があるかどうか確認して削除してデータベースのファイル名をnullにする。
// データベース編集してトップページにリダイレクトする。
$controller->execute('edit');
