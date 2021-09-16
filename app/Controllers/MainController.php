<?php

namespace App\Controllers;

use App\Templates;
use App\Utils\JsonUtil;

class MainController
{
    protected $jsonUtil;

    public function __construct()
    {
        $this->jsonUtil = new JsonUtil();
    }

    /*
     * This function force updates the local auxiliary json files
     */
    public function jsonUpdate()
    {
        $this->jsonUtil->getTypeEffectiveness(true);
        $this->jsonUtil->getMegaPokemons(true);
        $this->jsonUtil->getType(true);
        $this->jsonUtil->getStats(true);
        $this->jsonUtil->getQuickMoves(true);
        $this->jsonUtil->getChargeMoves(true);
        $this->jsonUtil->getCurrentPkmMoves(true);
    }

    /*
     * Retrieve complete Pokemon data to the front end
     */
    public function getPokemon($name)
    {
        $jsonType = $this->jsonUtil->getType();
        $jsonStats = $this->jsonUtil->getStats();
        $jsonCurrentMoves = $this->jsonUtil->getCurrentPkmMoves();

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
            ],
            'imgurl' => $this->formatImgUrl($jsonStats[$pokemonName]['id'], $pokemonName)
        ]);
    }

    /*
     * Retrieve move data to the front end
     */
    public function getMove($name, $type)
    {
        $name = str_replace('_', ' ', $name);

        $types = $this->jsonUtil->getTypeEffectiveness();

        $goodAgainst = [];
        $weakAgainst = [];
        $moveType = '';

        switch ($type) {
            case 'quick':
                $quickMovesJson = $this->jsonUtil->getQuickMoves();

                foreach (array_keys($quickMovesJson) as $quickMove) {
                    if ($quickMove === $name) {
                        $moveType = $quickMovesJson[$quickMove];
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
                $chargeMovesJson = $this->jsonUtil->getChargeMoves();

                foreach (array_keys($chargeMovesJson) as $chargeMove) {
                    if ($chargeMove === $name) {
                        $moveType = $chargeMovesJson[$chargeMove];
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

        $response = [
            'type' => strtoupper($moveType),
            'weakAgainst' => $weakAgainst,
            'goodAgainst' => $goodAgainst
        ];
        
        $url = "https://pokemongo.fandom.com/wiki/" . str_replace(' ', '_', $name);
        
        $file = file_get_contents($url);

		$file = strip_tags($file);

		$file = explode("Type effectiveness", $file);

		$file = explode("Trainer Battles", $file[0]);

		$moveData = trim($file[1]);

		$moveData = preg_replace("/\s+/", " ", $moveData);
		
		$moveData = explode(" ", $moveData);
		
		$moveData = ($type == 'quick') ?  [ $moveData[3], $moveData[4] ] : [ $moveData[2], $moveData[3] ] ;
        
        $response['power'] = $moveData[0];
        $response['energy'] = $moveData[1];

        echo json_encode($response);

        return $response;
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
     * This function is responsible for generating the pokemon database files for the pure front end version of this app
     */
    public function jsBuilderPokeData()
    {
        $stats = $this->jsonUtil->getStats();
        $types = $this->jsonUtil->getType();
        $currentMoves = $this->jsonUtil->getCurrentPkmMoves();

        $jsDB = "var pokeDB = {\n";

        foreach ($stats as $name => $pkm) {
            $pokeData['id'] = str_pad($pkm['id'], 3, '0', 0);
            $pokeData['imgurl'] = $this->formatImgUrl($pkm['id'], $name);
            unset($pkm['id']);
            $pokemonType = explode("/", $types[$name]);
            $pokeData['stats'] = $pkm;
            $pokeData['type'] = $pokemonType;
            $pokeData['name'] = $name;
            $pokeData['moveset']['quick'] = $currentMoves[$name]['quick'];
            $pokeData['moveset']['charge'] = $currentMoves[$name]['charge'];
            $pokeData['defense_data'] = $this->getPokemonDefenseData($pokemonType);

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

        foreach ($quickMoves as $name => $quickMove) {
            $getMove = $this->getMove($name, 'quick');

            $moveData['type'] = $getMove['type'];
            $moveData['weakAgainst'] = $getMove['weakAgainst'];
            $moveData['goodAgainst'] = $getMove['goodAgainst'];
            $moveData['power'] = $getMove['power'];
            $moveData['energy'] = $getMove['energy'];

            $jsDB .= "\"$name\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
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

        foreach ($chargeMoves as $name => $chargeMove) {
            $getMove = $this->getMove($name, 'charge1');

            $moveData['type'] = $getMove['type'];
            $moveData['weakAgainst'] = $getMove['weakAgainst'];
            $moveData['goodAgainst'] = $getMove['goodAgainst'];
            $moveData['power'] = $getMove['power'];
            $moveData['energy'] = $getMove['energy'];

            $jsDB .= "\"$name\": " . json_encode($moveData, JSON_PRETTY_PRINT) . ",\n";
        }

        $jsDB .= "}";

        file_put_contents('includes/files/db_charge.js', $jsDB);

    }

    public function teamAssembler()
    {

        $list = explode(",", $_POST['pkm-list']);

        $teams = [];
        foreach ($list as $itemA) {
            foreach ($list as $itemB) {
                foreach ($list as $itemC) {
                    if ($itemA != $itemB && $itemA != $itemC && $itemB != $itemC) {
                        $team = [$itemA, $itemB, $itemC];
                        sort($team);
                        $teams[] = implode(",", $team);
                    }
                }
            }
        }

        $finalTeams = [];

        set_time_limit(300);
        foreach (array_count_values($teams) as $key => $team) {
            $team = explode(",", $key);
            $innerTeam = [];
            $innerTeamResistances = 0;
            $innerTeamWeaknesses = 0;
            foreach ($team as $pkm) {
                $getData = $this->getPokemonAsReturn($pkm);
                $pokeData['name'] = $getData['name'];
                $pokeData['resistant_to'] = $getData['defense_data']['resistant_to'];
                $pokeData['vulnerable_to'] = $getData['defense_data']['vulnerable_to'];
                $innerTeamResistances += count($getData['defense_data']['resistant_to']);
                $innerTeamWeaknesses += count($getData['defense_data']['vulnerable_to']);
                $innerTeam['members'][] = $pokeData;
            }
            $innerTeam['name'] = $key;
            $innerTeam['resistances'] = $innerTeamResistances;
            $innerTeam['weaknesses'] = $innerTeamWeaknesses;
            $finalTeams[] = $innerTeam;
        }

        array_multisort(array_column($finalTeams, 'weaknesses'), SORT_ASC, $finalTeams);

        echo json_encode($finalTeams);
    }

    /*
     * Retrieve a list of released Pokemon
     */
    private function getPokemonsNameList()
    {
        $pokemonType = $this->jsonUtil->getType();
        $megaPokemonType = $this->jsonUtil->getMegaPokemons();

        $names = array_merge(array_keys($pokemonType), array_keys($megaPokemonType));

        sort($names);

        return $names;
    }

    /*
     * This function retrieves vulnerable/resistant information based on types
     */
    private function getPokemonDefenseData($inTypes)
    {
        $types = $this->jsonUtil->getTypeEffectiveness();

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

    private function formatImgUrl($id, $name)
    {
        $imgUrl = $id;

        $name = explode(" ", $name);

        if (sizeof($name) == 1) {
            return $imgUrl;
        }

        $form = $name[0];

        switch ($form)
        {
            case "Galarian":
                $formattedName = strtolower($name[1]) . '-galar';
                $pkm = $this->jsonUtil->getPokeApiJson($formattedName);
                $imgUrl = is_numeric($pkm['id']) ? $pkm['id'] : '';
                break;

            case "Alola":
                $formattedName = strtolower($name[1]) . '-alola';
                $pkm = $this->jsonUtil->getPokeApiJson($formattedName);
                $imgUrl = is_numeric($pkm['id']) ? $pkm['id'] : '';
                break;

            case "Shadow":
                break;

            default:
                $imgUrl = $id . '-' . strtolower($form);
        }

        return $imgUrl;
    }

    private function formatValue($number, $decimal = 0) {
        return number_format($number * 100, $decimal) . "%";
    }

    /*
     * Private function similar to getPokemon only is used by the teamAssembler method
     */
    private function getPokemonAsReturn($name)
    {
        $jsonType = $this->jsonUtil->getType();
        $jsonStats = $this->jsonUtil->getStats();

        $pokemonName = str_replace("_", " ", $name);

        $pokemonType = explode("/", $jsonType[$pokemonName]);

        $defense_data = $this->getPokemonDefenseData($pokemonType);

        $result = [
            'type' => $pokemonType,
            'defense_data' => $defense_data,
            'name' => $pokemonName,
            'imgurl' => $this->formatImgUrl($jsonStats[$pokemonName]['id'], $pokemonName)
        ];

        return $result;
    }
}
