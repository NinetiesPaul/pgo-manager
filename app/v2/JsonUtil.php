<?php

namespace Classes\v2;

class JsonUtil {
    
    protected $quickMovesDB = [];
    protected $chargeMovesDB = [];
    protected $pkmDB = [];

    public function run()
    {
        $pokedex = file_get_contents("https://pokemon-go-api.github.io/pokemon-go-api/api/pokedex.json");
        $pokemons = json_decode($pokedex);

        $regionForms = [];
        
        foreach ($pokemons as $pokemon) {
            $types = $this->format_types($pokemon);

            $stats = [
                "atk" => $pokemon->stats->attack,
                "def" => $pokemon->stats->defense,
                "sta" => $pokemon->stats->stamina,
            ];

            $new_pokemon = 
                [
                    "id" => str_pad($pokemon->dexNr, 3, '0', 0),
                    "stats" => $stats,
                    "type" => $types,
                    "name" => $pokemon->names->English,
                    "moveset" => [
                        "quick" => $this->format_quick_moves($pokemon),
                        "charge" => $this->format_charge_moves($pokemon),
                    ],
                    "defense_data" =>$this->getPokemonDefenseData($types),
                ];
            $this->pkmDB[] = $new_pokemon;

            if (isset($pokemon->regionForms) && $pokemon->regionForms != "null"){
                foreach ($pokemon->regionForms as $regionForm) {
                   $regionForms[] = $regionForm;
                }
            }
        }

        foreach ($regionForms as $regionForm) {
            $region = ucfirst(strtolower(explode("_", $regionForm->formId)[1]));
            $regionFormName = (in_array($region, [ "Alola", "Galarian" ])) ? $regionForm->names->English : $region . " " . $regionForm->names->English;

            $types = $this->format_types($regionForm);

            $stats = [
                "atk" => $regionForm->stats->attack,
                "def" => $regionForm->stats->defense,
                "sta" => $regionForm->stats->stamina,
            ];

            $new_pokemon = [
                    "id" => str_pad($regionForm->dexNr, 3, '0', 0),
                    "stats" => $stats,
                    "type" => $types,
                    "name" => $regionFormName,
                    "moveset" => [
                        "quick" => $this->format_quick_moves($regionForm),
                        "charge" => $this->format_charge_moves($regionForm),
                    ],
                    "defense_data" =>$this->getPokemonDefenseData($types),
            ];
            $this->pkmDB[] = $new_pokemon;
        }

        $this->writePokedata();
        //file_put_contents('includes/files/db/v2/quick.js', json_encode($this->quickMovesDB, JSON_PRETTY_PRINT));
        //file_put_contents('includes/files/db/v2/charge.js', json_encode($this->chargeMovesDB, JSON_PRETTY_PRINT));
        //file_put_contents('includes/files/db/v2/pokedata.js', json_encode($this->pkmDB, JSON_PRETTY_PRINT));
    }

    private function writePokeData()
    {
        $jsDB = "var pokeDB = {\n";

        foreach ($this->pkmDB as $key => $pkmData) {    
            $jsDB .= "\"" . $pkmData['name'] . "\": " . json_encode($pkmData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db/v2/pokedata.js', $jsDB);
    }

    private function format_types($pokemon)
    {
        $pokemonType = [ $pokemon->primaryType->names->English ];
        
        if (isset($pokemon->secondaryType->names->English) && $pokemon->secondaryType->names->English != "null") {
            $pokemonType[] = $pokemon->secondaryType->names->English;
        }
        
        return $pokemonType;
    }

    private function format_quick_moves($pokemon)
    {
        $quickMoves = [];

        foreach($pokemon->quickMoves as $quickMove) {
            $quickMoves[] = $quickMove->names->English;

            if (!in_array($quickMove->names->English, array_keys($this->quickMovesDB))) {
                $this->insertIntoQuickMoveDb($quickMove);
            }
        }

        foreach($pokemon->eliteQuickMoves as $quickMove) {
            $quickMoves[] = "*" . $quickMove->names->English;
            if (!in_array($quickMove->names->English, array_keys($this->quickMovesDB))) {
                $this->insertIntoQuickMoveDb($quickMove);
            }
        }
        
        return $quickMoves;
    }

    private function format_charge_moves($pokemon)
    {
        $chargedMoves = [];

        foreach($pokemon->cinematicMoves as $chargedMove) {
            $chargedMoves[] = $chargedMove->names->English;
            if (!in_array($chargedMove->names->English, array_keys($this->chargeMovesDB))) {
                $this->insertIntoChargeMoveDb($chargedMove);
            }
        }
        foreach($pokemon->eliteCinematicMoves as $chargedMove) {
            $chargedMoves[] = "*" . $chargedMove->names->English;
            if (!in_array($chargedMove->names->English, array_keys($this->chargeMovesDB))) {
                $this->insertIntoChargeMoveDb($chargedMove);
            }
        }
        
        return $chargedMoves;
    }

    private function getPokemonDefenseData($inTypes)
    {
        $types = $this->typeEffectiveness();

        $getTypeA = $inTypes[0];
        $getTypeB = false;
        $resistantToA = [];
        $vulnerableToA = [];

        if (count($inTypes)>1) {
            $getTypeB = $inTypes[1];
            $resistantToB = [];
            $vulnerableToB = [];
        }

        foreach ($types as $key => $type) {
            if ($type[$getTypeA] > 1) {
                $vulnerableToA[$key] = $type[$getTypeA];
            }

            if ($type[$getTypeA] < 1) {
                $resistantToA[$key] = $type[$getTypeA];
            }

            if ($getTypeB) {
                if ($type[$getTypeB] < 1) {
                    $resistantToB[$key] = $type[$getTypeB];
                }

                if ($type[$getTypeB] > 1) {
                    $vulnerableToB[$key] = $type[$getTypeB];
                }
            }
        }

        $finalVulnerableTo = [];
        $finalResistantTo = [];

        if (!$getTypeB) {
            $finalVulnerableTo = $vulnerableToA;
            $finalResistantTo = $resistantToA;

            foreach ($finalVulnerableTo as $key => $item) {
                $finalVulnerableTo[$key] = $this->formatValue($item);
            }

            foreach ($finalResistantTo as $key => $item) {
                $finalResistantTo[$key] = $this->formatValue($item, 1);
            }

            ksort($finalVulnerableTo, SORT_NATURAL | SORT_FLAG_CASE);
            ksort($finalResistantTo, SORT_NATURAL | SORT_FLAG_CASE);

            return [
                'vulnerable_to' => $finalVulnerableTo,
                'resistant_to' => $finalResistantTo
            ];
        }

        foreach ($vulnerableToA as $keyA => $item) {
            $finalVulnerableTo[$keyA] = $item;
            if (in_array($keyA, array_keys($vulnerableToB))) {
                $finalVulnerableTo[$keyA] *= $finalVulnerableTo[$keyA];
                unset($vulnerableToB[$keyA]);
            }
            if (in_array($keyA, array_keys($resistantToB))) {
                if ($resistantToB[$keyA] == 0.625) {
                    unset($finalVulnerableTo[$keyA]);
                } else {
                    unset($vulnerableToA[$keyA]);
                    unset($finalVulnerableTo[$keyA]);
                    $resistantToB[$keyA] = 0.625;
                }
            }
        }

        foreach ($resistantToA as $keyA => $item) {
            $finalResistantTo[$keyA] = $item;
            if (in_array($keyA, array_keys($resistantToB))) {
                $finalResistantTo[$keyA] *= $resistantToB[$keyA];
                unset($resistantToB[$keyA]);
            }
            if (in_array($keyA, array_keys($vulnerableToB))) {
                if ($vulnerableToB[$keyA] == 1.6) {
                    unset($finalResistantTo[$keyA]);
                }
            }
        }

        foreach ($vulnerableToB as $keyB => $item) {
            $finalVulnerableTo[$keyB] = $item;
            if (in_array($keyB, array_keys($resistantToA))) {
                if ($resistantToA[$keyB] == 0.625) {
                    unset($finalVulnerableTo[$keyB]);
                } else {
                    unset($finalVulnerableTo[$keyB]);
                    $finalResistantTo[$keyB] = 0.625;
                }
            }
        }

        foreach ($resistantToB as $keyB => $item) {
            $finalResistantTo[$keyB] = $item;
            if (in_array($keyB, array_keys($vulnerableToA))) {
                if ($vulnerableToA[$keyB] == 1.6) {
                    unset($finalResistantTo[$keyB]);
                }
            }
            if (in_array($keyB, array_keys($resistantToA))) {
                $finalResistantTo[$keyB] *= $resistantToA[$keyB];
            }
        }

        foreach ($finalVulnerableTo as $key => $item) {
            $finalVulnerableTo[$key] = $this->formatValue($item);
        }

        foreach ($finalResistantTo as $key => $item) {
            $finalResistantTo[$key] = $this->formatValue($item, 1);
        }

        ksort($finalVulnerableTo, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($finalResistantTo, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            'vulnerable_to' => $finalVulnerableTo,
            'resistant_to' => $finalResistantTo
        ];
    }
    
    private function formatValue($number, $decimal = 0) {
        return number_format($number * 100, $decimal) . "%";
    }

    private function typeEffectiveness()
    {
        if (!file_exists("includes/files/type_effectiveness.json")) {
            file_put_contents("includes/files/type_effectiveness.json", file_get_contents("https://pogoapi.net/api/v1/includes/files/type_effectiveness.json"));
        }

        $content = file_get_contents("includes/files/type_effectiveness.json");
        return json_decode($content, true);
    }

    private function insertIntoQuickMoveDb($quickMove)
    {
        $types = $this->typeEffectiveness();

        $goodAgainst = [];
        $weakAgainst = [];

        foreach ($types[$quickMove->type->names->English] as $key => $value)
        {
            if ($value > 1) {
                $goodAgainst[] = $key;
            }
            if ($value < 1) {
                $weakAgainst[] = $key;
            }
        }

        $this->quickMovesDB[$quickMove->names->English] = [
            "type" => $quickMove->type->names->English,
            'weakAgainst' => $weakAgainst,
            'goodAgainst' => $goodAgainst,
            "ept" => number_format((int) $quickMove->combat->energy / (int)  $quickMove->combat->turns, 2),
            "dpt" => number_format((int)  $quickMove->combat->power / (int)  $quickMove->combat->turns, 2),
        ];
    }

    private function insertIntoChargeMoveDb($chargedMove)
    {
        $types = $this->typeEffectiveness();

        $goodAgainst = [];
        $weakAgainst = [];

        foreach ($types[$chargedMove->type->names->English] as $key => $value)
        {
            if ($value > 1) {
                $goodAgainst[] = $key;
            }
            if ($value < 1) {
                $weakAgainst[] = $key;
            }
        }

        $this->chargeMovesDB[$chargedMove->names->English] = [
            "type" => $chargedMove->type->names->English,
            'weakAgainst' => $weakAgainst,
            'goodAgainst' => $goodAgainst,
            "energy" => $chargedMove->combat->energy,
            "dpe" => number_format((int)  $chargedMove->combat->power / (int)  $chargedMove->combat->energy, 2) * -1,
        ];
    }
}