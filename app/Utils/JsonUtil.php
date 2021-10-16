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
    const PKM_PVE_JSON = 'includes/files/pkm_pve.json';

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

                $type = $item['type'];

                $defense_data = $this->getPokemonDefenseData($type);

                $toWrite[$name]['type'] = $type;

                $toWrite[$name]['vulnerable_to'] = $defense_data['vulnerable_to'];

                $toWrite[$name]['resistant_to'] = $defense_data['resistant_to'];
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
            $types = $this->getTypeEffectiveness();
            $pvpStats = $this->getQuickMovesPvpStats();
            
            $read = file_get_contents("https://pogoapi.net/api/v1/fast_moves.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {

                $goodAgainst = [];
                $weakAgainst = [];

                foreach ($types[$item['type']] as $key => $value)
                {
                    if ($value > 1) {
                        $goodAgainst[] = $key;
                    }
                    if ($value < 1) {
                        $weakAgainst[] = $key;
                    }
                }

                $toWrite[$item['name']]['type'] = $item['type'];
                $toWrite[$item['name']]['weakAgainst'] = $weakAgainst;
                $toWrite[$item['name']]['goodAgainst'] = $goodAgainst;
                $toWrite[$item['name']]['dpt'] = $pvpStats[$item['name']]['DPT'];
                $toWrite[$item['name']]['ept'] = $pvpStats[$item['name']]['EPT'];
            }

            file_put_contents(self::QUICK_MOVES_JSON, json_encode($toWrite, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $content = file_get_contents(self::QUICK_MOVES_JSON);
        return json_decode($content, true);
    }

    private function getQuickMovesPvpStats($force = false)
    {
        $file = file_get_contents('https://gamepress.gg/pokemongo/pvp-fast-moves');
        $file = strip_tags($file);
        $file = explode("EPT", $file);
        $file = explode("if(detectWidth()", $file[2]);

        $moves = trim($file[0]);
        $moves = preg_replace("/\s+/", " ", $moves);
        $moves = explode(" ", $moves);

        $keys = [];
        foreach ($moves as $key => &$str) {
            if (!is_numeric($str)) {
                $name = $str;
                unset($moves[$key]);
                if (!is_numeric($moves[$key+1])) {
                    $name = $name . " " . $moves[$key+1];
                    unset($moves[$key+1]);
                    if (!is_numeric($moves[$key+2])) {
                        $name = $name . " " . $moves[$key+2];
                        unset($moves[$key+2]);
                    }
                }
                $keys[] = $name;
            }
        }

        $chunks = array_chunk($moves, 6);

        $chunks = array_map(function($chunk) {
            return array(
                //'PWR' => $chunk[0],
                //'T' => $chunk[1],
                //'E' => $chunk[2],
                //'CD' => $chunk[3],
                'DPT' => $chunk[4],
                'EPT' => $chunk[5],
            );
        }, $chunks);

        return array_combine($keys, $chunks);
            
    }

    public function getChargeMoves($force = false)
    {
        $types = $this->getTypeEffectiveness();

        if (!file_exists(self::CHARGE_MOVES_JSON) || $force) {
            $read = file_get_contents("https://pogoapi.net/api/v1/charged_moves.json");
            $read = json_decode($read, true);

            $toWrite = [];
            foreach ($read as $item) {

                $goodAgainst = [];
                $weakAgainst = [];

                foreach ($types[$item['type']] as $key => $value)
                {
                    if ($value > 1) {
                        $goodAgainst[] = $key;
                    }
                    if ($value < 1) {
                        $weakAgainst[] = $key;
                    }
                }

                $toWrite[$item['name']]['type'] = $item['type'];
                $toWrite[$item['name']]['weakAgainst'] = $weakAgainst;
                $toWrite[$item['name']]['goodAgainst'] = $goodAgainst;
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

    private function formatValue($number, $decimal = 0) {
        return number_format($number * 100, $decimal) . "%";
    }

    private function getPokemonDefenseData($inTypes)
    {
        $types = $this->getTypeEffectiveness();

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
}

