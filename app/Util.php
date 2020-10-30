<?php


namespace App;


class Util
{
    const SHADOW_JSON = 'includes/files/shadow_pokemon.json';

    public static function getShadowPokemons()
    {
        if (file_exists(self::SHADOW_JSON)) {
            $hashed = hash_file('md5', self::SHADOW_JSON);
            self::compareHashWithApi($hashed);
        }

        // baixa o arquivo
        // hasheia e compara
    }

    private static function compareHashWithApi($content)
    {
        $apiHash = file_get_contents("https://pogoapi.net/api/v1/api_hashes.json");
        $apiHash = json_decode($apiHash, true);
        echo $apiHash['shadow_pokemon.json']['hash_md5'];
        echo "\n\n";
        echo $content;

        // se hash bate, retornar conteudo
        // se hash não bate, atualizar arquivo e retornar conteudo
    }
}