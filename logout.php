<?php

require_once('config/init.php');

$controller = new Controller_Users();
$controller->setEnvs($_SERVER);
$controller->setSessions($_SESSION);
$controller->execute('logout');




