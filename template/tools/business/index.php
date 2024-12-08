<?php if(!defined('WPINC')) { die; }

/*  BUSINESS INFORMATION
	----------------------------- */

require_once('constructor.php');
require_once('shortcodes.php');

// Inject Business Logo Favicon
function op_businessFavicon() {
    $favicon = get_field('business_logo_favicon', 'options');
    $faviconMime = get_post_mime_type($favicon);
    $faviconUrl = wp_get_attachment_image_url($favicon);
    printf('<link rel="icon" type="%s" href="%s">', $faviconMime, $faviconUrl);
}
add_action('wp_head', 'op_businessFavicon');