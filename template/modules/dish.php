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
$dishSectionSep = util_templateReturn('images/deco', 'horizontal');


// Dish Header Section
$dishClose = sprintf('<a href="#" class="layer-close">%s</a>', util_icon('close'));
$dishDisplayHeader = sprintf('<header class="layer-heading"><h1>%s</h1>%s</header>', $dishTitle, $dishClose);

// Dish Details Section
$dishDetailSep = ' <span class="bullet">&bull;</span> ';
$dishCoursesList = sprintf('<span class="course">%s</span>', util_listPluck($dishCourses, $courses));
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
        $dishGoesWithList[] = sprintf('<a href="#%s" class="dish-link">%s</a>', $dishGoesDish, get_the_title($dishGoesDish));
    }
    $dishGoesWith = sprintf('%s<em>Goes With: %s</em>', util_icon('heart-add'), implode(', ', $dishGoesWithList));
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
if(!empty($dishIngredients)) {
    $dishIngredientsList = [];
    foreach($dishIngredients as $dishIngredient) {
        $dishIngredientAmount = sprintf('<strong>%s%s</strong><em>%s</em>', $dishIngredient['amount']['whole'], $dishIngredient['amount']['fraction'], $dishIngredient['amount']['unit']);
        $dishIngredientPrep = !empty($dishIngredient['notes']['prep']) ? sprintf(', %s', $dishIngredient['notes']['prep']) : '';
        $dishIngredientName = sprintf('<strong>%s%s</strong><em>%s</em>', get_the_title($dishIngredient['ingredient']), $dishIngredientPrep, $dishIngredient['notes']['note']);
        $dishIngredientsList[] = sprintf('<li data-ingredient="%s"><div>%s</div><p>%s</p></li>', $dishIngredient['ingredient'], $dishIngredientAmount, $dishIngredientName);
    }
    $dishDisplayIngredients = sprintf('<div class="ingredient-sep sep">%s</div><ul class="ingredients">%s</ul>', $dishSectionSep, implode('', $dishIngredientsList));
}

// Dish Steps Section
$dishDisplaySteps = '';
$dishDisplayStepNumber = 0;
if(!empty($dishSteps)) {
    $dishStepsList = [];
    foreach($dishSteps as $dishStep) {
        $dishDisplayStepNumber++;
        $dishDisplayStepText = sprintf('<span>Step<em>%s</em></span>', $dishDisplayStepNumber);
        if(!empty($dishStep['direction']) || !empty($dishStep['goal'])) {
            $dishStepDirection = $dishStep['direction'];
            $dishStepGoal = !empty($dishStep['goal']) ? sprintf('<strong>%s</strong>', $dishStep['goal']) : '';
            if(!empty($dishStep['direction']) && !empty($dishStep['goal'])) {
                $dishStepsList[] = sprintf('<li>%s<p>%s%s%s</p></li>', $dishDisplayStepText, $dishStepDirection, $dishDetailSep, $dishStepGoal);
            } else {
                $dishStepsList[] = sprintf('<li>%s<p>%s%s</p></li>', $dishDisplayStepText, $dishStepDirection, $dishStepGoal);
            }
        }
    }
    $dishDisplaySteps = sprintf('<div class="steps-sep sep">%s</div><ol class="steps">%s</ol>', $dishSectionSep, implode('', $dishStepsList));
}

// Dish Notes Section
$dishDisplayNotes = '';
if(!empty($dishNotes)) {
    $dishNotesList = [];
    foreach($dishNotes as $dishNote) {
        $dishNotesList[] = sprintf('<p>%s</p>', $dishNote['note']);
    }

    $dishDisplayNotes = sprintf('<footer class="notes">%s</footer>', implode('', $dishNotesList));
}

// Final Dish Output
printf('<article id="%s" class="dish">%s<div class="data">%s%s%s%s</div></article>', $d, $dishDisplayHeader, $dishDisplayDetails, $dishDisplayIngredients, $dishDisplaySteps, $dishDisplayNotes);