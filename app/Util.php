<?php


namespace App;


class Util
{
    const SHADOW_JSON = 'includes/files/shadow_pokemon.json';
    const POKEMON_NAMES_JSON = 'includes/files/pokemon_names.json';

    public static function getShadowPokemons()
    {
        if (file_exists(self::SHADOW_JSON)) {
            $hashed = hash_file('md5', self::SHADOW_JSON);
            self::compareHashWithApi($hashed, 'shadow_pokemon.json');
        }

        $shadowApi = file_get_contents("https://pogoapi.net/api/v1/shadow_pokemon.json");
        file_put_contents(SElf::SHADOW_JSON, $shadowApi);
        //return $shadowApi;
    }

    public static function getPokemonsNames()
    {
        if (file_exists(self::POKEMON_NAMES_JSON)) {
            $hashed = hash_file('md5', self::POKEMON_NAMES_JSON);
            self::compareHashWithApi($hashed, 'pokemon_names.json');
        }

        $namesApi = file_get_contents("https://pogoapi.net/api/v1/pokemon_names.json");
        file_put_contents(SElf::POKEMON_NAMES_JSON, $namesApi);
        //return $shadowApi;
    }

    private static function compareHashWithApi($content, $fileOnApi)
    {
        $apiHash = file_get_contents("https://pogoapi.net/api/v1/api_hashes.json");
        $apiHash = json_decode($apiHash, true);
        echo $apiHash[$fileOnApi]['hash_md5'];
        echo "<br>";
        echo $content;

        if ($apiHash[$fileOnApi]['hash_md5'] === $content) {
            echo "<br>igual";
        } else {
            echo "<br>diferente";
        }
    }
}