<?php if(!defined('WPINC')) { die; }

/*  SHARED UTILITIES
	----------------------------- */

// Format Phone Numbers
function util_phone($number) {
	$sepA = '(';
	$sepB = ') ';
	$sepC = '-';

	$number = str_replace([' ', '(', ')', '-'], '', $number);

	if(ctype_digit($number) && strlen($number) === 10) {
		$number = $sepA . substr($number, 0, 3) . $sepB . substr($number, 3, 3) . $sepC . substr($number, 6);
	} elseif(ctype_digit($number) && strlen($number) === 7) {
		$number = substr($number, 0, 3) . $sepC . substr($number, 3, 4);
	}
	return $number;
}

// Return get_template_part() instead of echo
function util_templateReturn($template_name, $part_name = null, $args = null) {
	ob_start();
	get_template_part($template_name, $part_name, $args);
	$var = ob_get_contents();
	ob_end_clean();
	return $var;
}

// Random String Generator
function util_randomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for($i = 0; $i < $length; $i++) {
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}
	return $randomString;
}

// Grab an image from the theme stylesheet directory
function util_themeImage($image, $echo = true) {
	$output = get_template_directory_uri() . '/images/' . $image;
	if($echo) {
		echo $output;
	} else {
		return $output;
	}
}

// Search Check
function util_searchHasResults() {
	return 0 != $GLOBALS['wp_query']->found_posts;
}

// String Linting
function util_stringLint($string) {
    return strtolower(str_replace(' ', '-', $string));
}

// Wrapper Creator
function util_wrapper($type, $content, $classes = array(), $id = '', $attrs = '') {
    $classList = implode(' ', $classes);
    $id_clean = $id ? util_stringLint($id) : util_randomString(6);
    $output = '';
    if($type === 'outer') {
        $output = sprintf('<section id="%s" class="%s" style="%s">%s</section>', $id_clean, $classList, $attrs, $content);
    } elseif($type === 'inner') {
        $output = sprintf('<div class="container %s">%s</div>', $classList, $content);
    }
    return $output;
}

// Icon Display
function util_icon($icon) {
    $output = '<span class="icon icon-' . $icon . '"></span>';
    return $output;
}

// Time Remaining
function util_timeAway($timestamp) {
    $periods = array(
        "second",
        "minute",
        "hour",
        "day",
        "week",
        "month",
        "year"
    );
    $lengths = array(
        "60",
        "60",
        "24",
        "7",
        "4.35",
        "12"
    );
    $current_timestamp = time();
    $difference = abs($current_timestamp - $timestamp);
    for($i = 0; $difference >= $lengths[$i] && $i < count($lengths) - 1; $i ++) {
        $difference /= $lengths[$i];
    }
    $difference = round($difference);
    if(isset($difference)) {
        if($difference != 1) {
            $periods[$i] .= "s";
        }
        $output = "$difference $periods[$i]";
        return $output;
    }
}
