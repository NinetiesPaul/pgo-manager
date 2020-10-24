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
    protected $lines_quick = "INSERT INTO moves (nome, tipo_id, classificao) VALUES ";
    protected $charge_rows;
    protected $lines_charge = "INSERT INTO moves (nome, tipo_id, classificao) VALUES ";
    protected $poke_rows;
    protected $lines_pokemon = "INSERT INTO pokemon (nome, tipo_id_1, tipo_id_2) VALUES ";
    protected $processed = 0;

    public function __construct()
    {
        register_shutdown_function(function(){
            \app\Controllers\ReaderController::shutdown();
        });
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
                $api = (isset($api->type->name)) ? $api->type->name : false;
                $tipo = ($api) ? Enum::TYPES_BY_NAME[ucfirst($api)] : 'NULL';
                $line = "('$row[0]', $tipo, 'QUICK')";
                $line .= ($key === count($this->quick_rows) - 1) ? ";" : ",";
                $this->lines_quick .= $line . "\n";
                $this->quick_rows[$key][1] = ($api) ? 'OK' : 'ERRO';
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
                $api = (isset($api->type->name)) ? $api->type->name : false;
                $tipo = ($api) ? Enum::TYPES_BY_NAME[ucfirst($api)] : 'NULL';
                $line = "('$row[0]', $tipo, 'CHARGE')";
                $line .= ($key === count($this->charge_rows) - 1) ? ";" : ",";
                $this->lines_charge .= $line . "\n";
                $this->charge_rows[$key][1] = ($api) ? 'OK' : 'ERRO';
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

                if (substr($this->lines_pokemon, -3) === ','){
                    $this->lines_pokemon[-3] = ";";
                }
                file_put_contents('includes/files/pokemon.sql', $this->lines_pokemon, FILE_APPEND);
                break;

            case 'QUICK':
                $fp = fopen('includes/files/quick_db.csv', 'w');

                foreach ($this->quick_rows as $row) {
                    fputcsv($fp, $row);
                }

                fclose($fp);

                if (substr($this->lines_quick, -3) === ','){
                    $this->lines_quick[-3] = ";";
                }
                file_put_contents('includes/files/quick.sql', $this->lines_quick, FILE_APPEND);
                break;

            case 'CHARGE':
                $fp = fopen('includes/files/charge_db.csv', 'w');

                foreach ($this->charge_rows as $row) {
                    fputcsv($fp, $row);
                }

                fclose($fp);

                if (substr($this->lines_charge, -3) === ','){
                    $this->lines_charge[-3] = ";";
                }
                file_put_contents('includes/files/charge.sql', $this->lines_charge, FILE_APPEND);
                break;
        }
    }
}
