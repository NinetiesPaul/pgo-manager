<?php

error_reporting(E_ERROR | E_WARNING);

include 'vendor/autoload.php';

use Classes\JsBuilderController;
use Classes\JsonUtil;

switch ($argv[1]) {
    case 'jsquick':
        $class = new JsBuilderController();
        $class->jsBuilderQuick();
        break;
    
    case 'jscharge':
        $class = new JsBuilderController();
        $class->jsBuilderCharge();
        break;

    case 'jsdb':
        $class = new JsBuilderController();
        $class->jsBuilderPokeData();
        break;

    case 'update_json':
        $class = new JsonUtil();
        $class->massUpdate();
        break;

    case 'update_jsquick':
        $class = new JsonUtil();
        $class->getQuickMoves(true);
        break;

    case 'update_jscharge':
        $class = new JsonUtil();
        $class->getChargeMoves(true);
        break;

    default:
        echo "Unknown option '$argv[1]'";
        break;
}
