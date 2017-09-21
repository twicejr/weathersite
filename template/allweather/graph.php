<?php
if ($csv):
    ?>
    <div class="wrapped-dygraph">
        <h2><?php echo $name; ?></h2>
        <div class="dygraph <?php echo isset($extraclass) ? $extraclass : '';?>"
             data-name="<?php echo $name; ?>"
             data-graph='<?php echo trim($csv); ?>'
              >
        </div>
    </div>
    <?php
 endif;
