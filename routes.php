<?php

use Pecee\SimpleRouter\SimpleRouter;
use App\Controllers\MainController;
use App\Controllers\PkmPveController;

SimpleRouter::get('/', function() {
    $controller = new MainController();
    $controller->teamBuilder();
});

SimpleRouter::get('/jsonUpdate', function() {
    $controller = new MainController();
    $controller->jsonUpdate();
});

SimpleRouter::get('/getPokemon/{name}', function($name) {
    $controller = new MainController();
    $controller->getPokemon($name);
});

SimpleRouter::get('/getMove/{name}/{type}', function($name, $type) {
    $controller = new MainController();
    $controller->getMove($name, $type);
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

SimpleRouter::get('/jsBuilderQuick', function() {
    $reader = new MainController();
    $reader->jsBuilderQuick();
});

SimpleRouter::get('/jsBuilderCharge', function() {
    $reader = new MainController();
    $reader->jsBuilderCharge();
});

SimpleRouter::get('/jsBuilderPokeData', function() {
    $reader = new MainController();
    $reader->jsBuilderPokeData();
});
