<?php

use Phinx\Migration\AbstractMigration;

class ForeignKeys1 extends AbstractMigration
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
        $table = $this->table('pokemon');
        $table->addForeignKey('tipo_1', 'types', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('tipo_2', 'types', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->save();

        $table = $this->table('pokemon_moves');
        $table->addForeignKey('pokemon_id', 'pokemon', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('move_id', 'moves', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->save();

        $table = $this->table('moves');
        $table->addForeignKey('tipo_id', 'types', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->save();
    }
}
