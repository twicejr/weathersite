<table class="allweather">
    <tr>
        <th>Datum</th>
        <th>Binnen temp</th>
        <th>Binnen vocht</th>
        <th>Buiten temp</th>
        <th>Buiten vocht</th>
     <!--   <th>Windstoten</th>
        <th>Wind</th>
        <th>Windrichting</th>-->
        <th>Rel. luchtdruk</th>
        <th>Regen cumulatief</th>
    </tr>
    <?php
    $i = 0;
    foreach($resultset as $row):
        $i++;
        ?><tr>
            <td><?php echo $row['datetime_pretty']; ?></td>
            <td><?php echo $row['indoor_temp']; ?>&deg;C</td>
            <td><?php echo $row['indoor_humidity']; ?>%</td>
            <td><?php echo $row['outdoor_temp']; ?>&deg;C</td>
            <td><?php echo $row['outdoor_humidity']; ?>%</td>
          <?php /*  <td><?php echo round($row['gust_wind_speed']); ?> km/h</td>
            <td><?php echo round($row['ave_wind_speed']); ?> km/h</td>
            <td><?php echo Allweather::humanWind($row['wind_dir']); ?></td> <?php */?>
            <td><?php echo $row['rel_pressure']; ?> hPa</td>
            <td><?php echo $row['rain']; ?>mm</td>
        </tr><?php
            
            if($i >= $limit):
                break;
            endif;
    endforeach;
?></table>
