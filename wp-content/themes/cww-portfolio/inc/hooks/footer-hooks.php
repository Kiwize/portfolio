<?php 
/**
* Functions & definations for theme footer
*
*
*/

add_action('cww_portfolio_footer','cww_portfolio_copyright', 10 );

if( ! function_exists('cww_portfolio_copyright')):
	function cww_portfolio_copyright(){

		$cww_footer_text = get_theme_mod('cww_footer_text');

		?>
		<footer id="colophon" class="site-footer">
			<div class="container cww-flex">
			<?php do_action('cww_portfolio_social_icons'); ?>
			<div class="site-info cww-flex">
				<p id="footer_id">Thomas PRADEAU 2023</p>
			</div><!-- .site-info -->
			</div>
		</footer><!-- #colophon -->
		<?php 
	}
endif;