<?php

/*
	Library for the Recent Posts, Random Posts, Recent Comments, and Super Related Posts plugins
	-- provides the routines which the plugins share
*/

define('SRPP_LIBRARY', true);

//Function to expand html tags form allowed html tags in wordpress    
function srpp_expanded_allowed_tags() {
        
	$my_allowed = wp_kses_allowed_html( 'post' );
	// form fields - input
	$my_allowed['input']  = array(
			'class'        => array(),
			'id'           => array(),
			'name'         => array(),
			'data-type'    => array(),
			'value'        => array(),
			'type'         => array(),
			'style'        => array(),
			'placeholder'  => array(),
			'maxlength'    => array(),
			'checked'      => array(),
			'readonly'     => array(),
			'disabled'     => array(),
			'width'        => array(),  
			'data-id'      => array(),
			'checked'      => array(),
			'step'         => array(),
			'min'          => array(),
			'max'          => array()
	);
	$my_allowed['hidden']  = array(                    
			'id'           => array(),
			'name'         => array(),
			'value'        => array(),
			'type'         => array(), 
			'data-id'         => array(), 
	);
	//number
	$my_allowed['number'] = array(
			'class'        => array(),
			'id'           => array(),
			'name'         => array(),
			'value'        => array(),
			'type'         => array(),
			'style'        => array(),                    
			'width'        => array(),
			'min'          => array(),
			'max'          => array(),                    
	);
	$my_allowed['script'] = array(
			'class'        => array(),
			'type'         => array(),
	);
	//textarea
	 $my_allowed['textarea'] = array(
			'class' => array(),
			'id'    => array(),
			'name'  => array(),
			'value' => array(),
			'type'  => array(),
			'style'  => array(),
			'rows'  => array(),                                                            
	);              
	// select
	$my_allowed['select'] = array(
			'class'    => array(),
			'multiple' => array(),
			'id'       => array(),
			'name'     => array(),
			'value'    => array(),
			'type'     => array(), 
			'data-type'=> array(),                    
	);
	// checkbox
	$my_allowed['checkbox'] = array(
			'class'  => array(),
			'id'     => array(),
			'name'   => array(),
			'value'  => array(),
			'type'   => array(),  
			'disabled'=> array(),  
	);
	//  options
	$my_allowed['option'] = array(
			'selected' => array(),
			'value'    => array(),
			'disabled' => array(),
			'id'       => array(),
	);                       
	// style
	$my_allowed['style'] = array(
			'types' => array(),
	);
	$my_allowed['a'] = array(
			'href'           => array(),
			'target'         => array(),
			'add-on'         => array(),
			'license-status' => array(),
			'class'          => array(),
			'data-id'        => array()
	);
	$my_allowed['p'] = array(                        
			'add-on' => array(),                        
			'class'  => array(),
	);
	return $my_allowed;
} 

function srpp_cache_flush(){
	global $wpdb;	
	$table = $wpdb->prefix.'super_related_cached';
	$wpdb->query( "TRUNCATE TABLE {$table}" );
}

function srpp_cache_fetch($postid, $cache_key){

	global $wpdb;	
	$table = $wpdb->prefix.'super_related_cached';	
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT cvalue FROM $table WHERE cpID = %d AND ckey = %s  LIMIT 1", $postid, $cache_key ) );	
	
	if ( is_object( $row ) ) {
			return $row->cvalue;
	}else{
			return false;
	}
}

function srpp_cache_store($postid, $cache_key, $output){
	global $wpdb;
	if ( is_scalar( $cache_key ) ) {
		$cache_key = trim( $cache_key );
	}

	if ( empty( $cache_key ) ) {
		return false;
	}
	//Output is already sanitize, whereever this method has been called.
	$output = maybe_serialize( $output );	
	$table = $wpdb->prefix.'super_related_cached';	
	$result = $wpdb->query( $wpdb->prepare( "INSERT INTO $table (`cpID`, `ckey`, `cvalue`) VALUES (%d, %s, %s) ON DUPLICATE KEY UPDATE `cpID` = VALUES(`cpID`), `ckey` = VALUES(`ckey`), `cvalue` = VALUES(`cvalue`)", $postid, $cache_key, $output ) );
	if ( ! $result ) {
		return false;
	}
	
}

function srpp_parse_args($args) {
	// 	$args is of the form 'key1=val1&key2=val2'
	//	The code copes with null values, e.g., 'key1=&key2=val2'
	//	and arguments with embedded '=', e.g. 'output_template=<li class="stuff">{...}</li>'.
	$result = array();
	if($args){
		// the default separator is '&' but you may wish to include the character in a title, say,
		// so you can specify an alternative separator by making the first character of $args
		// '&' and the second character your new separator...
		if (substr($args, 0, 1) === '&') {
			$s = substr($args, 1, 1);
			$args = substr($args, 2);
		} else {
			$s = '&';
		}
		// separate the arguments into key=value pairs
		$arguments = explode($s, $args);
		foreach($arguments as $arg){
			if($arg){
				// find the position of the first '='
				$i = strpos($arg, '=');
				// if not a valid format ('key=value) we ignore it
				if ($i){
					$key = substr($arg, 0, $i);
					$val = substr($arg, $i+1);
					$result[$key]=$val;
				}
			}
		}
	}
	return $result;
}

function srpp_set_options($option_key, $arg, $default_output_template) {
	$options = get_option($option_key);	
	
	// deal with compound options
	if (isset($arg['custom-key'])) {$arg['custom']['key'] = $arg['custom-key']; unset($arg['custom-key']);}
	if (isset($arg['custom-op'])) {$arg['custom']['op'] = $arg['custom-op']; unset($arg['custom-op']);}
	if (isset($arg['custom-value'])) {$arg['custom']['value'] = $arg['custom-value']; unset($arg['custom-value']);}

	if (isset($arg['age1']) && !isset($arg['age1']['direction'])) $arg['age1']['direction'] = stripslashes(@$options['age1']['direction']);
	if (isset($arg['age1']) && !isset($arg['age1']['length'])) $arg['age1']['length'] 		 = stripslashes(@$options['age1']['length']);
	if (isset($arg['age1']) && !isset($arg['age1']['duration'])) $arg['age1']['duration']   = stripslashes(@$options['age1']['duration']);

	if (isset($arg['age2']) && !isset($arg['age2']['direction'])) $arg['age2']['direction'] = stripslashes(@$options['age2']['direction']);
	if (isset($arg['age2']) && !isset($arg['age2']['length'])) $arg['age2']['length'] 		 = stripslashes(@$options['age2']['length']);
	if (isset($arg['age2']) && !isset($arg['age2']['duration'])) $arg['age2']['duration']   = stripslashes(@$options['age2']['duration']);

	if (isset($arg['age3']) && !isset($arg['age3']['direction'])) $arg['age3']['direction'] = stripslashes(@$options['age3']['direction']);
	if (isset($arg['age3']) && !isset($arg['age3']['length'])) $arg['age3']['length'] 		 = stripslashes(@$options['age3']['length']);
	if (isset($arg['age3']) && !isset($arg['age3']['duration'])) $arg['age3']['duration']   = stripslashes(@$options['age3']['duration']);
	
	if (isset($arg['sort-by1'])) {$arg['sort']['by1'] = $arg['sort-by1']; unset($arg['sort-by1']);}
	if (isset($arg['sort-by2'])) {$arg['sort']['by2'] = $arg['sort-by2']; unset($arg['sort-by2']);}
	// then fill in the defaults
	if (!isset($arg['limit']) && isset($options['limit'])) $arg['limit'] = stripslashes(@$options['limit']);
	if (!isset($arg['limit_2']) && isset($options['limit_2'])) $arg['limit_2'] = stripslashes(@$options['limit_2']);
	if (!isset($arg['limit_3']) && isset($options['limit_3'])) $arg['limit_3'] = stripslashes(@$options['limit_3']);
	if (!isset($arg['skip'])) $arg['skip'] = stripslashes(@$options['skip']);
	$arg['omit_current_post'] = 'true';
	if (!isset($arg['just_current_post']) && isset($options['just_current_post'])) $arg['just_current_post'] = @$options['just_current_post'];
	if (!isset($arg['tag_str']) && isset($options['tag_str'])) $arg['tag_str'] = stripslashes(@$options['tag_str']);
	if (!isset($arg['tag_str_2']) && isset($options['tag_str'])) $arg['tag_str_2'] = stripslashes(@$options['tag_str']);
	if (!isset($arg['tag_str_3']) && isset($options['tag_str'])) $arg['tag_str_3'] = stripslashes(@$options['tag_str']);
	if (!isset($arg['excluded_cats']) && isset($options['excluded_cats'])) $arg['excluded_cats'] = stripslashes(@$options['excluded_cats']);
	if (!isset($arg['included_cats']) && isset($options['included_cats'])) $arg['included_cats'] = stripslashes(@$options['included_cats']);
	if (!isset($arg['excluded_authors']) && isset($options['excluded_authors'])) $arg['excluded_authors'] = stripslashes(@$options['excluded_authors']);
	if (!isset($arg['included_authors']) && isset($options['included_authors'])) $arg['included_authors'] = stripslashes(@$options['included_authors']);
	if (!isset($arg['display_status_1']) && isset($options['display_status_1'])) $arg['display_status_1'] = stripslashes(@$options['display_status_1']);
	if (!isset($arg['display_status_2']) && isset($options['display_status_2'])) $arg['display_status_2'] = stripslashes(@$options['display_status_2']);
	if (!isset($arg['display_status_3']) && isset($options['display_status_3'])) $arg['display_status_3'] = stripslashes(@$options['display_status_3']);
	if (!isset($arg['sort_by_1']) && isset($options['sort_by_1'])) $arg['sort_by_1'] = stripslashes(@$options['sort_by_1']);
	if (!isset($arg['sort_by_2']) && isset($options['sort_by_2'])) $arg['sort_by_2'] = stripslashes(@$options['sort_by_2']);
	if (!isset($arg['sort_by_3']) && isset($options['sort_by_3'])) $arg['sort_by_3'] = stripslashes(@$options['sort_by_3']);
	if (!isset($arg['adv_filter_check']) && isset($options['adv_filter_check'])) $arg['adv_filter_check'] = stripslashes(@$options['adv_filter_check']);
	if (!isset($arg['adv_filter_check_2']) && isset($options['adv_filter_check'])) $arg['adv_filter_check_2'] = stripslashes(@$options['adv_filter_check']);
	if (!isset($arg['adv_filter_check_3']) && isset($options['adv_filter_check'])) $arg['adv_filter_check_3'] = stripslashes(@$options['adv_filter_check']);
	if (!isset($arg['excluded_posts']) && isset($options['excluded_posts'])) $arg['excluded_posts'] = stripslashes(@$options['excluded_posts']);
	if (!isset($arg['excluded_posts_2']) && isset($options['excluded_posts'])) $arg['excluded_posts_2'] = stripslashes(@$options['excluded_posts']);
	if (!isset($arg['excluded_posts_3']) && isset($options['excluded_posts'])) $arg['excluded_posts_3'] = stripslashes(@$options['excluded_posts']);
	if (!isset($arg['included_posts']) && isset($options['included_posts'])) $arg['included_posts'] = stripslashes(@$options['included_posts']);
	if (!isset($arg['included_posts_2']) && isset($options['included_posts'])) $arg['included_posts_2'] = stripslashes(@$options['included_posts']);
	if (!isset($arg['included_posts_3']) && isset($options['included_posts'])) $arg['included_posts_3'] = stripslashes(@$options['included_posts']);
	if (!isset($arg['re_design_1']) && isset($options['re_design_1'])) $arg['re_design_1'] = stripslashes(@$options['re_design_1']);
	if (!isset($arg['re_design_2']) && isset($options['re_design_2'])) $arg['re_design_2'] = stripslashes(@$options['re_design_2']);
	if (!isset($arg['re_design_3']) && isset($options['re_design_3'])) $arg['re_design_3'] = stripslashes(@$options['re_design_3']);
	if (!isset($arg['stripcodes']) && isset($options['stripcodes'])) $arg['stripcodes'] = @$options['stripcodes'];
	$arg['output_template'] = $default_output_template;
	if (!isset($arg['match_cat']) && isset($options['match_cat'])) $arg['match_cat'] = @$options['match_cat'];
	if (!isset($arg['match_cat_2']) && isset($options['match_cat'])) $arg['match_cat_2'] = @$options['match_cat'];
	if (!isset($arg['match_cat_3']) && isset($options['match_cat'])) $arg['match_cat_3'] = @$options['match_cat'];
	if (!isset($arg['match_tags']) && isset($options['match_tags'])) $arg['match_tags'] = @$options['match_tags'];
	if (!isset($arg['match_tags_2']) && isset($options['match_tags_2'])) $arg['match_tags_2'] = @$options['match_tags_2'];
	if (!isset($arg['match_tags_3']) && isset($options['match_tags_3'])) $arg['match_tags_3'] = @$options['match_tags_3'];
	if (!isset($arg['match_author']) && isset($options['match_author'])) $arg['match_author'] = @$options['match_author'];
	if (!isset($arg['age']) && isset($options['age'])) $arg['age'] = @$options['age'];
	if (!isset($arg['custom']) && isset($options['custom'])) $arg['custom'] = @$options['custom'];
	if (!isset($arg['sort']) && isset($options['sort'])) $arg['sort'] = @$options['sort'];
	if (!isset($arg['status']) && isset($options['status'])) $arg['status'] = @$options['status'];

	// just for recent_posts
	if (!isset($arg['date_modified']) && isset($options['date_modified'])) $arg['date_modified'] = @$options['date_modified'];

	// just for recent_comments
	if (!isset($arg['group_by']) && isset($options['group_by'])) $arg['group_by'] = @$options['group_by'];
	if (!isset($arg['group_template']) && isset($options['group_template'])) $arg['group_template'] = stripslashes(@$options['group_template']);
	if (!isset($arg['show_type']) && isset($options['show_type'])) $arg['show_type'] = @$options['show_type'];
	if (!isset($arg['no_author_comments']) && isset($options['no_author_comments'])) $arg['no_author_comments'] = @$options['no_author_comments'];
	if (!isset($arg['no_user_comments']) && isset($options['no_user_comments'])) $arg['no_user_comments'] = @$options['no_user_comments'];
	if (!isset($arg['unique']) && isset($options['unique'])) $arg['unique'] = @$options['unique'];

	// just for super_related_posts[feed]
	if (!isset($arg['combine']) && isset($options['crossmatch'])) $arg['combine'] = @$options['crossmatch'];
	if (!isset($arg['weight_content']) && isset($options['weight_content'])) $arg['weight_content'] = @$options['weight_content'];
	if (!isset($arg['weight_title']) && isset($options['weight_title'])) $arg['weight_title'] = @$options['weight_title'];
	if (!isset($arg['weight_tags']) && isset($options['weight_tags'])) $arg['weight_tags'] = @$options['weight_tags'];
	if (!isset($arg['num_terms']) && isset($options['num_terms'])) $arg['num_terms'] = stripslashes(@$options['num_terms']);
	if (!isset($arg['term_extraction']) && isset($options['term_extraction'])) $arg['term_extraction'] = @$options['term_extraction'];
	if (!isset($arg['hand_links']) && isset($options['hand_links'])) $arg['hand_links'] = @$options['hand_links'];

	// just for other_posts
	if (!isset($arg['orderby']) && isset($options['orderby'])) $arg['orderby'] = stripslashes(@$options['orderby']);
	if (!isset($arg['orderby_order']) && isset($options['orderby_order'])) $arg['orderby_order'] = @$options['orderby_order'];
	if (!isset($arg['orderby_case']) && isset($options['orderby_case'])) $arg['orderby_case'] = @$options['orderby_case'];

	// the last options cannot be set via arguments
	$arg['stripcodes'] = isset($options['stripcodes'])?@$options['stripcodes']:'';
	$arg['utf8'] = isset($options['utf8'])?@$options['utf8']:'';
	$arg['cjk'] = isset($options['cjk'])?@$options['cjk']:'';
	$arg['use_stemmer'] = isset($options['use_stemmer'])?@$options['use_stemmer']:'';
	$arg['batch'] = isset($options['batch'])?@$options['batch']:'';
	$arg['exclude_users'] = isset($options['exclude_users'])?@$options['exclude_users']:'';;
	$arg['count_home'] = isset($options['count_home'])?@$options['count_home']:'';
	$arg['count_feed'] = isset($options['count_feed'])?@$options['count_feed']:'';
	$arg['count_single'] = isset($options['count_single'])?@$options['count_single']:'';
	$arg['count_archive'] = isset($options['count_archive'])?@$options['count_archive']:'';
	$arg['count_category'] = isset($options['count_category'])?@$options['count_category']:'';
	$arg['count_page'] = isset($options['count_page'])?@$options['count_page']:'';
	$arg['count_search'] = isset($options['count_search'])?@$options['count_search']:'';

	return $arg;
}

function srpp_prepare_template($template) {
	// Now we process the output_template to find the embedded tags which are to be replaced
	// with values taken from the database.
	// A tag is of the form, {tag:ext}, where the tag part will be evaluated and replaced
	// and the optional ext part provides extra data pertinent to that tag

	preg_match_all('/{((?:[^{}]|{[^{}]*})*)}/', $template, $matches);
	$translations = array();
	if(is_array($matches)){

		foreach($matches[1] as $match) {
			if(strpos($match,':')!==false){
				list($tag, $ext) = explode(':', $match, 2);
			} else {
				$tag = $match;
				$ext = false;
			}
			$action = srpp_output_tag_action($tag);
			if (function_exists($action)) {
				// store the action that instantiates the tag
				$translations['acts'][] = $action;
				// add the tag in a form ready to use in translation later
				$translations['fulltags'][] = '{'.$match.'}';
				// the extra data if any
				$translations['exts'][] = $ext;
			}
		}
	}
	return $translations;
}

function srpp_expand_template($result, $template, $translations, $option_key) {
	global $wpdb, $wp_version;
	$replacements = array();

	if(array_key_exists('fulltags',$translations)){
		$numtags = count($translations['fulltags']);
		for ($i = 0; $i < $numtags; $i++) {
			$fulltag = $translations['fulltags'][$i];
			$act = $translations['acts'][$i];
			$ext = $translations['exts'][$i];
			$replacements[$fulltag] = $act($option_key, $result, $ext);
		}
	}
	// Replace every valid tag with its value
	$tmp = strtr($template, $replacements)."\n";
	return $tmp;
}


function srpp_sort_items($sort, $results, $option_key, $items) {
	$translations1 = srpp_prepare_template($sort['by1']);
	foreach ($results as $result) {
		$key1 = srpp_expand_template($result, $sort['by1'], $translations1, $option_key);
		if ($sort['case1'] !== 'false') $key1 = strtolower($key1);
		$keys1[] = $key1;
	}
	if ($sort['by2'] !== '') {
		$translations2 = srpp_prepare_template($sort['by2']);
		foreach ($results as $result) {
			$key2 = srpp_expand_template($result, $sort['by2'], $translations2, $option_key);
			if ($sort['case2'] !== 'false') $key2 = strtolower($key2);
			$keys2[] = $key2;
		}
	}
	if (!empty($keys2)) {
		array_multisort($keys1, intval($sort['order1']), $keys2, intval($sort['order2']), $results, $items);
	} else {
		array_multisort($keys1, intval($sort['order1']), $results, $items);
	}
	
	return $items;
}

// the $post global can be overwritten by the use of $wp_query so we go back to the source
// note the addition of a 'manual overide' allowing the current posts to me marked by super_related_posts_mark_current for example
function srpp_current_post_id ($manual_current_ID = -1) {
	$the_ID = -1;
	if ($manual_current_ID > 0) {
		$the_ID = $manual_current_ID;
	} else if (isset($GLOBALS['wp_the_query'])) {
		$the_ID = $GLOBALS['wp_the_query']->post->ID;
		if (!$the_ID) {
			$the_ID = $GLOBALS['wp_the_query']->posts[0]->ID;
		}
	} else {
		$the_ID = $GLOBALS['post']->ID;
	}
	return $the_ID;
}

/*
	Functions to fill in the WHERE part of the workhorse SQL
*/

function srpp_where_match_category($post_id=null) {
	$cat_ids = '';
	$catarray = array();
	$cat_id_obj = get_the_category();
	if(!empty($post_id)){
		$cat_id_obj = get_the_category($post_id);	
	}
	if(!empty($cat_id_obj)){
		foreach($cat_id_obj as $cat) {
			if ($cat->cat_ID) $cat_ids .= $cat->cat_ID . ',';
		}
		$cat_ids = rtrim($cat_ids, ',');
		$catarray = explode(',', $cat_ids);
		
		foreach ( $catarray as $cat ) {
			$catarray = array_merge($catarray, get_term_children($cat, 'category'));
		}	
		$catarray = array_unique($catarray);	

	}											
	return $catarray;
}

function srpp_where_match_product_category($product_category, $id=null)
{
	$product_id = get_the_ID();
	if(!empty($id)){
		$product_id = $id;	
	}
	$catarray = array(); $parent_cat = array(); $p_cat = array();
	$product_categories = wp_get_post_terms($product_id, $product_category);
	if (!empty($product_categories)) {
	    foreach ($product_categories as $category) {
	        $catarray[] = $category->term_id;
	    }

	    foreach ($product_categories as $pkey => $pvalue) {
	   		if($pvalue->parent == 0){
	   			$parent_cat[] = $pvalue->term_id;
	   			break;
	   		}	
	    }
	    $p_cat = array_merge($parent_cat, $catarray);
	    $p_cat = array_unique($p_cat);
	}
	return $p_cat; 
}

function srpp_where_match_tags() {

	$args 	  = array('fields' => 'ids');
	$tag_ids  = wp_get_object_terms(srpp_current_post_id(), 'post_tag', $args);

	return $tag_ids;			
}

function srpp_where_included_cats($included_cats) {
	global $wpdb, $wp_version;
	$catarray = explode(',', $included_cats);
	foreach ( $catarray as $cat ) {
		$catarray = array_merge($catarray, get_term_children($cat, 'category'));
	}
	$catarray = array_unique($catarray);
	$ids = get_objects_in_term($catarray, 'category');
	if ( is_array($ids) && count($ids) > 0 ) {
		$ids = array_unique($ids);
		$in_posts = "'" . implode("', '", $ids) . "'";
		$sql = "ID IN ($in_posts)";
	} else {
		$sql = "1 = 2";
	}
	return $sql;
}
/*

	End of SQL functions

*/

function srpp_microtime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/*

	Some routines to handle appending output

*/

// array of what to append to posts
global $srp_filter_data;
$srp_filter_data = array();

// each plugin calls this on startup to have content scanned for its own tag
function srpp_register_post_filter($type, $key, $class, $condition='') {

	global $srp_filter_data;
	$options = get_option($key);	

	$srp_filter_arr = [];
	
	if(isset($options[$type . '_priority'])){
		$srp_filter_arr['priority'] = $options[$type . '_priority'];
	}	
	if(isset($options[$type . '_parameters'])){
		$srp_filter_arr['parameters'] = $options[$type . '_parameters'];
	}
	$srp_filter_arr['type']  = $type;
	$srp_filter_arr['class'] = $class;
	$srp_filter_arr['key']   = $key;
	$srp_filter_arr['condition'] = stripslashes($condition);

	if(isset($options['pstn_rel_1'])){
		$srp_filter_arr['position'] = $options['pstn_rel_1'];
	}
	if( isset($options['re_position_type_1']) && $options['re_position_type_1'] == 'number_of_paragraph'){
		if(isset($options['para_rel_1'])){
			$srp_filter_arr['paragraph'] = $options['para_rel_1'];	
		}
	}else{
		if(isset($options['para_percent_1'])){
			$srp_filter_arr['percent'] = $options['para_percent_1'];	
		}
	}

	if(isset($options['re_design_1'])){
		$srp_filter_arr['design'] = $options['re_design_1'];		
	}			

	$srp_filter_data [] = $srp_filter_arr;
	sort($srp_filter_data);
}

function srpp_post_filter_1($content) {
	if ( is_singular() && in_the_loop() && is_main_query() ) {
		global $srp_filter_data;
		foreach ($srp_filter_data as $data) {
			if($data['position'] == 'atc'){
				$post_filter_param = '';
				if(isset($data['parameters'])){
					$post_filter_param = $data['parameters'];
				}
				$content .= call_user_func_array(array($data['class'], 'execute'), array($post_filter_param, '<li>{link}</li>', $data['key']));
			}elseif($data['position'] == 'ibc'){
			
				if(!empty($data['paragraph'])){
					
					$closing_p = '</p>';
					$paragraphs = explode( $closing_p, $content );
					$paragraph_id = $data['paragraph'];
					foreach ($paragraphs as $index => $paragraph) {
						if ( trim( $paragraph ) ) {
							$paragraphs[$index] .= $closing_p;
						}
						$pos = strpos($paragraph, '<p');
						if ( $paragraph_id == $index + 1 && $pos !== false ) {
							$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
						}
					}
					$content = implode( '', $paragraphs );
				}else{
					$percent_content = $data['percent'];
					if(!empty($percent_content)){
					
						$closing_p        = '</p>';
					    $paragraphs       = explode( $closing_p, $content );       
						$total_paragraphs = count($paragraphs);
						$paragraph_id = round($total_paragraphs*($percent_content/100));

						foreach ($paragraphs as $index => $paragraph) {

							if ( trim( $paragraph ) ) {
								$paragraphs[$index] .= $closing_p;
							}
							
							if ( $paragraph_id == $index + 1 ) {
								$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
							}
							
						}
						$content = implode( '', $paragraphs );
					
					}
				}
			}
		}
	}
	return $content;
}

function srpp_register_post_filter_2($type, $key, $class, $condition='') {
	if ( is_singular() && in_the_loop() && is_main_query() ) {}
	global $srp_filter_data2;
	$options = get_option($key);			

	$srp_filter_arr = [];

	if(isset($options[$type . '_priority'])){
		$srp_filter_arr['priority'] = $options[$type . '_priority'];
	}	
	if(isset($options[$type . '_parameters'])){
		$srp_filter_arr['parameters'] = $options[$type . '_parameters'];
	}
	$srp_filter_arr['type']  = $type;
	$srp_filter_arr['class'] = $class;
	$srp_filter_arr['key']   = $key;
	$srp_filter_arr['condition'] = stripslashes($condition);

	if(isset($options['pstn_rel_2'])){
		$srp_filter_arr['position'] = $options['pstn_rel_2'];
	}
	
	if( isset($options['re_position_type_2']) && $options['re_position_type_2'] == 'number_of_paragraph'){
		if(isset($options['para_rel_2'])){
			$srp_filter_arr['paragraph'] = $options['para_rel_2'];	
		}
	}else{
		if(isset($options['para_percent_2'])){
			$srp_filter_arr['percent'] = $options['para_percent_2'];	
		}
	}

	if(isset($options['re_design_2'])){
		$srp_filter_arr['design'] = $options['re_design_2'];		
	}									
	$srp_filter_data2 [] = $srp_filter_arr;	
	sort($srp_filter_data2);
}

function srpp_post_filter_2($content) {
	if ( is_singular() && in_the_loop() && is_main_query() ) {
		global $srp_filter_data2;
		foreach ($srp_filter_data2 as $data) {
			if($data['position'] == 'atc'){
				$post_filter_param = '';
				if(isset($data['parameters'])){
					$post_filter_param = $data['parameters'];
				}
				$content .= call_user_func_array(array($data['class'], 'execute2'), array($post_filter_param, '<li>{link}</li>', $data['key']));
			}elseif($data['position'] == 'ibc'){
				if(!empty($data['paragraph'])){
					$closing_p = '</p>';
					$paragraphs = explode( $closing_p, $content );
					$paragraph_id = $data['paragraph'];
					foreach ($paragraphs as $index => $paragraph) {
						if ( trim( $paragraph ) ) {
							$paragraphs[$index] .= $closing_p;
						}
						$pos = strpos($paragraph, '<p');
						if ( $paragraph_id == $index + 1 && $pos !== false ) {
							$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute2'), array($data['parameters'], '<li>{link}</li>', $data['key']));
						}
					}
					$content = implode( '', $paragraphs );
				}else{
					$percent_content = $data['percent'];
					if(!empty($percent_content)){
						$closing_p        = '</p>';
					    $paragraphs       = explode( $closing_p, $content );       
						$total_paragraphs = count($paragraphs);
						$paragraph_id = round($total_paragraphs*($percent_content/100));

						foreach ($paragraphs as $index => $paragraph) {

							if ( trim( $paragraph ) ) {
								$paragraphs[$index] .= $closing_p;
							}
							
							if ( $paragraph_id == $index + 1 ) {
								$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
							}
							
						}
						$content = implode( '', $paragraphs );
					}
				}
			}
		}
	}
	return $content;
}

function srpp_register_post_filter_3($type, $key, $class, $condition='') {

	global $srp_filter_data3;
	$options = get_option($key);		
	if(isset($options[$type . '_priority'])){
		$srp_filter_arr['priority'] = $options[$type . '_priority'];
	}	
	if(isset($options[$type . '_parameters'])){
		$srp_filter_arr['parameters'] = $options[$type . '_parameters'];
	}
	$srp_filter_arr['type']  = $type;
	$srp_filter_arr['class'] = $class;
	$srp_filter_arr['key']   = $key;
	$srp_filter_arr['condition'] = stripslashes($condition);

	if(isset($options['pstn_rel_3'])){
		$srp_filter_arr['position'] = $options['pstn_rel_3'];
	}
	
	if( isset($options['re_position_type_3']) && $options['re_position_type_3'] == 'number_of_paragraph'){
		if(isset($options['para_rel_3'])){
			$srp_filter_arr['paragraph'] = $options['para_rel_3'];	
		}
	}else{
		if(isset($options['para_percent_3'])){
			$srp_filter_arr['percent'] = $options['para_percent_3'];	
		}
	}

	if(isset($options['re_design_3'])){
		$srp_filter_arr['design'] = $options['re_design_3'];		
	}
	$srp_filter_data3 [] = $srp_filter_arr;	
	sort($srp_filter_data3);
}

function srpp_post_filter_3($content) {
	if ( is_singular() && in_the_loop() && is_main_query() ) {
		global $srp_filter_data3;
		foreach ($srp_filter_data3 as $data) {
			if($data['position'] == 'atc'){
				$post_filter_param = '';
				if(isset($data['parameters'])){
					$post_filter_param = $data['parameters'];
				}
				$content .= call_user_func_array(array($data['class'], 'execute3'), array($post_filter_param, '<li>{link}</li>', $data['key']));
			}elseif($data['position'] == 'ibc'){

				if(!empty($data['paragraph'])){
					$closing_p = '</p>';
					$paragraphs = explode( $closing_p, $content );
					$paragraph_id = $data['paragraph'];
					foreach ($paragraphs as $index => $paragraph) {
						if ( trim( $paragraph ) ) {
							$paragraphs[$index] .= $closing_p;
						}
						$pos = strpos($paragraph, '<p');
						if ( $paragraph_id == $index + 1 && $pos !== false ) {
							$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute3'), array($data['parameters'], '<li>{link}</li>', $data['key']));
						}
					}
					$content = implode( '', $paragraphs );
				}else{
					$percent_content = $data['percent'];
					if(!empty($percent_content)){
					
						$closing_p        = '</p>';
					    $paragraphs       = explode( $closing_p, $content );       
						$total_paragraphs = count($paragraphs);
						$paragraph_id = round($total_paragraphs*($percent_content/100));
						
						foreach ($paragraphs as $index => $paragraph) {
							
							if ( trim( $paragraph ) ) {
								$paragraphs[$index] .= $closing_p;
							}

							if ( $paragraph_id == $index + 1 ) {
								$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
							}
							
						}
						$content = implode( '', $paragraphs );
					}
				}
				
			}
		}
	}
	return $content;
}

function srpp_shortcode_content1() {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {		
		$content = call_user_func_array(array($data['class'], 'execute'), array() );
	}
	
	return $content;
}

function srpp_shortcode_content2() {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {
		$content = call_user_func_array(array($data['class'], 'execute2'), array());
	}
	return $content;
}

function srpp_shortcode_content3() {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {
		$content = call_user_func_array(array($data['class'], 'execute3'), array());
	}
	return $content;
}

function srpp_run_shortcode($attr){
	if(isset($attr['related_post']) && $attr['related_post'] == 2){
		return srpp_shortcode_content2();
	}else if(isset($attr['related_post']) && $attr['related_post'] == 3){
		return srpp_shortcode_content3();
	}else{
		return srpp_shortcode_content1();
	}	

}

function srpp_post_filter_init1() {

	if(!is_admin()){
		global $srp_options;
		if(!$srp_options){
			$srp_options = get_option('super-related-posts');
		}	
		if(isset($srp_options['display_status_1']) && $srp_options['display_status_1'] == 1){
	
			global $srp_filter_data;
			if (!$srp_filter_data) return;
			if(isset($srp_filter_data[0]['position']) && $srp_filter_data[0]['position'] != 'sc'){
				add_filter('the_content', 'srpp_post_filter_1', 5);
			}
	
		}
	}				
}

function srpp_post_filter_init2() {
	if(!is_admin()){
		global $srp_options;
		if(!$srp_options){
			$srp_options = get_option('super-related-posts');
		}	
		if(isset($srp_options['display_status_2']) && $srp_options['display_status_2'] == 1){
			global $srp_filter_data2;
			if (!$srp_filter_data2) return;
			if(isset($srp_filter_data2[0]['position']) && $srp_filter_data2[0]['position'] != 'sc'){
				add_filter('the_content', 'srpp_post_filter_2', 5);
			}
		}
	}		
}

function srpp_post_filter_init3() {

	if(!is_admin()){
		global $srp_options;
		if(!$srp_options){
			$srp_options = get_option('super-related-posts');
		}	
		if(isset($srp_options['display_status_3']) && $srp_options['display_status_3'] == 1){
			global $srp_filter_data3;
			if (!$srp_filter_data3) return;
			if(isset($srp_filter_data3[0]['position']) && $srp_filter_data3[0]['position'] != 'sc'){
				add_filter('the_content', 'srpp_post_filter_3', 5);
			}
		}	
	}		
}

function srpp_post_filter_shortcode(){
	add_shortcode('super-related-posts', 'srpp_run_shortcode');
}

// watch out that the registration functions are called earlier
add_action ('init', 'srpp_post_filter_init1');
add_action ('init', 'srpp_post_filter_init2');
add_action ('init', 'srpp_post_filter_init3');
add_action ('init', 'srpp_post_filter_shortcode');

/*
	Now some routines to handle content filtering
*/

// the '|'-separated list of valid content filter tags
global $srp_filter_tags;

// each plugin calls this on startup to have content scanned for its own tag
function srpp_register_content_filter($tag) {
	global $srp_filter_tags;
	if (!$srp_filter_tags) {
		$srp_filter_tags = $tag;
	} else {
		$tags = explode('|', $srp_filter_tags);
		$tags[] = $tag;
		$tags = array_unique($tags);
		$srp_filter_tags = implode('|', $tags);
	}
}


function srpp_do_replace($matches) {
	return call_user_func(array($matches[1], 'execute'), $matches[2]);
}

function srpp_content_filter($content) {
	global $srp_filter_tags;
	// replaces every instance of "<!--RecentPosts-->", for example, with the output of the plugin
	// the filter tag can be followed by text which will be used as a parameter string to change the behaviour of the plugin
	return preg_replace_callback("/<!--($srp_filter_tags)\s*(.*)-->/", "srpp_do_replace", $content);
}

function srpp_content_filter_init() {
	global $srp_filter_tags;
	if (!$srp_filter_tags) return;
	add_filter( 'the_content',     'srpp_content_filter', 5 );
	add_filter( 'the_content_rss', 'srpp_content_filter', 5 );
	add_filter( 'the_excerpt',     'srpp_content_filter', 5 );
	add_filter( 'the_excerpt_rss', 'srpp_content_filter', 5 );
	add_filter( 'widget_text',     'srpp_content_filter', 5 );
}

// watch out that the registration functions are called earlier
add_action ('init', 'srpp_content_filter_init');

/**
 * We are registering our widget here in wordpress
 */
function suprp_related_post_widget(){
    register_widget('Suprp_Related_Post_Widget');
}
add_action('widgets_init', 'suprp_related_post_widget');

function srpwp_label_text($label_key){
	
	global $translation_panel_options;
	$srp_translation = get_option('srp_data');
	
	if(isset($srp_translation[$label_key]) && $srp_translation[$label_key] !=''){
		return $srp_translation[$label_key];
	}else{
		return $translation_panel_options[$label_key];
	}
							
}

function srpwp_on_uninstall()
{
	global $wpdb, $table_prefix;
    
   $options = get_option('srp_data'); 
   if(isset($options['srpwp_rmv_data_on_uninstall'])){
   		delete_option('super-related-posts');
		delete_option('super-related-posts-feed');
		delete_option('widget_rrm_super_related_posts');
		delete_option('srp_posts_offset');
		delete_option('srp_posts_caching_status');
		delete_option('srp_data');
		delete_option('super_related_posts_meta');
		delete_option('srp_posts_reset_status');

		$table_name = $table_prefix . 'super_related_posts';
		$wpdb->query("DROP TABLE `$table_name`");

		$cached_table = $table_prefix . 'super_related_cached';
		$wpdb->query("DROP TABLE `$cached_table`");
   }
}