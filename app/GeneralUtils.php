<?php

namespace Classes;

class GeneralUtils {

	public function formatImgUrl($id, $name)
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
                $pkm = $this->getPokeApiJson($formattedName);
                $imgUrl = is_numeric($pkm['id']) ? $pkm['id'] : '';
                break;

            case "Alola":
                $formattedName = strtolower($name[1]) . '-alola';
                $pkm = $this->getPokeApiJson($formattedName);
                $imgUrl = is_numeric($pkm['id']) ? $pkm['id'] : '';
                break;

            case "Shadow":
                break;

            default:
                $imgUrl = $id . '-' . strtolower($form);
        }

        return $imgUrl;
    }

    private function getPokeApiJson($pokemon)
    {
        $read = file_get_contents("https://pokeapi.co/api/v2/pokemon/" . $pokemon);
        return json_decode($read, true);
    }
    
    public function formatNameForJsBuilder($name)
    {
        if ($name == 'Galarian Sirfetch’d') {
            return "Sirfetch’d";
        }
        if ($name == 'Galarian Obstagoon') {
            return "Obstagoon";
        }
        if ($name == 'Galarian Perrserker') {
            return "Perrserker";
        }
        if ($name == 'Galarian Mr. Rime') {
            return "Mr. Rime";
        }
        if ($name == "Galarian Runerigus") {
            return "Runerigus";
        }
    }
    
    public function formatImgurlForJsBuilder($name)
    {
        if ($name == "Galarian Sirfetch’d") {
            return 865;
        }
        if ($name == "Galarian Obstagoon") {
            return 862;
        }
        if ($name == "Galarian Perrserker") {
            return 863;
        }
        if ($name == "Galarian Runerigus") {
            return 867;
        }
        if ($name == "Galarian Mr. Rime") {
            return 866;
        }
        if ($name == "Baile Oricorio") {
            return 741;
        }
        if ($name == "Sensu Oricorio") {
            return 10125;
        }
        if ($name == "Pau Oricorio") {
            return 10124;
        }
        if ($name == "Pompom Oricorio") {
            return 10123;
        }
        if ($name == "Midday Lycanroc") {
            return 745;
        }
        if ($name == "Midnight Lycanroc") {
            return 10126;
        }
        if ($name == "Hisuian Arcanine") {
            return 10230;
        }
        if ($name == "Hisuian Electrode") {
            return 10232;
        }
        if ($name == "Hisuian Qwilfish") {
            return 10234;
        }
        if ($name == "Hisuian Sneasel") {
            return 10235;
        }
        if ($name == "Hisuian Braviary") {
            return 10240;
        }
        if ($name == "Hisuian Aqualung") {
            return 10243;
        }
    }
}