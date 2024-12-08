<?php if(!defined('WPINC')) { die; }

/*  GUT & CLEAN UP WP
	----------------------------- */

global $op_controller;
$op_cleaner = $op_controller['cleaner'];

// Remove RSS Version
function op_cleanRssVersion() {
    global $op_cleaner;
    if(in_array('rss-version', $op_cleaner['data'])) {
        return '';
    }
}

// Remove Extra Stylesheets & Scripts
function op_cleanStylesScripts() {
    global $op_cleaner;
    if(in_array('global-styles', $op_cleaner['styles'])) { wp_dequeue_style('global-styles'); }
    if(in_array('classic-theme-styles', $op_cleaner['styles'])) { wp_dequeue_style('classic-theme-styles'); }
    if(in_array('wp-block-library', $op_cleaner['styles'])) { wp_dequeue_style('wp-block-library'); }
    if(in_array('wp-block-library-theme', $op_cleaner['styles'])) { wp_dequeue_style('wp-block-library-theme'); }
}

// Remove injected CSS for recent comments
function op_cleanWidgetCommentStyle() {
    global $op_cleaner;
    if (has_filter('wp_head', 'wp_widget_recent_comments_style') && in_array('recent-comments', $op_cleaner['styles'])) {
        remove_filter('wp_head', 'wp_widget_recent_comments_style');
    }
}

// Remove injected CSS from recent comments widget
function op_cleanWidgetCommentRecents() {
    global $op_cleaner;
    if(in_array('recent-comments', $op_cleaner['styles'])) {
        global $wp_widget_factory;
        if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
            remove_action('wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
        }
    }
}

// Remove injected CSS from gallery
function op_cleanGalleryStyles($css) {
    global $op_cleaner;
    if(in_array('gallery', $op_cleaner['styles'])) {
        return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
    }
}

// Remove the <p> tag from around images
function op_cleanRemovePTagsOnImages($content) {
    global $op_cleaner;
    if(in_array('p-wrap', $op_cleaner['data'])) {
        return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
    }
}

// Remove WP version from scripts
function op_cleanRemoveWpVersionFromScriptsStyles($src) {
    global $op_cleaner;
    if(in_array('scripts-wp-version', $op_cleaner['data']) && strpos($src, 'ver=')){
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

// Remove default WP garbage in heading output
function op_cleanWpDefaults() {
    global $op_cleaner;
    if(in_array('emojis', $op_cleaner['styles'])) {
        remove_action('wp_head', 'print_emoji_detection_script', 7); // remove injected CSS for Emojis
        remove_action('wp_print_styles', 'print_emoji_styles'); // remove injected CSS for Emojis
    }
    if(in_array('feed-links', $op_cleaner['data'])) {
        remove_action('wp_head', 'feed_links_extra', 3); // category feeds
        remove_action('wp_head', 'feed_links', 2); // post and comment feeds
        remove_action('wp_head', 'rsd_link'); // remove RSD link
    }
    if(in_array('rest-links', $op_cleaner['data'])) {
        remove_action('wp_head', 'rest_output_link_wp_head'); // Remove the REST API lines from the HTML Header
    }
    if(in_array('oembed-discovery', $op_cleaner['data'])) {
        remove_action('wp_head', 'wp_oembed_add_discovery_links'); // Remove the REST API lines from the HTML Header
    }
    if(in_array('post-family', $op_cleaner['data'])) {
        remove_action('wp_head', 'parent_post_rel_link'); // previous link
        remove_action('wp_head', 'start_post_rel_link'); // start link
        remove_action('wp_head', 'index_rel_link'); // remove link to index page
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head'); // links for adjacent posts
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    }
    if(in_array('windows-livewriter', $op_cleaner['data'])) {
        remove_action('wp_head', 'wlwmanifest_link' ); // windows live writer
    }

    add_filter('the_generator', 'op_cleanRssVersion'); // remove version from RSS
    add_filter('wp_head', 'op_cleanWidgetCommentStyle', 1 ); // remove injected css for recent comments widget
    add_action('wp_head', 'op_cleanWidgetCommentRecents', 1); // clean up comment styles in the head
    add_filter('gallery_style', 'op_cleanGalleryStyles'); // clean up gallery output
    add_filter('the_content', 'op_cleanRemovePTagsOnImages'); // cleaning up random code around images
}

// Fire upon WP initiation
function op_configInit() {
    op_cleanWpDefaults();
}
add_action('init', 'op_configInit');

// Clear the 1x1 width/height on SVG's in get_attachment_image operations
function op_cleanRemoveSinglePixelOnSvg($image, $attachment_id, $size, $icon) {
    if (is_array($image) && preg_match('/\.svg$/i', $image[0]) && $image[1] <= 1) {
        if(is_array($size)) {
            $image[1] = $size[0];
            $image[2] = $size[1];
        } elseif(($xml = simplexml_load_file($image[0])) !== false) {
            $attr = $xml->attributes();
            $viewbox = explode(' ', $attr->viewBox);
            $image[1] = isset($attr->width) && preg_match('/\d+/', $attr->width, $value) ? (int) $value[0] : (count($viewbox) == 4 ? (int) $viewbox[2] : null);
            $image[2] = isset($attr->height) && preg_match('/\d+/', $attr->height, $value) ? (int) $value[0] : (count($viewbox) == 4 ? (int) $viewbox[3] : null);
        } else {
            $image[1] = $image[2] = null;
        }
    }
    return $image;
}
add_filter('wp_get_attachment_image_src', 'op_cleanRemoveSinglePixelOnSvg', 10, 4);
