<?php
// このファイルも基本はindex.phpからの遷移で到達する

require_once('config/init.php');

$controller = new Controller_Bulletin();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setFiles($_FILES);
$controller->setEnvs($_SERVER);
$controller->setSessions($_SESSION);
$controller->execute('edit');
