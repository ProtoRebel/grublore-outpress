<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=1.0, user-scalable=yes">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?= op_business('social', ['accounts']); ?>
<header id="header-contact" class="header">
	<?= op_business('name'); ?>
	<?= op_business('email'); ?>
	<?= op_business('phone'); ?>
	<?= op_business('address', ['physical', 'inline']); ?>
	<?= op_business('tagline', ['motto']); ?>
	<?= op_business('tagline', ['services']); ?>
	<?= op_business('tagline', ['area']); ?>
</header>
<header id="header" class="header">
	<div class="container">
		<a href="/" class="logo">
			<?= op_business('logo', ['alt']) . op_business('logo', ['emblem']); ?>
		</a>
		<a href="#" id="nav-toggle">
			<?php for($i = 1; $i <= 3; $i++) { echo '<span class="line"></span>'; } ?>
		</a>
		<nav id="nav-primary">
			<?php
			echo op_business('logo');

			wp_nav_menu(array(
				'theme_location'	=> 'primary'
			));
			?>
		</nav>
	</div>
</header>
<main id="content">