<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package GreatMag
 */

?>

			</div>
		</div><!-- .container -->
	</div><!-- #content -->

	<?php do_action('greatmag_before_footer'); ?>

	<footer id="colophon" class="site-footer">

		<?php do_action('greatmag_footer'); ?>
		
	</footer><!-- #colophon -->

	<?php do_action('greatmag_after_footer'); ?>
</div><!-- #page -->

<?php wp_footer(); ?>


</body>
</html>
