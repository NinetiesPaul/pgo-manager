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

    protected $addNewChargeMoves = [
        "Scorching Sands" => [
            "type" => "Ground",
            "weakAgainst" => [
                "Bug",
                "Flying",
                "Grass"
            ],
            "goodAgainst" => [
                "Electric",
                "Fire",
                "Poison",
                "Rock",
                "Steel"
            ],
            "energy" => "-50",
            "power" => "80",
            "dpe" => "1.66"
        ],
        "Trailblaze" => [
            "type" => "Grass",
            "weakAgainst" => [
                "Bug",
                "Dragon",
                "Fire",
                "Flying",
                "Grass",
                "Poison",
                "Steel"
            ],
            "goodAgainst" => [
                "Ground",
                "Rock",
                "Water"
            ],
            "energy" => "-50",
            "power" => "65",
            "dpe" => "1.3"
        ],
        "Triple Axel" => [
            "type" => "Ice",
            "weakAgainst" => [
                "Fire",
                "Ice",
                "Steel",
                "Water"
            ],
            "goodAgainst" => [
                "Dragon",
                "Flying",
                "Grass",
                "Ground"
            ],
            "energy" => "-45",
            "power" => "60",
            "dpe" => "1.33"
        ],
    ];

    protected $forceMoves = [
        "charge" => [
            'Scorching Sands' => [
                "Sandslash", "Ninetales", "Arcanine", "Rapidash", "Entei", "Trapinch", "Vibrava", "Flygon", "Claydol", "Hippowdon", "Magmar", "Magmortar", "Diggersby", "Excadrill", "Sandygast", "Palossand"
            ]
        ],
        "charge" => [
            'Trailblaze' => [
                "Tauros", "Sudowoodo", "Mareep", "Flaaffy", "Ampharos", "Scyther", "Scizor", "Kleavor", "Teddiursa", "Ursaring", "Ursaluna", "Deerling", "Sawsbuck", "Rockruff", "Lycanroc", "Fomantis", "Lurantis", "Skwovet", "Stunky", "Skuntank", "Galarian Meowth", "Perrserker", "Girafarig", "Phanpy"
            ]
        ],
        "charge" => [
            'Triple Axel' => [
                "Galarian Mr. Mime", "Sneasel", "Weavile", "Hitmontop", "Kirlia", "Gardevoir", "Lopunny", "Mr. Rime", "Steenee", "Tsareena"
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
    public function jsBuilderPokeData($mergeSources = false)
    {
        $stats = $this->jsonUtil->getStats();
        $types = $this->jsonUtil->getType();
        $currentMoves = $this->jsonUtil->getCurrentPkmMoves();
        $csv = $this->generalUtil->getCsv();

        if ($mergeSources) {
            $secondSource = file_get_contents("includes/files/db/v2/pokedata_as_json.json");
            $secondSource = json_decode($secondSource, true);
        }

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
            $pokeData['moveset']['quick'] = ($mergeSources && isset($secondSource[$name]['quick'])) ? array_values(array_unique(array_merge($currentMoves[$name]['quick'], $secondSource[$name]['quick']))) : $currentMoves[$name]['quick']; //array_merge($currentMoves[$name]['quick'],  isset($this->forceMoves[$name]['quick']) ? $this->forceMoves[$name]['quick'] : []);
            $pokeData['moveset']['charge'] = ($mergeSources && isset($secondSource[$name]['charge'])) ? array_values(array_unique(array_merge($currentMoves[$name]['charge'], $secondSource[$name]['charge']))) : $currentMoves[$name]['charge']; //array_merge($currentMoves[$name]['charge'], isset($this->forceMoves[$name]['charge']) ? $this->forceMoves[$name]['charge'] : []);
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

        $chargeMoves = array_merge($chargeMoves, $this->addNewChargeMoves);

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