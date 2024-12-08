<?php if(!defined('WPINC')) { die; }

/*  THEME CONFIGURATION
	----------------------------- */

$manifest = json_decode(file_get_contents(__DIR__ . '/../build/assets.json', true));
$main = $manifest->main;

// Register the built out scripts
function op_themeScriptsStyles() {
    $manifest = json_decode(file_get_contents(__DIR__ . '/../build/assets.json', true));
    $main = $manifest->main;
    wp_enqueue_style('outpress-style', get_template_directory_uri() . '/build/' . $main->css,  false, null);
    wp_enqueue_script('outpress-script', get_template_directory_uri() . '/build/' . $main->js, ['jquery'], null, true);
    op_cleanStylesScripts();
}
add_action('wp_enqueue_scripts', 'op_themeScriptsStyles', 100);

// Adding WP 3+ Functions & Theme Support
function op_themeSupport() {
    add_theme_support('automatic-feed-links'); // RSS for all post types
    add_theme_support('title-tag'); // Adding Post title to Title Tag
    add_theme_support('post-thumbnails'); // Enable featured image
    add_theme_support('menus'); // Enable WP Menus
    add_theme_support('html5', array(
        'comment-list',
        'search-form',
        'comment-form',
        'caption',
        'gallery',
        'style',
        'script'
    ));
}

// Limit post revisions to 5
if(!defined('WP_POST_REVISIONS')) define('WP_POST_REVISIONS',5);

// Fire after theme setup
function op_configAfterSetup() {
    op_themeSupport();
    add_action('wp_default_scripts', 'op_cleanJqueryMigrate');
}
add_action('after_setup_theme', 'op_configAfterSetup');

// Remove jQuery + jQuery Migrate
function op_cleanJqueryMigrate($scripts) {
    global $op_cleaner;
    if(is_admin()) return;
    $disableJquery = in_array('jquery', $op_cleaner['scripts']);
    $disableJqueryMigrate = in_array('jquery-migrate', $op_cleaner['scripts']);
    if($disableJquery) {
        $scripts->remove('jquery');
        $scripts->add('jquery', false, array(), '1.10.2');
    } elseif(!$disableJquery && $disableJqueryMigrate) {
        $scripts->remove('jquery');
        $scripts->add('jquery', false, array('jquery-core'), '1.10.2');
    }
}

// Add page slug to body class
function op_configBodyAddClass($classes) {
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }
    return $classes;
}
add_filter('body_class', 'op_configBodyAddClass');

// Admin footer attribution
function op_configAdminAttribution() {
    echo '<span id="footer-thankyou">Outpress by <a href="https://domesticjones.com/" target="_blank">Domestic Jones</a></span>';
}
add_filter('admin_footer_text', 'op_configAdminAttribution');


/**
 * This fixes the wordpress rest-api so we can just lookup pages by their full
 * path (not just their name). This allows us to use React Router.
 *
 * @return WP_Error|WP_REST_Response
 */
add_action('rest_api_init', function () {
    $namespace = 'outpress/v1';
    register_rest_route( $namespace, '/path/(?P<url>.*?)', array(
        'methods'  => 'GET',
        'callback' => 'get_post_for_url',
    ));
});
function get_post_for_url($data) {
    $postId    = url_to_postid($data['url']);
    $postType  = get_post_type($postId);
    $controller = new WP_REST_Posts_Controller($postType);
    $request    = new WP_REST_Request('GET', "/wp/v2/{$postType}s/{$postId}");
    $request->set_url_params(array('id' => $postId));
    return $controller->get_item($request);
}
