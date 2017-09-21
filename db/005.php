<?php

class kanbeterdb005 extends DbMigrate
{

    public function change()
    {
        $this->table('alldust')
            ->addColumn('recorddate', 'datetime')
            ->addColumn('pm25', 'decimal', array('precision' => 4, 'scale' => 1))
            ->addColumn('pm10', 'decimal', array('precision' => 5, 'scale' => 1))
            ->addIndex('recorddate', array('unique' => true))
            ->save()
        ;
    }

}
