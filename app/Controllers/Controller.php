<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Templates;
use App\Util;

class Controller
{
    protected $POKEMON_DB_FOLDER = 'includes/files/pokedb/';

    public function __construct()
    {

    }

    public function storePkmPvp()
    {
        $newPkm = [
            "name" => $_POST['pkmpve'],
            "role" => $_POST['role'],
            "cp" => $_POST['cp'],
            "lv" => $_POST['lv'],
            "sta-iv" => $_POST['staiv'],
            "def-iv" => $_POST['defiv'],
            "atk-iv" => $_POST['atkiv'],
            "iv-percentage" => $_POST['ivpercentage'],
        ];

        $pkmsPvp = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPvp = json_decode($pkmsPvp, true);

        $pkmsPvp[$_POST['idpkmpve']] = $newPkm;
        file_put_contents('includes/files/pkm_pve.json', json_encode($pkmsPvp, JSON_PRETTY_PRINT));

        $newRow = "<tr id='$_POST[idpkmpve]' class='pkm-pve-row' ><td>$_POST[pkmpve]</td><td>$_POST[cp]</td><td>$_POST[lv]</td><td>$_POST[staiv]</td><td>$_POST[defiv]</td><td>$_POST[atkiv]</td><td>$_POST[ivpercentage]</td><td></td><td></td><td></td><td>$_POST[role]</td></tr>";
        
        $nextId = max(array_keys($pkmsPvp)) + 1;
        
        $result = [
            'newRow' => $newRow,
            'nextId' => $nextId,
        ];
        
        echo json_encode($result);
    }

    public function deletePkmPvp($idPkm)
    {
        $pkmsPvp = file_get_contents('includes/files/pkm_pve.json');
        $pkmsPvp = json_decode($pkmsPvp, true);

        unset($pkmsPvp[$idPkm]);
        file_put_contents('includes/files/pkm_pve.json', json_encode($pkmsPvp, JSON_PRETTY_PRINT));

        echo 'supostamente deletou';
    }

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
                    <td>".$pkmPve['sta-iv']."</td>
                    <td>".$pkmPve['atk-iv']."</td>
                    <td>".$pkmPve['def-iv']."</td>
                    <td>".$pkmPve['iv-percentage']."</td>
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

    public function pokeDB()
    {
        $pokemonsCsv = array_map('str_getcsv', file('includes/files/comprehensive_dps.csv'));

        $pokemonTypeApi = Util::getType();
        $pokemonType = [];
        foreach ($pokemonTypeApi as $item) {
            $type = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0] ;
            $pokemonType[$item['pokemon_name']] = $type;
        }

        $statsApi = Util::getStats();
        $stats = [];
        foreach ($statsApi as $item) {
            $pokemonStats = [
                'atk' => $item['base_attack'],
                'def' => $item['base_defense'],
                'sta' => $item['base_stamina'],
            ];
            $name = ($item['form'] === 'Shadow') ? "Shadow " . $item['pokemon_name'] : $item['pokemon_name'];

            $stats[$name] = $pokemonStats;
        }

        $megaPokemonTypeApi = Util::getMegaPokemons();
        $megaPokemonType = [];
        foreach ($megaPokemonTypeApi as $item) {
            $type = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0] ;
            $stats[$item['mega_name']] = [
                'atk' => $item['stats']['base_attack'],
                'def' => $item['stats']['base_defense'],
                'sta' => $item['stats']['base_stamina'],
            ];
            $megaPokemonType[$item['mega_name']] = $type;
        }

        // $quickApi = file_get_contents("https://pogoapi.net/api/v1/fast_moves.json");

        // $chargeApi = file_get_contents("https://pogoapi.net/api/v1/charged_moves.json");

        $pokemons = [];

        $quickMoves = [];

        $chargeMoves = [];

        foreach ($pokemonsCsv as $row => $line) {

            if ($row === 0)
                continue;

            if (!in_array($line[0], array_keys($pokemons))) {
                $type = false;
                if (in_array($line[0], array_keys($pokemonType))) {
                    $type = $pokemonType[$line[0]];
                }

                if (in_array($line[0], array_keys($megaPokemonType))) {
                    $type = $megaPokemonType[$line[0]];
                }

                if (strpos($line[0], "Shadow") !== false) {
                    $realName = explode(" ", $line[0])[1];
                    $type = $pokemonType[$realName];
                }

                $newPokemon = [
                    'type' => ($type) ? $type : 'PENDING',
                    'stats' => ($type) ? $stats[$line[0]] : [],
                    'moveset' => [
                        'quick' => [],
                        'charge' => []
                    ]
                ];
                $pokemons[$line[0]] = $newPokemon;
            }

            if (!in_array($line[1], $quickMoves)) {
                $quickMoves[] = $line[1];
            }

            if (!in_array($line[1], $pokemons[$line[0]]['moveset']['quick'])) {
                $pokemons[$line[0]]['moveset']['quick'][] = $line[1];
            }

            if (!in_array($line[2], $chargeMoves)) {
                $chargeMoves[] = $line[2];
            }

            if (!in_array($line[2], $pokemons[$line[0]]['moveset']['charge'])) {
                $pokemons[$line[0]]['moveset']['charge'][] = $line[2];
            }

        }

        $summary = '';

        $hashdb = file_get_contents($this->POKEMON_DB_FOLDER . "_hash.json");
        $hashdb = json_decode($hashdb, true);

        foreach (array_keys($pokemons) as $pokemon) {
            $name = strtolower(str_replace(" ", "_", $pokemon));

            switch (file_exists($this->POKEMON_DB_FOLDER . $name . ".json")) {
                case true:
                    $innerSummary = "<br>[consta]: $pokemon";

                    if (in_array($name, array_keys($hashdb)) && $hashdb[$name] !== hash('md5', json_encode($pokemons[$pokemon]))){
                        file_put_contents($this->POKEMON_DB_FOLDER . $name . ".json", json_encode($pokemons[$pokemon], JSON_PRETTY_PRINT));
                        $hashdb[$name] = hash('md5', json_encode($pokemons[$pokemon]));
                        $innerSummary = "<br>[atualizado]: $pokemon";
                    }

                    $summary .= $innerSummary;

                    break;

                case false:
                    $summary .= "<br>[criado]: $pokemon";

                    file_put_contents($this->POKEMON_DB_FOLDER . $name . ".json", json_encode($pokemons[$pokemon], JSON_PRETTY_PRINT));
                    $hashdb[$name] = hash('md5', json_encode($pokemons[$pokemon]));
                    break;
            }

            file_put_contents($this->POKEMON_DB_FOLDER . "_hash.json", json_encode($hashdb, JSON_PRETTY_PRINT));
        }

        echo $summary;
    }

    private function getPokemonDefenseData($inTypes)
    {
        $types = Util::getTypeEffectiveness();

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

    private function getPokemonData($getName)
    {
        $simplifiedName = strtolower(str_replace(" ", "_", $getName));
        if (file_exists($this->POKEMON_DB_FOLDER . $simplifiedName . ".json")) {
            $poke = file_get_contents($this->POKEMON_DB_FOLDER . $simplifiedName . ".json");
            $poke =  json_decode($poke, true);
            $poke['fromdb'] = true;
            return $poke;
        }

        $pokemonsCsv = array_map('str_getcsv', file('includes/files/comprehensive_dps.csv'));

        $pokemonTypeApi = Util::getType();
        $pokemonType = [];
        foreach ($pokemonTypeApi as $item) {
            $type = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0] ;
            $pokemonType[$item['pokemon_name']] = $type;
        }

        $statsApi = Util::getStats();
        $stats = [];
        foreach ($statsApi as $item) {
            $pokemonStats = [
                'atk' => $item['base_attack'],
                'def' => $item['base_defense'],
                'sta' => $item['base_stamina'],
            ];
            $name = ($item['form'] === 'Shadow') ? "Shadow " . $item['pokemon_name'] : $item['pokemon_name'];

            $stats[$name] = $pokemonStats;
        }

        $megaPokemonTypeApi = Util::getMegaPokemons();
        $megaPokemonType = [];
        foreach ($megaPokemonTypeApi as $item) {
            $type = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0] ;
            $stats[$item['mega_name']] = [
                'atk' => $item['stats']['base_attack'],
                'def' => $item['stats']['base_defense'],
                'sta' => $item['stats']['base_stamina'],
            ];
            $megaPokemonType[$item['mega_name']] = $type;
        }

        // $quickApi = file_get_contents("https://pogoapi.net/api/v1/fast_moves.json");

        // $chargeApi = file_get_contents("https://pogoapi.net/api/v1/charged_moves.json");

        $pokemons = [];

        $quickMoves = [];

        $chargeMoves = [];

        foreach ($pokemonsCsv as $row => $line) {

            if ($row === 0)
                continue;

            if (!in_array($line[0], array_keys($pokemons))) {
                $type = false;
                if (in_array($line[0], array_keys($pokemonType))) {
                    $type = $pokemonType[$line[0]];
                }

                if (in_array($line[0], array_keys($megaPokemonType))) {
                    $type = $megaPokemonType[$line[0]];
                }

                if (strpos($line[0], "Shadow") !== false) {
                    $realName = explode(" ", $line[0])[1];
                    $type = $pokemonType[$realName];
                }

                $newPokemon = [
                    'type' => ($type) ? $type : 'PENDING',
                    'stats' => ($type) ? $stats[$line[0]] : [],
                    'moveset' => [
                        'quick' => [],
                        'charge' => []
                    ]
                ];
                $pokemons[$line[0]] = $newPokemon;
            }

            if (!in_array($line[1], $quickMoves)) {
                $quickMoves[] = $line[1];
            }

            if (!in_array($line[1], $pokemons[$line[0]]['moveset']['quick'])) {
                $pokemons[$line[0]]['moveset']['quick'][] = $line[1];
            }

            if (!in_array($line[2], $chargeMoves)) {
                $chargeMoves[] = $line[2];
            }

            if (!in_array($line[2], $pokemons[$line[0]]['moveset']['charge'])) {
                $pokemons[$line[0]]['moveset']['charge'][] = $line[2];
            }

        }

        return $pokemons[$getName];
    }

    private function getPokemonsNameList()
    {
        $pokemonNameApi = Util::getPokemonsNames();
        $pokemonName = [];
        foreach ($pokemonNameApi as $item) {
            $pokemonName[$item['name']] = $item['id'];
        }

        $galarianApi = Util::getGalarianPokemons();
        foreach ($galarianApi as $item) {
            $galarianName = "Galarian " . $item['name'];
            $pokemonName[$galarianName] = $item['id'];
        }

        $alolanApi = Util::getAlolanPokemons();
        foreach ($alolanApi as $item) {
            $alolanName = "Alolan " . $item['name'];
            $pokemonName[$alolanName] = $item['id'];
        }

        $shadowApi = Util::getShadowPokemons();
        foreach ($shadowApi as $item) {
            $shadowName = "Shadow " . $item['name'];
            $pokemonName[$shadowName] = $item['id'];
        }

        $megaPokemonTypeApi = Util::getMegaPokemons();
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
