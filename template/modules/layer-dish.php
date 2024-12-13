<?php
$dishesSelectHeader = '<header id="dishes-select-header"><h2>Choose a Dish</h2></header>';
printf('%s<section id="dishes">%s</section>', $dishesSelectHeader, implode('', $args['dishes']));
?>