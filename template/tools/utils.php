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

// Pull values from a list of array indexes
function util_listPluck($idArray, $associativeArray, $sep = ', ') {
    if (!is_array($idArray) || !is_array($associativeArray) || empty($idArray)) {
        return '';
    }
    $result = array_map(function($id) use ($associativeArray) {
        return isset($associativeArray[$id]) ? $associativeArray[$id] : null;
    }, $idArray);
    $result = array_filter($result);

    return implode($sep, $result);
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

// Icon Display
function util_icon($icon) {
    $output = util_templateReturn('images/icons', null, ['icon' => $icon]);
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


// Minutes to Hours + Minutes Conversion
function util_hoursMins($totalMinutes) {
    $hours = floor($totalMinutes / 60);
    $minutes = $totalMinutes % 60;

    if ($hours == 0) {
        return sprintf("%d minutes", $minutes);
    }

    if ($minutes == 0) {
        return sprintf("%d hour%s", $hours, $hours > 1 ? 's' : '');
    }

    return sprintf("%d hour%s %d minute%s",
        $hours,
        $hours > 1 ? 's' : '',
        $minutes,
        $minutes > 1 ? 's' : ''
    );
}