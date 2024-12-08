<?php if(!defined('WPINC')) { die; }

/*  THEME NERVE CENTER DIRECTORY
	----------------------------- */

require_once('tools/security.php');
require_once('tools/themeConfig.php');
require_once('tools/themeSettings.php');

function op_errorAcf() {
    printf('<div class="notice notice-error"><p><strong>WHOA!</strong> Advanced Custom Fields is required with Outpress.</p></div>');
}
function op_errorController() {
    printf('<div class="notice notice-warning"><p>Could not find controller configuration. Please save <a href="%sadmin.php?page=controller">Controller Settings</a> to fix front-end display errors.</p></div>', get_admin_url());
}

if(function_exists('the_field')) {
    if(empty(get_field('controller', 'options'))) {
        add_action('admin_notices', 'op_errorController');
    } else {
        if(!empty(get_field('recaptcha_keys', 'options')['site'])) {
            require_once('tools/controller/captcha.php');
        }
        require_once('tools/utils.php');
        require_once('tools/utils-posts.php');
        require_once('tools/controller/index.php');
        require_once('tools/controller/login.php');
        require_once('tools/business/index.php');
        require_once('tools/design/index.php');
    }
} else {
    add_action('admin_notices', 'op_errorAcf');
}
