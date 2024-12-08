<?php if(!defined('WPINC')) { die; }

/*  GLOBAL URL REDIRECTS
	----------------------------- */

// Redirect old URL's to new ones
function op_controllerRedirects() {
	$redirects = get_field('redirects', 'options');
	if(!empty($redirects)) {
		foreach($redirects as $r) {
			$code = $r['code'];
			$old = '';
			$new = '';
			if($r['type'] == 'Specific URL') {
				$old = parse_url($r['old_url'], PHP_URL_PATH);
				$new = $r['new_url'];
			} elseif($r['type'] == 'URL Range') {
				// TODO: Create function to find wildcards in strings and replace them
				$old = 'unsupported';
				$new = 'unsupported';
			}
			if($_SERVER['REQUEST_URI'] == $old) {
				wp_redirect($new, $code);
				exit;
			}
		}
	}
}
add_action('init', 'op_controllerRedirects', 1);