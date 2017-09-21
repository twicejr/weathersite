Middeling: <?php foreach ($scales as $value => $scale): ?>
<a <?php if ($value == $scale_chosen): ?>class="active"<?php endif; ?> href="<?php echo Uri::addParams(array('m' => $value));?>"><?php echo $scale; ?></a>
<?php endforeach; ?>
<br>
Parameters:<br>
<a <?php if ($graphs): ?>class="active"<?php endif; ?> href="<?php echo Uri::addParams(array('g' => 1, 'l' => $period));?>">Grafiek aan / houdt periode</a>
<a <?php if (!$graphs): ?>class="active"<?php endif; ?>  href="<?php echo Uri::addParams(array('g' => 0, 'l' => null));?>">Grafiek uit / reset periode</a>
<div class="clear"></div>
