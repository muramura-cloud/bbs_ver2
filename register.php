<?php

require_once('config/init.php');

$controller = new Controller_Users();
$controller->setParams(array_merge($_GET, $_POST));
$controller->setEnvs($_SERVER);
$controller->execute('register');

