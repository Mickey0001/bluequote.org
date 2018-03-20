<?php
/**
 * Pro Designs and Plugins Feed
 *
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Action to add menu
add_action('admin_menu', 'wpnw_register_design_page');

/**
 * Register plugin design page in admin menu
 * 
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_register_design_page() {
	add_submenu_page( 'edit.php?post_type='.WPNW_POST_TYPE, __('How it works - WP News and Scrolling Widgets', 'sp-news-and-widget'), __('How It Works', 'sp-news-and-widget'), 'edit_posts', 'wpnw-designs', 'wpnw_designs_page' );
}

/**
 * Function to display plugin design HTML
 * 
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_designs_page() {

	$wpos_feed_tabs = wpnw_help_tabs();
	$active_tab 	= isset($_GET['tab']) ? $_GET['tab'] : 'how-it-work';
?>
		
	<div class="wrap wpnwm-wrap">

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ($wpos_feed_tabs as $tab_key => $tab_val) {
				$tab_name	= $tab_val['name'];
				$active_cls = ($tab_key == $active_tab) ? 'nav-tab-active' : '';
				$tab_link 	= add_query_arg( array( 'post_type' => WPNW_POST_TYPE, 'page' => 'wpnw-designs', 'tab' => $tab_key), admin_url('edit.php') );
			?>

			<a class="nav-tab <?php echo $active_cls; ?>" href="<?php echo $tab_link; ?>"><?php echo $tab_name; ?></a>

			<?php } ?>
		</h2>
		
		<div class="wpnwm-tab-cnt-wrp">
		<?php
			if( isset($active_tab) && $active_tab == 'how-it-work' ) {
				wpnw_howitwork_page();
			}
			else if( isset($active_tab) && $active_tab == 'plugins-feed' ) {
				echo wpnw_get_plugin_design( 'plugins-feed' );
			} else {
				echo wpnw_get_plugin_design( 'offers-feed' );
			}
		?>
		</div><!-- end .wpnwm-tab-cnt-wrp -->

	</div><!-- end .wpnwm-wrap -->

<?php
}

/**
 * Gets the plugin design part feed
 *
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_get_plugin_design( $feed_type = '' ) {
	
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
	
	// If tab is not set then return
	if( empty($active_tab) ) {
		return false;
	}

	// Taking some variables
	$wpos_feed_tabs = wpnw_help_tabs();
	$transient_key 	= isset($wpos_feed_tabs[$active_tab]['transient_key']) 	? $wpos_feed_tabs[$active_tab]['transient_key'] 	: 'wpnwm_' . $active_tab;
	$url 			= isset($wpos_feed_tabs[$active_tab]['url']) 			? $wpos_feed_tabs[$active_tab]['url'] 				: '';
	$transient_time = isset($wpos_feed_tabs[$active_tab]['transient_time']) ? $wpos_feed_tabs[$active_tab]['transient_time'] 	: 172800;
	$cache 			= get_transient( $transient_key );
	
	if ( false === $cache ) {
		
		$feed 			= wp_remote_get( esc_url_raw( $url ), array( 'timeout' => 120, 'sslverify' => false ) );
		$response_code 	= wp_remote_retrieve_response_code( $feed );
		
		if ( ! is_wp_error( $feed ) && $response_code == 200 ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( $transient_key, $cache, $transient_time );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the data from the server. Please try again later.', 'sp-news-and-widget' ) . '</div>';
		}
	}
	return $cache;	
}

/**
 * Function to get plugin feed tabs
 *
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_help_tabs() {
	$wpos_feed_tabs = array(
						'how-it-work' 	=> array(
													'name' => __('How It Works', 'sp-news-and-widget'),
												),
						'plugins-feed' 	=> array(
													'name' 				=> __('Our Plugins', 'sp-news-and-widget'),
													'url'				=> 'http://wponlinesupport.com/plugin-data-api/plugins-data.php',
													'transient_key'		=> 'wpos_plugins_feed',
													'transient_time'	=> 172800
												),
						'offers-feed' 	=> array(
													'name'				=> __('WPOS Offers', 'sp-news-and-widget'),
													'url'				=> 'http://wponlinesupport.com/plugin-data-api/wpos-offers.php',
													'transient_key'		=> 'wpos_offers_feed',
													'transient_time'	=> 86400,
												)
					);
	return $wpos_feed_tabs;
}

/**
 * Function to get 'How It Works' HTML
 *
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_howitwork_page() { ?>

	<style type="text/css">
		.wpos-pro-box .hndle{background-color:#0073AA; color:#fff;}
		.wpos-pro-box .postbox{background:#dbf0fa none repeat scroll 0 0; border:1px solid #0073aa; color:#191e23;}
		.postbox-container .wpos-list li:before{font-family: dashicons; content: "\f139"; font-size:20px; color: #0073aa; vertical-align: middle;}
		.wpnwm-wrap .wpos-button-full{display:block; text-align:center; box-shadow:none; border-radius:0;}
		.wpnwm-shortcode-preview{background-color: #e7e7e7; font-weight: bold; padding: 2px 5px; display: inline-block; margin:0 0 2px 0;}
	</style>

	<div class="post-box-container">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<!--How it workd HTML -->
				<div id="post-body-content">
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
								
								<h3 class="hndle">
									<span><?php _e( 'How It Works - Display and Shortcode', 'sp-news-and-widget' ); ?></span>
								</h3>
								
								<div class="inside">
									<table class="form-table">
										<tbody>
											<tr>
												<th>
													<label><?php _e('Geeting Started', 'sp-news-and-widget'); ?></label>
												</th>
												<td>
													<ul>
														<li><?php _e('Step-1: This plugin create a News menu tab in WordPress menu with custom post type.".', 'sp-news-and-widget'); ?></li>														
														<li><?php _e('Step-2: Go to "News > Add news item tab".', 'sp-news-and-widget'); ?></li>
														<li><?php _e('Step-3: Add news title, description, category, and image as featured image.', 'sp-news-and-widget'); ?></li>
														<li><?php _e('Step-4: Repeat this process and add multiple news item.', 'sp-news-and-widget'); ?></li>	
														<li><?php _e('Step-4: To display news category wise you can use category shortcode under "News > News category"', 'sp-news-and-widget'); ?></li>															
													</ul>
												</td>
											</tr>

											<tr>
												<th>
													<label><?php _e('How Shortcode Works', 'sp-news-and-widget'); ?></label>
												</th>
												<td>
													<ul>
														<li><?php _e('Step-1. Create a page like Our News OR Latest News.', 'sp-news-and-widget'); ?></li>
														<li><?php _e('<b>Please make sure that Permalink link should not be "/news" Otherwise all your news will go to archive page. You can give it other name like "/ournews, /latestnews etc"</b>', 'sp-news-and-widget'); ?></li>
														<li><?php _e('Step-2. Put below shortcode as per your need.', 'sp-news-and-widget'); ?></li>
													</ul>
												</td>
											</tr>

											<tr>
												<th>
													<label><?php _e('All Shortcodes', 'sp-news-and-widget'); ?></label>
												</th>
												<td>
													<span class="wpnwm-shortcode-preview">[sp_news grid="list"]</span> – <?php _e('News in List View', 'sp-news-and-widget'); ?> <br />
													<span class="wpnwm-shortcode-preview">[sp_news grid="1"]</span> – <?php _e('Display News in grid 1', 'sp-news-and-widget'); ?> <br />
													<span class="wpnwm-shortcode-preview">[sp_news grid="2"]</span> – <?php _e('Display News in grid 2', 'sp-news-and-widget'); ?> <br />
													<span class="wpnwm-shortcode-preview">[sp_news grid="3"]</span> – <?php _e('Display News in grid 3', 'sp-news-and-widget'); ?>
												</td>
											</tr>						
												
											<tr>
												<th>
													<label><?php _e('Need Support?', 'sp-news-and-widget'); ?></label>
												</th>
												<td>
													<p><?php _e('Check plugin document for shortcode parameters and demo for designs.', 'sp-news-and-widget'); ?></p> <br/>
													<a class="button button-primary" href="https://www.wponlinesupport.com/plugins-documentation/document-wp-news-and-scrolling-widgets/?utm_source=hp&event=doc" target="_blank"><?php _e('Documentation', 'sp-news-and-widget'); ?></a>									
													<a class="button button-primary" href="http://demo.wponlinesupport.com/sp-news/?utm_source=hp&event=demo" target="_blank"><?php _e('Demo for Designs', 'sp-news-and-widget'); ?></a>
												</td>
											</tr>
										</tbody>
									</table>
								</div><!-- .inside -->
							</div><!-- #general -->
						</div><!-- .meta-box-sortables ui-sortable -->
					</div><!-- .metabox-holder -->
				</div><!-- #post-body-content -->
				
				<!--Upgrad to Pro HTML -->
				<div id="postbox-container-1" class="postbox-container">
					<div class="metabox-holder wpos-pro-box">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox" style="">
									
								<h3 class="hndle">
									<span><?php _e( 'Upgrate to Pro', 'sp-news-and-widget' ); ?></span>
								</h3>
								<div class="inside">										
									<ul class="wpos-list">
										<li>120+ stunning and cool designs</li>
										<li>6 shortcodes</li>
										<li>50 Designs for News Grid Layout.</li>
										<li>45 Designs for News Slider/Carousel Layout.</li>
										<li>8 Designs for News List View.</li>
										<li>3 Designs News Grid Box.</li>
										<li>8 Designs News Grid Box Slider.</li>
										<li>Visual Composer Page Builder Support</li>
										<li>News Ticker.</li>
										<li>7 different types of Latest News widgets.</li>
										<li>Recent News Slider</li>
										<li>Recent News Carousel</li>
										<li>Recent News in Grid view</li>
										<li>Create a News Page OR News website</li>										
										<li>Custom Read More link for News Post</li>
										<li>News display with categories</li>
										<li>Drag & Drop feature to display News post in your desired order and other 6 types of order parameter</li>
										<li>Publicize' support with Jetpack to publish your News post on your social network</li>
										<li>Custom CSS</li>
										<li>100% Multi language</li>
									</ul>
									<a class="button button-primary wpos-button-full" href="https://www.wponlinesupport.com/wp-plugin/sp-news-and-scrolling-widgets/?utm_source=hp&event=go_premium" target="_blank"><?php _e('Go Premium ', 'sp-news-and-widget'); ?></a>	
									<p><a class="button button-primary wpos-button-full" href="http://demo.wponlinesupport.com/prodemo/news-plugin-pro/?utm_source=hp&event=pro_demo" target="_blank"><?php _e('View PRO Demo ', 'sp-news-and-widget'); ?></a>			</p>								
								</div><!-- .inside -->
							</div><!-- #general -->
						</div><!-- .meta-box-sortables ui-sortable -->
					</div><!-- .metabox-holder -->

					<div class="metabox-holder wpos-pro-box">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <h3 class="hndle">
                                    <span><?php _e('Need PRO Support?', 'sp-news-and-widget'); ?></span>
                                </h3>
                                <div class="inside">
                                    <p><?php _e('Hire our experts for WordPress website support.', 'sp-news-and-widget'); ?></p>
                                    <p><a class="button button-primary wpos-button-full" href="https://www.wponlinesupport.com/projobs-support/?utm_source=hp&event=projobs" target="_blank"><?php _e('PRO Support', 'sp-news-and-widget'); ?></a></p>
                                </div><!-- .inside -->
                            </div><!-- #general -->
                        </div><!-- .meta-box-sortables ui-sortable -->
                    </div><!-- .metabox-holder -->

					<!-- Help to improve this plugin! -->
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
									<h3 class="hndle">
										<span><?php _e( 'Help to improve this plugin!', 'sp-news-and-widget' ); ?></span>
									</h3>									
									<div class="inside">										
										<p>Enjoyed this plugin? You can help by rate this plugin <a href="https://wordpress.org/support/plugin/sp-news-and-widget/reviews/?filter=5" target="_blank">5 stars!</a></p>
									</div><!-- .inside -->
							</div><!-- #general -->
						</div><!-- .meta-box-sortables ui-sortable -->
					</div><!-- .metabox-holder -->
				</div><!-- #post-container-1 -->

			</div><!-- #post-body -->
		</div><!-- #poststuff -->
	</div><!-- #post-box-container -->
<?php }