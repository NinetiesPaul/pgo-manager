<?php

use Pecee\SimpleRouter\SimpleRouter;
use App\Controllers\MainController;
use App\Controllers\PkmPveController;

SimpleRouter::get('/', function() {
    $controller = new MainController();
    $controller->teamBuilder();
});

SimpleRouter::get('/pokedb', function() {
    $controller = new MainController();
    $controller->pokeDB();
});

SimpleRouter::get('/json_update', function() {
    $controller = new MainController();
    $controller->jsonUpdate();
});

SimpleRouter::get('/getPokemon/{name}', function($name) {
    $controller = new MainController();
    $controller->getPokemon($name);
});

SimpleRouter::post('/pkmpve', function() {
    $reader = new PkmPveController();
    $reader->storePkmPve();
});

SimpleRouter::delete('/pkmpve/{idPkm}', function($idPkm) {
    $reader = new PkmPveController();
    $reader->deletePkmPve($idPkm);
});

SimpleRouter::post('/pkmpve/{idPkm}', function($idPkm) {
    $reader = new PkmPveController();
    $reader->updatePkmPve($idPkm);
});

SimpleRouter::get('/pkmpve/{idPkm}', function($idPkm) {
    $reader = new PkmPveController();
    $reader->getPkmPve($idPkm);
});