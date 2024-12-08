<?php if(!defined('WPINC')) { die; }

/*  LOGIN SETTINGS
	----------------------------- */

// Limit Login Attempts: Values
function op_securityLimitLogin($return) {
    $output = null;
    $values = get_field('limit_login_attempts', 'options');
    if($return === 'enable') {
        $output = $values['enable'];
    } elseif($return === 'tries') {
        $output = $values['tries'];
    } elseif($return === 'wait') {
        $output = $values['wait'] * 60;
    }
    return $output;
}

// Limit Login Attempts: Check
function op_securityLimitLoginCheck($user, $username, $password) {
    if(get_transient('attempted_login')) {
        $data = get_transient('attempted_login');
        if($data['tried'] >= op_securityLimitLogin('tries')) {
            $until = get_option('_transient_timeout_' . 'attempted_login');
            $time = util_timeAway($until);
            return new WP_Error('too_many_tried',  sprintf(__( '<strong>There\'s a problem</strong>: Too many incorrect login attempts. Wait %1$s and you can try again.'), $time));
        }
    }
    return $user;
}

// Limit Login Attempts: Set transient and log status
function op_securityLimitLoginFailure($username) {
    if(get_transient('attempted_login')) {
        $data = get_transient('attempted_login');
        $data['tried']++;
        if($data['tried'] <= op_securityLimitLogin('tries')) {
            set_transient('attempted_login', $data , op_securityLimitLogin('wait'));
        }
    } else {
        $data = array(
            'tried' => 1
        );
        set_transient('attempted_login', $data , op_securityLimitLogin('wait'));
    }
}

if(op_securityLimitLogin('enable')) {
    add_filter('authenticate', 'op_securityLimitLoginCheck', 30, 3);
    add_action('wp_login_failed', 'op_securityLimitLoginFailure');
}