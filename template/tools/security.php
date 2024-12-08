<?php if(!defined('WPINC')) { die; }

/*  SECURITY SETTINGS
	----------------------------- */

// Remove WP version from public view
remove_action('wp_head', 'wp_generator');

// Block Bad Queries
$request_uri = $_SERVER['REQUEST_URI'];
$query_string = $_SERVER['QUERY_STRING'];
if(empty($_SERVER['HTTP_USER_AGENT'])) {
	$user_agent = null;
} else {
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
}
if(
	stripos($request_uri, 'eval(') ||
	stripos($request_uri, 'CONCAT') ||
	stripos($request_uri, 'UNION+SELECT') ||
	stripos($request_uri, '(null)') ||
	stripos($request_uri, 'base64_') ||
	stripos($request_uri, '/localhost') ||
	stripos($request_uri, '/pingserver') ||
	stripos($request_uri, '/config.') ||
	stripos($request_uri, '/wwwroot') ||
	stripos($request_uri, '/makefile') ||
	stripos($request_uri, 'crossdomain.') ||
	stripos($request_uri, 'proc/self/environ') ||
	stripos($request_uri, 'etc/passwd') ||
	stripos($request_uri, '/https/') ||
	stripos($request_uri, '/http/') ||
	stripos($request_uri, '/ftp/') ||
	stripos($request_uri, '/cgi/') ||
	stripos($request_uri, '.cgi') ||
	stripos($request_uri, '.exe') ||
	stripos($request_uri, '.sql') ||
	stripos($request_uri, '.ini') ||
	stripos($request_uri, '.dll') ||
	stripos($request_uri, '.asp') ||
	stripos($request_uri, '.jsp') ||
	stripos($request_uri, '/.bash') ||
	stripos($request_uri, '/.git') ||
	stripos($request_uri, '/.svn') ||
	stripos($request_uri, '/.tar') ||
	stripos($request_uri, ' ') ||
	stripos($request_uri, '<') ||
	stripos($request_uri, '>') ||
	stripos($request_uri, '/=') ||
	stripos($request_uri, '...') ||
	stripos($request_uri, '+++') ||
	stripos($request_uri, '://') ||
	stripos($request_uri, '/&&') ||
	// query strings
	stripos($query_string, '?') ||
	stripos($query_string, ':') ||
	stripos($query_string, '[') ||
	stripos($query_string, ']') ||
	stripos($query_string, '../') ||
	stripos($query_string, '127.0.0.1') ||
	stripos($query_string, 'loopback') ||
	stripos($query_string, '%0A') ||
	stripos($query_string, '%0D') ||
	stripos($query_string, '%22') ||
	stripos($query_string, '%27') ||
	stripos($query_string, '%3C') ||
	stripos($query_string, '%3E') ||
	stripos($query_string, '%00') ||
	stripos($query_string, '%2e%2e') ||
	stripos($query_string, 'union') ||
	stripos($query_string, 'input_file') ||
	stripos($query_string, 'execute') ||
	stripos($query_string, 'mosconfig') ||
	stripos($query_string, 'environ') ||
	//stripos($query_string, 'scanner') ||
	stripos($query_string, 'path=.') ||
	stripos($query_string, 'mod=.') ||
	// user agents
	stripos($user_agent, 'binlar') ||
	stripos($user_agent, 'casper') ||
	stripos($user_agent, 'cmswor') ||
	stripos($user_agent, 'diavol') ||
	stripos($user_agent, 'dotbot') ||
	stripos($user_agent, 'finder') ||
	stripos($user_agent, 'flicky') ||
	stripos($user_agent, 'libwww') ||
	stripos($user_agent, 'nutch') ||
	stripos($user_agent, 'planet') ||
	stripos($user_agent, 'purebot') ||
	stripos($user_agent, 'pycurl') ||
	stripos($user_agent, 'skygrid') ||
	stripos($user_agent, 'sucker') ||
	stripos($user_agent, 'turnit') ||
	stripos($user_agent, 'vikspi') ||
	stripos($user_agent, 'zmeu')
) {
	@header('HTTP/1.1 403 Forbidden');
	@header('Status: 403 Forbidden');
	@header('Connection: Close');
	@exit;
}

// Obscure login screen error messages
function op_securityLoginObscure($error) {
    global $errors;
    $err_codes = $errors->get_error_codes();
    if ( in_array( 'invalid_username', $err_codes ) || in_array( 'incorrect_password', $err_codes ) ) {
        $error = '<strong>Sorry</strong>: username/email or password is incorrect.';
    }
    return $error;
}
add_filter('login_errors', 'op_securityLoginObscure');

// Disable self pingbacks
function op_securityNoSelfPing(&$links) {
	$home = home_url();
	foreach($links as $l => $link) {
		if(0 === strpos($link, $home)) {
			unset($links[$l]);
		}
	}
}
add_action('pre_ping', 'op_securityNoSelfPing');

// Remove xpingback header
function op_securityRemoveXPingback($headers) {
	unset($headers['X-Pingback']);
	return $headers;
}
add_filter('wp_headers', 'op_securityRemoveXPingback');

// Disable XMLRPC Class compeletely
add_filter('wp_xmlrpc_server_class', '__return_false');
add_filter('xmlrpc_enabled', '__return_false');

// Enable SVG & ICO upload in admin
function op_securityImageAllow($mimes = array()) {
	$mimes['svg']   = 'image/svg+xml';
    $mimes['svgz']  = 'image/svg+xml';
	$mimes['ico']   = 'image/vnd.microsoft.icon';
	return $mimes;
}
add_filter('upload_mimes', 'op_securityImageAllow');

// Allow Editors to edit Menus
$roleObject = get_role('editor');
if (!$roleObject->has_cap('edit_theme_options')) {
	$roleObject->add_cap('edit_theme_options');
}