<?php


namespace App\Utils;


class JsonUtil
{
    /*
     * This class is responsible for retrieving auxiliary JSON content
     */

    const MEGA_JSON = 'includes/files/mega_pokemon.json';
    const TYPES_JSON = 'includes/files/pokemon_types.json';
    const TYPE_EFFECTIVENESS_JSON = 'includes/files/type_effectiveness.json';
    const STATS_JSON = 'includes/files/pokemon_stats.json';
    const QUICK_MOVES_JSON = 'includes/files/fast_moves.json';
    const CHARGE_MOVES_JSON = 'includes/files/charged_moves.json';
    const CURRENT_PKM_MOVES_JSON = 'includes/files/current_pokemon_moves.json';
    const FORMS_JSON = 'includes/files/pokemon_forms.json';

    protected $pkmForms = [];

    public function __construct()
    {
        if (!file_exists(self::FORMS_JSON)) {
            $read = file_get_contents("https://pogoapi.net/api/v1/pokemon_stats.json");

            $pkms = json_decode($read, true);

            $forms = [];
            foreach ($pkms as $pkm) {
                $form = $pkm['form'];
                if (!strpos($form, "_") && !is_numeric($form) && $form !== "Normal" && $form !== "Purified")
                    if (!in_array($pkm['form'], $forms))
                        $forms[] = $pkm['form'];
            }

            file_put_contents(self::FORMS_JSON, json_encode($forms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $this->pkmForms = json_decode(file_get_contents(self::FORMS_JSON), true);
    }

    public function getMegaPokemons($force = false)
    {
        if (!file_exists(self::MEGA_JSON) || $force) {
            file_put_contents(self::MEGA_JSON, file_get_contents("https://pogoapi.net/api/v1/mega_pokemon.json"));
            $read = file_get_contents("https://pogoapi.net/api/v1/mega_pokemon.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {

                $pokemon = [
                    'stats' => [
                        'atk' => $item['stats']['base_attack'],
                        'def' => $item['stats']['base_defense'],
                        'sta' => $item['stats']['base_stamina'],
                    ],
                    'type' => (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0],
                ];

                $toWrite[$item['mega_name']] = $pokemon;
            }

            file_put_contents(self::MEGA_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::MEGA_JSON);
        return json_decode($content, true);
    }

    public function getTypeEffectiveness($force = false)
    {
        if (!file_exists(self::TYPE_EFFECTIVENESS_JSON) || $force) {
            file_put_contents(self::TYPE_EFFECTIVENESS_JSON, file_get_contents("https://pogoapi.net/api/v1/type_effectiveness.json"));
        }

        $content = file_get_contents(self::TYPE_EFFECTIVENESS_JSON);
        return json_decode($content, true);
    }

    public function getType($force = false)
    {
        if (!file_exists(self::TYPES_JSON) || $force) {
            $read = file_get_contents("https://pogoapi.net/api/v1/pokemon_types.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {

                $name = $item['pokemon_name'];

                if (in_array($item['form'], $this->pkmForms)) {
                    $name = $item['form'] . " " . $item['pokemon_name'];
                }

                $toWrite[$name] = (count($item['type']) > 1) ? implode("/", $item['type']) : $item['type'][0];
            }

            file_put_contents(self::TYPES_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::TYPES_JSON);
        return json_decode($content, true);
    }

    public function getStats($force = false)
    {
        if (!file_exists(self::STATS_JSON) || $force) {
            $read = file_get_contents("https://pogoapi.net/api/v1/pokemon_stats.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {

                $name = $item['pokemon_name'];

                if (in_array($item['form'], $this->pkmForms)) {
                    $name = $item['form'] . " " . $item['pokemon_name'];
                }

                $toWrite[$name] = [
                    'atk' => $item['base_attack'],
                    'def' => $item['base_defense'],
                    'sta' => $item['base_stamina'],
                    'id' => $item['pokemon_id']
                ];
            }

            file_put_contents(self::STATS_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::STATS_JSON);
        return json_decode($content, true);
    }

    public function getQuickMoves($force = false)
    {
        if (!file_exists(self::QUICK_MOVES_JSON) || $force) {
            $read = file_get_contents("https://pogoapi.net/api/v1/fast_moves.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {
                $toWrite[$item['name']] = $item['type'];
            }

            file_put_contents(self::QUICK_MOVES_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::QUICK_MOVES_JSON);
        return json_decode($content, true);
    }

    public function getChargeMoves($force = false)
    {
        if (!file_exists(self::CHARGE_MOVES_JSON) || $force) {
            $read = file_get_contents("https://pogoapi.net/api/v1/charged_moves.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {
                $toWrite[$item['name']] = $item['type'];
            }

            file_put_contents(self::CHARGE_MOVES_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::CHARGE_MOVES_JSON);
        return json_decode($content, true);
    }

    public function getCurrentPkmMoves($force = false)
    {
        if (!file_exists(self::CURRENT_PKM_MOVES_JSON) || $force) {
            $read = file_get_contents("https://pogoapi.net/api/v1/current_pokemon_moves.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {

                $name = $item['pokemon_name'];

                if (in_array($item['form'], $this->pkmForms)) {
                    $name = $item['form'] . " " . $item['pokemon_name'];
                }

                $toWrite[$name]['quick'] = array_merge(
                    $item['fast_moves'],
                    array_map(
                        function ($move)
                        {
                            return $move . "*";
                        }
                        ,
                        $item['elite_fast_moves']
                    )
                );

                $toWrite[$name]['charge'] = array_merge(
                    $item['charged_moves'],
                    array_map(
                        function ($move)
                        {
                            return $move . "*";
                        }
                        , $item['elite_charged_moves']
                    )
                );
            }

            file_put_contents(self::CURRENT_PKM_MOVES_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::CURRENT_PKM_MOVES_JSON);
        return json_decode($content, true);
    }

    public function getPokeApiJson($pokemon)
    {
        $read = file_get_contents("https://pokeapi.co/api/v2/pokemon/" . $pokemon);
        return json_decode($read, true);
    }

    function eliteFormatter($move)
    {
        return $move . "*";
    }
}

