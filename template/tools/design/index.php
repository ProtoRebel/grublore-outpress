<?php if(!defined('WPINC')) { die; }

/*  OUTPRESS DESIGN SETTINGS
	----------------------------- */

// Design Function
function op_design($component, $option) {
    $output = '';
    switch($component) {
        case 'color':
            $colors = get_field('design_colors', 'options');
            $colorGroup = '';
            foreach($colors as $pk => $pv) {
                foreach($pv as $ck => $cv) {
                    if($ck === $option) {
                        $colorGroup = $pk;
                        break 2;
                    }
                }
            }
            $output = $colors[$colorGroup][$option];
            break;
    }
    return $output;
}

// NOTE: Disabled until the full design colors are implemented

// Inject Theme Color Meta
//function op_designThemeColor() {
//    $output = sprintf('<meta name="theme-color" content="%s" />', op_design('color', 'primary'));
//    echo $output;
//}
//add_action('wp_head', 'op_designThemeColor');


// TODO: When adding the global :root vars, ensure to load them onto the admin panel also