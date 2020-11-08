<?php

use Pecee\SimpleRouter\SimpleRouter;
use App\Controllers\Controller;

SimpleRouter::get('/', function() {
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

SimpleRouter::post('/pkmpvp', function() {
    $reader = new Controller();
    $reader->storePkmPve();
});

SimpleRouter::delete('/pkmpvp/{idPkm}', function($idPkm) {
    $reader = new Controller();
    $reader->deletePkmPve($idPkm);
});

SimpleRouter::post('/pkmpvp/{idPkm}', function($idPkm) {
    $reader = new Controller();
    $reader->updatePkmPve($idPkm);
});

SimpleRouter::get('/pkmpvp/{idPkm}', function($idPkm) {
    $reader = new Controller();
    $reader->getPkmPve($idPkm);
});