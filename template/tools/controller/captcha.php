<?php if(!defined('WPINC')) { die; }

/*  CAPTCHA: Add reCaptcha to Forms
	----------------------------- */
// NOTE: Generate v2 Captcha Keys here https://www.google.com/recaptcha/admin/create

$captchaKeys = get_field('recaptcha_keys', 'options');
$captchaKeySite = $captchaKeys['site'];
$captchaKeySecret = $captchaKeys['secret'];

// Log In Form
function oc_captchaLoginStyle() {
    wp_register_script('login-recaptcha', 'https://www.google.com/recaptcha/api.js', false, NULL);
    wp_enqueue_script('login-recaptcha');
    echo '<style>p.submit, p.forgetmenot {margin-top: 10px!important;}.login form{width: 303px;} div#login_error {width: 324px;}</style>';
}
function oc_captchaLogin() {
    global $captchaKeySite;
    printf('<div class="g-recaptcha brochure__form__captcha" data-sitekey="%s"></div>', $captchaKeySite);
}
function oc_captchaLoginCheck($user, $password) {
    global $captchaKeySecret;
    if(!empty($_POST['g-recaptcha-response'])) {
        $secret = $captchaKeySecret;
        $ip = $_SERVER['REMOTE_ADDR'];
        $captcha = $_POST['g-recaptcha-response'];
        $rsp = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $captcha .'&remoteip='. $ip);
        $valid = json_decode($rsp, true);
        if($valid['success'] == true) {
            return $user;
        } else {
            return new WP_Error('Captcha Invalid', __('<p><strong>Captcha Error</strong>: at least prove you\'re human!</p>'));
        }
    } else {
        return new WP_Error('Captcha Invalid', __('<p><strong>Captcha Error</strong>: at least prove you\'re human!</p>'));
    }
}
add_action('login_enqueue_scripts', 'oc_captchaLoginStyle');
add_action('login_form','oc_captchaLogin');
add_action('wp_authenticate_user', 'oc_captchaLoginCheck', 10, 2);

// Add to Lost Password form
function op_captchaLostPasswordStyle() {
    global $captchaKeySite;
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
    printf('<div class="g-recaptcha brochure__form__captcha" data-sitekey="%s"></div>', $captchaKeySite);
    echo '<style>.message {width: 324px;}</style>';
}
function op_captchaLostPasswordCheck($errors) {
    global $captchaKeySecret;
    if(!empty($_POST['g-recaptcha-response'])) {
        $secret = $captchaKeySecret;
        $ip = $_SERVER['REMOTE_ADDR'];
        $captcha = $_POST['g-recaptcha-response'];
        $rsp = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $captcha .'&remoteip='. $ip);
        $valid = json_decode($rsp, true);
        if(!$valid['success'] == true) {
            $errors->add('Captcha Invalid', __('<strong>Captcha Error</strong>: Prove you\'re human!'));
        }
    } else {
        $errors->add('Captcha Invalid', __('<strong>Captcha Error</strong>: Prove you\'re human!'));
    }
}
add_action('lostpassword_form', 'op_captchaLostPasswordStyle');
add_action( 'lostpassword_post', 'op_captchaLostPasswordCheck');
