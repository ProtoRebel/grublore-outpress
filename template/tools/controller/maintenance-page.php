<?php
$maintenanceMode = get_field('controller_maintenance_mode', 'options');
if(!$maintenanceMode['enable']) {
    exit; die;
} else {
    global $post;
}
?>
    <html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0, user-scalable=yes">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <?php wp_head(); ?>
    </head>
    <body class="maintenance">
    <main id="maintenance-mode">
        <?php
        if(have_posts()) {
            while(have_posts()) {
                the_post();
                $post = get_post($maintenanceMode['page']);
                setup_postdata($post);
                the_content();
                wp_reset_postdata();
            }
        }
        ?>
    </main>
    <?php wp_footer(); ?>
    </body>
    </html>
<?php exit; ?>