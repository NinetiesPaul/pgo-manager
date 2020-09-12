<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controllers;

use App\Enum;
use App\Templates;

class ReaderController
{
    protected $population_type;
    protected $quick_rows;
    protected $lines_quick;
    protected $charge_rows;
    protected $lines_charge;
    protected $poke_rows;
    protected $lines_pokemon;
    protected $processed = 0;

    public function __construct()
    {
        register_shutdown_function(function(){
            \app\Controllers\ReaderController::shutdown();
        });
    }

    public function reader()
    {
        $pokemon = '618-galarian';

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

    public function populate_pokemons()
    {
        $this->population_type = 'POKEMONS';
        $this->poke_rows = array_map('str_getcsv', file('includes/files/pokemon_db.csv'));

        //pre carregar nomes de pokemons da tabela

        foreach ($this->poke_rows as $key => $row) {
            if (strpos($row[0], " ") === false && $row[1] === 'PENDENTE'){ //verificar se pokemon existe na lista pre carregada
                $nome = strtolower($row[0]);
                $nome = str_replace(" ", "-", $nome);
                $api = file_get_contents("https://pokeapi.co/api/v2/pokemon/$nome");
                $api = json_decode($api);
                $tipo_1 = Enum::TYPES_BY_NAME[ucfirst($api->types[0]->type->name)];
                $tipo_2 = ($api->types[1]->type->name) ? Enum::TYPES_BY_NAME[ucfirst($api->types[1]->type->name)] : 'NULL';
                $line = "('$row[0]', ".$tipo_1.", ".$tipo_2.")";
                $line .= ($key === count($this->poke_rows)-1) ? ";" : ",";
                $this->lines_pokemon .= $line . "\n";
                $this->poke_rows[$key][1] = 'OK';
                $this->processed += 1;
            }
        }

        echo "FINALIZADO! $this->processed linhas processadas. Atualizando arquivo<br>";
        $this->writeFiles();
    }

    public function populate_quick()
    {
        $this->population_type = 'QUICK';
        $this->quick_rows = array_map('str_getcsv', file('includes/files/quick_db.csv'));

        //pre carregar moves da tabela

        foreach ($this->quick_rows as $key => $row) {
            if ($row[1] === 'PENDENTE') { //verificar se move existe na lista pre carregada
                $nome = strtolower($row[0]);
                $nome = str_replace(" ", "-", $nome);
                echo "chamando https://pokeapi.co/api/v2/move/$nome<br>";
                $api = file_get_contents("https://pokeapi.co/api/v2/move/$nome");
                $api = json_decode($api);
                $tipo = ($api->type->name) ? $api->type->name : 'move nao encontrado';
                $line = "('$row[0]', " . Enum::TYPES_BY_NAME[ucfirst($tipo)] . ", 'QUICK')";
                $line .= ($key === count($this->quick_rows) - 1) ? ";" : ",";
                $this->lines_quick .= $line . "\n";
                $this->quick_rows[$key][1] = 'OK';
                $this->processed += 1;
            }
        }

        echo "FINALIZADO! $this->processed linhas processadas. Atualizando arquivo<br>";
        $this->writeFiles();
    }

    public function populate_charge()
    {
        $this->population_type = 'CHARGE';
        $this->charge_rows = array_map('str_getcsv', file('includes/files/charge_db.csv'));

        //pre carregar moves da tabela

        foreach ($this->charge_rows as $key => $row) {
            if ($row[1] === 'PENDENTE') { //verificar se move existe na lista pre carregada
                $nome = strtolower($row[0]);
                $nome = str_replace(" ", "-", $nome);
                echo "chamando https://pokeapi.co/api/v2/move/$nome<br>";
                $api = file_get_contents("https://pokeapi.co/api/v2/move/$nome");
                $api = json_decode($api);
                $tipo = ($api->type->name) ? $api->type->name : 'move nao encontrado';
                $line = "('$row[0]', " . Enum::TYPES_BY_NAME[ucfirst($tipo)] . ", 'CHARGE')";
                $line .= ($key === count($this->charge_rows) - 1) ? ";" : ",";
                $this->lines_charge .= $line . "\n";
                $this->charge_rows[$key][1] = 'OK';
                $this->processed += 1;
            }
        }

        echo "FINALIZADO! $this->processed linhas processadas. Atualizando arquivo<br>";
        $this->writeFiles();
    }

    private function getType($string)
    {
        $array = explode('/', $string);

        foreach ($array as $item) {
            if (strpos($item, ".gif")) {
                return explode(".gif", $item)[0];
            }
        }
    }

    private function shutdown()
    {
        $a = error_get_last();

        if ($a == null) {
            echo "No errors";}
        else {
            echo "ABORTADO por estouro de tempo! $this->processed linhas processadas. salvando progresso<br>";
            $this->writeFiles();
        }
    }

    private function writeFiles()
    {
        switch($this->population_type) {
            case 'POKEMONS':
                $fp = fopen('includes/files/pokemon_db.csv', 'w');

                foreach ($this->poke_rows as $row) {
                    fputcsv($fp, $row);
                }

                fclose($fp);

                file_put_contents('includes/files/pokemon.sql', $this->lines_pokemon);
                break;

            case 'QUICK':
                $fp = fopen('includes/files/quick_db.csv', 'w');

                foreach ($this->quick_rows as $row) {
                    fputcsv($fp, $row);
                }

                fclose($fp);

                file_put_contents('includes/files/quick.sql', $this->lines_quick);
                break;

            case 'CHARGE':
                $fp = fopen('includes/files/charge_db.csv', 'w');

                foreach ($this->charge_rows as $row) {
                    fputcsv($fp, $row);
                }

                fclose($fp);

                file_put_contents('includes/files/charge.sql', $this->lines_charge);
                break;
        }
    }
}
