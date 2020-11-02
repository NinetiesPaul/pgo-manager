<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Enum;
use App\Templates;
use App\Util;

class Controller
{
    public function __construct()
    {

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

        $args = [
            'LISTA' => $pokemonList
        ];

        new Templates('reader.html', $args);
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
