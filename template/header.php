<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0, user-scalable=yes">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<header id="header">
	<?php get_template_part('images/deco', 'horizontal'); ?>
</header>
<main id="content">