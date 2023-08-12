<?php

namespace Classes;

class GeneralUtils {

    public function getCsv()
    {
        $data = file_get_contents('https://raw.githubusercontent.com/PokeAPI/pokeapi/master/data/v2/csv/pokemon.csv');

        $data = explode("\n", $data);
        
        $arrayResult = [];
        
        foreach ($data as $n => $row) {
            if ($row !== "" && $n > 0){
                $explodedRow = explode(",", $row);
                $arrayResult[$explodedRow[1]] = $explodedRow[0];
            }
        }

        return $arrayResult;
    }

	public function formatImgUrl($id, $name, $csv)
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
                $imgUrl = $csv[$formattedName];
                break;

            case "Alola":
                $formattedName = strtolower($name[1]) . '-alola';
                $imgUrl = $csv[$formattedName];
                break;

            case "Hisuian":
                $formattedName = strtolower($name[1]) . '-hisui';
                $imgUrl = $csv[$formattedName];
                break;

            case "Shadow":
                break;

            default:
                $imgUrl = $id . '-' . strtolower($form);
        }

        return $imgUrl;
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
    }
}