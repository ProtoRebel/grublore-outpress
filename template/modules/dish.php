<?php
// Imported Vars
$d = $args['dish'];
$courses = $args['courses'];
$cuisines = $args['cuisines'];
$ingredients = $args['ingredients'];

// Dish data
$dishTitle = get_the_title($d);
$dishGoes = get_field('goes_with', $d);
$dishMeta = get_field('meta', $d);
$dishPrep = get_field('preparation', $d);
$dishIngredients = get_field('ingredients', $d);
$dishSteps = get_field('steps', $d);
$dishNotes = get_field('eating_notes', $d);
$dishCourses = wp_list_pluck(get_the_terms($d, 'course'), 'term_id');
$dishCuisines = wp_list_pluck(get_the_terms($d, 'cuisine'), 'term_id');
$dishPhoto = has_post_thumbnail($d) ? get_the_post_thumbnail($d, 'medium') : util_templateReturn('images/logo', 'emblem');

// Dish Header Section
$dishClose = sprintf('<a href="#" class="close-dish">%s</a>', util_icon('close'));
$dishDisplayHeader = sprintf('<header><h1>%s</h1>%s</header>', $dishTitle, $dishClose);

// Dish Details Section
$dishDetailSep = ' <span class="bullet">&bull;</span> ';
$dishCoursesList = util_listPluck($dishCourses, $courses);
$dishCuisineList = util_listPluck($dishCuisines, $cuisines);
$dishCourseMeta = sprintf('<em>%s</em>', implode($dishDetailSep, [$dishCoursesList,$dishCuisineList]));

$dishServingsDesc = $dishMeta['servings']['description'] ? $dishDetailSep . $dishMeta['servings']['description'] : '';
$dishServings = sprintf('%s<em><strong>%s Servings</strong>%s</em>', util_icon('dishes'), $dishMeta['servings']['amount'], $dishServingsDesc);

$dishPrepTimeTotal = intval($dishPrep['time']['active']) + intval($dishPrep['time']['idle']);
$dishPrepTimeIdle = $dishPrep['time']['idle'] > 0 ? $dishDetailSep . util_hoursMins($dishPrep['time']['idle']) . ' idle' : '';
$dishPrepTime = $dishPrepTimeTotal > 0 ? sprintf('%s<em><strong>%s</strong>%s</em>', util_icon('timer'), util_hoursMins($dishPrepTimeTotal), $dishPrepTimeIdle) : null;

$dishLink = !empty($dishMeta['recipe_source']['url']) ? sprintf('%s<a href="%s" target="_blank">%s</a>', util_icon('link'), $dishMeta['recipe_source']['url'], $dishMeta['recipe_source']['title']) : '';

$dishDifficulty = sprintf('%s<em>Attention: <strong>%s/10</strong>%sTechnique: <strong>%s/10</strong></em>', util_icon('bolt'), $dishPrep['difficulty']['attention'], $dishDetailSep, $dishPrep['difficulty']['technique']);

$dishPrepTools = '';
if(!empty($dishPrep['special_equipment'])) {
    $dishPrepToolsList = [];
    foreach($dishPrep['special_equipment'] as $dishPrepTool) {
        $dishPrepToolsList[] = $dishPrepTool['item'];
    }
    $dishPrepTools = sprintf('%s<em>%s</em>', util_icon('tools'), implode(', ', $dishPrepToolsList));
}

$dishDescription = !empty($dishMeta['description']) ? sprintf('%s<em>%s</em>', util_icon('quote'), $dishMeta['description']) : '';

$dishGoesWith = '';
if(!empty($dishGoes)) {
    $dishGoesWithList = [];
    foreach($dishGoes as $dishGoesDish) {
        $dishGoesWithList[] = sprintf('<a href="#%s" class="link-dish">%s</a>', $dishGoesDish, get_the_title($dishGoesDish));
    }
    $dishGoesWith = sprintf('%s<em>Goes With: %s', util_icon('heart-add'), implode(', ', $dishGoesWithList));
}

$dishDetailsListItems = array_filter([
    $dishCourseMeta,
    $dishServings,
    $dishPrepTime,
    $dishLink,
    $dishDifficulty,
    $dishPrepTools,
    $dishDescription,
    $dishGoesWith
]);
$dishDetailsList = '';

if(!empty($dishDetailsListItems)) {
    $dishDetailsList = '<ul class="details">';
    foreach($dishDetailsListItems as $dishDetailsListItem) {
        $dishDetailsList .= sprintf('<li>%s</li>', $dishDetailsListItem);
    }
    $dishDetailsList .= '</ul>';
}
$dishDisplayDetails = sprintf('<section><div class="photo">%s</div>%s</section>', $dishPhoto, $dishDetailsList);

// Dish Ingredients Section
$dishDisplayIngredients = '';

// Dish Steps Section
$dishDisplaySteps = '';

// Dish Notes Section
$dishDisplayNotes = '';

// Final Dish Output
printf('<article id="%s" class="dish">%s%s%s%s%s</article>', $d, $dishDisplayHeader, $dishDisplayDetails, $dishDisplayIngredients, $dishDisplaySteps, $dishDisplayNotes);