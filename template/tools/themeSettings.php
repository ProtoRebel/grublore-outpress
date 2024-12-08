<?php if(!defined('WPINC')) { die; }

/*  CUSTOMIZABLE THEME SETTINGS
	----------------------------- */

// Remove pages from the admin menu
function op_settingsAdminMenu() {
    remove_menu_page('edit.php'); // Posts
    remove_menu_page('link-manager.php'); // Links
}
add_action('admin_init', 'op_settingsAdminMenu');

// Define the nav menus
register_nav_menus(
    array(
        'primary'   => __('Primary', 'outpress'),
        'legal'     => __('Legal', 'outpress')
    )
);

if (!isset($content_width)) {
    $content_width = 1520;
}

// Overwrite Existing Image Options
function op_settingsImagesResizeDefaults() {
    update_option('thumbnail_size_w', 200);
    update_option('thumbnail_size_h', 200);
    update_option('thumbnail_crop', 1);
    update_option('medium_size_w', 800);
    update_option('medium_size_h', 800);
    update_option('large_size_w', 1200);
    update_option('large_size_h', 1200);
}
add_action('admin_init', 'op_settingsImagesResizeDefaults', 1);

// Define custom image sizes
if (function_exists('add_image_size')) {
    add_image_size('small', 400, 400);
    add_image_size('jumbo', 2000, 2000);
    add_image_size('thumbnail', 200, 200, true);
    add_image_size('thumbnail-medium', 400, 400, true);
    add_image_size('thumbnail-large', 800, 800, true);
}

// Name the different sizes
function op_settingsImagesCustomNames($sizes) {
    return array_merge($sizes, array(
        'thumbnail'         => ('Thumbnail - Small'),
        'thumbnail-medium'  => ('Thumbnail - Medium'),
        'thumbnail-large'   => ('Thumbnail - Large'),
        'jumbo'             => ('Jumbo')
    ));
}
add_filter('image_size_names_choose', 'op_settingsImagesCustomNames');