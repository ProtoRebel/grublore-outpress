<?php
get_header();

// Global data stores for quick reference in mass displays later on.
$allCourses = $allCuisines =  $allIngredients = $allIngredientTypes = $allDishes = [];

// Pull the taxonomies into array
$taxAllNames = ['course', 'cuisine', 'ingredient-type'];

foreach ($taxAllNames as $taxName) {
    $taxTerms = get_terms([
        'taxonomy'      => $taxName,
        'hide_empty'    => false,
        'fields'        => 'id=>name'
    ]);
    if (!is_wp_error($taxTerms) && !empty($taxTerms)) {
        switch ($taxName) {
            case 'course':
                $allCourses = $taxTerms;
                break;
            case 'cuisine':
                $allCuisines = $taxTerms;
                break;
            case 'ingredient-type':
                $allIngredientTypes = $taxTerms;
                break;
        }
    }
}

// Pull all the ingredients into array
$allIngredientQueryArgs = [
    'post_type'         => 'ingredient',
    'posts_per_page'    => -1,
    'fields'            => 'id=>title'
];

$allIngredientQuery = new WP_Query($allIngredientQueryArgs);
if ($allIngredientQuery->have_posts()) {
    foreach ($allIngredientQuery->posts as $post) {
        $allIngredients[$post->ID] = $post->post_title;
    }
}
wp_reset_postdata();

// Pull all the dishes into array
$allDishQueryArgs = [
    'post_type'         => 'dish',
    'posts_per_page'    => -1,
    'fields'            => 'ids',
    'orderby'           => 'title',
    'order'             => 'ASC'
];

$allDishQuery = new WP_Query($allDishQueryArgs);

if ($allDishQuery->have_posts()) {
    foreach ($allDishQuery->posts as $dish) {
        get_template_part('modules/dish', null, array(
            'courses'       => $allCourses,
            'cuisines'      => $allCuisines,
            'ingredients'   => $allIngredients,
            'dish'          => $dish
        ));
    }
}

wp_reset_postdata();

get_footer();
?>
