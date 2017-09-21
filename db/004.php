<?php

class kanbeterdb004 extends DbMigrate
{

    public function change()
    {
        $this->table('allweather')
            ->changeColumn('gust_wind_speed', 'decimal', array('precision' => 5, 'scale' => 1, 'null' => true))
            ->changeColumn('ave_wind_speed', 'decimal', array('precision' => 5, 'scale' => 1, 'null' => true))
            ->changeColumn('wind_dir', 'decimal', array('precision' => 4, 'scale' => 1, 'null' => true))
            ->save()
        ;
    }

}
