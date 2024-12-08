<?php if(!defined('WPINC')) { die; }

/*  MAINTENANCE MODE
	----------------------------- */

// Swap & Inject Header
function op_maintenanceMode() {
    $maintenanceMode = get_field('controller_maintenance_mode', 'options');
	if($maintenanceMode['enable'] && !is_user_logged_in() && !current_user_can('edit_posts')) {
		add_filter('wp_robots', function($robots) {
			$robots['noindex'] = true;
			return $robots;
		});
        get_template_part('tools/controller/maintenance', 'page', array('page_id' => $maintenanceMode['page']));
	}
}
add_action('get_header', 'op_maintenanceMode');