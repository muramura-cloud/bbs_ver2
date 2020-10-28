<?php

// 基本的に使う関数、よく使うパス、ファイル検索の起点を追加して、クラス自動読み込み設定
require_once('config/init.php');

// ここでController_Bulletinクラスをファイルを読み込まずに使っているように見えるけど、init.phpのメソッド自動読み込み処理をしているから自動でController_Bulletinは読み込まれる。
// ClassLoaderのautoload(Controller_Bulletin)が呼ばれる。（クラス名がパスとして加工し易い形になっている）
// $filePathがController/Bulletin.phpとなって、
// /var/www/html/bbs/ebine_bbs6/classes/Controller/Bulletin.phpに引っかかり
// require_once('/var/www/html/bbs/ebine_bbs6/classes/Controller/Bulletin.php')で読み込まれる。
// インスタンス化と同時に画像に保存先がプロパティにセットされる。
$controller = new Controller_Bulletin();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setFiles($_FILES);
$controller->setEnvs($_SERVER);
$controller->setSessions($_SESSION);
$controller->execute('index');
