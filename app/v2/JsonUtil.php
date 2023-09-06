<?php

namespace Classes\v2;

class JsonUtil {
    
    protected $pokeDatabase = [];
    protected $quickMoves = [];
    protected $chargeMoves = [];

    protected $formattedPokeDatabase = [];
    protected $formattedQuickMoves = [];
    protected $formattedChargeMoves = [];

    protected $moveIds = [
        387 => 'GEOMANCY',
        389 => 'OBLIVION_WING',
        391 => 'TRIPLE_AXEL',
        392 => 'TRAILBLAZE',
        393 => 'SCORCHING_SANDS'
    ];

    protected $fixQuickMoveTurnValues = [
        'FURY_CUTTER' => [
            'turns' => 0
        ],
        'BUG_BITE' => [
            'turns' => 0
        ],
        'BITE' => [
            'turns' => 0
        ],
        'DRAGON_BREATH' => [
            'turns' => 0
        ],
        'LICK' => [
            'turns' => 0
        ],
        'SCRATCH' => [
            'turns' => 0
        ],
        'TACKLE' => [
            'turns' => 0
        ],
        'CUT' => [
            'turns' => 0
        ],
        'LOCK_ON' => [
            'turns' => 0
        ],
        'WATER_GUN' => [
            'turns' => 0
        ],
    ];

    protected $csv = [];

    public function __construct()
    {
        if (!file_exists("includes/files/v2/latest.json")) {
            file_put_contents("includes/files/v2/latest.json", file_get_contents("https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json"));
        }
        if (!file_exists("includes/files/v2/pokemon.csv")) {
            file_put_contents("includes/files/v2/pokemon.csv", file_get_contents("https://raw.githubusercontent.com/PokeAPI/pokeapi/master/data/v2/csv/pokemon.csv"));
        }

        $this->csv = $this->getCsv();
    }

    public function run()
    {
        $content = file_get_contents("includes/files/v2/latest.json");
        $content = json_decode($content);

        foreach ($content as $entry) {
            $entryName = explode("_", $entry->templateId);
            if (preg_match("/(V)([0-9]{1,4})/", $entryName[0]) && isset($entry->data->pokemonSettings->stats->baseAttack) ) {
                $pokemon = $entry->data->pokemonSettings;
            
                $name = (isset($pokemon->form)) ? $pokemon->form : $pokemon->pokemonId;
                
                if (preg_match("/(_NORMAL)/", $name) ||
                    preg_match("/(_2019)/", $name)) {
                    continue;
                }
                
                if (!isset($pokemon->quickMoves)) {
                    continue;
                }
                
                $pokemonQuickMoves = array_merge($pokemon->quickMoves, isset($pokemon->eliteQuickMove) ? array_map(function($innerMove) { return $innerMove . "*"; }, $pokemon->eliteQuickMove) : []);
                foreach($pokemonQuickMoves as $key => $value) {
                    if (in_array($value, array_keys($this->moveIds))) {
                        $pokemonQuickMoves[] = $this->moveIds[$value];
                        unset($pokemonQuickMoves[$key]); 
                        $pokemonQuickMoves = array_values($pokemonQuickMoves);
                    }
                }
                
                $pokemonChargeMoves = array_merge($pokemon->cinematicMoves, isset($pokemon->eliteCinematicMove) ? array_map(function($innerMove) { return $innerMove . "*"; }, $pokemon->eliteCinematicMove) : []);
                foreach($pokemonChargeMoves as $key => $value) {
                    if (in_array($value, array_keys($this->moveIds))) {
                        $pokemonChargeMoves[] = $this->moveIds[$value];
                        unset($pokemonChargeMoves[$key]); 
                        $pokemonChargeMoves = array_values($pokemonChargeMoves);
                    }
                }
                
                $name = str_replace("_", "-", $name);
                $name = $this->formatPokemonName($name);

                $types = isset($pokemon->type2) ? [ $this->formatType($pokemon->type), $this->formatType($pokemon->type2) ] : [ $this->formatType($pokemon->type) ];
                
                $this->pokeDatabase[$name] = [
                    'id' => substr($entryName[0], 1),
                    'stats' => [
                        'atk' => $entry->data->pokemonSettings->stats->baseAttack,
                        'def' => $entry->data->pokemonSettings->stats->baseDefense,
                        'sta' => $entry->data->pokemonSettings->stats->baseStamina
                    ],
                    'type' => $types,
                    'imgurl' => $this->getPokemonImgUrl(strtolower($name)),//strtolower($name),
                    'name' => $name,
                    'moveset' => [
                        'quick' => $this->formatQuickMoves($pokemonQuickMoves),
                        'charge' => $this->formatChargeMoves($pokemonChargeMoves)
                    ],
                    "defense_data" => $this->getPokemonDefenseData($types),
                    //'templateId' => $entry->templateId
                ];
            }
            
            if (preg_match("/COMBAT_V/", $entry->templateId)) {
                if ($entry->templateId == "COMBAT_V0242_MOVE_TRANSFORM_FAST"){
                    continue;
                }

                $typeEffectiveness = $this->typeEffectiveness();
        
                $uniqueId = explode("_", $entry->templateId);
                $uniqueId = explode("V", $uniqueId[1]);
        
                $move = $entry->data->combatMove;
                if ($move->energyDelta < 0) {
                    $this->chargeMoves[$entry->templateId] = $move;
        
                    $formattedName = explode("_MOVE_", $entry->templateId);

                    $goodAgainst = [];
                    $weakAgainst = [];
            
                    foreach ($typeEffectiveness[$this->formatType($move->type)] as $key => $value)
                    {
                        if ($value > 1) {
                            $goodAgainst[] = $key;
                        }
                        if ($value < 1) {
                            $weakAgainst[] = $key;
                        }
                    }
        
                    $this->formattedChargeMoves[$this->formatSpacedName($formattedName[1])] = [
                        'uniqueId' => $uniqueId[1],
                        'name' => $this->formatSpacedName($formattedName[1]),
                        'type' => $this->formatType($move->type),
                        'goodAgainst' => $goodAgainst,
                        'weakAgainst' => $weakAgainst,
                        'energy' => $move->energyDelta,
                        'power' => isset($move->power) ? $move->power : 0,
                        'dpe' => number_format( (isset($move->power) ? $move->power : 0) / $move->energyDelta, 2),
                    ];

                    ksort($this->formattedChargeMoves );
                } else {
                    $this->quickMoves[$entry->templateId] = $move;
                    
                    $formattedName = explode("_MOVE_", $entry->templateId);
                    $formattedName = explode("_FAST", $formattedName[1]);
                    
                    $moveTurns = isset($move->durationTurns) ? $move->durationTurns : $this->fixQuickMoveTurnValues[$formattedName[0]]['turns'];
                    $moveTurns++;
                    $movePower = isset($move->power) ? $move->power : 0;
                    
                    if ($moveTurns == 0){
                        echo "$formattedName[0] SKIP FILHO DA PUTA \n\n";die();
                        continue;
                    }

                    $goodAgainst = [];
                    $weakAgainst = [];
            
                    foreach ($typeEffectiveness[$this->formatType($move->type)] as $key => $value)
                    {
                        if ($value > 1) {
                            $goodAgainst[] = $key;
                        }
                        if ($value < 1) {
                            $weakAgainst[] = $key;
                        }
                    }
                    
                    $this->formattedQuickMoves[$this->formatSpacedName($formattedName[0])] = [
                        'uniqueId' => $uniqueId[1],
                        'name' => $this->formatSpacedName($formattedName[0]),
                        'type' => $this->formatType($move->type),
                        'power' => $movePower,
                        'energy' => $move->energyDelta,
                        'turns' => $moveTurns,
                        'goodAgainst' => $goodAgainst,
                        'weakAgainst' => $weakAgainst,
                        'dpt' => number_format($movePower / ($moveTurns), 2),
                        'ept' => number_format($move->energyDelta / ($moveTurns), 2),
                    ];

                    ksort($this->formattedQuickMoves);
                }
            }
        }
        
        file_put_contents("includes/files/v2/raw_pokemonDatabase.json", json_encode($this->pokeDatabase, JSON_PRETTY_PRINT));
        file_put_contents("includes/files/v2/raw_quickMoves.json", json_encode($this->quickMoves, JSON_PRETTY_PRINT));
        file_put_contents("includes/files/v2/raw_chargeMoves.json", json_encode($this->chargeMoves, JSON_PRETTY_PRINT));
        
        $this->writePokeData();
        $this->writeQuickData();
        $this->writeChargeData();
    }
    
    private function formatQuickMoves($quickMoves){
        $formatting = [];
    
        foreach($quickMoves as $quickMove) {
            $formatting[] = $this->formatSpacedName(explode("_FAST", $quickMove)[0] . (str_contains($quickMove, "*") ?  "*" : ''));
        }
        return $formatting;
    }
    
    private function formatChargeMoves($chargeMoves){
        $formatting = [];
    
        foreach($chargeMoves as $chargeMove) {
            $formatting[] = $this->formatSpacedName($chargeMove);
        }
        return $formatting;
    }

    private function formatSpacedName($spacedName) {
        $names = explode("_", $spacedName);
    
        foreach($names as &$name) {
            $name = ucfirst(strtolower($name));
        }
        return implode(" ", $names);
    }
    
    private function formatType($rawType) {
        return ucfirst(strtolower(explode("_", $rawType)[2]));
    }
    
    private function formatPokemonName($name) {
        $names = explode("-", $name);
        
        foreach($names as &$name) {
            $name = ucfirst(strtolower($name));
        }

        if (implode(" ", $names) == "Ho Oh") {
            return str_replace(" ", "-", implode(" ", $names));
        }

        if (preg_match("/Lycanroc/", implode(" ", $names))) {
            if (preg_match("/Midday/", implode(" ", $names))) {
                $names = array_reverse($names);
            }

            if (preg_match("/Midnight/", implode(" ", $names))) {
                $names = array_reverse($names);
            }

            if (preg_match("/Dusk/", implode(" ", $names))) {
                $names = array_reverse($names);
            }
        }

        if(in_array("Hisuian", $names) || in_array("Galarian", $names) || in_array("Alola", $names) || in_array("Deoxys", $names)) {
            $names = array_reverse($names);
        }

        if(in_array("Florges", $names)) {
            $names = array_reverse($names);
        }

        if(in_array("Gourgeist", $names)) {
            $names = array_reverse($names);
        }

        return implode(" ", $names);
    }

    private function getPokemonImgUrl($pkm)
    {
        $pkmName = $pkm;
        
        if(preg_match("/(florges)/", $pkmName) && preg_match("/( )/", $pkmName)) {
            return "671-" . explode(" ", $pkmName)[1];
        }
        
        if(preg_match("/(gourgeist)/", $pkmName) && preg_match("/( )/", $pkmName)) {
            $pkmName = str_replace(" ", "-", $pkmName);
            return $pkmName;
        }

        if(str_contains($pkm, "hisuian")) {
            $pkmName = explode(" ", $pkmName)[1] . "-hisui";
        }
        
        if(str_contains($pkm, "galarian")) {
            $pkmName = explode(" ", $pkmName)[1] . "-galar";
        }
        
        if(str_contains($pkm, "alola")) {
            $pkmName = explode(" ", $pkmName)[1] . "-alola";
        }
        
        if(preg_match("/(lycanroc)/", $pkmName) && preg_match("/(midday)/", $pkmName)) {
            $pkmName = "lycanroc-midday";
        }
        
        if(preg_match("/(lycanroc)/", $pkmName) && preg_match("/(dusk)/", $pkmName)) {
            $pkmName = "lycanroc-dusk";
        }
        
        if(preg_match("/(lycanroc)/", $pkmName) && preg_match("/(midnight)/", $pkmName)) {
            $pkmName = "lycanroc-midnight";
        }
        
        if(preg_match("/(mime)/", $pkmName)) {
            $pkmName = str_replace(" ", "-", $pkmName);
        }
        
        if(preg_match("/(mime)/", $pkmName) && preg_match("/(galar)/", $pkmName)) {
            $pkmName = "mr-mime-galar";
        }
        
        if(preg_match("/(mr rime)/", $pkmName)) {
            $pkmName = str_replace(" ", "-", $pkmName);
        }

        return $this->csv[$pkmName];
    }

    private function writePokeData()
    {
        $jsDB = "var pokeDB = {\n";

        foreach ($this->pokeDatabase as $key => $pkmData) {    
            $jsDB .= "\"" . $pkmData['name'] . "\": " . json_encode($pkmData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/v2/db/pokedata.js', $jsDB);
    }

    private function writeQuickData()
    {
        $jsDB = "var quickMoveDB = {\n";

        foreach ($this->formattedQuickMoves as $quickMove => $data) {
            $moveData['type'] = $data['type'];
            $moveData['weakAgainst'] = $data['weakAgainst'];
            $moveData['goodAgainst'] = $data['goodAgainst'];
            $moveData['ept'] = $data['ept'];
            $moveData['dpt'] = $data['dpt'];

            $jsDB .= "\"$quickMove\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/v2/db/quick.js', $jsDB);
    }

    private function writeChargeData()
    {
        $jsDB = "var chargeMoveDB = {\n";

        foreach ($this->formattedChargeMoves as $chargeMove => $data) {
            $moveData['type'] = $data['type'];
            $moveData['weakAgainst'] = $data['weakAgainst'];
            $moveData['goodAgainst'] = $data['goodAgainst'];
            $moveData['energy'] = $data['energy'];
            $moveData['power'] = $data['power'];
            $moveData['dpe'] = $data['dpe'];

            $jsDB .= "\"$chargeMove\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/v2/db/charge.js', $jsDB);
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

    private function getCsv()
    {
        $data = file_get_contents('includes/files/v2/pokemon.csv');

        $data = explode("\n", $data);
        
        $arrayResult = [];
        
        foreach ($data as $n => $row) {
            if ($row !== "" && $n > 0){
                $explodedRow = explode(",", $row);
                $arrayResult[$explodedRow[1]] = $explodedRow[0];
            }
        }

        return $arrayResult;
    }
}