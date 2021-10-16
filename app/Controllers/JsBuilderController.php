<?php

namespace App\Controllers;

use App\Utils\JsonUtil;
use App\Utils\GeneralUtils;

class JsBuilderController
{
    protected $jsonUtil;

    protected $generalUtil;

    public function __construct()
    {
        $this->jsonUtil = new JsonUtil();
        $this->generalUtil = new GeneralUtils();
    }

    /*
     * This function is responsible for generating the pokemon database files for the pure front end version of this app
     */
    public function jsBuilderPokeData()
    {
        $stats = $this->jsonUtil->getStats();
        $types = $this->jsonUtil->getType();
        $currentMoves = $this->jsonUtil->getCurrentPkmMoves();

        $jsDB = "var pokeDB = {\n";

        foreach ($stats as $name => $pkm) {
            $defense_data['vulnerable_to'] = $currentMoves[$name]['vulnerable_to'];
            $defense_data['resistant_to'] = $currentMoves[$name]['resistant_to'];

            $pokeData['id'] = str_pad($pkm['id'], 3, '0', 0);
            $pokeData['imgurl'] = $this->generalUtil->formatImgUrl($pkm['id'], $name);
            unset($pkm['id']);
            $pokemonType = explode("/", $types[$name]);
            $pokeData['stats'] = $pkm;
            $pokeData['type'] = $pokemonType;
            $pokeData['name'] = $name;
            $pokeData['moveset']['quick'] = $currentMoves[$name]['quick'];
            $pokeData['moveset']['charge'] = $currentMoves[$name]['charge'];
            $pokeData['defense_data'] = $defense_data;

            $jsDB .= "\"$name\": " . json_encode($pokeData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db_pokedata.js', $jsDB);

    }

    /*
     * This function is responsible for generating the pokemon database files for the pure front end version of this app
     */
    public function jsBuilderQuick()
    {

        $quickMoves = $this->jsonUtil->getQuickMoves();

        $jsDB = "var quickMoveDB = {\n";

        foreach ($quickMoves as $quickMove => $data) {
            $moveData['type'] = $data['type'];
            $moveData['weakAgainst'] = $data['weakAgainst'];
            $moveData['goodAgainst'] = $data['goodAgainst'];
            $moveData['ept'] = $data['ept'];
            $moveData['dpt'] = $data['dpt'];

            $jsDB .= "\"$quickMove\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db_quick.js', $jsDB);

    }

    /*
     * This function is responsible for generating the pokemon database files for the pure front end version of this app
     */
    public function jsBuilderCharge()
    {

        $chargeMoves = $this->jsonUtil->getChargeMoves();

        $jsDB = "var chargeMoveDB = {\n";

        foreach ($chargeMoves as $chargeMove => $data) {
            $moveData['type'] = $data['type'];
            $moveData['weakAgainst'] = $data['weakAgainst'];
            $moveData['goodAgainst'] = $data['goodAgainst'];

            $jsDB .= "\"$chargeMove\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db_charge.js', $jsDB);

    }
}