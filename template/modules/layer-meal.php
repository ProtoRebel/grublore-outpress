<?php
$mealClose = sprintf('<a href="#" class="layer-close">%s</a>', util_icon('close'));
$mealEditIcon = util_icon('pencil');
$mealHeader = sprintf('<header class="layer-heading"><h1><span id="meal-name" contenteditable="true">Nothing Loaded</span>%s</h1>%s</header>', $mealEditIcon, $mealClose);

$mealDishDelete = sprintf('<a href="#" class="meal-dish-remove">%s</a>', util_icon('trash'));
$mealDishList = sprintf('<ul id="meal-dishes"><li id="meal-dish-template" data-dish=""><em>Course</em><strong>Name</strong>%s</li></ul>', $mealDishDelete);

$mealNotes = sprintf('<div class="note"><textarea id="meal-note" placeholder="Add a note to this meal" rows="4"></textarea>%s</div>', util_icon('pencil'));

$mealDishAdd = '<button id="dish-add" type="button"><span>Add Dish</span></button>';

$mealRemove = sprintf('<footer><button id="meal-remove" type="button" class="danger">Remove Meal</button></footer>');

$mealDeco = util_templateReturn('images/deco', 'side');

printf('<aside id="meal">%s<div class="content"><span class="background">%s</span>%s%s%s%s</div></aside>', $mealHeader, $mealDeco, $mealDishList, $mealDishAdd, $mealNotes, $mealRemove);
?>