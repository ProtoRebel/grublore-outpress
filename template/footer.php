</main>
<footer id="footer" class="footer">
	<div class="container">
		<?php
		printf('<p class="copyright">&copy; %s &bull; All Rights Reserved</p>', date('Y'));
		wp_nav_menu(array('theme_location' => 'legal'));
		?>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
