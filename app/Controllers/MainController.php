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

        $pokemonType = $jsonType[$pokemonName]['type'];

        $defense_data['vulnerable_to'] = $jsonType[$pokemonName]['vulnerable_to'];
        $defense_data['resistant_to'] = $jsonType[$pokemonName]['resistant_to'];

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
            $defense_data['vulnerable_to'] = $currentMoves[$name]['vulnerable_to'];
            $defense_data['resistant_to'] = $currentMoves[$name]['resistant_to'];

            $pokeData['id'] = str_pad($pkm['id'], 3, '0', 0);
            $pokeData['imgurl'] = $this->formatImgUrl($pkm['id'], $name);
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
        $types = $this->jsonUtil->getType();
        $stats = $this->jsonUtil->getStats();

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
                $pokeData['name'] = $pkm;
                $pokeData['resistant_to'] = $types[$pkm]['resistant_to'];
                $pokeData['vulnerable_to'] = $types[$pkm]['vulnerable_to'];
                $innerTeamResistances += count($types[$pkm]['resistant_to']);
                $innerTeamWeaknesses += count($types[$pkm]['vulnerable_to']);
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
}
