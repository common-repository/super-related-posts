<?php
/*
Plugin Name: Super Related Posts
Description: Add a highly configurable list of related posts to any posts. Related posts can be based on any combination of word usage in the content, title, or tags.
Version: 1.7
Text Domain: super-related-posts
Author: Magazine3
Author URI: https://magazine3.company/
Donate link: https://www.paypal.me/Kaludi/25
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'SRPP_VERSION' ) ) {
    define( 'SRPP_VERSION', '1.7' );
}

define('SRPP_DIR_NAME', dirname( __FILE__ ));
define('SRPP_PLUGIN_URI', plugin_dir_url(__FILE__));

if (!defined('SRPP_LIBRARY')) require(SRPP_DIR_NAME.'/includes/common_functions.php');
if (!defined('SRPP_OT_LIBRARY')) require(SRPP_DIR_NAME.'/includes/output_tags.php');
if (!defined('SRPP_ASRP_LIBRARY')) require(SRPP_DIR_NAME.'/admin/admin_common_functions.php');
if (!defined('SRPP_ADMIN_SUBPAGES_LIBRARY')) require(SRPP_DIR_NAME.'/admin/admin-subpages.php');
require_once SRPP_DIR_NAME.'/admin/related-post-widget.php';
require_once SRPP_DIR_NAME.'/includes/elementor/widget.php';
require_once SRPP_DIR_NAME.'/includes/gutenberg/includes/class-gutenberg.php';

$sprp_current_ID = -1;

class SuperRelatedPosts {
  static $version = 0;

  static function get_plugin_version() {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
    SuperRelatedPosts::$version = $plugin_data['version'];

    return $plugin_data['version'];
  } // get_plugin_version

  // check if plugin's admin page is shown
  static function is_plugin_admin_page($page = 'settings') {
    $current_screen = get_current_screen();

    if ($page == 'settings' && $current_screen->id == 'settings_page_super-related-posts') {
      return true;
    }

    return false;
  } // is_plugin_admin_page

  // add settings link to plugins page
  static function plugin_action_links($links) {
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=super-related-posts')) . '" title="Settings for Super Related Posts">'.esc_html__( 'Settings' , 'super-related-posts').'</a>';

    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


	static function execute($args='', $default_output_template='<li>{link}</li>', $option_key='super-related-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID, $srp_execute_sql_1, $srp_execute_result;
		$start_time = srpp_microtime();
											
		$postid = srpp_current_post_id($sprp_current_ID);
		
		$cache_key = $option_key.$postid.$args.'re1';
		$result = srpp_cache_fetch($postid, $cache_key);
		if ($result !== false) {
			return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (srpp_microtime() - $start_time));
		}

		$table_name = $table_prefix . 'super_related_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = srpp_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srpp_set_options($option_key, $options, $default_output_template);
		
		if (0 < $options['limit']) {
			$match_tags = ($options['match_tags'] !== 'false' && $wp_version >= 2.3);			
			$match_category = ($options['match_cat'] === 'true');
			$sort_by       = $options['sort_by_1'];		
			$check_age = 1;
			if(isset($options['age1']) && isset($options['age1']['direction'])){	
				$check_age = ('none' !== $options['age1']['direction']);	
			}					
			$des = isset($options['re_design_1']) ? $options['re_design_1'] : 'd1';

									
			$join   = "INNER JOIN `$table_name` sp ON p.ID=sp.pID ";
				
			$category = "category";	$yoast_wpseo_primary = "_yoast_wpseo_primary_category";
			$cat_ids = $tag_ids = array();
			if ($match_category){
				$cat_ids = srpp_where_match_category();
				$current_post_type = get_post_type();
				switch ($current_post_type){
					case 'product':
							$category = 'product_cat';
							$yoast_wpseo_primary = '_yoast_wpseo_primary_'.$category;
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category);
						break;
					case 'al_product':
							$category = 'al_product-cat';
							$yoast_wpseo_primary = '_yoast_wpseo_primary_'.$category;
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category);
						break;
					default:
							$category = 'category';
				}
								
			}	
			if ($match_tags){			
				$tag_ids     = srpp_where_match_tags();				
			}
						
			if($cat_ids){	
				$is_primary = false;
				$cat_sql = $cat_ids[0];
				if(count($cat_ids) > 1){
					foreach($cat_ids as $cat_id){
						if( get_post_meta($postid, $yoast_wpseo_primary,true) == $cat_id ) {
						$cat_sql = $cat_id;
						$is_primary = true;
						break;
						}
					}
				}
				
				if($is_primary){
					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$wp_post_meta = $table_prefix.'postmeta';
					$join   .= $wpdb->prepare(
						"inner join $wp_post_meta pm on pm.post_id = p.ID
						inner join $wp_term_re tt on tt.object_id = p.ID
						inner join $wp_term_taxo tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join $wp_terms te on  tte.term_id = te.term_id
						and tte.taxonomy = '".$category."' and pm.meta_key = '".$yoast_wpseo_primary."'
						and pm.meta_value = %d
						and te.term_id = %d ",
						$cat_sql,
						$cat_sql
					);
				}else{
					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$join   .= $wpdb->prepare(
						"inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join `$wp_terms` te on  tte.term_id = te.term_id
						and tte.taxonomy = '".$category."'
						and te.term_id = %d ",
						$cat_sql
					);		
				}
			}

			if($tag_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$join   .= $wpdb->prepare(
					   "inner join `$wp_term_re` tr on tr.object_id = p.ID
						inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
						inner join `$wp_term_taxo` tts on tts.term_taxonomy_id = t.term_id			
						and tts.taxonomy = 'post_tag'
						and tr.term_taxonomy_id = %d",
					 	$tag_ids[0]
			 );		
				
			}
			$where = $wpdb->prepare("p.post_status = %s", 'publish');
			if($sort_by == 'recent'){
				$orderby = $wpdb->prepare(" ORDER BY id DESC LIMIT 0, %d", $options['limit']);								
			}else{
				if ($check_age) {
					$today = date_create(date('Y-m-d'));
					$age = date_sub($today,date_interval_create_from_date_string($options['age1']['length']." ".$options['age1']['duration']));
					$age = $age->format('Ymd');			
					if($options['age1']['direction'] === 'before'){
						$where .= $wpdb->prepare( " AND (sp.spost_date <= %d)",  $age );
					}else{
						$where .= $wpdb->prepare( " AND (sp.spost_date >= %d)",  $age );
					}
				}
				$orderby = $wpdb->prepare(" ORDER BY sp.views DESC LIMIT 0, %d", $options['limit']);				
				
			}		
			

			$cpost_id 		   = get_the_ID();

			$current_post_type = get_post_type();
			if(strpos($current_post_type, 'product') !== false || strpos($current_post_type, 'al_product') !== false){
				$where .= $wpdb->prepare( " AND p.post_type = %s",  $current_post_type );
			}
						
			$options_length = get_option('super-related-posts');
			$post_excerpt = $options_length['post_excerpt'];
			if($post_excerpt === 'true'){
				$excerpt_length = $options_length['excerpt_length_1'] ? $options_length['excerpt_length_1'] : '0';
				$sql = "SELECT ID, post_title, substring(`post_excerpt`, 1, $excerpt_length) as `post_excerpt`  FROM `$wpdb->posts` p $join WHERE $where $orderby";			
			}else{
				$sql = "SELECT ID, post_title FROM `$wpdb->posts` p $join WHERE $where $orderby";			
			}
			$srp_execute_sql_1 = $sql;	
			
			$results = array();
			$fetch_result = $wpdb->get_results($sql);

			if(!empty($fetch_result)){
				foreach ($fetch_result as $value) {					
					if($value->ID == $cpost_id) {
						continue;
					}
					$results[] = $value;
				}
			}						
			$srp_execute_result = $results;			
		} else {
			$results = false;
		}
		$allowed_html = srpp_expanded_allowed_tags();

	    if ($results) {
			
			$translations = srpp_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = srpp_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = srpp_sort_items($options['sort'], $results, $option_key, $items);
			$opt_divider = isset($options['divider'])?$options['divider']:"\n";
			$output = implode($opt_divider, $items);
			//Output
			//Output escaping is done below before rendering html
			$output = '<div class="sprp '.esc_attr($des).'"><h4>'.esc_html__(srpwp_label_text('translation-related-content') , 'super-related-posts').'</h4><ul>' . wp_kses($output, $allowed_html) . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = srpp_prepare_template($options['none_text']);
				$output = $options['prefix'] . srpp_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
	
		if($output){			
			$output       = wp_kses($output, $allowed_html);
			srpp_cache_store($postid, $cache_key, $output);
		}
		
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (srpp_microtime() - $start_time)) : '';
	}

	static function execute2($args='', $default_output_template='<li>{link}</li>', $option_key='super-related-posts'){
		
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID, $srp_execute_sql_1, $srp_execute_sql_2, $srp_execute_result;
		$start_time = srpp_microtime();
		$postid = srpp_current_post_id($sprp_current_ID);	

		$cache_key = $option_key.$postid.$args.'re2';
		$result = srpp_cache_fetch($postid, $cache_key);
		if ($result !== false) {
			return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (srpp_microtime() - $start_time));
		}

		$table_name = $table_prefix . 'super_related_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = srpp_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srpp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit_2']) {
			$match_tags = ($options['match_tags_2'] !== 'false' && $wp_version >= 2.3);			
			$match_category = ($options['match_cat_2'] === 'true');
			$sort_by       = $options['sort_by_2'];				
			$use_tag_str = ('' != trim($options['tag_str_2']) && $wp_version >= 2.3);
			$check_age = 1;
			if(isset($options['age2']) && isset($options['age2']['direction'])){
				$check_age = ('none' !== $options['age2']['direction']);
			}
			$check_custom = (trim($options['custom']['key']) !== '');
			$limit = '0'.', '.$options['limit_2'];
			$des = isset($options['re_design_2']) ? $options['re_design_2'] : 'd1';

			
			$join   = "INNER JOIN `$table_name` sp ON p.ID=sp.pID ";

			$category = "category";	$yoast_wpseo_primary = "_yoast_wpseo_primary_category";
			$cat_ids = $tag_ids = array();
			if ($match_category){
				$cat_ids = srpp_where_match_category();
				$current_post_type = get_post_type();
				switch ($current_post_type){
					case 'product':
							$category = 'product_cat';
							$yoast_wpseo_primary = '_yoast_wpseo_primary_'.$category;
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category);
						break;
					case 'al_product':
							$category = 'al_product-cat';
							$yoast_wpseo_primary = '_yoast_wpseo_primary_'.$category;
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category);
						break;
					default:
							$category = 'category';
				}
			}	
			if ($match_tags){			
				$tag_ids     = srpp_where_match_tags();				
			}
			
			
			if($cat_ids){	
				$is_primary = false;
				$cat_sql = $cat_ids[0];
				if(count($cat_ids) > 1){
					foreach($cat_ids as $cat_id){
						if( get_post_meta($postid, $yoast_wpseo_primary,true) == $cat_id ) {
						$cat_sql = $cat_id;
						$is_primary = true;
						break;
						}
					}
				}

				if($is_primary){
					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$wp_post_meta = $table_prefix.'postmeta';
					$join   .= $wpdb->prepare(
						"inner join $wp_post_meta pm on pm.post_id = p.ID
						inner join $wp_term_re tt on tt.object_id = p.ID
						inner join $wp_term_taxo tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join $wp_terms te on  tte.term_id = te.term_id
						and tte.taxonomy = '".$category."' and pm.meta_key = '".$yoast_wpseo_primary."'
						and pm.meta_value = %d
						and te.term_id = %d ",
						$cat_sql,
						$cat_sql
					);
				}else{
					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$join   .= $wpdb->prepare(
						"inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join `$wp_terms` te on  tte.term_id = te.term_id
						and tte.taxonomy = '".$category."'
						and te.term_id = %d ",
						$cat_sql
					);		
				}	
			}

			if($tag_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$join   .= $wpdb->prepare(
					   "inner join `$wp_term_re` tr on tr.object_id = p.ID
						inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
						inner join `$wp_term_taxo` tts on tts.term_taxonomy_id = t.term_id			
						and tts.taxonomy = 'post_tag'
						and tr.term_taxonomy_id = %d",
					 	$tag_ids[0]
					);				
			}

			$where = $wpdb->prepare("p.post_status = %s", 'publish');
			$limit = " LIMIT 0, 5";

			if($sort_by == 'recent'){
				$orderby = $wpdb->prepare(" ORDER BY id DESC ");								
			}else{
				if ($check_age) {		
					$today = date_create(date('Y-m-d'));
					$age = date_sub($today,date_interval_create_from_date_string($options['age1']['length']." ".$options['age1']['duration']));
					$age = $age->format('Ymd');			
					if($options['age1']['direction'] === 'before'){
						$where .= $wpdb->prepare( " AND (sp.spost_date <= %d)",  $age );
					}else{
						$where .= $wpdb->prepare( " AND (sp.spost_date >= %d)",  $age );
					}
				}
				$orderby = $wpdb->prepare(" ORDER BY sp.views DESC");				
				
			}					
							
			$cpost_id 		   = get_the_ID();
			
			$current_post_type = get_post_type();
			if(strpos($current_post_type, 'product') !== false || strpos($current_post_type, 'al_product') !== false){
				$where .= $wpdb->prepare( " AND p.post_type = %s",  $current_post_type );
			}		
			
			$options_length = get_option('super-related-posts');
			$post_excerpt = $options_length['post_excerpt_2'];
			if($post_excerpt === 'true'){
				$excerpt_length = $options_length['excerpt_length_2'] ? $options_length['excerpt_length_2'] : '0';
				$sql = "SELECT ID, post_title, substring(`post_excerpt`, 1, $excerpt_length) as `post_excerpt` FROM `$wpdb->posts` p $join WHERE $where $orderby $limit";				
			}else{
				$sql = "SELECT ID, post_title FROM `$wpdb->posts` p $join WHERE $where $orderby $limit";			
			}

			if($srp_execute_sql_1 === $sql){				
				$sql =  strstr($sql, 'LIMIT', true);
				$sql.= $wpdb->prepare("LIMIT %d, %d", ($options['limit']+1), $options['limit_2']);
			}
			
			$srp_execute_sql_2 = $sql;					
			$results = array();
			$fetch_result = $wpdb->get_results($sql);
			if(!empty($fetch_result)){
				foreach ($fetch_result as $value) {					
					if($value->ID == $cpost_id) {
						continue;
					}
					$results[] = $value;
				}
			}
						
			
		} else {
			$results = false;
		}
		$allowed_html = srpp_expanded_allowed_tags();
	    if ($results) {
			$translations = srpp_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = srpp_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = srpp_sort_items($options['sort'], $results, $option_key, $items);
			$opt_divider = isset($options['divider'])?$options['divider']:"\n";
			$output = implode($opt_divider, $items);
			//Output
			//Output escaping is done below before rendering html
			$output = '<div class="sprp '.esc_attr($des).'"><h4>'.esc_html__(srpwp_label_text('translation-related-content') , 'super-related-posts').'</h4><ul>' . wp_kses($output, $allowed_html) . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = srpp_prepare_template($options['none_text']);
				$output = $options['prefix'] . srpp_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		if($output){			
			$output       = wp_kses($output, $allowed_html);
			srpp_cache_store($postid, $cache_key, $output);
		}		
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (srpp_microtime() - $start_time)) : '';
	}

	static function execute3($args='', $default_output_template='<li>{link}</li>', $option_key='super-related-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID, $srp_execute_sql_1, $srp_execute_sql_2, $srp_execute_sql_3, $srp_execute_result;
		$start_time = srpp_microtime();		
		$postid = srpp_current_post_id($sprp_current_ID);

		$cache_key = $option_key.$postid.$args.'re3';
		$result = srpp_cache_fetch($postid, $cache_key);
		if ($result !== false)
		{
			return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (srpp_microtime() - $start_time));
		}
		
		$table_name = $table_prefix . 'super_related_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = srpp_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srpp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit_3']) {
			$match_tags = ($options['match_tags_3'] !== 'false' && $wp_version >= 2.3);			
			$sort_by       = $options['sort_by_3'];
			$match_category = ($options['match_cat_3'] === 'true');			
			$check_age = ('none' !== $options['age3']['direction']);			
			$limit = '0'.', '.$options['limit_3'];
			$des = isset($options['re_design_3']) ? $options['re_design_3'] : 'd1';

			
			$join   = "INNER JOIN `$table_name` sp ON p.ID=sp.pID ";
			
			$category = "category";	$yoast_wpseo_primary = "_yoast_wpseo_primary_category";
			$cat_ids = $tag_ids = array();
			if ($match_category){
				$cat_ids = srpp_where_match_category();
				$current_post_type = get_post_type();
				switch ($current_post_type){
					case 'product':
							$category = 'product_cat';
							$yoast_wpseo_primary = '_yoast_wpseo_primary_'.$category;
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category);
						break;
					case 'al_product':
							$category = 'al_product-cat';
							$yoast_wpseo_primary = '_yoast_wpseo_primary_'.$category;
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category);
						break;
					default:
							$category = 'category';
				}
								
			}	
			if ($match_tags){			
				$tag_ids     = srpp_where_match_tags();				
			}
			
			
			if($cat_ids){	
				$is_primary = false;
				$cat_sql = $cat_ids[0];
				if(count($cat_ids) > 1){
					foreach($cat_ids as $cat_id){
						if( get_post_meta($postid, $yoast_wpseo_primary,true) == $cat_id ) {
						$cat_sql = $cat_id;
						$is_primary = true;
						break;
						}
					}
				}

				if($is_primary){
					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$wp_post_meta = $table_prefix.'postmeta';
					$join   .= $wpdb->prepare(
						"inner join $wp_post_meta pm on pm.post_id = p.ID
						inner join $wp_term_re tt on tt.object_id = p.ID
						inner join $wp_term_taxo tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join $wp_terms te on  tte.term_id = te.term_id
						and tte.taxonomy = '".$category."' and pm.meta_key = '".$yoast_wpseo_primary."'
						and pm.meta_value = %d
						and te.term_id = %d ",
						$cat_sql,
						$cat_sql
					);
				}else{
					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$join   .= $wpdb->prepare(
						"inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join `$wp_terms` te on  tte.term_id = te.term_id
						and tte.taxonomy = '".$category."'
						and te.term_id = %d ",
						$cat_sql
					);		
				}	
			}

			if($tag_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$join   .= $wpdb->prepare(
					   "inner join `$wp_term_re` tr on tr.object_id = p.ID
						inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
						inner join `$wp_term_taxo` tts on tts.term_taxonomy_id = t.term_id			
						and tts.taxonomy = 'post_tag'
						and tr.term_taxonomy_id = %d",
					 	$tag_ids[0]
			 );	
			}
			
			$where = $wpdb->prepare("p.post_status = %s", 'publish');
			$limit = " LIMIT 0, 5";

			if($sort_by == 'recent'){
				$orderby = $wpdb->prepare(" ORDER BY id DESC ");								
			}else{
				if ($check_age) {		
					$today = date_create(date('Y-m-d'));
					$age = date_sub($today,date_interval_create_from_date_string($options['age1']['length']." ".$options['age1']['duration']));
					$age = $age->format('Ymd');			
					if($options['age1']['direction'] === 'before'){
						$where .= $wpdb->prepare( " AND (sp.spost_date <= %d)",  $age );
					}else{
						$where .= $wpdb->prepare( " AND (sp.spost_date >= %d)",  $age );
					}
				}
				$orderby = $wpdb->prepare(" ORDER BY sp.views DESC");				
				
			}				
							
			$cpost_id 		   = get_the_ID();		
			
			$current_post_type = get_post_type();
			if(strpos($current_post_type, 'product') !== false || strpos($current_post_type, 'al_product') !== false){
				$where .= $wpdb->prepare( " AND p.post_type = %s",  $current_post_type );
			}

			$options_length = get_option('super-related-posts');
			$post_excerpt = $options_length['post_excerpt_3'];
			if($post_excerpt === 'true'){
				$excerpt_length = $options_length['excerpt_length_3'] ? $options_length['excerpt_length_3'] : '0';
				$sql = "SELECT ID, post_title, substring(`post_excerpt`, 1, $excerpt_length) as `post_excerpt` FROM `$wpdb->posts` p $join WHERE $where $orderby $limit";				
			}else{
				$sql = "SELECT ID, post_title FROM `$wpdb->posts` p $join WHERE $where $orderby $limit";
			}


			if($sql === $srp_execute_sql_1 || $sql === $srp_execute_sql_2){							
				$sql =  strstr($sql, 'LIMIT', true);
				$sql.= $wpdb->prepare("LIMIT %d, %d", ($options['limit'] + $options['limit_2'] + 1), $options['limit_3'] );				
			}
			$srp_execute_sql_3 = $sql;			
			$results = array();
			$fetch_result = $wpdb->get_results($sql);
			if(!empty($fetch_result)){
				foreach ($fetch_result as $value) {					
					if($value->ID == $cpost_id) {
						continue;
					}
					$results[] = $value;
				}
			}			
			
			$srp_execute_result = $results;			
						
		} else {
			$results = false;
		}
		$allowed_html = srpp_expanded_allowed_tags();
	    if ($results) {
			$translations = srpp_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = srpp_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = srpp_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			//Output escaping is done below before rendering html
			$output = '<div class="sprp '.esc_attr($des).'"><h4>'.esc_html__(srpwp_label_text('translation-related-content') , 'super-related-posts').'</h4><ul>' . wp_kses($output, $allowed_html) . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if (isset($options['no_text']) && $options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$none_text = '';
				if(isset($options['none_text'])){
					$none_text = $options['none_text'];
				}
				$translations = srpp_prepare_template($none_text);
				$opt_suffix = isset($options['suffix'])?$options['suffix']:'';
				$output = $opt_suffix . srpp_expand_template(array(), isset($options['none_text'])?$options['none_text']:'', $translations, $option_key) . $opt_suffix;
			}
		}
		if($output){			
			$output       = wp_kses($output, $allowed_html);
			srpp_cache_store($postid,$cache_key, $output);
		}
				
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (srpp_microtime() - $start_time)) : '';
	}

  // save some info
  static function activate() {
    $options = get_option('super_related_posts_meta', array());

    if (empty($options['first_version'])) {
      $options['first_version'] = SuperRelatedPosts::get_plugin_version();
      $options['first_install'] = current_time('timestamp');
      update_option('super_related_posts_meta', $options);
    }
  } // activate

} // SuperRelatedPosts class



function srpp_save_index_entry($postID) {

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'super_related_posts';
	
	$post = $wpdb->get_row(
		$wpdb->prepare("SELECT post_content, post_date, post_title, post_type, post_status 
						FROM $wpdb->posts WHERE ID = %d", $postID), ARRAY_A);

	if ($post['post_type'] === 'revision'
		|| $post['post_type'] === 'wp_global_styles'
		|| $post['post_type'] === 'attachment'
		|| $post['post_type'] === 'elementor_library'
		|| $post['post_type'] === 'mgmlp_media_folder'
		|| $post['post_type'] === 'custom_css'
		|| $post['post_type'] === 'nav_menu_item'
		|| $post['post_type'] === 'oembed_cache'
		){
	
		return $postID;
	} 
	//extract its terms
	$options = get_option('super-related-posts');
	$utf8 = (isset($options['utf8']) && $options['utf8'] === 'true');
	$cjk = (isset($options['cjk']) && $options['cjk'] === 'true');
	$use_stemmer = '';
	if(isset($options['use_stemmer'])){
		$use_stemmer = $options['use_stemmer'];
	}	
	$title = srpp_get_title_terms($post['post_title'], $utf8, $use_stemmer, $cjk);
	$tags = srpp_get_tag_terms($postID, $utf8);
	$sdate  = date("Ymd",strtotime($post['post_date']));	
	//check to see if the field is set
	$pid = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT pID FROM $table_name WHERE pID=%d limit 1", 
			$postID
		)		
	);	
	//then insert if empty
	if($post['post_status'] == 'publish'){

		if (is_null($pid)) {
			
			$wpdb->insert( 
				$table_name, 
				array(
					'pID'          => $postID,  
					'title'        => $title,  
					'tags'         => $tags, 
					'spost_date'   => $sdate, 					
				), 
				array('%d','%s', '%s', '%s') 
			);
			update_option('srp_posts_reset_status', 'continue');
		} else {
			$wpdb->query(
				$wpdb->prepare("UPDATE $table_name SET title=%s, tags=%s, spost_date=%s WHERE pID=%d",
				$title, $tags, $sdate, $postID
				)				
			);
		}
	}else{		
		$wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE pID = %d", $postID));
	}
	
	return $postID;
}

function srpp_delete_index_entry($postID) {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'super_related_posts';
	$wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE pID = %d", $postID));	
	return $postID;
}

function srpp_clean_words($text) {
	$text = strip_tags($text);
	$text = strtolower($text);
	$text = str_replace("’", "'", $text); // convert MSWord apostrophe
	$text = preg_replace(array('/\[(.*?)\]/', '/&[^\s;]+;/', '/‘|’|—|“|”|–|…/', "/'\W/"), ' ', $text); //anything in [..] or any entities or MS Word droppings
	return $text;
}

function srpp_mb_clean_words($text) {
	mb_regex_encoding('UTF-8');
	mb_internal_encoding('UTF-8');
	$text = strip_tags($text);
	$text = mb_strtolower($text);
	$text = str_replace("’", "'", $text); // convert MSWord apostrophe
	$text = preg_replace(array('/\[(.*?)\]/u', '/&[^\s;]+;/u', '/‘|’|—|“|”|–|…/u', "/'\W/u"), ' ', $text); //anything in [..] or any entities
	return 	$text;
}

function srpp_mb_str_pad($text, $n, $c) {
	mb_internal_encoding('UTF-8');
	$l = mb_strlen($text);
	if ($l > 0 && $l < $n) {
		$text .= str_repeat($c, $n-$l);
	}
	return $text;
}

function srpp_cjk_digrams($string) {
	mb_internal_encoding("UTF-8");
    $strlen = mb_strlen($string);
	$ascii = '';
	$prev = '';
	$result = array();
	for ($i = 0; $i < $strlen; $i++) {
		$c = mb_substr($string, $i, 1);
		// single-byte chars get combined
		if (strlen($c) > 1) {
			if ($ascii) {
				$result[] = $ascii;
				$ascii = '';
				$prev = $c;
			} else {
				$result[] = srpp_mb_str_pad($prev.$c, 4, '_');
				$prev = $c;
			}
		} else {
			$ascii .= $c;
		}
    }
	if ($ascii) $result[] = $ascii;
    return implode(' ', $result);
}

$tinywords = array('the' => 1, 'and' => 1, 'of' => 1, 'a' => 1, 'for' => 1, 'on' => 1);

function srpp_get_title_terms($text, $utf8, $use_stemmer, $cjk) {
	global $tinywords;
	if ($utf8) {
		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');
		$wordlist = mb_split("\W+", srpp_mb_clean_words($text));
		$words = '';
		foreach ($wordlist as $word) {
			if (!isset($tinywords[$word])) {
				switch ($use_stemmer) {
				case 'true':
					$words .= srpp_mb_str_pad(stem($word), 4, '_') . ' ';
					break;
				case 'fuzzy':
					$words .= srpp_mb_str_pad(metaphone($word), 4, '_') . ' ';
					break;
				case 'false':
				default:
					$words .= srpp_mb_str_pad($word, 4, '_') . ' ';
				}
			}
		}
	} else {
		$wordlist = str_word_count(srpp_clean_words($text), 1);
		$words = '';
		foreach ($wordlist as $word) {
			if (!isset($tinywords[$word])) {
				switch ($use_stemmer) {
				case 'true':
					$words .= str_pad(stem($word), 4, '_') . ' ';
					break;
				case 'fuzzy':
					$words .= str_pad(metaphone($word), 4, '_') . ' ';
					break;
				case 'false':
				default:
					$words .= str_pad($word, 4, '_') . ' ';
				}
			}
		}
	}
	if ($cjk) $words = srpp_cjk_digrams($words);
	return $words;
}

function srpp_get_tag_terms($ID, $utf8) {
	global $wpdb;
	if (!function_exists('get_object_term_cache')) return '';
	$tags = array();
	
	$tags = $wpdb->get_col($wpdb->prepare(
			"SELECT t.name FROM $wpdb->terms AS t INNER JOIN 
			$wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id 
			INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id 
			WHERE tt.taxonomy = 'post_tag' AND tr.object_id = %d"
		),
		$ID
	);
	if (!empty ($tags)) {
		if ($utf8) {
			mb_internal_encoding('UTF-8');
			foreach ($tags as $tag) {
				$newtags[] = srpp_mb_str_pad(mb_strtolower(str_replace('"', "'", $tag)), 4, '_');
			}
		} else {
			foreach ($tags as $tag) {
				$newtags[] = str_pad(strtolower(str_replace('"', "'", $tag)), 4, '_');
			}
		}
		$newtags = str_replace(' ', '_', $newtags);
		$tags = implode (' ', $newtags);
	} else {
		$tags = '';
	}
	return $tags;
}

if ( is_admin() ) {
	require(SRPP_DIR_NAME.'/admin/super-related-posts-admin.php');
	require(SRPP_DIR_NAME.'/includes/helper-function.php');
	require_once( SRPP_DIR_NAME . '/admin/newsletter.php' );

}

global $translation_panel_options;
$translation_panel_options = array(
	'translation-related-content' => 'Related Content'
);

global $overusedwords;
if(is_array($overusedwords)) {
	$overusedwords = array_flip($overusedwords);
}

function srpp_wp_admin_style() {
  if (SuperRelatedPosts::is_plugin_admin_page('settings')) {
        wp_register_style( 'super-related-posts-admin', plugins_url('', __FILE__) . '/css/super-related-posts-admin.css', false, SuperRelatedPosts::$version );
        wp_enqueue_style( 'super-related-posts-admin' );
  }
}

function srpp_init_start () {
	global $wp_db_version;
	load_plugin_textdomain('super_related_posts');

  	SuperRelatedPosts::get_plugin_version();

	$options = get_option('super-related-posts');
	if (isset($options['content_filter']) && $options['content_filter'] === 'true' && function_exists('srpp_register_content_filter')) srpp_register_content_filter('SuperRelatedPosts');
	$condition = 'true';
	$condition = (stristr($condition, "return")) ? $condition : "return ".$condition;
	$condition = rtrim($condition, '; ') . ';';

	srpp_register_post_filter('append', 'super-related-posts', 'SuperRelatedPosts', $condition);
	
	srpp_register_post_filter_2('append', 'super-related-posts', 'SuperRelatedPosts', $condition);
	
	srpp_register_post_filter_3('append', 'super-related-posts', 'SuperRelatedPosts', $condition);

	//install the actions to keep the index up to date
	add_action('save_post', 'srpp_save_index_entry', 1);
	add_action('delete_post', 'srpp_delete_index_entry', 1);		
	add_action( 'admin_enqueue_scripts', 'srpp_wp_admin_style' );

  // aditional links in plugin description
  add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__),
             array('SuperRelatedPosts', 'plugin_action_links'));
} // init

add_action ('init', 'srpp_init_start', 1);
register_activation_hook(__FILE__, array('SuperRelatedPosts', 'activate'));

register_uninstall_hook( __FILE__, 'srpwp_on_uninstall' );

add_action('wp_enqueue_scripts', 'sprp_front_css_and_js');

function sprp_front_css_and_js(){

	wp_register_style( 'super-related-posts', plugins_url('', __FILE__) . '/css/super-related-posts.css', false, SuperRelatedPosts::$version );
	wp_enqueue_style( 'super-related-posts' );

	$local = array(     		   
		'ajax_url'                     => admin_url( 'admin-ajax.php' ),            
		'srp_security_nonce'           => wp_create_nonce('srp_ajax_check_nonce'),
		'post_id'                      => get_the_ID()
	);            

	$local = apply_filters('srp_front_data',$local,'srp_localize_front_data');

	wp_register_script( 'srp-front-js', SRPP_PLUGIN_URI . 'js/srp.js', array('jquery'), SuperRelatedPosts::$version , true );                        
	wp_localize_script( 'srp-front-js', 'srp_localize_front_data', $local );        
	wp_enqueue_script( 'srp-front-js');
}

add_action( 'wp_ajax_nopriv_srp_update_post_views_ajax', 'srp_update_post_views_via_ajax');  
add_action( 'wp_ajax_srp_update_post_views_ajax', 'srp_update_post_views_via_ajax') ;  

function srp_update_post_views_via_ajax(){

	 if ( ! isset( $_POST['srp_security_nonce'] ) ){
		return;
	 }
	 
	 if ( !wp_verify_nonce( $_POST['srp_security_nonce'], 'srp_ajax_check_nonce' ) ){
		return;
	 }
   
	if(isset($_POST['post_id'])){

	   $post_id = intval($_POST['post_id']);	   

	   try{
    
		global $wpdb;

		$count = $wpdb->get_var($wpdb->prepare( "SELECT views FROM {$wpdb->prefix}super_related_posts WHERE pID = %d ", $post_id) );
		$count++;	
		$wpdb->query($wpdb->prepare(
			"UPDATE {$wpdb->prefix}super_related_posts SET `views` = %d WHERE (`pID` = %d)",
			$count,
			$post_id			
		));				
		if($wpdb->last_error){            
			echo json_encode(array('status' => 'error', 'message' => $wpdb->last_error));
		}else{
			echo json_encode(array('status' => 'Post Views Updated'));            
		}
		
		} catch (\Exception $ex) {
			echo json_encode(array('status' => 'error', 'message' => $ex->getMessage()));			
		}

	}
	
	wp_die();
					
}

add_action( 'wp_ajax_nopriv_srp_load_related_content', 'srp_load_related_content');  
add_action( 'wp_ajax_srp_load_related_content', 'srp_load_related_content') ;
function srp_load_related_content()
{
	if ( ! isset( $_POST['srp_security_nonce'] ) ){
		return;
	}
	 
	if ( !wp_verify_nonce( $_POST['srp_security_nonce'], 'srp_ajax_check_nonce' ) ){
		return;
	}

	if(!isset($_POST['post_id']) || (isset($_POST['post_id']) && empty($_POST['post_id']))){
		return;
	}
	$response_data = array();
	$response_data['post_content'] = '';
	$response_data['offset'] = '';

	$post_id = intval($_POST['post_id']);

		if(get_post_type($post_id) == 'post'){
			global $table_prefix, $wpdb;
			$srp_option_data = get_option('srp_data');
			if(isset($srp_option_data['srpwp_infinite_scrolling'])){

				$table_name = $table_prefix . 'super_related_posts';

				$offset = 0;
				if(isset($_POST['offset']) && $_POST['offset'] > 0){
					$offset = intval($_POST['offset']);	
				}else{
					update_option('srp_post_offset', $offset);
				}

				$join   = "INNER JOIN `$table_name` sp ON p.ID=sp.pID ";
					
				$category = "category";
				$cat_ids = array();

				$cat_ids = srpp_where_match_category($post_id);
				$current_post_type = get_post_type($post_id);
				
				switch ($current_post_type){
					case 'product':
							$category = 'product_cat';
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category, $post_id);
						break;
					case 'al_product':
							$category = 'al_product-cat';
							$cat_ids = array();
							$cat_ids = srpp_where_match_product_category($category, $post_id);
						break;
					default:
							$category = 'category';
				}				
							
				if($cat_ids){	
					$is_primary = false;
					$cat_sql = $cat_ids[0];

					$wp_term_re   = $table_prefix.'term_relationships';
					$wp_terms     = $table_prefix.'terms';
					$wp_term_taxo = $table_prefix.'term_taxonomy';
					$join   .= $wpdb->prepare(
						"inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id =tt.term_taxonomy_id
						inner join `$wp_terms` te on  tte.term_id = te.term_id
						and tte.taxonomy = %s
						and te.term_id = %d ",
						$category,$cat_sql
					);		
				}

				$where = $wpdb->prepare("p.post_status = %s", 'publish');
				$where .= $wpdb->prepare(" AND p.ID != %d", $post_id);
				$where .= $wpdb->prepare(" AND post_type = %s", 'post');
				$orderby = $wpdb->prepare(" ORDER BY id DESC LIMIT %d, 1", $offset);				
							
				$sql = $wpdb->prepare("SELECT ID, post_title, post_content FROM `$wpdb->posts` p $join WHERE $where $orderby");			

				$fetch_result = $wpdb->get_results($sql);

				if(empty($fetch_result)){
					$response_data['offset'] = 'finished';	
				}else{
					if(isset($fetch_result) && $fetch_result[0] ){
						if($fetch_result[0]->ID !== $post_id){
							if(isset($fetch_result[0]->post_content)){
								$response_data['post_title'] = $fetch_result[0]->post_title;
								$response_data['post_content'] = apply_filters('the_content', $fetch_result[0]->post_content);
								$response_data['post_parmalink'] = get_permalink($fetch_result[0]->ID);
								$featured_image = '';
								if (has_post_thumbnail($fetch_result[0]->ID)) {
								    $featured_image = get_the_post_thumbnail($fetch_result[0]->ID, 'full');
								}
								$response_data['featured_image'] = $featured_image;
							}
						}
					}
					if(get_option('srp_post_offset') >= 0){
						$response_data['offset'] = get_option('srp_post_offset') + 1;
					}
				}
				update_option('srp_post_offset', $response_data['offset']);
			}
		}
		echo json_encode($response_data);
		wp_die();
}

add_filter('the_content', 'srp_add_div_wrapper', 4);
function srp_add_div_wrapper($content)
{
	$srp_option_data = get_option('srp_data');
	if(isset($srp_option_data['srpwp_infinite_scrolling'])){
		$content .= '<input type="hidden" id="srp-infinite-scroll" value="on" />';
		$content .= '<input type="hidden" id="srp-current-post-id" value="'.esc_attr(get_the_ID()).'" />';
		$content .= '<input type="hidden" id="srp-current-post-type" value="'.esc_attr(get_post_type()).'" />';
		$content .= '<div id="srp-content-wrapper"></div>';
	}
	return $content;
}