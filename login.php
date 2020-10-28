<?php

require_once('config/init.php');

// 多分セッション情報とか受け取るのかな？
$controller = new Controller_Users();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setEnvs($_SERVER);
$controller->setSessions($_SESSION);
$controller->execute('login');
