<?php

namespace App\Utils;

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
}