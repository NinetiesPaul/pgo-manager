<?php

namespace App\Controllers;

use App\Templates;
use App\Utils\JsonUtil;
use App\Utils\GeneralUtils;

class MainController
{
    protected $jsonUtil;

    protected $generalUtil;

    public function __construct()
    {
        $this->jsonUtil = new JsonUtil();
        $this->generalUtil = new GeneralUtils();
    }

    /*
     * This function force updates the local auxiliary json files
     */
    public function jsonUpdate()
    {
        $this->jsonUtil->getQuickMoves(true);
        $this->jsonUtil->getChargeMoves(true);
        $this->jsonUtil->getTypeEffectiveness(true);
        $this->jsonUtil->getMegaPokemons(true);
        $this->jsonUtil->getType(true);
        $this->jsonUtil->getStats(true);
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
            'imgurl' => $this->generalUtil->formatImgUrl($jsonStats[$pokemonName]['id'], $pokemonName)
        ]);
    }

    /*
     * Retrieve move data to the front end
     */
    public function getMove($name, $type)
    {
        $name = str_replace('_', ' ', $name);

        $moveData = ($type == 'quick') ? $this->jsonUtil->getQuickMoves() : $this->jsonUtil->getChargeMoves();

        $response = [
            'type' => strtoupper($moveData[$name]['type']),
            'weakAgainst' => $moveData[$name]['weakAgainst'],
            'goodAgainst' => $moveData[$name]['goodAgainst']
        ];
        
        /*$url = "https://pokemongo.fandom.com/wiki/" . str_replace(' ', '_', $name);
        
        $file = file_get_contents($url);

		$file = strip_tags($file);

		$file = explode("Type effectiveness", $file);

		$file = explode("Trainer Battles", $file[0]);

		$moveData = trim($file[1]);

		$moveData = preg_replace("/\s+/", " ", $moveData);
		
		$moveData = explode(" ", $moveData);
		
		$moveData = ($type == 'quick') ?  [ $moveData[3], $moveData[4] ] : [ $moveData[2], $moveData[3] ] ;
        
        $response['power'] = $moveData[0];
        $response['energy'] = $moveData[1];*/

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

        $pkmsPve = '';

        if (file_exists(JsonUtil::PKM_PVE_JSON)) {
            $pkmsPve = file_get_contents(JsonUtil::PKM_PVE_JSON);
        } else {
            file_put_contents(JsonUtil::PKM_PVE_JSON, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $pkmsPve = file_get_contents(JsonUtil::PKM_PVE_JSON);
        }
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

        $id = (count($pkmsPve) > 0) ? max(array_keys($pkmsPve)) + 1 : 0;

        $pkmPveRows .= "<tr id='" . $id . "' class='store'><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";

        $args = [
            'LISTA' => $pokemonList,
            'PKMPVE' => $pkmPveRows,
        ];

        new Templates('reader.html', $args);
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
                $pokeData['type'] = $types[$pkm]['type'];
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
}
