<?php

namespace App\Controllers;

use App\Templates;
use App\Utils\JsonUtil;

class MainController
{
    public function __construct()
    {
    }

    /*
     * Retrieve complete Pokemon data to the front end
     */
    public function getPokemon($name)
    {
        $name = str_replace("_", " ", $name);

        $pokemon = $this->getPokemonData($name);

        $pokemon['type'] = explode("/", $pokemon['type']);

        $defense_data = $this->getPokemonDefenseData($pokemon['type']);

        $pokemon['defense_data'] = $defense_data;

        $pokemon['name'] = $name;

        echo json_encode($pokemon);
    }

    /*
     * Retrieve move data to the front end
     */
    public function getMove($name, $type)
    {
        $name = str_replace('_', ' ', $name);

        $types = JsonUtil::getTypeEffectiveness();

        $goodAgainst = [];
        $weakAgainst = [];

        switch ($type) {
            case 'quick':
                $quickMovesApi = JsonUtil::getQuickMoves();

                foreach ($quickMovesApi as $quickMove) {
                    if ($quickMove['name'] === $name) {
                        foreach ($types[$quickMove['type']] as $key => $value)
                        {
                            if ($value > 1) {
                                $goodAgainst[] = $key;
                            }
                            if ($value < 1) {
                                $weakAgainst[] = $key;
                            }
                        }
                    }
                }

                break;

            case 'charge1' || 'charge2':
                $chargeMovesApi = JsonUtil::getChargeMoves();

                foreach ($chargeMovesApi as $chargeMove) {
                    if ($chargeMove['name'] === $name) {
                        foreach ($types[$chargeMove['type']] as $key => $value)
                        {
                            if ($value > 1) {
                                $goodAgainst[] = $key;
                            }
                            if ($value < 1) {
                                $weakAgainst[] = $key;
                            }
                        }
                    }
                }

                break;
        }

        $result = [
            'weakAgainst' => $weakAgainst,
            'goodAgainst' => $goodAgainst
        ];

        echo json_encode($result);
    }

    /*
     * Main loading method for the App
     */
    public function teamBuilder()
    {
        $pokemonsNameList = $this->getPokemonsNameList();

        $pokemonList = '';
        foreach ($pokemonsNameList as $name) {
            $pokemonList .= "<option value='".explode(" - ", $name)[1]."'>$name</option>";
        }

        $pkmsPve = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPve = json_decode($pkmsPve, true);

        $pkmPveRows = '';
        foreach ($pkmsPve as $key => $pkmPve) {
            $pkmPveRows .=
                "<tr id='" . $key . "' class='pkm-pve-row' >
                    <td>$pkmPve[name]</td>
                    <td>$pkmPve[cp]</td>
                    <td>$pkmPve[lv]</td>
                    <td>".$pkmPve['sta_iv']."</td>
                    <td>".$pkmPve['atk_iv']."</td>
                    <td>".$pkmPve['def_iv']."</td>
                    <td>".$pkmPve['iv_percentage']."</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>$pkmPve[role]</td>
                </tr>";
        }

        $pkmPveRows .= "<tr id='" . (max(array_keys($pkmsPve)) + 1) . "' class='store'><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";

        $args = [
            'LISTA' => $pokemonList,
            'PKMPVE' => $pkmPveRows,
        ];

        new Templates('reader.html', $args);
    }

    /*
     * This function force updates the local auxiliary json files
     */
    public function jsonUpdate()
    {
        file_put_contents(JsonUtil::SHADOW_JSON, file_get_contents("https://pogoapi.net/api/v1/shadow_pokemon.json"));
        file_put_contents(JsonUtil::POKEMON_NAMES_JSON, file_get_contents("https://pogoapi.net/api/v1/pokemon_names.json"));
        file_put_contents(JsonUtil::GALARIAN_JSON, file_get_contents("https://pogoapi.net/api/v1/galarian_pokemon.json"));
        file_put_contents(JsonUtil::ALOLAN_JSON, file_get_contents("https://pogoapi.net/api/v1/alolan_pokemon.json"));
        file_put_contents(JsonUtil::MEGA_JSON, file_get_contents("https://pogoapi.net/api/v1/mega_pokemon.json"));
        file_put_contents(JsonUtil::TYPE_EFFECTIVENESS_JSON, file_get_contents("https://pogoapi.net/api/v1/type_effectiveness.json"));
        file_put_contents(JsonUtil::TYPES_JSON, file_get_contents("https://pogoapi.net/api/v1/pokemon_types.json"));
        file_put_contents(JsonUtil::STATS_JSON, file_get_contents("https://pogoapi.net/api/v1/pokemon_stats.json"));
        file_put_contents(JsonUtil::CP_MULTIPLIER_JSON, file_get_contents("https://pogoapi.net/api/v1/cp_multiplier.json"));
        file_put_contents(JsonUtil::QUICK_MOVES_JSON, file_get_contents("https://pogoapi.net/api/v1/fast_moves.json"));
        file_put_contents(JsonUtil::CHARGE_MOVES_JSON, file_get_contents("https://pogoapi.net/api/v1/charged_moves.json"));
    }

    /*
     * This function updates the PokeDB json file
     */
    public function pokeDB()
    {
        $pokemonsCsv = array_map('str_getcsv', file('includes/files/comprehensive_dps.csv'));

        $pokemonTypeApi = JsonUtil::getType();
        $pokemonType = [];
        $pokemonId = [];
        foreach ($pokemonTypeApi as $item) {
            $type = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0];

            $name = $item['pokemon_name'];
            switch ($item['form']) {
                case 'Galarian':
                    $name = "Galarian " . $item['pokemon_name'];
                    break;
                case 'Alola':
                    $name = "Alolan " . $item['pokemon_name'];
                    break;
            }
            $pokemonType[$name] = $type;
        }

        $statsApi = JsonUtil::getStats();
        $stats = [];
        foreach ($statsApi as $item) {
            $pokemonStats = [
                'atk' => $item['base_attack'],
                'def' => $item['base_defense'],
                'sta' => $item['base_stamina'],
            ];

            $name = $item['pokemon_name'];
            switch ($item['form']) {
                case 'Shadow':
                    $name = 'Shadow ' . $item['pokemon_name'];
                    break;
                case 'Galarian':
                    $name = 'Galarian ' . $item['pokemon_name'];
                    break;
                case 'Alola':
                    $name = 'Alolan ' . $item['pokemon_name'];
                    break;
            }

            $stats[$name] = $pokemonStats;
            $pokemonId[$name] =  str_pad($item['pokemon_id'], 3, '0', 0);
        }

        $megaPokemonTypeApi = JsonUtil::getMegaPokemons();
        foreach ($megaPokemonTypeApi as $item) {
            $type = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0] ;
            $stats[$item['mega_name']] = [
                'atk' => $item['stats']['base_attack'],
                'def' => $item['stats']['base_defense'],
                'sta' => $item['stats']['base_stamina'],
            ];
            $pokemonType[$item['mega_name']] = $type;
            $pokemonId[$item['mega_name']] =  str_pad($item['pokemon_id'], 3, '0', 0);
        }

        $pokemons = [];

        foreach ($pokemonsCsv as $row => $line) {

            if ($row === 0)
                continue;

            if (!in_array($line[0], array_keys($pokemons))) {
                $type = 'PENDING';

                if (in_array($line[0], array_keys($pokemonType))) {
                    $type = $pokemonType[$line[0]];
                }

                if (strpos($line[0], "Shadow") !== false) {
                    $realName = explode(" ", $line[0])[1];
                    $type = $pokemonType[$realName];
                }

                $newPokemon = [
                    'id' => $pokemonId[$line[0]],
                    'type' => $type,
                    'stats' => $stats[$line[0]],
                    'moveset' => [
                        'quick' => [],
                        'charge' => []
                    ]
                ];
                $pokemons[$line[0]] = $newPokemon;
            }

            if (!in_array($line[1], $pokemons[$line[0]]['moveset']['quick'])) {
                $pokemons[$line[0]]['moveset']['quick'][] = $line[1];
            }

            if (!in_array($line[2], $pokemons[$line[0]]['moveset']['charge'])) {
                $pokemons[$line[0]]['moveset']['charge'][] = $line[2];
            }

        }

        $summary = '';

        $pokedb = file_get_contents('includes/files/pokedb.json');
        $pokedb = json_decode($pokedb, true);

        foreach (array_keys($pokemons) as $pokemon) {
            switch (in_array($pokemon, array_keys($pokedb))) {
                case true:
                    $innerSummary = "<br>[consta]: $pokemon";

                    if ($pokedb[$pokemon]['hash'] !== hash('md5', json_encode($pokemons[$pokemon]))){
                        $pokemons[$pokemon]['hash'] = hash('md5', json_encode($pokemons[$pokemon]));
                        $pokedb[$pokemon] = $pokemons[$pokemon];
                        $innerSummary = "<br>[atualizado]: $pokemon";
                    }

                    $summary .= $innerSummary;
                    break;

                case false:
                    $summary .= "<br>[criado]: $pokemon";

                    $pokemons[$pokemon]['hash'] = hash('md5', json_encode($pokemons[$pokemon]));
                    $pokedb[$pokemon] = $pokemons[$pokemon];
                    break;
            }
        }

        file_put_contents('includes/files/pokedb.json', json_encode($pokedb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo $summary;
    }

    /*
     * This function retrieves vulnerable/resistant information based on types
     */
    private function getPokemonDefenseData($inTypes)
    {
        $types = JsonUtil::getTypeEffectiveness();

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

    /*
     * This function retrieves a pokemon data from PokeDB json file
     */
    private function getPokemonData($getName)
    {
        $pokedb = file_get_contents('includes/files/pokedb.json');
        $pokedb = json_decode($pokedb, true);

        return $pokedb[$getName];
    }

    /*
     * Retrieve a list of released Pokemon
     */
    private function getPokemonsNameList()
    {
        $pokemonNameApi = JsonUtil::getPokemonsNames();
        $pokemonName = [];
        foreach ($pokemonNameApi as $item) {
            $pokemonName[$item['name']] = $item['id'];
        }

        $galarianApi = JsonUtil::getGalarianPokemons();
        foreach ($galarianApi as $item) {
            $galarianName = "Galarian " . $item['name'];
            $pokemonName[$galarianName] = $item['id'];
        }

        $alolanApi = JsonUtil::getAlolanPokemons();
        foreach ($alolanApi as $item) {
            $alolanName = "Alolan " . $item['name'];
            $pokemonName[$alolanName] = $item['id'];
        }

        $shadowApi = JsonUtil::getShadowPokemons();
        foreach ($shadowApi as $item) {
            $shadowName = "Shadow " . $item['name'];
            $pokemonName[$shadowName] = $item['id'];
        }

        $megaPokemonTypeApi = JsonUtil::getMegaPokemons();
        foreach ($megaPokemonTypeApi as $item) {
            $pokemonName[$item['mega_name']] = $item['pokemon_id'];
        }

        $names = $this->getPokemonsNamesFromCsv();
        foreach ($names as $index => $name) {
            if (!in_array($name, array_keys($pokemonName))) {
                unset($names[$index]);
                continue;
            }
            $names[$index] = str_pad($pokemonName[$name], 3, '0', 0) . " - " . $names[$index];
        }

        sort($names);

        return $names;
    }

    /*
     * Retrieve a list of Pokemon names from the CSV file
     */
    private function getPokemonsNamesFromCsv()
    {
        $pokemonsCsv = array_map('str_getcsv', file('includes/files/comprehensive_dps.csv'));

        $pokemonsNameList = [];

        foreach ($pokemonsCsv as $row => $line) {

            if ($row === 0)
                continue;

            if (!in_array($line[0], $pokemonsNameList)) {
                $pokemonsNameList[] = $line[0];
            }
        }

        sort($pokemonsNameList);

        return $pokemonsNameList;
    }

    private function formatValue($number, $decimal = 0) {
        return number_format($number * 100, $decimal) . "%";
    }
}
