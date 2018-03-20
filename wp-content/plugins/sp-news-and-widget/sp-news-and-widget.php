<?php
/*
Plugin Name: WP News and Scrolling Widgets
Plugin URL: https://www.wponlinesupport.com
Text Domain: sp-news-and-widget
Domain Path: /languages/
Description: A simple News and three widgets(static, scrolling and with thumbs) plugin
Version: 3.3.4
Author: WP Online Support
Author URI: https://www.wponlinesupport.com
Contributors: WP Online Support
*/

if( !defined( 'WPNW_VERSION' ) ) {
    define( 'WPNW_VERSION', '3.3.4' ); // Version of plugin
}
if( !defined( 'WPNW_DIR' ) ) {
    define( 'WPNW_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'WPNW_POST_TYPE' ) ) {
    define( 'WPNW_POST_TYPE', 'news' ); // Plugin post type
}

/* Plugin Analytics Data Starts */

function wnasw_fs() {
    global $wnasw_fs;

    if ( ! isset( $wnasw_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $wnasw_fs = fs_dynamic_init( array(
            'id'                  => '1773',
            'slug'                => 'sp-news-and-widget',
            'type'                => 'plugin',
            'public_key'          => 'pk_0b3f591a735ebcfd6f8bf21db5d9e',
            'is_premium'          => false,
            'has_addons'          => false,
            'has_paid_plans'      => false,
            'menu'                => array(
                'slug'           => 'edit.php?post_type=news',
                'account'        => false,
                'contact'        => false,
                'support'        => false,
            ),
        ));
    }
    return $wnasw_fs;
}

// Init Freemius.
wnasw_fs();

// Signal that SDK was initiated.
do_action( 'wnasw_fs_loaded' );

/* Plugin Analytics Data Ends */

register_activation_hook( __FILE__, 'install_newsfree_version' );
function install_newsfree_version(){
if( is_plugin_active('wp-news-and-widget-pro/sp-news-and-widget.php') ){
     add_action('update_option_active_plugins', 'deactivate_newsfree_version');
    }
}
function deactivate_newsfree_version(){
   deactivate_plugins('wp-news-and-widget-pro/sp-news-and-widget.php',true);
}
add_action( 'admin_notices', 'freenews_admin_notice');
function freenews_admin_notice() {
    $dir = ABSPATH . 'wp-content/plugins/wp-news-and-widget-pro/sp-news-and-widget.php';
    if( is_plugin_active( 'sp-news-and-widget/sp-news-and-widget.php' ) && file_exists($dir)) {
        global $pagenow;
        if( $pagenow == 'plugins.php' ){
            deactivate_plugins ( 'wp-news-and-widget-pro/sp-news-and-widget.php',true);
            if ( current_user_can( 'install_plugins' ) ) {
                echo '<div id="message" class="updated notice is-dismissible"><p><strong>Thank you for activating WP News and three widgets</strong>.<br /> It looks like you had PRO version <strong>(<em>WP News and Five Widgets Pro</em>)</strong> of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it. </p></div>';
            }
        }
    }
} 

add_action('plugins_loaded', 'sp_news_load_textdomain');
function sp_news_load_textdomain() {
	load_plugin_textdomain( 'sp-news-and-widget', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

// Initialization function
add_action('init', 'sp_cpt_news_init');
function sp_cpt_news_init() {
  // Create new News custom post type
    $news_labels = array(
    'name'                 => _x('News', 'sp-news-and-widget'),
    'singular_name'        => _x('news', 'sp-news-and-widget'),
    'add_new'              => _x('Add News Item', 'sp-news-and-widget'),
    'add_new_item'         => __('Add New News Item', 'sp-news-and-widget'),
    'edit_item'            => __('Edit News Item', 'sp-news-and-widget'),
    'new_item'             => __('New News Item', 'sp-news-and-widget'),
    'view_item'            => __('View News Item', 'sp-news-and-widget'),
    'search_items'         => __('Search  News Items','sp-news-and-widget'),
    'not_found'            =>  __('No News Items found', 'sp-news-and-widget'),
    'not_found_in_trash'   => __('No News Items found in Trash', 'sp-news-and-widget'), 
    '_builtin'             =>  false, 
    'parent_item_colon'    => '',
    'menu_name'          => _x( 'News', 'admin menu', 'sp-news-and-widget' )
  );
  $news_args = array(
    'labels'              => $news_labels,
    'public'              => true,
    'publicly_queryable'  => true,
    'exclude_from_search' => false,
    'show_ui'             => true,
    'show_in_menu'        => true, 
    'query_var'           => true,
    'rewrite'             => array( 
							'slug' => 'news',
							'with_front' => false
							),
    'capability_type'     => 'post',
    'has_archive'         => true,
    'hierarchical'        => false,
    'menu_position'       => 5,
	'menu_icon'   		  => 'dashicons-feedback',
    'supports'            => array('title','editor','thumbnail','excerpt','comments'),
    'taxonomies'          => array('post_tag')
  );
  register_post_type('news',$news_args);
}
/* Register Taxonomy */
add_action( 'init', 'news_taxonomies');
function news_taxonomies() {
    $labels = array(
        'name'              => _x( 'Category', 'sp-news-and-widget' ),
        'singular_name'     => _x( 'Category', 'sp-news-and-widget' ),
        'search_items'      => __( 'Search Category', 'sp-news-and-widget' ),
        'all_items'         => __( 'All Category', 'sp-news-and-widget' ),
        'parent_item'       => __( 'Parent Category', 'sp-news-and-widget' ),
        'parent_item_colon' => __( 'Parent Category:', 'sp-news-and-widget' ),
        'edit_item'         => __( 'Edit Category', 'sp-news-and-widget' ),
        'update_item'       => __( 'Update Category', 'sp-news-and-widget' ),
        'add_new_item'      => __( 'Add New Category', 'sp-news-and-widget' ),
        'new_item_name'     => __( 'New Category Name', 'sp-news-and-widget' ),
        'menu_name'         => __( 'News Category', 'sp-news-and-widget' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'news-category' ),
    );

    register_taxonomy( 'news-category', array( 'news' ), $args );
}

function wpnaw_rewrite_flush() {  
	sp_cpt_news_init();  
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpnaw_rewrite_flush' );

add_action( 'wp_enqueue_scripts','wpnawstyle_css_script' );
function wpnawstyle_css_script() {
    wp_enqueue_style( 'cssnews',  plugin_dir_url( __FILE__ ). 'css/stylenews.css', array(), WPNW_VERSION );
    wp_enqueue_script( 'vnewsticker', plugin_dir_url( __FILE__ ) . 'js/jquery.newstape.js', array( 'jquery' ), WPNW_VERSION);
}

require_once( 'widget_function.php' );	

function wpnaw_get_news( $atts, $content = null ){
    // setup the query
    extract(shortcode_atts(array(
		"limit"                 => '',	
		"category"              => '',
		"grid"                  => '',
        "show_date"             => '',
        "show_category_name"    => '',
        "show_content"          => '',
		"show_full_content"     => '',
        "content_words_limit"   => '',
        "pagination_type"       => 'numeric',
	), $atts));
	
    // Define limit
	
    if( $limit ) { 
		$posts_per_page = $limit; 
	} else {
		$posts_per_page = '-1';
	}
	
    if( $category ) { 
		$cat = $category; 
	} else {
		$cat = '';
	}
	
    if( $grid ) { 
		$gridcol = $grid; 
	} else {
		$gridcol = '1';
	}
    
    if( $show_date ) { 
        $showDate = $show_date; 
    } else {
        $showDate = 'true';
    }
	
    if( $show_category_name ) { 
        $showCategory = $show_category_name; 
    } else {
        $showCategory = 'true';
    }
    
    if( $show_content ) { 
        $showContent = $show_content; 
    } else {
        $showContent = 'true';
    }
	
    if( $show_full_content ) { 
        $showFullContent = $show_full_content; 
    } else {
        $showFullContent = 'false';
    }
	
    if( $content_words_limit ) { 
        $words_limit = $content_words_limit; 
    } else {
        $words_limit = '20';
    }

    if($pagination_type == 'numeric'){

       $pagination_type = 'numeric';
    }else{

        $pagination_type = 'next-prev';
    }

	ob_start();
	
	global $paged;
	
    if(is_home() || is_front_page()) {
		  $paged = get_query_var('page');
	} else {
		 $paged = get_query_var('paged');
	}

	$post_type 		= 'news';
	$orderby 		= 'date';
	$order 			= 'DESC';

    $args = array ( 
        'post_type'      => $post_type,
        'post_status'    => array( 'publish' ),
        'orderby'        => $orderby,
        'order'          => $order,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    );

    if($cat != "") {
        $args['tax_query'] = array(
            array(
                'taxonomy'  => 'news-category',
                'field'     => 'term_id',
                'terms'     => $cat
            ));
    }

    $query = new WP_Query($args);

    global $post;
    $post_count = $query->post_count;
    $count = 0;
	?>
	<div class="wpnawfree-plugin news-clearfix">
	<?php
    if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();
        
        $count++;
        $terms = get_the_terms( $post->ID, 'news-category' );
        $news_links = array();

        if($terms) {
            foreach ( $terms as $term ) {
                $term_link = get_term_link( $term );
                $news_links[] = '<a href="' . esc_url( $term_link ) . '">'.$term->name.'</a>';
            }
        }
        
        $cate_name = join( ", ", $news_links );
        $css_class="wpnaw-news-post";

        if ( ( is_numeric( $grid ) && ( $grid > 0 ) && ( 0 == ($count - 1) % $grid ) ) || 1 == $count ) { $css_class .= ' wpnaw-first'; }
        if ( ( is_numeric( $grid ) && ( $grid > 0 ) && ( 0 == $count % $grid ) ) || $post_count == $count ) { $css_class .= ' wpnaw-last'; }
        if($showDate == 'true'){ $date_class = "has-date"; } else { $date_class = "has-no-date";} ?>
	
    	<div id="post-<?php the_ID(); ?>" class="news type-news news-col-<?php echo $gridcol.' '.$css_class.' '.$date_class; ?>">
			<div class="news-inner-wrap-view news-clearfix">	
				<div class="news-thumb">    			
					<?php if ( has_post_thumbnail()) {    				
						if($gridcol == '1'){ ?>    					
							<div class="grid-news-thumb">    				    
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('url'); ?></a>
							</div>
						<?php } else if($gridcol > '2') { ?>    					
							<div class="grid-news-thumb">	    				    
								<a href="<?php the_permalink(); ?>">	<?php the_post_thumbnail('large'); ?></a>
							</div>
						<?php	} else { ?>        			    
							<div class="grid-news-thumb">        				
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('large'); ?></a>
							</div>
						<?php } 
					} ?>
				</div>			
				<div class="news-content">    			
					<?php if($gridcol == '1') {                    
						if($showDate == 'true'){ ?>        				
							<div class="date-post">            			
								<h2><span><?php echo get_the_date('j'); ?></span></h2>            			
								<p><?php echo get_the_date('M y'); ?></p>
							</div>
						<?php }?>
					<?php } else {  ?>    				
						<div class="grid-date-post">        			
							<?php echo ($showDate == "true")? get_the_date() : "" ;?>                    
							<?php echo ($showDate == "true" && $showCategory == "true" && $cate_name != '') ? " / " : "";?>                    
							<?php echo ($showCategory == 'true' && $cate_name != '') ? $cate_name : ""?>
						</div>
					<?php  } ?>    			
					<div class="post-content-text">    				
						<?php the_title( sprintf( '<h3 class="news-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );	?>    			    
						<?php if($showCategory == 'true' && $gridcol == '1'){ ?>    				
							<div class="news-cat">                        
								<?php echo $cate_name; ?>
							</div>
						<?php }?>
						<?php if($showContent == 'true'){?>        			 
							<div class="news-content-excerpt">            			
								<?php  if($showFullContent == "false" ) {
									$excerpt = get_the_content(); ?>                				
									<div class="news-short-content">                                    
										<?php echo string_limit_newswords( $post->ID, $excerpt, $words_limit, '...'); ?>
									</div>                				
									<a href="<?php the_permalink(); ?>" class="news-more-link"><?php _e( 'Read More', 'sp-news-and-widget' ); ?></a>	
								<?php } else {             				
									the_content();
								} ?>
							</div><!-- .entry-content -->
						<?php }?>
					</div>
				</div>
			</div><!-- #post-## -->
        </div><!-- #post-## -->
    <?php  endwhile; endif; ?>
	</div>		
    <div class="news_pagination">        
        <?php if($pagination_type == 'numeric'){ 
            echo news_pagination( array( 'paged' => $paged , 'total' => $query->max_num_pages ) );
        }else{ ?>    		
            <div class="button-news-p"><?php next_posts_link( ' Next >>', $query->max_num_pages ); ?></div>    		
            <div class="button-news-n"><?php previous_posts_link( '<< Previous' ); ?> </div>
        <?php } ?>
	</div><?php
    
    wp_reset_query(); 
				
	return ob_get_clean();
	}
add_shortcode('sp_news','wpnaw_get_news');

function string_limit_newswords( $post_id = null, $content = '', $word_length = '55', $more = '...' ) {

    $has_excerpt  = false;
    $word_length    = !empty($word_length) ? $word_length : '55';

    // If post id is passed
    if( !empty($post_id) ) {
        if (has_excerpt($post_id)) {
            $has_excerpt    = true;
            $content        = get_the_excerpt();
        } else {
            $content = !empty($content) ? $content : get_the_content();
        }
    }

    if( !empty($content) && (!$has_excerpt) ) {
        $content = strip_shortcodes( $content ); // Strip shortcodes
        $content = wp_trim_words( $content, $word_length, $more );
    }

    return $content;
}

function spnews_display_tags( $query ) {
    if( is_tag() && $query->is_main_query() ) {       
       $post_types = array( 'post', 'news' );
        $query->set( 'post_type', $post_types );
    }
}
add_filter( 'pre_get_posts', 'spnews_display_tags' );


// Manage Category Shortcode Columns

add_filter("manage_news-category_custom_column", 'news_category_columns', 10, 3);
add_filter("manage_edit-news-category_columns", 'news_category_manage_columns'); 
function news_category_manage_columns($theme_columns) {
    $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name'),
            'news_shortcode' => __( 'News Category Shortcode', 'sp-news-and-widget' ),
            'slug' => __('Slug'),
            'posts' => __('Posts')
			);
    return $new_columns;
}

function news_category_columns($out, $column_name, $theme_id) {
    $theme = get_term($theme_id, 'news-category');
    switch ($column_name) {
        case 'title':
            echo get_the_title();
        break;
        case 'news_shortcode': 
             echo '[sp_news category="' . $theme_id. '"]';
        break;
        default:
            break;
    }
    return $out; 
}

function news_pagination($args = array()){    
    $big = 999999999; // need an unlikely integer
    $paging = apply_filters('news_blog_paging_args', array(
                    'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                    'format'    => '?paged=%#%',
                    'current'   => max( 1, $args['paged'] ),
                    'total'     => $args['total'],
                    'prev_next' => true,
                    'prev_text' => __('« Previous', 'wp-blog-and-widgets'),
                    'next_text' => __('Next »', 'wp-blog-and-widgets'),
                ));
    
    echo paginate_links($paging);
}

function news_get_unique() {
  static $unique = 0;
  $unique++;

  return $unique;
}

// How it work file, Load admin files
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( WPNW_DIR . '/admin/wpnw-how-it-work.php' );
}