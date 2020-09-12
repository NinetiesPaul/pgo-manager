<?php

use Pecee\SimpleRouter\SimpleRouter;
use App\Controllers\IndexController;
use App\Controllers\ReaderController;

SimpleRouter::get('/', function() {
    $index = new IndexController();
    $index->index();
});

SimpleRouter::get('/reader', function() {
    $reader = new ReaderController();
    $reader->reader();
});

SimpleRouter::get('/populate_pokemons', function() {
    $reader = new ReaderController();
    $reader->populate_pokemons();
});

SimpleRouter::get('/populate_quick', function() {
    $reader = new ReaderController();
    $reader->populate_quick();
});

SimpleRouter::get('/populate_charge', function() {
    $reader = new ReaderController();
    $reader->populate_charge();
});