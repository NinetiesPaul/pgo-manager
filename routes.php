<?php

use Pecee\SimpleRouter\SimpleRouter;
use App\Controllers\IndexController;
use App\Controllers\DataController;

SimpleRouter::get('/', function() {
    $index = new IndexController();
    $index->index();
});

SimpleRouter::get('/reader', function() {
    $index = new IndexController();
    $index->reader();
});