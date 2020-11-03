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

SimpleRouter::get('/pokedb', function() {
    $reader = new Controller();
    $reader->pokeDB();
});

SimpleRouter::get('/getPokemon/{name}', function($name) {
    $reader = new Controller();
    $reader->getPokemon($name);
});