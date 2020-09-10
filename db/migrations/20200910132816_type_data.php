<?php

use Phinx\Migration\AbstractMigration;

use App\Enum;

class TypeData extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $types = [
            [ 'id' => Enum::TYPE_NORMAL_ID, 'nome' => 'Normal' ],
            [ 'id' => Enum::TYPE_GRASS_ID, 'nome' => 'Grass' ],
            [ 'id' => Enum::TYPE_FIRE_ID, 'nome' => 'Fire' ],
            [ 'id' => Enum::TYPE_WATER_ID, 'nome' => 'Water' ],
            [ 'id' => Enum::TYPE_FIGHTING_ID, 'nome' => 'Fighting' ],
            [ 'id' => Enum::TYPE_FLYING_ID, 'nome' => 'Flying' ],
            [ 'id' => Enum::TYPE_POISON_ID, 'nome' => 'Poison' ],
            [ 'id' => Enum::TYPE_GROUND_ID, 'nome' => 'Ground' ],
            [ 'id' => Enum::TYPE_ROCK_ID, 'nome' => 'Rock' ],
            [ 'id' => Enum::TYPE_ICE_ID, 'nome' => 'Ice' ],
            [ 'id' => Enum::TYPE_FAIRY_ID, 'nome' => 'Fairy' ],
            [ 'id' => Enum::TYPE_STEEL_ID, 'nome' => 'Steel' ],
            [ 'id' => Enum::TYPE_PSYCHIC_ID, 'nome' => 'Psychic' ],
            [ 'id' => Enum::TYPE_DARK_ID, 'nome' => 'Dark' ],
            [ 'id' => Enum::TYPE_ELECTRIC_ID, 'nome' => 'Electric' ],
            [ 'id' => Enum::TYPE_DRAGON_ID, 'nome' => 'Dragon' ],
            [ 'id' => Enum::TYPE_BUG_ID, 'nome' => 'Bug' ],
            [ 'id' => Enum::TYPE_GHOST_ID, 'nome' => 'Ghost' ],
        ];

        $this->table('types')->insert($types)->save();
    }
}
