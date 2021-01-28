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
     * This function force updates the local auxiliary json files
     */
    public function jsonUpdate()
    {
        JsonUtil::getTypeEffectiveness(true);
        JsonUtil::getMegaPokemons(true);
        JsonUtil::getType(true);
        JsonUtil::getStats(true);
        JsonUtil::getQuickMoves(true);
        JsonUtil::getChargeMoves(true);
        JsonUtil::getCurrentPkmMoves(true);
    }

    /*
     * Retrieve complete Pokemon data to the front end
     */
    public function getPokemon($name)
    {
        $jsonType = JsonUtil::getType();
        $jsonStats = JsonUtil::getStats();
        $jsonCurrentMoves = JsonUtil::getCurrentPkmMoves();

        $pokemonName = str_replace("_", " ", $name);

        $pokemonType = explode("/", $jsonType[$pokemonName]);

        $defense_data = $this->getPokemonDefenseData($pokemonType);

        echo json_encode([
            'id' => str_pad($jsonStats[$pokemonName]['id'], 3, '0', 0),
            'type' => $pokemonType,
            'defense_data' => $defense_data,
            'name' => $pokemonName,
            'stats' => [
                'atk' => $jsonStats[$pokemonName]['atk'],
                'def' => $jsonStats[$pokemonName]['def'],
                'sta' => $jsonStats[$pokemonName]['sta'],
            ],
            'moveset' => [
                'quick' => $jsonCurrentMoves[$pokemonName]['quick'],
                'charge' => $jsonCurrentMoves[$pokemonName]['charge']
            ]
        ]);
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
                $quickMovesJson = JsonUtil::getQuickMoves();

                foreach (array_keys($quickMovesJson) as $quickMove) {
                    if ($quickMove === $name) {
                        foreach ($types[$quickMovesJson[$quickMove]] as $key => $value)
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
                $chargeMovesJson = JsonUtil::getChargeMoves();

                foreach (array_keys($chargeMovesJson) as $chargeMove) {
                    if ($chargeMove === $name) {
                        foreach ($types[$chargeMovesJson[$chargeMove]] as $key => $value)
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
            $pokemonList .= "<option>$name</option>";
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
     * Retrieve a list of released Pokemon
     */
    private function getPokemonsNameList()
    {
        $pokemonType = JsonUtil::getType();
        $megaPokemonType = JsonUtil::getMegaPokemons();

        $names = array_merge(array_keys($pokemonType), array_keys($megaPokemonType));

        sort($names);

        return $names;
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

    private function formatValue($number, $decimal = 0) {
        return number_format($number * 100, $decimal) . "%";
    }
}
