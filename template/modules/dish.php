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

// Calculate total time
$totalTime = intval($dishPrep['time']['active']) + intval($dishPrep['time']['idle']);
$prepTime = intval($dishPrep['time']['active']);
$cookTime = intval($dishPrep['time']['idle']);

// Build structured data array
$recipeSchema = array(
    '@context' => 'https://schema.org/',
    '@type' => 'Recipe',
    'name' => $dishTitle,
    'image' => $dishPhoto,
    'author' => array(
        '@type' => 'Organization',
        'name' => op_business('name')
    ),
    'datePublished' => get_the_date('Y-m-d', $d),
    'description' => $dishMeta['description'],
    'prepTime' => "PT{$prepTime}M",
    'cookTime' => "PT{$cookTime}M",
    'totalTime' => "PT{$totalTime}M",
    'recipeYield' => $dishMeta['servings']['amount'] . ' ' . $dishMeta['servings']['description'],
    'recipeCategory' => util_listPluck($dishCourses, $courses),
    'recipeCuisine' => util_listPluck($dishCuisines, $cuisines)
);

// Add ingredients to schema
if (!empty($dishIngredients)) {
    $recipeSchema['recipeIngredient'] = array();
    foreach ($dishIngredients as $ingredient) {
        if ($ingredient['acf_fc_layout'] === 'ingredient') {
            $amount = $ingredient['amount']['whole'] . $ingredient['amount']['fraction'] . ' ' . $ingredient['amount']['unit'];
            $prep = !empty($ingredient['notes']['prep']) ? ', ' . $ingredient['notes']['prep'] : '';
            $recipeSchema['recipeIngredient'][] = trim($amount . ' ' . get_the_title($ingredient['ingredient']) . $prep);
        }
    }
}

// Add instructions to schema
if (!empty($dishSteps)) {
    $recipeSchema['recipeInstructions'] = array();
    foreach ($dishSteps as $step) {
        if (!empty($step['direction'])) {
            $instruction = array(
                '@type' => 'HowToStep',
                'text' => $step['direction']
            );
            if (!empty($step['goal'])) {
                $instruction['text'] .= ' (' . $step['goal'] . ')';
            }
            $recipeSchema['recipeInstructions'][] = $instruction;
        }
    }
}

// Output JSON-LD structured data
printf('<script type="application/ld+json">%s</script>', json_encode($recipeSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Start HTML output
printf('<article id="%s" class="dish" itemscope itemtype="https://schema.org/Recipe">', $d);

// Dish Header Section
$dishClose = sprintf('<a href="#" class="layer-close">%s</a>', util_icon('close'));
$dishDisplayHeader = sprintf(
    '<header class="layer-heading"><h1 itemprop="name">%s</h1>%s</header>',
    $dishTitle,
    $dishClose
);

// Dish Details Section
$dishDetailSep = ' <span class="bullet">&bull;</span> ';
$dishCoursesList = sprintf('<span class="course" itemprop="recipeCategory">%s</span>', util_listPluck($dishCourses, $courses));
$dishCuisineList = sprintf('<span itemprop="recipeCuisine">%s</span>', util_listPluck($dishCuisines, $cuisines));
$dishCourseMeta = sprintf('<em>%s</em>', implode($dishDetailSep, [$dishCoursesList, $dishCuisineList]));

$dishServingsDesc = $dishMeta['servings']['description'] ? $dishDetailSep . $dishMeta['servings']['description'] : '';
$dishServings = sprintf(
    '%s<em><strong itemprop="recipeYield">%s Servings</strong>%s</em>',
    util_icon('dishes'),
    $dishMeta['servings']['amount'],
    $dishServingsDesc
);

$dishPrepTimeTotal = intval($dishPrep['time']['active']) + intval($dishPrep['time']['idle']);
$dishPrepTimeIdle = $dishPrep['time']['idle'] > 0 ? $dishDetailSep . util_hoursMins($dishPrep['time']['idle']) . ' idle' : '';
$dishPrepTime = $dishPrepTimeTotal > 0 ? sprintf(
    '%s<em><strong><meta itemprop="totalTime" content="PT%dM">%s</strong>%s</em>',
    util_icon('timer'),
    $dishPrepTimeTotal,
    util_hoursMins($dishPrepTimeTotal),
    $dishPrepTimeIdle
) : null;

$dishLink = !empty($dishMeta['recipe_source']['url']) ? sprintf(
    '%s<a href="%s" target="_blank">%s &rarr;</a>',
    util_icon('link'),
    $dishMeta['recipe_source']['url'],
    $dishMeta['recipe_source']['title']
) : '';

$dishDifficultyAttention = $dishPrep['difficulty']['attention'] > 0 ? sprintf(
    '<em>Attention: <strong>%s/10</strong></em>',
    $dishPrep['difficulty']['attention']
) : '';
$dishDifficultyTechnique = $dishPrep['difficulty']['technique'] > 0 ? sprintf(
    '<em>Technique: <strong>%s/10</strong></em>',
    $dishPrep['difficulty']['technique']
) : '';
$dishDifficultySep = !empty($dishDifficultyAttention && $dishDifficultyTechnique) ? sprintf('&nbsp;%s&nbsp;', $dishDetailSep) : '';
$dishDifficulty = !empty($dishDifficultyAttention || $dishDifficultyTechnique) ? util_icon('bolt') . $dishDifficultyAttention . $dishDifficultySep . $dishDifficultyTechnique : '';

$dishPrepTools = '';
if(!empty($dishPrep['special_equipment'])) {
    $dishPrepToolsList = [];
    foreach($dishPrep['special_equipment'] as $dishPrepTool) {
        $dishPrepToolsList[] = $dishPrepTool['item'];
    }
    $dishPrepTools = sprintf('%s<em>%s</em>', util_icon('tools'), implode(', ', $dishPrepToolsList));
}

$dishDescription = !empty($dishMeta['description']) ? sprintf(
    '%s<em itemprop="description">%s</em>',
    util_icon('quote'),
    $dishMeta['description']
) : '';

$dishGoesWith = '';
if(!empty($dishGoes)) {
    $dishGoesWithList = [];
    foreach($dishGoes as $dishGoesDish) {
        $dishGoesWithList[] = sprintf(
            '<a href="#%s" class="dish-link">%s</a>',
            $dishGoesDish,
            get_the_title($dishGoesDish)
        );
    }
    $dishGoesWith = sprintf('%s<em>Goes With: %s</em>', util_icon('heart'), implode(', ', $dishGoesWithList));
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

// Photo and details section
$dishDisplayDetails = sprintf(
    '<section><div class="photo" itemprop="image" content="%s">%s</div>%s</section>',
    get_the_post_thumbnail($d, 'medium'),
    $dishPhoto,
    $dishDetailsList
);

// Dish Ingredients Section
$dishDisplayIngredients = '';
if(!empty($dishIngredients)) {
    $dishIngredientsList = [];
    foreach($dishIngredients as $dishIngredient) {
        if($dishIngredient['acf_fc_layout'] === 'ingredient') {
            $dishIngredientAmount = sprintf(
                '<strong>%s%s</strong><em>%s</em>',
                $dishIngredient['amount']['whole'],
                $dishIngredient['amount']['fraction'],
                $dishIngredient['amount']['unit']
            );
            $dishIngredientPrep = !empty($dishIngredient['notes']['prep']) ? sprintf(', %s', $dishIngredient['notes']['prep']) : '';
            $dishIngredientName = sprintf(
                '<strong>%s%s</strong><em>%s</em>',
                get_the_title($dishIngredient['ingredient']),
                $dishIngredientPrep,
                $dishIngredient['notes']['note']
            );
            $dishIngredientsList[] = sprintf(
                '<li class="ingredient" data-ingredient="%s" itemprop="recipeIngredient"><div>%s</div><p>%s</p></li>',
                $dishIngredient['ingredient'],
                $dishIngredientAmount,
                $dishIngredientName
            );
        } elseif($dishIngredient['acf_fc_layout'] === 'section') {
            $dishIngredientSectionTitle = empty($dishIngredient['title']) ? '' : sprintf('<h3>%s</h3>', $dishIngredient['title']);
            $dishIngredientSectionNote = empty($dishIngredient['note']) ? '' : sprintf('<p>%s</p>', $dishIngredient['note']);
            $dishIngredientsList[] = sprintf(
                '<li class="section">%s%s</li>',
                $dishIngredientSectionTitle,
                $dishIngredientSectionNote
            );
        }
    }
    $dishDisplayIngredients = sprintf(
        '<div class="ingredient-sep sep">%s</div><ul class="ingredients">%s</ul>',
        $dishSectionSep,
        implode('', $dishIngredientsList)
    );
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
                $dishStepsList[] = sprintf(
                    '<li itemprop="recipeInstructions" itemscope itemtype="https://schema.org/HowToStep">%s<p itemprop="text">%s%s%s</p></li>',
                    $dishDisplayStepText,
                    $dishStepDirection,
                    $dishDetailSep,
                    $dishStepGoal
                );
            } else {
                $dishStepsList[] = sprintf(
                    '<li itemprop="recipeInstructions" itemscope itemtype="https://schema.org/HowToStep">%s<p itemprop="text">%s%s</p></li>',
                    $dishDisplayStepText,
                    $dishStepDirection,
                    $dishStepGoal
                );
            }
        }
    }
    $dishDisplaySteps = sprintf(
        '<div class="steps-sep sep">%s</div><ol class="steps">%s</ol>',
        $dishSectionSep,
        implode('', $dishStepsList)
    );
}

// Dish Notes Section
$dishDisplayNotes = '';
if(!empty($dishNotes)) {
    $dishNotesList = [];
    foreach($dishNotes as $dishNote) {
        $dishNotesList[] = sprintf(
            '<p itemprop="notes">%s%s</p>',
            util_icon('quote'),
            $dishNote['note']
        );
    }
    $dishDisplayNotes = sprintf('<footer class="notes">%s</footer>', implode('', $dishNotesList));
}

// Final Output
printf(
    '%s<div class="data">%s%s%s%s</div></article>',
    $dishDisplayHeader,
    $dishDisplayDetails,
    $dishDisplayIngredients,
    $dishDisplaySteps,
    $dishDisplayNotes
);