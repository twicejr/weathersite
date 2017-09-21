<table class="records">
    <tr>
        <th>Record</th>
        <th>Waarde</th>
        <th>Datum laatst behaald</th>        
    </tr>
    <?php
    $i = 0;
    foreach($resultset as $row):
        $i++;
        ?><tr>
            <td><?php echo $row['type']; ?></td>
            <td><?php echo $row['value']; ?></td>
            <td><?php echo $row['added_at']; ?></td>
        </tr><?php
    endforeach;
?></table>
