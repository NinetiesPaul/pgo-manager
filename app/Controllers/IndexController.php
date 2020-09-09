<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Templates;

class IndexController
{

    public function __construct()
    {
    }
    
    public function index()
    {
        new Templates('index.html');
    }

    public function reader()
    {
        $pokemon = '150';

        $data = file_get_contents("https://gamepress.gg/pokemongo/pokemon/$pokemon");

        $data = "<pre>".htmlspecialchars($data) ."</pre>";

        $array = explode(" ", $data);

        $recording = false;

        $weakTo = [];
        $weakToFixed = [];
        $innerKeyWeakTo = 0;

        foreach ($array as $key => $item) {

            if (strpos($item, 'weak-table')) {
                $recording = true;
            }

            if ($recording) {
                if (preg_match('/\b(?:data-cfsrc)\b/i', $item)) {
                    $weakTo[$innerKeyWeakTo][] = str_replace('%22', "'", $item);
                }

                if (preg_match('/\b(?:type-weak-value-)\b/i', $item)) {
                    $weakTo[$innerKeyWeakTo][] = $item;
                    $innerKeyWeakTo++;
                }
            }

            if (strpos($item, "/table")) {
                $recording = false;
            }

        }

        $resistTo = [];
        $resistToFixed = [];
        $innerKeyResistTo = 0;

        foreach ($array as $key => $item) {

            if (strpos($item, 'resist-table')) {
                $recording = true;
            }

            if ($recording) {
                if (preg_match('/\b(?:data-cfsrc)\b/i', $item)) {
                    $resistTo[$innerKeyResistTo][] = str_replace('%22', "'", $item);
                }

                if (preg_match('/\b(?:type-resist-value-)\b/i', $item)) {
                    $resistTo[$innerKeyResistTo][] = $item;
                    $innerKeyResistTo++;
                }
            }

            if (strpos($item, "/table")) {
                $recording = false;
            }

        }

        foreach ($weakTo as $item) {
            $innerItem = json_encode($item);
            if (strpos($innerItem, 'type-weak-value-160')) {
                $weakToFixed["160%"][] = $this->getType($item[0]);
            }
            if (strpos($innerItem, 'type-weak-value-256')) {
                $weakToFixed["256%"][] = $this->getType($item[0]);
            }

        }
        //echo "<pre>" . json_encode($weakToFixed, JSON_PRETTY_PRINT) . "</pre>";

        foreach ($resistTo as $item) {
            $innerItem = json_encode($item);
            if (strpos($innerItem, 'type-resist-value-62.5')) {
                $resistToFixed["62.5%"][] = $this->getType($item[0]);
            }
            if (strpos($innerItem, 'type-resist-value-39.1')) {
                $resistToFixed["39.1%"][] = $this->getType($item[0]);
            }
            if (strpos($innerItem, 'type-resist-value-24.4')) {
                $resistToFixed["24.4%"][] = $this->getType($item[0]);
            }

        }
        //echo "<pre>" . json_encode($resistToFixed, JSON_PRETTY_PRINT) . "</pre>";

        $args = [
            'NOME' => 'Galarian Stunfisk',
            'WEAK' => json_encode($resistToFixed),
            'RESIST' => json_encode($weakToFixed),
        ];

        new Templates('reader.html', $args);
    }

    private function getType($string) {
        $array = explode('/', $string);

        foreach ($array as $item) {
            if (strpos($item, ".gif")) {
                return explode(".gif", $item)[0];
            }
        }
    }
}
