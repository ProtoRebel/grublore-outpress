<?php
$listSeparator = util_templateReturn('images/deco', 'horizontal');
$listDecoSide = sprintf('<span class="deco">%s</span>', util_templateReturn('images/deco', 'side'));

$listMealTemplate = sprintf('<li id="meal-template" class="meal-listing">%s<h2>Unnamed Meal</h2><p><strong></strong>No dishes have been added to this meal</p></li>', $listDecoSide);
$listSampleDataOne = sprintf('<li id="93jdks" class="meal-listing">%s<h2>Sunday Dinner</h2><p><strong>Butter Chicken + Biryani Rice + Garlic Naan</strong>Buy naan and spread garlic on it</p></li>', $listDecoSide);

$listMealAddButton = util_templateReturn('modules/button', null, ['id' => 'meal-add', 'text' => 'Add Meal']);
$listMealAddDeco = sprintf('<span class="deco-add">%s</span>', util_templateReturn('images/deco', 'square'));

$listLinkAccount = is_user_logged_in() ? 'Manage Data' : 'Log In';
$listLinks = sprintf('<nav class="list-nav"><a href="%s">%s</a></nav>', get_admin_url(), $listLinkAccount);

$listLogo = sprintf('<footer class="list-logo">%s</footer>', util_templateReturn('images/logo', 'text'));

printf(
    '<article><header>%s</header><ul id="meals">%s</ul>%s%s%s%s</article>',
    $listSeparator,
    implode('', [$listMealTemplate, $listSampleDataOne]),
    $listMealAddButton,
    $listMealAddDeco,
    $listLinks,
    $listLogo
);
?>