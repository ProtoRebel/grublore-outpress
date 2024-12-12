<?php
$mealClose = sprintf('<a href="#" class="layer-close">%s</a>', util_templateReturn('images/icons', null, ['icon' => 'close']));
$mealEditIcon = util_templateReturn('images/icons', null, ['icon' => 'pencil']);
$mealHeader = sprintf('<header class="layer-heading"><h1><span id="meal-name" contenteditable="true">Nothing Loaded</span>%s</h1>%s</header>', $mealEditIcon, $mealClose);

$mealRemove = sprintf('<footer><button id="meal-remove" type="button">Remove Meal</button></footer>');

printf('<aside id="meal">%s%s</aside>', $mealHeader, $mealRemove);
?>