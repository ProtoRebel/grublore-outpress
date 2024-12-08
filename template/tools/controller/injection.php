<?php if(!defined('WPINC')) { die; }

/*  CONTROLLER & CODE INJECTION
	----------------------------- */

// Global Head/Footer code injection
function op_controllerCodeInject($location) {
    // Global
    $globalCode = get_field('code_blocks', 'options');
    if($globalCode) {
        foreach($globalCode as $c) {
            if($c['location'] === $location) {
                echo $c['code'];
            }
        }
    }
    // Post & Page Specific
    if(is_home()) {
        $id = get_option('page_for_posts');
        if($id) {
            echo get_field($location . '_code', $id);
        }
    } else {
        global $post;
        $id = is_404() ? 'options' : $post->ID;
        echo get_field($location . '_code', $id);
    }
}

add_action('wp_head', function() {
    op_controllerCodeInject('head');
});

add_action('wp_footer', function() {
    op_controllerCodeInject('foot');
});
