<?php

use Pecee\SimpleRouter\SimpleRouter;
use App\Controllers\IndexController;
use App\Controllers\Controller;

SimpleRouter::get('/', function() {
    $index = new IndexController();
    $index->index();
});

SimpleRouter::get('/reader', function() {
    $reader = new Controller();
    $reader->teamBuilder();
});

SimpleRouter::get('/main_reader', function() {
    $reader = new Controller();
    $reader->debug();
});

SimpleRouter::get('/name', function() {
    $reader = new Controller();
    $reader->name();
});

SimpleRouter::get('/getPokemon/{name}', function($name) {
    $reader = new Controller();
    $reader->getPokemon($name);
});