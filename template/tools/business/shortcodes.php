<?php if(!defined('WPINC')) { die; }

/*  Shortcodes to display business info
	----------------------------- */

// Master shortcode
function op_businessShortcodes($atts) {
    $info = $atts[0];
    if(($attsFind = array_search($info, $atts)) !== false) {
        unset($atts[$attsFind]);
    }
    $attsOptions = array_values($atts);
    $output = op_business($info, $attsOptions);
    return $output;
}
add_shortcode('business', 'op_businessShortcodes');