<?php

error_reporting(E_ERROR | E_WARNING);

use Pecee\SimpleRouter\SimpleRouter;

include 'vendor/autoload.php';

require_once 'helpers.php';
require_once 'routes.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

SimpleRouter::setDefaultNamespace('Controllers');
SimpleRouter::start();