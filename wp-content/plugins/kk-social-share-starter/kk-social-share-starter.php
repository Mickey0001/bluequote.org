<?php
/*
Plugin Name: Social Share Starter by KK
Plugin URI: http://newinternetorder.com/giveaway-heres-why-social-share-counters-suck-plus-what-i-can-give-you-that-doesnt/
Description: Displays simple sharing buttons.
Author: Karol K
Author URI: http://newinternetorder.com/
Version: 0.7.7
License: GPL2
*/

// this function does the magic
function sss_kk($content) {

	if(!is_single() || get_post_status() != 'publish')
		return $content;
	
	/////////////// params ////////////////////
	$sss_options = get_option('sss_plugin_options');
	if($show_where = $sss_options['show_where'])
		$show_where = stripslashes($show_where);
	else
		$show_where = 'both';
	if(!$exclude_ids = $sss_options['exclude_ids']) 
		$exclude_ids = array();
	///////////////////////////////////////////
	
	if(in_array(intval(get_the_ID()), $exclude_ids))
		return $content;
	
	$share_buttons = sss_kk_execute();

	if($show_where == 'bottom')
		return $content.$share_buttons;
	elseif($show_where == 'top')
		return $share_buttons.$content;
	else
		return $share_buttons.$content.$share_buttons;
}

// the actual execution
function sss_kk_execute() {
	
	/////////////// params ////////////////////
	$sss_options = get_option('sss_plugin_options');
	if($max_width = $sss_options['max_width'])
		$max_width = stripslashes($max_width).'px';
	else
		$max_width = '560px';
	if($start_showing_from = $sss_options['start_showing_from'])
		$start_showing_from = intval(stripslashes($start_showing_from));
	else
		$start_showing_from = 6;
	if($twitter_name = $sss_options['twitter_name'])
		$twitter_name = '%20by%20@'.stripslashes($twitter_name);
	else
		$twitter_name = '';
	if($story_source = $sss_options['story_source'])
		$story_source = stripslashes($story_source);
	else
		$story_source = get_bloginfo('name');
	if(!$which_buttons_arr = $sss_options['which_buttons'])
		$which_buttons_arr = array();
	///////////////////////////////////////////
	
	$s_number = intval(sss_kk_calculate());
	
	//get it from http://www.sharelinkgenerator.com/
	
	$twitter_button = '<a rel="nofollow" href="http://twitter.com/home?status='.str_replace(array('&amp; ', ' '), array('', '%20'), the_title_attribute('echo=0')).'%20'.get_permalink().$twitter_name.'" target="_blank" title="Tweet this"><img src="'.plugins_url('_.png', __FILE__).'" class="sss_twitter_button" /></a> ';
	
	$fb_button = '<a rel="nofollow" href="https://www.facebook.com/sharer/sharer.php?u='.get_permalink().'" title="Share this on Facebook" target="_blank"><img src="'.plugins_url('_.png', __FILE__).'" class="sss_fb_button" /></a> ';
	
	$gplus_button = '<a rel="nofollow" href="https://plus.google.com/share?url='.get_permalink().'" target="_blank" title="Share this on Google Plus"><img src="'.plugins_url('_.png', __FILE__).'" class="sss_gplus_button"  /></a> ';
	
	/*$post_excerpt = substr(urlencode(get_the_title()), 0, 256);
	if(has_excerpt()) $post_excerpt = substr(urlencode(get_the_excerpt()), 0, 256);*/
	$l_in_button = '<a rel="nofollow" href="http://www.linkedin.com/shareArticle?mini=true&url='.urlencode(get_permalink()).'&title=&summary=&source='.str_replace(' ', '%20', $story_source).'" target="_blank" title="Share this on LinkedIn"><img src="'.plugins_url('_.png', __FILE__).'" class="sss_l_in_button" /></a> ';
	
	$f_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'single-post-thumbnail');
	$pin_button = '';
	if(is_array($f_image)) {
		$pin_button = '<a rel="nofollow" href="//www.pinterest.com/pin/create/button/?url='.get_permalink().'&media='.$f_image[0].'&description='.str_replace(array('&amp; ', ' '), array('', '%20'), the_title_attribute('echo=0')).'" target="_blank" title="Share this on Pinterest"><img src="'.plugins_url('_.png', __FILE__).'" class="sss_pin_button" /></a> ';
	}
	
	//$email_button = '<a rel="nofollow" href="mailto:?subject=Article from '.str_replace(' ', '%20', $story_source).': '.str_replace(' ', '%20', get_the_title()).'&body=Hi,%0D%0A%0D%0AI found this article on '.str_replace(' ', '%20', $story_source).', and I thought you might like it:%0D%0A%0D%0A'.get_permalink().'%0D%0A%0D%0AEnjoy!" title="Share this via Email"><img src="'.plugins_url('_.png', __FILE__).'" class="sss_email_button" /></a> ';
	
	/////////////// buttons ////////////////////
	$share_buttons = '<div class="sss_kk_main" style="max-width: '.$max_width.';">';
	if($s_number >= $start_showing_from) {
		$share_buttons .= '<div class="sss_shares_block"><center><span class="sss_shares_number">'.$s_number.'</span><br /><strong>SHARES</strong></center></div> ';
	}
	$share_buttons .= '<img src="'.plugins_url('a2.png', __FILE__).'" class="sss_a_button" /> ';
	$share_buttons .= $fb_button;
	$share_buttons .= $twitter_button;
	if (in_array('googleplus', $which_buttons_arr))
		$share_buttons .= $gplus_button;
	if (in_array('linkedin', $which_buttons_arr))
		$share_buttons .= $l_in_button;
	if (in_array('pinterest', $which_buttons_arr)) 
		$share_buttons .= $pin_button;
	//$share_buttons .= $email_button;
	$share_buttons .= '</div>';
	////////////////////////////////////////////
	
	return $share_buttons;
}

function sss_kk_calculate() {
	$new_shares = 0;
	$real_shares_count = 0;
	
	if(function_exists('curl_version')) {
		$version = curl_version();
		$bitfields = array(
			'CURL_VERSION_IPV6', 
			'CURLOPT_IPRESOLVE'
		);

		foreach($bitfields as $feature) {
			if($version['features'] & constant($feature)) {
				$real_shares = new shareCount(get_permalink());
				
				$real_shares_count += $real_shares->get_tweets();
				$real_shares_count += $real_shares->get_fb();
				$real_shares_count += $real_shares->get_linkedin();
				$real_shares_count += $real_shares->get_plusones();
				$real_shares_count += $real_shares->get_pinterest();
				break;
			}
		}
	}
	
	$total_shares = $new_shares + $real_shares_count;
	
	return $total_shares;
}

// this functions adds the stylesheet to the head
function sss_kk_styles() {
	wp_register_style('doctypes_styles', plugins_url('kk-social-share-starter.css', __FILE__));
	wp_enqueue_style('doctypes_styles');
}

// adds our new option page to the admin menu
function modify_menu_for_sss() {
	add_submenu_page(
		'options-general.php',			//The new options page will be added as a sub menu to the Settings menu. 
		'Social Share Starter by KK',	//Page <title>
		'Social Share Starter by KK',	//Menu title
		'manage_options',				//Capability
		'sss-by-kk',					//Slug
		'sss_options'					//Function to call
	);  
}

// shows the option page
function sss_options () {
	echo '<div class="wrap"><h2>Social Share Starter by KK</h2>';
	if(isset($_POST['submit'])) {
		update_sss_options();
	}
	print_sss_form();
	echo '</div>';
}

// updates options if the submit button has been clicked
function update_sss_options() {
	$sss_options = get_option('sss_plugin_options');
	if(!$sss_options)
		$sss_options = array();
	
	if ($_POST['sss_set_max_width']) { 
		$safe_val_max_width = intval(addslashes(strip_tags($_POST['sss_set_max_width'])));
		$sss_options['max_width'] = $safe_val_max_width;
	}
	else
		$sss_options['max_width'] = 560;
	if ($_POST['sss_start_showing_from']) { 
		$safe_val_start_showing_from = intval(addslashes(strip_tags($_POST['sss_start_showing_from'])));
		$sss_options['start_showing_from'] = $safe_val_start_showing_from;
	}
	else
		$sss_options['start_showing_from'] = 6;
	if ($_POST['sss_twitter_name']) { 
		$safe_val_twitter_name = addslashes(strip_tags($_POST['sss_twitter_name']));
		$sss_options['twitter_name'] = $safe_val_twitter_name;
	}
	else
		$sss_options['twitter_name'] = '';
	if ($_POST['sss_story_source']) { 
		$safe_val_story_source = addslashes(strip_tags($_POST['sss_story_source']));
		$sss_options['story_source'] = $safe_val_story_source;
	}
	else
		$sss_options['story_source'] = get_bloginfo('name');
	if ($_POST['sss_show_where']) { 
		$safe_val_show_where = addslashes(strip_tags($_POST['sss_show_where']));
		$sss_options['show_where'] = $safe_val_show_where;
	}
	else
		$sss_options['show_where'] = 'both';
		
	// button selection settings
	if ($buttons_arr = $_POST['sss_which_buttons']) {
		$sss_options['which_buttons'] = array();
		foreach ($buttons_arr as $btn) {
			array_push($sss_options['which_buttons'], addslashes(strip_tags($btn)));
		}
	}
	else
		$sss_options['which_buttons'] = array();
	
	//excluding IDs
	if ($_POST['sss_exclude_ids']) { 
		$safe_val_exclude_ids = addslashes(strip_tags(preg_replace('/\s+/', '', $_POST['sss_exclude_ids'])));
		$sss_exclude_ids = array();
		$sss_exclude_ids_temp = explode(',', $safe_val_exclude_ids);
		foreach($sss_exclude_ids_temp as $single_id)
			if(strlen($single_id) > 0 && intval($single_id) > 0)
				array_push($sss_exclude_ids, intval($single_id));
		$sss_options['exclude_ids'] = array_unique($sss_exclude_ids);
	}
	else
		$sss_options['exclude_ids'] = array();
	
	if (update_option('sss_plugin_options', $sss_options)) {
		echo '<div id="message" class="updated fade">';
		echo '<p>Updated!</p>';
		echo '</div>';
	} /*else {
		echo '<div id="message" class="error fade">';
		echo '<p>Unable to update. My bad.</p>';
		echo '</div>';
	}*/
}

// prints the form that users will see
function print_sss_form() {
	$sss_options = get_option('sss_plugin_options');
	if($sss_max_width = $sss_options['max_width'])
		$sss_max_width = stripslashes($sss_max_width);
	else
		$sss_max_width = '560';
	if($sss_start_showing_from = $sss_options['start_showing_from'])
		$sss_start_showing_from = stripslashes($sss_start_showing_from);
	else
		$sss_start_showing_from = 6;
	if($sss_twitter_name = $sss_options['twitter_name'])
		$sss_twitter_name = stripslashes($sss_twitter_name);
	else
		$sss_twitter_name = '';
	if($sss_story_source = $sss_options['story_source'])
		$sss_story_source = stripslashes($sss_story_source);
	else
		$sss_story_source = get_bloginfo('name');
	if($sss_show_where = $sss_options['show_where'])
		$sss_show_where = stripslashes($sss_show_where);
	else
		$sss_show_where = 'both';
	if(!$sss_which_buttons_arr = $sss_options['which_buttons'])
		$sss_which_buttons_arr = array();
	if($sss_exclude_ids = $sss_options['exclude_ids']) {
		$sss_exclude_ids_temp = '';
		foreach ($sss_exclude_ids as $single_id)
			$sss_exclude_ids_temp .= $single_id.',';
		$sss_exclude_ids = $sss_exclude_ids_temp;
	}
	else
		$sss_exclude_ids = '';
	?>
<h3>Social media buttons for new and low traffic sites - simple - and easy to use.</h3>
<p>Here's what's waiting inside:
<ol>
	<li><strong>Cumulative counters with the possibility to set the minimal displayed number of shares.</strong></li>
	<li>Works right from the get-go (if you don't like playing with the settings).</li>
	<li>No fancy, time-consuming features, just the basic button and counter.</li>
	<li>Built-in time machine - meaning that it retrieves the numbers for posts published in the past.</li>
	<li><strong>Responsive and mobile-friendly.</strong></li>
	<li>Inherits your theme's fonts (looks like it's been custom-designed for you).</li>
	<li>Works on every browser.</li>
	<li>Light-weight.</li>
	<li>Retina-ready.</li>
	<li>Buttons for Facebook, Twitter, G+, LinkedIn, Pinterest.</li>
	<li>Shortcode - use <code>[sss_counters_here /]</code> to place the buttons wherever you wish.</li>
	<li><b>NEW</b>: Button selection feature.</li>
	<li><b>NEW</b>: You can now exclude the post IDs you don't want to show the buttons on.</li>
</ol>
</p>
<h3>Settings (all optional):</h3>
<form method="post">
<p>Minimal displayed number of shares: <input type="text" name="sss_start_showing_from" size="10" value="<?=$sss_start_showing_from?>" /></p>
<p>Maximum width of the whole share buttons block: <input type="text" name="sss_set_max_width" size="10" value="<?=$sss_max_width?>" />px</p>
<p>Your Twitter name: @<input type="text" name="sss_twitter_name" size="15" value="<?=$sss_twitter_name?>" /></p>
<p>The story source parameter for LinkedIn: <input type="text" name="sss_story_source" size="25" value="<?=$sss_story_source?>" /></p>
<div style="float: left; margin-right: 10px;">
<p>Where to display the buttons: </p>
</div>
<div style="float: left;">
<p><input type="radio" name="sss_show_where" id="sss_show_where_top" value="top" <?=($sss_show_where == 'top' ? 'checked' : '')?> /> <label for="sss_show_where_top">Above the post</label></p>
<p><input type="radio" name="sss_show_where" id="sss_show_where_both" value="both" <?=($sss_show_where == 'both' ? 'checked' : '')?> /> <label for="sss_show_where_both">Above + below the post</label></p>
<p><input type="radio" name="sss_show_where" id="sss_show_where_bottom" value="bottom" <?=($sss_show_where == 'bottom' ? 'checked' : '')?> /> <label for="sss_show_where_bottom">Below the post</label></p>
</div>
<div style="clear: both;"></div>
<div style="float: left; margin-right: 10px;">
<p>Buttons to use: </p>
</div>
<div style="float: left;">
<p><label><input type="checkbox" name="sss_which_buttons_fake" value="facebook" onclick="return false;" disabled="disabled" checked />Facebook</label></p>
<p><label><input type="checkbox" name="sss_which_buttons_fake" value="twitter" onclick="return false;" disabled="disabled" checked />Twitter</label></p>
<p><label><input type="checkbox" name="sss_which_buttons[]" value="googleplus" <?=(in_array('googleplus', $sss_which_buttons_arr) ? 'checked' : '')?> />Google +</label></p>
<p><label><input type="checkbox" name="sss_which_buttons[]" value="linkedin" <?=(in_array('linkedin', $sss_which_buttons_arr) ? 'checked' : '')?> />LinkedIn</label></p>
<p><label><input type="checkbox" name="sss_which_buttons[]" value="pinterest" <?=(in_array('pinterest', $sss_which_buttons_arr) ? 'checked' : '')?> />Pinterest</label> &nbsp; <em>(Note. the Pinterest button will only appear if there's a featured image assigned to the post.)</em></p>
</div>
<div style="clear: both; margin-top: 20px;">
<p>The post IDs you don't want to show the buttons on (comma-separated): <input type="text" name="sss_exclude_ids" size="50" value="<?=$sss_exclude_ids?>" /></p>
<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" /></p>
</div>
<h3>Lastly, don't forget to visit me at <a href="http://newinternetorder.com/" target="_blank">newinternetorder.com</a> - the place for online business advice for normal people.</h3>
<p style="text-align: center;"><a href="http://newinternetorder.com/about/" target="_blank"><img src="<?=plugins_url('manifesto2.png', __FILE__)?>" width="350" style="-o-box-shadow: 1px 1px 18px #888; -icab-box-shadow: 1px 1px 18px #888; -khtml-box-shadow: 1px 1px 18px #888; -moz-box-shadow: 1px 1px 18px #888; -webkit-box-shadow: 1px 1px 18px #888; box-shadow: 1px 1px 18px #888;" /></a></p>
</form>
<?php
}

//Uses a modified PHP Social Share Count Class by Sunny Verma http://toolspot.org
class shareCount {

	private $url,$timeout;

	function __construct($url,$timeout=10) {
		$this->url=rawurlencode($url);
		$this->timeout=$timeout;
	}

	function get_tweets() {
		$json_string = $this->file_get_contents_curl('http://urls.api.twitter.com/1/urls/count.json?url=' . $this->url);
		
		if($json_string === false) return 0;
		
		$json = json_decode($json_string, true);
		
		return isset($json['count'])?intval($json['count']):0;
	}

	function get_linkedin() {
		//$json_string = $this->file_get_contents_curl("http://www.linkedin.com/countserv/count/share?url=$this->url&format=json");
		$json_string = $this->file_get_contents_curl('http://www.linkedin.com/countserv/count/share?url='.$this->url.'&format=json');
		
		if($json_string === false) return 0;
		
		$json = json_decode($json_string, true);
		
		return isset($json['count'])?intval($json['count']):0;
	}

	function get_fb() {
		//$json_string = $this->file_get_contents_curl('http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$this->url);
		$json_string = $this->file_get_contents_curl('http://graph.facebook.com/?id='.$this->url);
		
		if($json_string === false) return 0;
		
		$json = json_decode($json_string, true);
		
		//return isset($json[0]['total_count'])?intval($json[0]['total_count']):0;
		return isset($json['shares'])?intval($json['shares']):0;
	}

	function get_plusones() {
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.rawurldecode($this->url).'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		
		$curl_results = curl_exec($curl);
		
		curl_close($curl);
		
		if($curl_results === false) return 0;
		
		$json = json_decode($curl_results, true);
		return isset($json[0]['result']['metadata']['globalCounts']['count'])?intval( $json[0]['result']['metadata']['globalCounts']['count'] ):0;
	}
	
	function get_pinterest() {
		$return_data = $this->file_get_contents_curl('http://api.pinterest.com/v1/urls/count.json?url='.$this->url);
		
		if($return_data === false) return 0;
		
		$json_string = preg_replace('/^receiveCount\((.*)\)$/', "\\1", $return_data);
		$json = json_decode($json_string, true);
		
		return isset($json['count'])?intval($json['count']):0;
	}

	private function file_get_contents_curl($url) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		//curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4'))
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
		$cont = curl_exec($ch);
		if(0 !== curl_errno($ch) || 200 !== curl_getinfo($ch, CURLINFO_HTTP_CODE))
			$cont = false;
		
		/*if(curl_errno($ch))
			die(curl_error($ch));*/
		
		curl_close($ch);
		return $cont;
	}
}

// SHORTCODE
function sss_kk_shortcode_handler($atts, $content=null) {
	return sss_kk_execute();
}
add_shortcode('sss_counters_here', 'sss_kk_shortcode_handler');

// HOOKS =============

add_action('admin_menu', 'modify_menu_for_sss');
add_filter('the_content', 'sss_kk');
add_action('wp_enqueue_scripts', 'sss_kk_styles');
