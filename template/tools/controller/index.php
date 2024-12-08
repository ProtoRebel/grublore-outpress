<?php if(!defined('WPINC')) { die; }

/*  OUTPRESS TOOLBOX CONTROLS
	----------------------------- */
$op_controller = !empty(get_field('controller', 'options')) ? get_field('controller', 'options') : null;


// Choose the environment type then load options to optimize the environment
function op_controllerEnvironment() {
    $env = get_field('environment', 'options');
    if($env) {
        define('WP_ENVIRONMENT_TYPE', $env);
        if(wp_get_environment_type() === 'production') {
            update_option('blog_public', 1);
            add_filter('style_loader_src', 'op_cleanRemoveWpVersionFromScriptsStyles', 9999); // remove WP version from css
            add_filter('script_loader_src', 'op_cleanRemoveWpVersionFromScriptsStyles', 9999); // remove WP version from scripts
        } else {
            update_option('blog_public', 0);
        }
    }
}
add_action('init', 'op_controllerEnvironment', 1);

// Toggle WP Admin Bar
show_admin_bar($op_controller['admin_bar']);

// Toggle Gutenberg
if(!$op_controller['gutenberg']) {
    add_filter( 'use_block_editor_for_post', '__return_false');
    add_filter( 'use_widgets_block_editor', '__return_false');
}

// Remove unnecessary user data

function op_cleanRemoveUserFields($fields) {
    global $op_controller;
    $userFields = explode(',', str_replace(' ', '', $op_controller['user_fields']));
    foreach($userFields as $f) {
        unset($fields[$f]);
    }
    return $fields;
}
add_filter('user_contactmethods','op_cleanRemoveUserFields');

require_once('cleaner.php');
require_once('injection.php');
require_once('redirects.php');
require_once('maintenance.php');
require_once('comments.php');