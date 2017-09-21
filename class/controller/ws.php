<?php

    class ControllerWs extends ControllerHtml
    {
        protected $_scales = array
        (
            1 => "real",
            Date::MINUTE => '1 min',
            Date::MINUTE*5 => '5 min',
            Date::QUARTER => 'kwartier',
            Date::HOUR/2 => 'half uur',
            Date::HOUR => 'uur',
            Date::DAY => 'dag',
            Date::WEEK => 'week',
            Date::MONTH => 'maand',
        );
        protected $_dustset = array
        (
            'datetime',
            'pm25',
            'pm10'
        );
        protected $_graphs = array
        (
            'Temperatuur' => array(
                'datetime',
                'indoor_temp',
                'outdoor_temp',
            ),
            'Luchtvochtigheid' => array(
                'datetime',
                'indoor_humidity',
                'outdoor_humidity',
            ),
            'Luchtvochtigheid absoluut (gr/m<sup>3</sup>)' => array(
                'datetime',
                'indoor_humidity_absolute',
                'outdoor_humidity_absolute',
            ),
            'Druk' => array(
                'datetime',
                'rel_pressure',
            ),
            'Dauwinfo/binnentemp' => array(
                'datetime',
                'indoor_temp',
                'dewpoint_indoor',
                'dewpoint_outdoor',
                'outdoor_temp'
            ),
/*            'Windsnelheid' => array(
                'datetime',
                'gust_wind_speed',
                'ave_wind_speed',
            ),
            'Windrichting' => array(
                'datetime',
                'wind_dir',
            ),*/
            'Regen cumu' => array(
                'datetime',
                'rain',
            ),
        );

        protected function _addAssets()
        {
            parent::_addAssets();
            $this->msie_js(array('lte IE 8' => array('js/excanvas.js')));
        }

        public function action_ajax()
        {
            $return = array();
            $resultset = $this->_result(1, false);
            $row = current($resultset);
            foreach($this->_graphs as $name => $set)
            {
                $return[$name] = array();
                foreach($set as $key)
                {
                    $return[$name][$key] = $row[$key];
                }
            }
            $result['fijnstof'] = array();
            
            $res2 = $this->_resultDust(1, false);
            $row2 = current($res2);
            foreach($this->_dustset as $key)
            {
                $return['fijnstof'][$key] = $row2[$key];
            }
            echo json_encode($return);
            exit;
        }
        
        public function action_index()
        {
            $this->addAssets('css', 'front');
            
            $middling = (float) (isset($_GET['m']) ? $_GET['m'] : 300);
            $period = (float) (isset($_GET['l']) ? $_GET['l'] : ($middling >= 3600 ? 9999999 : ($middling >= 3600 ? 168 : ($middling > 900 ? 48 : ($middling > 60 ? 24 : 2) ))));
            
            return 
              $this->_graphAndTable($middling, $period)
           /*   .
              $this->_trendRecords($middling, $period)*/
            ;
        }

        public function action_storedust()
        {
            $rows = array();
            $dec = current(json_decode($_POST['data'], true));
            foreach($dec as $row)
            {
                $date = Date::toSql(strtotime($row[0]), null, false, true); //to system time from utc
                $rows[$date] = array('recorddate' => $date, 'pm25' => ((int)$row[1])/10, 'pm10' => ((int)$row[2])/10);
            }
try
{
            Db::in()->alldust()->insert_multi(array_values($rows));
}
catch(Exception $e)
{
            foreach($dec as $row)
            {
 try
{
                $date = Date::toSql(strtotime($row[0]));
                Db::in()->alldust()->insert(array('recorddate' => $date, 'pm25' => ((int)$row[1])/10, 'pm10' => ((int)$row[2])/10));
}
catch(Exception $e)
{
}
            }

}
            echo 1;
Request::in()->shutdown();
        }
        
        protected function _trendRecords($middling, $period)
        {
            return
                Template::in('allweather/table.records.php')
                ->resultset($this->_resultMinMaxPeriod($period))
                ->render()
            ;
            
            
//            Trends +-
//            30m
//            1h
//            2h
//            4h
//            12h
//            24h
//            1w
//
//            Records avg, records up, records down
//            5m
//            1h
//            24h
//            1w
//            1m
//            1y
//            
//            Current
//            1m
//            1h
//            24h
//            1w


        }
        
        protected function _resultMinMaxPeriod($period)
        {
            $query = array();
            $columns = array
            (
                'max' => array
                (
                    'indoor_temp',
                    'outdoor_temp',
                    'indoor_humidity',
                    'outdoor_humidity',
                    'abs_pressure',
                ),
                'min' => array
                (
                    'indoor_temp',
                    'outdoor_temp',
                    'indoor_humidity',
                    'outdoor_humidity',
                    'abs_pressure',
                ),
            );
            $result = array();
            foreach($columns['max'] as $column)
            {
                $result[$column . '_max'] = current($this->_queryArray(
                <<<SQL
SELECT 
    'max_$column' AS type,
    added_at, 
    $column AS value
FROM allweather
WHERE 
    added_at > DATE_SUB(NOW(), INTERVAL $period HOUR)
    ORDER BY value DESC, added_at DESC
LIMIT 1
SQL
                ));
            }
            foreach($columns['min'] as $column)
            {
                $result[$column . '_min'] = current($this->_queryArray(
                <<<SQL
SELECT 
    'min_$column' AS type,
    added_at, 
    $column AS value
FROM allweather
WHERE 
    added_at > DATE_SUB(NOW(), INTERVAL $period HOUR)
    ORDER BY value ASC, added_at DESC
LIMIT 1
SQL
                ));
            }
            krsort($result);
            return $result;
        }
        
        protected function _graphAndTable($middling, $period)
        {
            $resultset = $this->_result($middling, $period);
            $graphs = '';
            if(isset($_GET['g']) && $_GET['g'] || !isset($_GET['g']))
            {
                foreach($this->_graphs as $name => $set)
                {
                    $graphs .= Template::in('allweather/graph.php')
                      ->name($name)
                      ->csv($this->_genCsv($resultset, $set))
                      ->render()
                    ;
                }
            }
            $legend = <<<HTML
<style type="text/css">
table:hover
{
    opacity: .5 !important;
}
</style>
<table style="font-family: courier; font-weight: bold;">
    <tr style="background:#009966;color:#ffffff;">
        <td>Goed</td>
        <td><= 12</td>
    </tr>   
    <tr style="background:#FFDE33;color:#000;">
        <td>Middelmatig</td>
        <td>&gt;= 12.1 &nbsp;&lt; 35.5</td>
    </tr>   
    <tr style="background:#FF9933;color:#000;">
        <td style="padding-right: 12px;">Ongezond voor gevoelige groepen</td>
        <td>&gt;= 35.5 &nbsp;&lt; 55.5</td>
    </tr>   
    <tr style="background:#CC0033;color:#ffffff;">
        <td>Ongezond</td>
        <td>&gt;= 55.5 &nbsp;&lt; 150.5</td>
    </tr>
    <tr style="background:#660099;color:#ffffff;">
        <td>Zeer ongezond</td>
        <td>&gt;= 150.5 &lt; 250.5</td>
    </tr>   
    <tr style="background:#7E0023;color:#ffffff;">
        <td>Gevaarlijk</td>
        <td>&gt;= 250.5</td>
    </tr>   
</table>
HTML;
                
            return 
                Template::in('allweather/scales.php')
                ->scale_chosen($middling)
                ->period($period)
                ->graphs($graphs)
                ->scales($this->_scales)->render()
                .  (isset($_GET['dust']) ? Template::in('allweather/graph.php')
                      ->name('stof')
                      ->csv($this->_genCsv( $this->_resultDust($middling, $period), $this->_dustset))
                      ->render()
                . $legend.
                Template::in('allweather/graph.php')
                      ->name('fijnstof')
                      ->extraclass('dustbars')
                      ->csv($this->_genCsv( $this->_resultDust($middling, $period), $this->_dustset))
                      ->render() : '')
                .$graphs .
                Template::in('allweather/table.php')
                ->resultset($resultset)
                ->limit(250)
                ->render()
. \SqlFormatter\SqlFormatter::format($this->query())
            ;
        }

        protected function _genCsv($resultset, $set)
        {
            $csv = implode(',', $set) . PHP_EOL;
            foreach($resultset as $row)
            {
                $g = array();
                foreach($set as $idx)
                {
                    $v = $row[$idx];
                    $g[] = $v;
                }
                $csv .= implode(',', $g) . PHP_EOL;
            }
            return $csv;
        }
        
        protected function _result($middling, $period = false)
        {
            $middling2 = $middling / 2;
            $date_start = "DATE_SUB(NOW(), INTERVAL $period*60 MINUTE)";
            if($period === false)
{
               $date_start = "MAX(added_at)";
$period = .04;
}

            if($middling >= Date::WEEK)
            {
                $format = '%d-%m-%Y';
            }
            elseif($middling >= Date::DAY)
            {
                $format = '%d-%m-%Y';
            }
            elseif($middling >= Date::HOUR)
            {
                $format = '%d-%m-%Y %H:00';
            }
            else
            {
                $format = '%d-%m-%Y %H:%i';
            }
            
            if($middling >= Date::MONTH)
            {
                $dt = "DATE_FORMAT( added_at , '%Y-%m' )";
                $grp = "DATE_FORMAT( added_at , '%Y-%m')";
            }
            elseif($middling >= Date::WEEK)
            {
                $dt = "CONCAT('Week ', WEEK(DATE_ADD(added_at, INTERVAL 24 HOUR), 3), ' (gestart:  ', STR_TO_DATE( concat( concat( date_format( added_at , '%Y' ) , WEEKOFYEAR( DATE_ADD(added_at, INTERVAL 24 HOUR) ) ) , ' Monday' ) , '%X%V %W' ), ')')";
                $grp = "CONCAT(date_format( added_at , '%Y' ), WEEK(added_at, 3))";
            }
            elseif($middling >= Date::DAY)
            {
                $dt = "DATE_FORMAT(added_at, '$format')";
                $grp = "DATE(added_at)";
            }
            elseif($middling > Date::MINUTE)
            {
                $dt = "FROM_UNIXTIME($middling * ROUND((UNIX_TIMESTAMP(added_at) + $middling2)/$middling), '$format')";
                $grp = "ROUND((UNIX_TIMESTAMP(added_at) + $middling2)/$middling)";
            }
            else
            {
                $dt = "DATE_FORMAT(added_at, '$format')";
                $grp = "added_at";
            }
            
            
            $query = <<<SQL
              
SELECT 
	$dt AS datetime_pretty,
        added_at AS datetime,
	ROUND(AVG(indoor_temp), 2) AS indoor_temp,
    ROUND(AVG(indoor_humidity), 2) AS indoor_humidity,
    ROUND(AVG(outdoor_temp), 2) AS outdoor_temp,
    ROUND(AVG(outdoor_humidity), 2) AS outdoor_humidity,
 /*   ROUND(AVG(gust_wind_speed*3.6), 2) AS gust_wind_speed,
    ROUND(AVG(ave_wind_speed*3.6), 2) AS ave_wind_speed,
           
    AVG(wind_dir) AS wind_dir,*/
              
    ROUND(AVG(abs_pressure - 3), 2) AS rel_pressure,
              
    (MAX(rain_total) - (SELECT rain_total FROM `allweather` WHERE added_at > DATE_SUB(NOW(), INTERVAL $period HOUR) ORDER BY id ASC LIMIT 1)) AS rain,
              
     FORMAT(POW(indoor_humidity/100,0.125)*(112+0.9*indoor_temp)+0.1*indoor_temp-112,2) AS dewpoint_indoor,
     FORMAT(POW(outdoor_humidity/100,0.125)*(112+0.9*outdoor_temp)+0.1*outdoor_temp-112,2) AS dewpoint_outdoor,
    (6.112 * POW(2.71828, ((17.67 * indoor_temp)/(indoor_temp+243.5))) * indoor_humidity * 2.1674) / (273.15+indoor_temp) AS indoor_humidity_absolute,
    (6.112 * POW(2.71828, ((17.67 * outdoor_temp)/(outdoor_temp+243.5))) * outdoor_humidity * 2.1674) / (273.15+outdoor_temp) AS outdoor_humidity_absolute
              
FROM `allweather`
WHERE added_at >= $date_start
GROUP BY $grp
ORDER BY added_at ASC
SQL;
            $this->query($query);
            return $this->_queryArray($query);
        }
        
        protected function _resultDust($middling, $period = false)
        {
            $origin_dtz = new DateTimeZone('UTC');
            $remote_dtz = new DateTimeZone(SYSTEM_TIMEZONE);
            $origin_dt = new DateTime("now", $origin_dtz);
            $remote_dt = new DateTime("now", $remote_dtz);
            $offset = ($remote_dtz->getOffset($remote_dt) - $origin_dtz->getOffset($origin_dt)) / 3600;
            if($period === false)
               $date_start = "MAX(recorddate)";
            $period += $offset;

            $middling = $middling;
            $middling2 = $middling / 2;
 
            if(!isset($date_start))
                $date_start = "DATE_SUB(NOW(), INTERVAL $period*60 MINUTE)"; //minute, not hour... too big for raspi

            if($middling >= Date::WEEK)
            {
                $format = '%d-%m-%Y';
            }
            elseif($middling >= Date::DAY)
            {
                $format = '%d-%m-%Y';
            }
            elseif($middling >= Date::HOUR)
            {
                $format = '%d-%m-%Y %H:00';
            }
            else
            {
                $format = '%d-%m-%Y %H:%i:%s';
            }
            
            if($middling >= Date::MONTH)
            {
                $dt = "DATE_FORMAT( DATE_ADD(recorddate, INTERVAL {$offset} HOUR) , '%Y-%m' )";
                $grp = "DATE_FORMAT( recorddate , '%Y-%m')";
            }
            elseif($middling >= Date::WEEK)
            {
                $dt = "CONCAT('Week ', WEEK(DATE_ADD(recorddate, INTERVAL {$offset}+24 HOUR), 3), ' (gestart:  ', STR_TO_DATE( concat( concat( date_format( recorddate , '%Y' ) , WEEKOFYEAR( DATE_ADD(recorddate, INTERVAL 24 HOUR) ) ) , ' Monday' ) , '%X%V %W' ), ')')";
                $grp = "CONCAT(date_format( recorddate , '%Y' ), WEEK(recorddate, 3))";
            }
            elseif($middling >= Date::DAY)
            {
                $dt = "DATE_FORMAT(DATE_ADD(recorddate, INTERVAL {$offset} HOUR), '$format')";
                $grp = "DATE(recorddate)";
            }
            elseif($middling > 0)
            {
                $dt = "FROM_UNIXTIME(({$offset} * 3600) + $middling * ROUND((UNIX_TIMESTAMP(recorddate) + $middling2)/$middling), '$format')";
                $grp = "ROUND((UNIX_TIMESTAMP(recorddate) + $middling2)/$middling)";
            }
            else
            {
                $dt = "DATE_FORMAT(DATE_ADD(recorddate, INTERVAL {$offset} HOUR), '$format')";
                $grp = "recorddate";
            }
            
            $query = <<<SQL

SELECT 
    $dt AS datetime_pretty,
    DATE_ADD(recorddate, INTERVAL {$offset} HOUR) AS datetime,
    ROUND(AVG(pm25), 2) AS pm25,
    ROUND(AVG(pm10), 2) AS pm10          
FROM alldust
WHERE recorddate >= $date_start
GROUP BY $grp
ORDER BY recorddate ASC
SQL;

            return $this->_queryArray($query);
        }
        
        protected function _queryArray($query)
        {
            $result = Db::pdo()->query($query);
            $return = array();
            foreach($result as $row)
            {
                $return[] = $row;
            }
            return $return;
        }
    }
    
