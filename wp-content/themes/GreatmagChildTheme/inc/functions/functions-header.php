<?php
/**
 * Header functions
 *
 * @package GreatMag
 */


/**
 * Site title, logo and menu bar
 */
function greatmag_header_bar() {
	$menu_pos = get_theme_mod('menu_position', 'after-branding');
	?>
	<header id="masthead" class="site-header">

	<?php if ( $menu_pos == 'after-branding' ) :
		greatmag_site_branding();

		greatmag_desktop_menu();

		greatmag_mobile_menu();
	else :
		greatmag_desktop_menu();

		greatmag_mobile_menu();

		greatmag_site_branding();
	endif; ?>

	</header><!-- #masthead -->
	<?php
}
add_action('greatmag_header', 'greatmag_header_bar');

/**
 * Preloader
 */
function greatmag_preloader() {
	$preloader 	= get_theme_mod( 'preloader_text', __('Loading...', 'greatmag') );
	$disable 	= get_theme_mod( 'disable_preloader', 0 );

	if ( $disable == 1 ) {
		return;
	}

	?>
	<div class="preloader">
		<div><span><?php echo esc_html($preloader); ?></span></div>
	</div>
	<?php
}
add_action( 'greatmag_before_header', 'greatmag_preloader', 1);

/**
 * Site branding
 */
if ( !function_exists('greatmag_site_branding') ) :
function greatmag_site_branding() {
	$header_ad_image 	= get_theme_mod('header_ad_image');
	$header_ad_url		= get_theme_mod('header_ad_url');
	?>
		<div class="site-branding vhome3 row m0">
			<div class="container">
				<div class="main-logo">
					<div class="media">
						<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
						<div class="media-left">
							<?php the_custom_logo(); ?>
						</div>
						<?php endif; ?>
						<div class="media-body">
							<?php
							$description = get_bloginfo( 'description', 'display' );
							if ( $description || is_customize_preview() ) : ?>
								<p class="site-description site-slogan"><?php echo $description; /* WPCS: xss ok. */ ?></p>
							<?php
							endif;
							if ( is_front_page() && is_home() ) : ?>
								<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
							<?php else : ?>
								<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
							<?php
							endif;
							?>
						</div>
					</div>
				</div>
				<?php if ( $header_ad_image ) : ?>
					<div class="header-ad">
						<a target="_blank" href="<?php echo esc_url($header_ad_url); ?>"><img src="<?php echo esc_url($header_ad_image); ?>"/></a>
					</div>
				<?php endif; ?>
			</div>
		</div><!-- .site-branding -->
	<?php
}
endif;

/**
 * Desktop menu
 */
if ( !function_exists('greatmag_desktop_menu') ) :
function greatmag_desktop_menu() {
	?>
		<nav id="site-navigation" class="navbar navbar-static-top navbar-default main-navigation bgf6">
			<div class="container">
				<div class="row">

					<?php if ( function_exists('max_mega_menu_is_enabled') && max_mega_menu_is_enabled('primary') ) : ?>
						<?php wp_nav_menu( array( 'theme_location' => 'primary') ); ?>
					<?php else: ?>
			        <?php
			            wp_nav_menu( array(
			                'menu'              => 'primary',
			                'theme_location'    => 'primary',
			                'container'         => 'div',
			                'container_class'   => 'collapse navbar-collapse',
			                'menu_class'        => 'nav navbar-nav',
			                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
			                'walker'            => new wp_bootstrap_navwalker())
			            );
			        ?>
					<button class="off-canvas-trigger" aria-controls="primary" aria-expanded="false">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<?php endif; ?>
				</div>
			</div>
		</nav><!-- #site-navigation -->
	<?php
}
endif;

/**
 * Mobile menu
 */
if ( !function_exists('greatmag_mobile_menu') ) :
function greatmag_mobile_menu() {
	?>
		<div class="off-close outer"></div>
		<div class="off-canvas row">
			<div class="off-logo-box off-widget">
				<button class="off-close"><i class="fa fa-times"></i></button><br>
				<a class="off-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php
					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) : ?>
						<p class="site-description site-slogan"><?php echo $description; ?></p>
					<?php endif; ?>
					<h4 class="site-title"><?php bloginfo( 'name' ); ?></h4>
				</a>
			</div>
			<div class="mob-menu-box1 off-widget">
			        <?php
			            wp_nav_menu( array(
			                'menu'              => 'primary',
			                'theme_location'    => 'primary',
			                'container'         => 'ul',
			                'container_class'   => 'collapse navbar-collapse',
			                'menu_class'        => 'nav navbar-nav mob-menu',
			                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
			                'walker'            => new wp_bootstrap_navwalker())
			            );
			        ?>
			</div>
		</div>
	<?php
}
endif;

/**
 * Filter logo and add proper Schema support
 */
function greatmag_wrap_logo( $html ) {
    return sprintf(
        '<div itemscope itemtype="%1$s">%2$s</div>',
        esc_url( 'https://schema.org/Brand' ),
        $html
    );
}
add_filter( 'get_custom_logo', 'greatmag_wrap_logo' );

/**
 * Top bar
 */
function greatmag_top_bar() {

	$hide_search_icon = get_theme_mod( 'hide_search_icon', 0 );

	?>
		<?php if( $hide_search_icon == 0 ) { ?>
		<div class="top-search-form row">
			<?php get_search_form(); ?>
		</div>
		<?php } ?>
		<div class="top-header row">
			<div class="container">
				<div class="row">
					<div class="col-sm-8">
						<?php greatmag_latest_news(); ?>
					</div>
					<div class="col-sm-4 auth-social">
						<?php greatmag_social_login(); ?>
					</div>
				</div>
			</div>
		</div>
	<?php
}
add_action( 'greatmag_before_header', 'greatmag_top_bar');

/**
 * Latest news
 */
function greatmag_latest_news() {
	$number 	= get_theme_mod('latest_news_number', 5);
	$ln_title 	= get_theme_mod('latest_news_title', __( 'Latest news', 'greatmag') );
	$query = new WP_Query( array(
		'posts_per_page'      => $number,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true
	) );
	?>

	<div class="media breaking-news">
		<div class="media-left">
			<div class="bnews-label"><?php echo esc_html($ln_title); ?></div>
		</div>
		<div class="media-body">
			<div class="bnews-ticker">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<div class="item"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></div>
			<?php endwhile;	?>
			<?php wp_reset_postdata(); ?>
			</div>
		</div>
	</div>

	<?php
}

/**
 * Social / search / login
 */
function greatmag_social_login() {

	$socials = array(
		'twitter',
		'instagram',
		'facebook',
		'linkedin',
		'pinterest',
		'youtube',
		'googleplus',
		'dribbble',
		'flickr',
		'vimeo',
		'foursquare',
		'tumblr',
		'behance',
		'deviantart',
		'soundcloud',
		'spotify',
		'weibo',
		'xing',
		'trello'
	);
	$soc_url = array();
	foreach ( $socials as $social ) {
		$soc_url[$social] = get_theme_mod('social_link_'.$social);
	}

	$hide_login_dropdown 	= get_theme_mod( 'hide_login_dropdown', 0 );
	$hide_search_icon = get_theme_mod( 'hide_search_icon', 0 );

	?>
	<ul class="nav nav-pills auth-social-nav">
		<?php if($hide_login_dropdown == 0) { ?>
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user"></i></a>
			<div class="dropdown-menu login-drop">
				<?php wp_login_form(); ?>
				<div class="login-drop-footer">
					<a href="<?php echo esc_url(wp_lostpassword_url()); ?>" title="<?php echo __( 'Lost your password?', 'greatmag' ); ?>"><?php echo __( 'Lost your password?', 'greatmag' ); ?></a>
					<?php wp_register('', ''); ?>
				</div>
			</div>
		</li>
		<?php } ?>

		<?php if( $hide_search_icon == 0 ) { ?>
		<li class="search-top"><a href="#"><i class="fa fa-search"></i></a></li>
		<?php } ?>

		<?php
		foreach ( $socials as $social ) {
			if($soc_url[$social]) {
				$fa_class = $social;
				if($social == 'youtube'){
					$fa_class = 'youtube-play';
				}
				if($social == 'googleplus'){
					$fa_class = 'google-plus';
				}
			?>
				<li><a href="<?php echo esc_url($soc_url[$social]); ?>"><i class="fa fa-<?php echo $fa_class; ?>"></i></a></li>
		<?php
			}
		}?>

	</ul>
	<?php
}
