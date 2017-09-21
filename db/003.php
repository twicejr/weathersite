<?php

class kanbeterdb003 extends DbMigrate
{

    public function change()
    {
        $this->table('allweather')
            ->addColumn('added_at', 'datetime')
            ->addColumn('indoor_temp', 'decimal', array('precision' => 3, 'scale' => 1))
            ->addColumn('indoor_humidity', 'integer', array('length' => 3))
            ->addColumn('outdoor_temp', 'decimal', array('precision' => 3, 'scale' => 1))
            ->addColumn('outdoor_humidity', 'integer', array('length' => 3))
          
            ->addColumn('gust_wind_speed', 'decimal', array('precision' => 5, 'scale' => 1))
            ->addColumn('ave_wind_speed', 'decimal', array('precision' => 5, 'scale' => 1))
            ->addColumn('wind_dir', 'decimal', array('precision' => 4, 'scale' => 1))
            ->addColumn('abs_pressure', 'decimal', array('precision' => 5, 'scale' => 1))
            ->addColumn('rain_total', 'decimal', array('precision' => 8, 'scale' => 1))
          
            ->addIndex(array('added_at'), array('unique' => true))
            ->save()
        ;
    }

}
