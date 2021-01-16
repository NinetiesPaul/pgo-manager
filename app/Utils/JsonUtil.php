<?php


namespace App\Utils;


class JsonUtil
{
    /*
     * This class is responsible for retrieving auxiliary JSON content
     */

    const SHADOW_JSON = 'includes/files/shadow_pokemon.json';
    const POKEMON_NAMES_JSON = 'includes/files/pokemon_names.json';
    const GALARIAN_JSON = 'includes/files/galarian_pokemon.json';
    const ALOLAN_JSON = 'includes/files/alolan_pokemon.json';
    const MEGA_JSON = 'includes/files/mega_pokemon.json';
    const TYPES_JSON = 'includes/files/pokemon_types.json';
    const TYPE_EFFECTIVENESS_JSON = 'includes/files/type_effectiveness.json';
    const STATS_JSON = 'includes/files/pokemon_stats.json';
    const CP_MULTIPLIER_JSON = 'includes/files/cp_multiplier.json';
    const QUICK_MOVES_JSON = 'includes/files/fast_moves.json';
    const CHARGE_MOVES_JSON = 'includes/files/charged_moves.json';

    public static function getShadowPokemons()
    {
        if (!file_exists(self::SHADOW_JSON)) {
            file_put_contents(self::SHADOW_JSON, file_get_contents("https://pogoapi.net/api/v1/shadow_pokemon.json"));
        }

        $content = file_get_contents(self::SHADOW_JSON);
        return json_decode($content, true);
    }

    public static function getPokemonsNames()
    {
        if (!file_exists(self::POKEMON_NAMES_JSON)) {
            file_put_contents(self::POKEMON_NAMES_JSON, file_get_contents("https://pogoapi.net/api/v1/pokemon_names.json"));
        }

        $content = file_get_contents(self::POKEMON_NAMES_JSON);
        return json_decode($content, true);
    }

    public static function getGalarianPokemons()
    {
        if (!file_exists(self::GALARIAN_JSON)) {
            file_put_contents(self::GALARIAN_JSON, file_get_contents("https://pogoapi.net/api/v1/galarian_pokemon.json"));
        }

        $content = file_get_contents(self::GALARIAN_JSON);
        return json_decode($content, true);
    }

    public static function getAlolanPokemons()
    {
        if (!file_exists(self::ALOLAN_JSON)) {
            file_put_contents(self::ALOLAN_JSON, file_get_contents("https://pogoapi.net/api/v1/alolan_pokemon.json"));
        }

        $content = file_get_contents(self::ALOLAN_JSON);
        return json_decode($content, true);
    }

    public static function getMegaPokemons()
    {
        if (!file_exists(self::MEGA_JSON)) {
            file_put_contents(self::MEGA_JSON, file_get_contents("https://pogoapi.net/api/v1/mega_pokemon.json"));
        }

        $content = file_get_contents(self::MEGA_JSON);
        return json_decode($content, true);
    }

    public static function getTypeEffectiveness()
    {
        if (!file_exists(self::TYPE_EFFECTIVENESS_JSON)) {
            file_put_contents(self::TYPE_EFFECTIVENESS_JSON, file_get_contents("https://pogoapi.net/api/v1/type_effectiveness.json"));
        }

        $content = file_get_contents(self::TYPE_EFFECTIVENESS_JSON);
        return json_decode($content, true);
    }

    public static function getType()
    {
        if (!file_exists(self::TYPES_JSON)) {
            file_put_contents(self::TYPES_JSON, file_get_contents("https://pogoapi.net/api/v1/pokemon_types.json"));
        }

        $content = file_get_contents(self::TYPES_JSON);
        return json_decode($content, true);
    }

    public static function getStats()
    {
        if (!file_exists(self::STATS_JSON)) {
            file_put_contents(self::STATS_JSON, file_get_contents("https://pogoapi.net/api/v1/pokemon_stats.json"));
        }

        $content = file_get_contents(self::STATS_JSON);
        return json_decode($content, true);
    }

    public static function getQuickMoves()
    {
        if (!file_exists(self::QUICK_MOVES_JSON)) {
            file_put_contents(self::QUICK_MOVES_JSON, file_get_contents("https://pogoapi.net/api/v1/fast_moves.json"));
        }

        $content = file_get_contents(self::QUICK_MOVES_JSON);
        return json_decode($content, true);
    }

    public static function getChargeMoves()
    {
        if (!file_exists(self::CHARGE_MOVES_JSON)) {
            file_put_contents(self::CHARGE_MOVES_JSON, file_get_contents("https://pogoapi.net/api/v1/charged_moves.json"));
        }

        $content = file_get_contents(self::CHARGE_MOVES_JSON);
        return json_decode($content, true);
    }

    public static function getCpMultiplier()
    {
        if (!file_exists(self::CP_MULTIPLIER_JSON)) {
            file_put_contents(self::CP_MULTIPLIER_JSON, file_get_contents("https://pogoapi.net/api/v1/cp_multiplier.json"));
        }

        $content = file_get_contents(self::CP_MULTIPLIER_JSON);
        return json_decode($content, true);
    }

    private static function compareHashes()
    {
    }
}