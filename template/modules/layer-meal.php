<?php
$mealClose = sprintf('<a href="#" class="layer-close">%s</a>', util_templateReturn('images/icons', null, ['icon' => 'close']));
$mealEditIcon = util_templateReturn('images/icons', null, ['icon' => 'pencil']);
$mealHeader = sprintf('<header class="layer-heading"><h1><span id="meal-name" contenteditable="true">Nothing Loaded</span>%s</h1>%s</header>', $mealEditIcon, $mealClose);

$mealDishDelete = sprintf('<button class="meal-dish-remove">%s</button>', util_templateReturn('images/icons', null, ['icon' => 'trash']));
$mealDishList = sprintf('<ul class="meal-dishes"><li id="meal-dish-template"><em></em><strong></strong>%s</li></ul>', $mealDishDelete);

$mealNotes = sprintf('<div class="note"><textarea id="meal-note" placeholder="Add a note to this meal" rows="4"></textarea>%s</div>', util_templateReturn('images/icons', null, ['icon' => 'pencil']));

$mealDishAdd = '<button id="dish-add" type="button"><span>Add Dish</span></button>';

$mealRemove = sprintf('<footer><button id="meal-remove" type="button" class="danger">Remove Meal</button></footer>');

$mealDeco = util_templateReturn('images/deco', 'side');

printf('<aside id="meal">%s<span class="background">%s</span><div class="content">%s%s%s%s</div></aside>', $mealHeader, $mealDeco, $mealDishList, $mealDishAdd, $mealNotes, $mealRemove);
?>