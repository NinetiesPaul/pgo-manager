<?php

namespace Classes;

class JsBuilderController
{
    protected $jsonUtil;

    protected $generalUtil;

    protected $forceImgUrl = [
        "Galarian Sirfetch’d",
        "Galarian Runerigus",
        "Galarian Obstagoon",
        "Galarian Perrserker",
        "Galarian Mr. Rime",
        "Baile Oricorio",
        "Sensu Oricorio",
        "Pompom Oricorio",
        "Pau Oricorio",
        "Midday Lycanroc",
        "Midnight Lycanroc",
    ];

    protected $forceName = [
        "Galarian Sirfetch’d",
        "Galarian Runerigus",
        "Galarian Obstagoon",
        "Galarian Perrserker",
        "Galarian Mr. Rime",
    ];

    protected $forceMoves = [
        "Poliwrath" => [
            'quick' => [
                '*Counter'
            ]
        ],
        "Politoed" => [
            'charge' => [
                '*Ice Beam'
            ]
        ],
        "Greninja" => [
            'charge' => [
                '*Hydro Cannon'
            ]
        ],
    ];

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
        $csv = $this->generalUtil->getCsv();

        $jsDB = "var pokeDB = {\n";

        foreach ($stats as $name => $pkm) {
            echo "\n Processing $name";
            $defense_data['vulnerable_to'] = $types[$name]['vulnerable_to'];
            $defense_data['resistant_to'] = $types[$name]['resistant_to'];

            $pokeData['id'] = str_pad($pkm['id'], 3, '0', 0);
            $pokeData['imgurl'] = (in_array($name, $this->forceImgUrl))
                ? $this->generalUtil->formatImgurlForJsBuilder($name)
                : $this->generalUtil->formatImgUrl($pkm['id'], $name, $csv);
            unset($pkm['id']);
            $pokeData['stats'] = $pkm;
            $pokeData['type'] = $types[$name]['type'];
            $pokeData['name'] = $name;
            $pokeData['moveset']['quick'] = array_merge($currentMoves[$name]['quick'],  isset($this->forceMoves[$name]['quick']) ? $this->forceMoves[$name]['quick'] : []);
            $pokeData['moveset']['charge'] = array_merge($currentMoves[$name]['charge'], isset($this->forceMoves[$name]['charge']) ? $this->forceMoves[$name]['charge'] : []);
            $pokeData['defense_data'] = $defense_data;

            if (in_array($name, $this->forceName)) {
                $name = $this->generalUtil->formatNameForJsBuilder($name);
                $pokeData['name'] = $name;
            }

            $jsDB .= "\"$name\": " . json_encode($pokeData, JSON_PRETTY_PRINT) . ",\n";
            echo "\n Finished processing $name";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db/pokedata.js', $jsDB);

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

        file_put_contents('includes/files/db/quick.js', $jsDB);

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
            $moveData['energy'] = $data['energy'];
            $moveData['power'] = $data['power'];
            $moveData['dpe'] = $data['dpe'];

            $jsDB .= "\"$chargeMove\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db/charge.js', $jsDB);

    }
}