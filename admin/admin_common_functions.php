<?php

/*
	Library for the Recent Posts, Random Posts, Recent Comments, and Super Related Posts Posts plugins
	-- provides the admin routines which the plugins share
*/

define('SRPP_ASRP_LIBRARY', true);

function srpp_options_from_post($options, $args) {
	foreach ($args as $arg) {
		switch ($arg) {
		case 'limit':
		case 'skip':
		    $options[$arg] = srpp_check_cardinal(intval($_POST[$arg]));
			break;
		case 'excluded_cats':
		case 'included_cats':			
			if (!empty($_POST[$arg])) {
				
				if (function_exists('get_term_children')) {	
					//$_POST contains array of integer so we have used array_map to iterate over it and sanitize the it.									
					$catarray = array_map( 'intval', wp_unslash($_POST[$arg]) );
					foreach ($catarray as $cat) {
						$catarray = array_merge($catarray, get_term_children($cat, 'category'));
					}
					$argids = array_unique($catarray);
				}
				$options[$arg] = implode(',', $argids);
			} else {
				$options[$arg] = '';
			}
			break;
		case 'excluded_authors':
		case 'included_authors':						
			if (!empty($_POST[$arg])) {
				//$_POST contains array of integer so we have used array_map to iterate over it and sanitize the it.									
				$authorIds = array_map( 'intval', wp_unslash($_POST[$arg]));
				$options[$arg] = implode(',', $authorIds);
			} else {
				$options[$arg] = '';
			}
			break;
		case 'excluded_posts':
		case 'included_posts':
			$check = explode(',', rtrim(sanitize_text_field($_POST[$arg])));
			$ids = array();
			foreach ($check as $id) {
				$id = srpp_check_cardinal($id);
				if ($id !== 0) $ids[] = $id;
			}
			$options[$arg] = implode(',', array_unique($ids));
			break;
		case 'stripcodes':
			$st = explode("\n", trim(sanitize_text_field($_POST['starttags'])));
			$se = explode("\n", trim(sanitize_text_field($_POST['endtags'])));
			if (count($st) != count($se)) {
				$options['stripcodes'] = array(array());
			} else {
				$num = count($st);
				for ($i = 0; $i < $num; $i++) {
					$options['stripcodes'][$i]['start'] = $st[$i];
					$options['stripcodes'][$i]['end'] = $se[$i];
				}
			}
			break;
		case 'age1':
			$options['age1'] = array();
			$options['age1']['direction'] = sanitize_text_field($_POST['age1-direction']);
			$options['age1']['length'] 	  = srpp_check_cardinal(intval($_POST['age1-length']));
			$options['age1']['duration']  = sanitize_text_field($_POST['age1-duration']);
				break;
		case 'age2':
			$options['age2'] = array();
			$options['age2']['direction'] = sanitize_text_field($_POST['age2-direction']);
			$options['age2']['length']    = srpp_check_cardinal(intval($_POST['age2-length']));
			$options['age2']['duration']  = sanitize_text_field($_POST['age2-duration']);
				break;
		case 'age3':
			$options['age3'] = array();
			$options['age3']['direction'] = sanitize_text_field($_POST['age3-direction']);
			$options['age3']['length']    = srpp_check_cardinal(intval($_POST['age3-length']));
			$options['age3']['duration']  = sanitize_text_field($_POST['age3-duration']);
				break;
		case 'custom':
			$options['custom']['key'] = sanitize_text_field($_POST['custom-key']);
			$options['custom']['op']   = sanitize_text_field($_POST['custom-op']);
			$options['custom']['value'] = sanitize_text_field($_POST['custom-value']);
			break;
		case 'sort':
			$options['sort']['by1'] = sanitize_text_field($_POST['sort-by1']);
			$options['sort']['order1'] = sanitize_text_field($_POST['sort-order1']);
			if ($options['sort']['order1'] === 'SORT_ASC') $options['sort']['order1'] = SORT_ASC; else $options['sort']['order1'] = SORT_DESC;
			$options['sort']['case1'] = sanitize_text_field($_POST['sort-case1']);
			$options['sort']['by2'] = sanitize_text_field($_POST['sort-by2']);
			$options['sort']['order2'] = sanitize_text_field($_POST['sort-order2']);
			if ($options['sort']['order2'] === 'SORT_ASC') $options['sort']['order2'] = SORT_ASC; else $options['sort']['order2'] = SORT_DESC;
			$options['sort']['case2'] = sanitize_text_field($_POST['sort-case2']);
			if ($options['sort']['by1'] === '') {
				$options['sort']['order1'] = SORT_ASC;
				$options['sort']['case1'] = 'false';
				$options['sort']['by2'] = '';
			}
			if ($options['sort']['by2'] === '') {
				$options['sort']['order2'] = SORT_ASC;
				$options['sort']['case2'] = 'false';
			}
			break;
		case 'num_terms':
			$options['num_terms'] = sanitize_text_field($_POST['num_terms']);
			if ($options['num_terms'] < 1) $options['num_terms'] = 20;
			break;
		default:
			$options[$arg] = isset( $_POST[ $arg ] ) ? trim( sanitize_text_field($_POST[ $arg ]) ) : '';
		}
	}
	return $options;
}

function srpp_check_cardinal($string) {
	$value = intval($string);
	return ($value > 0) ? $value : 0;
}

function srpp_display_available_tags($plugin_name) {
	?>
		<h3><?php echo esc_html__('Available Tags', 'super-related-posts'); ?></h3>
		<ul style="list-style-type: none;">
		<li title="">{author}</li>
		<li title="">{authorurl}</li>
		<li title="">{categoryid}</li>
		<li title="">{categorylinks}</li>
		<li title="">{categorynames}</li>
		<li title="">{commentcount}</li>
		<li title="">{custom}</li>
		<li title="">{date}</li>
		<li title="">{dateedited}</li>
		<li title="">{excerpt}</li>
		<li title="">{fullpost}</li>
		<li title="">{gravatar}</li>
		<li title="">{if}</li>
		<li title="">{image}</li>
		<li title="">{imagealt}</li>
		<li title="">{imagesrc}</li>
		<li title="">{link}</li>
		<li title="">{php}</li>
		<li title="">{postid}</li>
		<li title="">{postviews}</li>
		<?php if ($plugin_name === 'super-related-posts') { ?>
			<li title="">{score}</li>
		<?php } ?>
		<li title="">{snippet}</li>
		<li title="">{tags}</li>
		<li title="">{taglinks}</li>
		<li title="">{title}</li>
		<li title="">{time}</li>
		<li title="">{timeedited}</li>
		<li title="">{totalpages}</li>
		<li title="">{totalposts}</li>
		<li title="">{url}</li>
		</ul>
	<?php
}

function srpp_display_available_comment_tags() {
	?>
		<ul style="list-style-type: none;">
		<li title="">{commentexcerpt}</li>
		<li title="">{commentsnippet}</li>
		<li title="">{commentdate}</li>
		<li title="">{commenttime}</li>
		<li title="">{commentdategmt}</li>
		<li title="">{commenttimegmt}</li>
		<li title="">{commenter}</li>
		<li title="">{commenterip}</li>
		<li title="">{commenterurl}</li>
		<li title="">{commenterlink}</li>
		<li title="">{commenturl}</li>
		<li title="">{commentpopupurl}</li>
		<li title="">{commentlink}</li>
		<li title="">{commentlink2}</li>
		</ul>
	<?php
}

/*

	inserts a form button to completely remove the plugin and all its options etc.

*/

function srpp_confirm_eradicate() {
 return (isset($_POST['eradicate-check']) && 'yes'=== sanitize_text_field($_POST['eradicate-check']));
}

function srpp_deactivate_plugin($plugin_file) {
	$current = get_option('active_plugins');
	$plugin_file = substr($plugin_file, strlen(WP_PLUGIN_DIR)+1);
	$plugin_file = str_replace('\\', '/', $plugin_file);
	if (in_array($plugin_file, $current)) {
		array_splice($current, array_search($plugin_file, $current), 1);
		update_option('active_plugins', $current);
	}
}

/*

	For the display of the option pages

*/

function srpp_display_limit($limit) {
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="limit"><?php echo esc_html__('Number of posts to show:', 'super-related-posts') ?></label></th>
		<td><input min="1" name="limit" type="number" id="limit" style="width: 60px;" value="<?php echo esc_attr($limit); ?>" size="2" /></td>
	</tr>
	<?php
}


function srpp_display_limit_i($limit, $num) {
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="limit_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Number of posts to show:', 'super-related-posts') ?></label></th>
		<td><input min="1" name="limit_<?php echo esc_attr($num); ?>" type="number" id="limit_<?php echo esc_attr($num); ?>" style="width: 60px;" value="<?php echo esc_attr($limit); ?>" size="2" /></td>
	</tr>
	<?php
}

function srpp_display_unique($unique) {
	?>
	<tr valign="top">
		<th scope="row"><label for="unique"><?php echo esc_html__('Show just one comment per post?', 'super-related-posts') ?></label></th>
		<td>
		<select name="unique" id="unique" >
			<option <?php if($unique == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			<option <?php if($unique == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
		</select>
		</td>
	</tr>
	<?php
}

function srpp_display_just_current_post($just_current_post) {
	?>
	<tr valign="top">
		<th scope="row"><label for="just_current_post"><?php echo esc_html__('Show just the current post?', 'super-related-posts') ?></label></th>
		<td>
		<select name="just_current_post" id="just_current_post" >
			<option <?php if($just_current_post == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			<option <?php if($just_current_post == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
		</select>
		</td>
	</tr>
	<?php
}

function srpp_sort_post_by_recent_popular_i($sort_by, $num) {
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="sort_by_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Sort post\'s by', 'super-related-posts') ?></label></th>
		<td>
			<select name="sort_by_<?php echo esc_attr($num); ?>" id="sort_by_<?php echo esc_attr($num); ?>">			
				<option <?php if($sort_by == 'recent') { echo 'selected="selected"'; } ?> value="recent"><?php echo esc_html__( 'Recent' , 'super-related-posts') ?></option>
				<option <?php if($sort_by == 'popular') { echo 'selected="selected"'; } ?> value="popular"><?php echo esc_html__( 'Popular' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_match_cat($match_cat) {
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="match_cat"><?php echo esc_html__('Match the current post\'s category?', 'super-related-posts') ?></label></th>
		<td>
			<select name="match_cat" id="match_cat">			
			<option <?php if($match_cat == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
			<option <?php if($match_cat == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_match_cat_i($match_cat, $num) {
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="match_cat_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Match the current post\'s category?', 'super-related-posts') ?></label></th>
		<td>
			<select name="match_cat_<?php echo esc_attr($num); ?>" id="match_cat_<?php echo esc_attr($num); ?>">			
				<option <?php if($match_cat == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
				<option <?php if($match_cat == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_match_tags($match_tags) {
	global $wp_version;
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="match_tags"><?php echo esc_html__('Match the current post\'s tags?', 'super-related-posts') ?></label></th>
		<td>
			<select name="match_tags" id="match_tags" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
				<option <?php if($match_tags == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
				<option <?php if($match_tags == 'any') { echo 'selected="selected"'; } ?> value="any"><?php echo esc_html__( 'Any tag' , 'super-related-posts') ?></option>
				<option <?php if($match_tags == 'all') { echo 'selected="selected"'; } ?> value="all"><?php echo esc_html__( 'Every tag' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}


function srpp_display_post_excerpt($match_cat) {
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="post_excerpt"><?php echo esc_html__('Excerpt?', 'super-related-posts') ?></label></th>
		<td>
			<select name="post_excerpt" id="post_excerpt">			
			<option <?php if($match_cat == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
			<option <?php if(empty($match_cat)) { echo 'selected="selected"'; } ?> <?php if($match_cat == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_post_excerpt_i($post_excerpt, $num) {
	
	?>
	<tr valign="top" <?php echo $post_excerpt; ?> class="srpp-parent-options">
		<th scope="row"><label for="post_excerpt_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Excerpt?', 'super-related-posts') ?></label></th>
		<td>
			<select name="post_excerpt_<?php echo esc_attr($num); ?>" id="post_excerpt_<?php echo esc_attr($num); ?>">			
				<option <?php if($post_excerpt == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
				<option <?php if($post_excerpt == 'false') { echo 'selected="selected"'; } ?> <?php if(empty($post_excerpt)) { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_post_excerpt_length_i( $excerpt_type, $excerpt_length, $num) {
	?>
	<tr valign="top" <?php if(empty($excerpt_type) || $excerpt_type == 'false') { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="excerpt_length_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Excerpt Length:', 'super-related-posts') ?></label></th>
		<td><input min="1" name="excerpt_length_<?php echo esc_attr($num); ?>" type="number" id="excerpt_length_<?php echo esc_attr($num); ?>" style="width: 60px;" value="<?php if(!empty($excerpt_length)){ echo esc_attr($excerpt_length); }else{ echo '5'; } ?>" size="2" /></td>
	</tr>
	<?php
}

function srpp_display_match_tags_i($match_tags, $num) {
	global $wp_version;
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="match_tags_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Match the current post\'s tags?', 'super-related-posts') ?></label></th>
		<td>
			<select name="match_tags_<?php echo esc_attr($num); ?>" id="match_tags_<?php echo esc_attr($num); ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
			<option <?php if($match_tags == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
			<option <?php if($match_tags == 'any') { echo 'selected="selected"'; } ?> value="any"><?php echo esc_html__( 'Any tag' , 'super-related-posts') ?></option>
			<option <?php if($match_tags == 'all') { echo 'selected="selected"'; } ?> value="all"><?php echo esc_html__( 'Every tag' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_position_related_i($pstn_rel, $num) {
	global $wp_version;
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="pstn_rel_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Position:', 'super-related-posts') ?></label></th>
		<td>
			<select name="pstn_rel_<?php echo esc_attr($num); ?>" id="pstn_rel_<?php echo esc_attr($num); ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
			<option <?php if($pstn_rel == 'atc') { echo 'selected="selected"'; } ?> value="atc"><?php echo esc_html__( 'After the Content' , 'super-related-posts') ?></option>
			<option <?php if($pstn_rel == 'ibc') { echo 'selected="selected"'; } ?> value="ibc"><?php echo esc_html__( 'In Between Content' , 'super-related-posts') ?></option>
			<option <?php if($pstn_rel == 'sc') { echo 'selected="selected"'; } ?> value="sc"><?php echo esc_html__( 'Shortcode' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}


function srpp_position_type_i($para_pos_type, $pos, $num) {
	global $wp_version;
	?>
	<tr valign="top <?php echo $pos; ?>" <?php if(empty($pos) || ($pos == 'atc') || ($pos == 'sc')) { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="re_position_type_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Position Type:', 'super-related-posts') ?></label></th>
		<td>
			<select name="re_position_type_<?php echo esc_attr($num); ?>" id="re_position_type_<?php echo esc_attr($num); ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
				<option <?php if($para_pos_type == 'number_of_paragraph') { echo 'selected="selected"'; } ?> value="number_of_paragraph"><?php echo esc_html__( 'Number of paragraph' , 'super-related-posts') ?></option>
				<option <?php if($para_pos_type == '50_of_the_content') { echo 'selected="selected"'; } ?> value="50_of_the_content"><?php echo esc_html__( 'Percent of the content' , 'super-related-posts') ?></option>	
			</select>
		</td>
	</tr>
	<?php
}

function srpp_design_related_i($design, $num) {
	global $wp_version;
	?>
	<tr valign="top" class="srpp-parent-options">
		<th scope="row"><label for="re_design_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Design:', 'super-related-posts') ?></label></th>
		<td>
			<select name="re_design_<?php echo esc_attr($num); ?>" id="re_design_<?php echo esc_attr($num); ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
				<option <?php if($design == 'd1') { echo 'selected="selected"'; } ?> value="d1"><?php echo esc_html__( 'Design 1' , 'super-related-posts') ?></option>
				<option <?php if($design == 'd2') { echo 'selected="selected"'; } ?> value="d2"><?php echo esc_html__( 'Design 2' , 'super-related-posts') ?></option>
				<option <?php if($design == 'd3') { echo 'selected="selected"'; } ?> value="d3"><?php echo esc_html__( 'Design 3' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}


function srpp_demo_design_related_i($design, $num) {
	global $wp_version;
	$img_dir = SRPP_PLUGIN_URI.'images/related_designs/';
	if($design == 'd1'){
		$design_number = "design1.jpg";
	}elseif($design == 'd2'){
		$design_number = "design2.jpg";
	}else{
		$design_number = "design3.jpg";
	} ?>
	   <input type="hidden" class="suprp_image_path" value="<?php echo $img_dir; ?>" />
	   <img src="<?php echo $img_dir.$design_number; ?>" class="suprp-design-related-img" id="design<?php echo esc_attr($num."_".$design); ?>" alt="design<?php echo esc_attr($num."_".$design); ?>"  />	
	<?php
}

function srpp_display_shortcode_i($para, $pos, $num) {
	?>
	<tr valign="top" <?php if($pos != 'sc') { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="limit_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Shortcode:', 'super-related-posts') ?></label></th>
		<td><strong id="shortcode_<?php echo esc_attr($num); ?>">[super-related-posts related_post="<?php echo esc_attr($num); ?>"]</strong></td>
	</tr>
	<?php
}

function srpp_display_shortcode($para, $pos, $num) {
	?>
	<tr valign="top" <?php if($pos != 'sc') { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="limit_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Shortcode:', 'super-related-posts') ?></label></th>
		<td><strong id="shortcode_<?php echo esc_attr($num); ?>">[super-related-posts related_post="<?php echo esc_attr($num); ?>"]</strong></td>
	</tr>
	<?php
}

function srpp_paragraph_i( $position_type, $para, $pos, $num) {
	?>
	<tr valign="top <?php echo $position_type; ?>" <?php if($pos != 'ibc' || $position_type == '50_of_the_content' ) { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="para_rel_<?php echo esc_attr($num); ?>"><?php echo esc_html__('After Number of paragraphs?', 'super-related-posts') ?></label></th>
		<td><input min="1" name="para_rel_<?php echo esc_attr($num); ?>" type="number" id="para_rel_<?php echo esc_attr($num); ?>" style="width: 60px;" value="<?php if(!empty($para)){ echo esc_attr($para); }else{ echo '1'; } ?>" size="2" /></td>
	</tr>
	<?php
}

function srpp_percent_i( $position_type, $para_percent, $pos, $num) {
	?>
	<tr valign="top" <?php if(empty($position_type) || $position_type == 'number_of_paragraph'){ echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="para_percent_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Percent:', 'super-related-posts') ?></label></th>
		<td><input min="1" name="para_percent_<?php echo esc_attr($num); ?>" type="number" id="para_percent_<?php echo esc_attr($num); ?>" style="width: 60px;" value="<?php if(!empty($para_percent)){ echo esc_attr($para_percent); }else{ echo '50'; } ?>" size="2" /></td>
	</tr>
	<?php
}
function srpp_adv_filter_switch($filter_check, $num){
	?>
	<tr valign="top" class="srpp-parent-options">
	    <th scope="row"><label for="adv_filter_check_<?php echo esc_attr($num); ?>" class="adv_filter_check_label"><?php echo esc_html__( 'Advanced Filter Options' , 'super-related-posts') ?></label></th>
	    <td>
	      <label class="srpp-switch">
	        <input type="checkbox" id="adv_filter_check_<?php echo esc_attr($num); ?>" name="adv_filter_check_<?php echo esc_attr($num); ?>" value="1" <?php if( $filter_check == 1 ){echo 'checked'; } ?> class="srpwp-adv-filter-check">
	        <span class="slider round"></span>
	      </label>            
	    </td>
	</tr>
	<?php 
}
function srpp_display_status($filter_check, $num){
	$text = 'Related Posts '.$num.' Module'; 
	?>
	<tr valign="top srpp-parent-options">
	    <th scope="row">
	    	<label for="display_status_<?php echo esc_attr($num); ?>" class="display_status_label"><?php echo esc_html( $text ) ?></label>
	    	<p style="font-weight: 400"><?php echo esc_html__('This will enable the Related Posts Module for your site', 'super-related-posts') ?> 
	    		<!-- <a href="#"><?php //echo esc_html__(' Learn More', 'super-related-posts') ?></a> -->
	    	</p>
	    </th>
	    <td>
	      <label class="srpp-switch">
	        <input type="checkbox" class="srpp-display-status" id="display_status_<?php echo esc_attr($num); ?>" name="display_status_<?php echo esc_attr($num); ?>" value="1" <?php if( $filter_check == 1 ){echo 'checked'; } ?> >
	        <span class="slider round"></span>
	      </label>            
	    </td>
	</tr>
	<?php 
}

function srpp_display_tag_str($tag_str) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="tag_str"><?php echo esc_html__('Match posts with tags:<br />(a,b matches posts with either tag, a+b only matches posts with both tags)', 'super-related-posts') ?></label></th>
		<td><input name="tag_str" type="text" id="tag_str" value="<?php echo esc_attr($tag_str); ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> size="40" /></td>
	</tr>
	<?php
}

function srpp_display_tag_str_i($tag_str, $num) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="tag_str_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Match posts with tags:<br />(a,b matches posts with either tag, a+b only matches posts with both tags)', 'super-related-posts') ?></label></th>
		<td><input name="tag_str_<?php echo esc_attr($num); ?>" type="text" id="tag_str_<?php echo esc_attr($num); ?>" value="<?php echo esc_attr($tag_str); ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> size="40" /></td>
	</tr>
	<?php
}

function srpp_display_excluded_posts($excluded_posts) {
	?>
	<tr valign="top">
		<th scope="row"><label for="excluded_posts"><?php echo esc_html__('Posts to exclude:', 'super-related-posts') ?></label></th>
		<td><input name="excluded_posts" type="text" id="excluded_posts" value="<?php echo esc_attr($excluded_posts); ?>" size="40" /> <?php echo esc_html__('comma-separated IDs', 'super-related-posts'); ?></td>
	</tr>
	<?php
}

function srpp_display_excluded_posts_i($excluded_posts, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="excluded_posts_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Posts to exclude:', 'super-related-posts') ?></label></th>
		<td><input name="excluded_posts_<?php echo esc_attr($num); ?>" type="text" id="excluded_posts_<?php echo esc_attr($num); ?>" value="<?php echo esc_attr($excluded_posts); ?>" size="40" /> <?php echo esc_html__('comma-separated IDs', 'super-related-posts'); ?></td>
	</tr>
	<?php
}

function srpp_display_included_posts($included_posts) {
	?>
	<tr valign="top">
		<th scope="row"><label for="included_posts"><?php echo esc_html__('Posts to include:', 'super-related-posts') ?></label></th>
		<td><input name="included_posts" type="text" id="included_posts" value="<?php echo esc_attr($included_posts); ?>" size="40" /> <?php echo esc_html__('comma-separated IDs', 'super-related-posts'); ?></td>
	</tr>
	<?php
}

function srpp_display_included_posts_i($included_posts, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="included_posts_<?php echo esc_attr($num); ?>"><?php echo esc_html__('Posts to include:', 'super-related-posts') ?></label></th>
		<td><input name="included_posts_<?php echo esc_attr($num); ?>" type="text" id="included_posts_<?php echo esc_attr($num); ?>" value="<?php echo esc_attr($included_posts); ?>" size="40" /> <?php echo esc_html__('comma-separated IDs', 'super-related-posts'); ?></td>
	</tr>
	<?php
}

function srpp_display_authors($excluded_authors, $included_authors) {
	global $wpdb;
	?>
	<tr valign="top">
		<th scope="row"><?php echo esc_html__('Authors to exclude/include:', 'super-related-posts') ?></th>
		<td>
			<table class="srpp-inner-table">
			<?php
				$users = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users ORDER BY user_login");
				if ($users) {
					$excluded = explode(',', $excluded_authors);
					$included = explode(',', $included_authors);
					echo "\n\t<tr valign=\"top\"><td><strong>".esc_html__( 'Author' , 'super-related-posts')."</strong></td><td><strong>".esc_html__( 'Exclude' , 'super-related-posts')."</strong></td><td><strong>".esc_html__( 'Include' , 'super-related-posts')."</strong></td></tr>";
					foreach ($users as $user) {
						if (false === in_array($user->ID, $excluded)) {
							$ex_ischecked = '';
						} else {
							$ex_ischecked = 'checked';
						}
						if (false === in_array($user->ID, $included)) {
							$in_ischecked = '';
						} else {
							$in_ischecked = 'checked';
						}
						echo "\n\t<tr valign=\"top\"><td>".esc_html($user->user_login)."</td><td><input type=\"checkbox\" name=\"excluded_authors[]\" value=".esc_attr($user->ID)." ".esc_attr($ex_ischecked)." /></td><td><input type=\"checkbox\" name=\"included_authors[]\" value=".esc_attr($user->ID)." ".esc_attr($in_ischecked)." /></td></tr>";
					}
				}
			?>
			</table>
		</td>
	</tr>
	<?php
}

function srpp_display_cats($excluded_cats, $included_cats) {
	global $wpdb;
	?>
	<tr valign="top">
		<th scope="row"><?php echo esc_html__('Categories to exclude/include:', 'super-related-posts') ?></th>
		<td>
			<table class="srpp-inner-table">
			<?php
				if (function_exists("get_categories")) {
					$categories = get_categories();
				} else {					
					$categories = $wpdb->get_results("SELECT * FROM $wpdb->categories ORDER BY cat_name");
				}
				if ($categories) {
					echo "\n\t<tr valign=\"top\"><td><strong>".esc_html__( 'Category' , 'super-related-posts')."</strong></td><td><strong>".esc_html__( 'Exclude' , 'super-related-posts')."</strong></td><td><strong>".esc_html__( 'Include' , 'super-related-posts')."</strong></td></tr>";
					$excluded = explode(',', $excluded_cats);
					$included = explode(',', $included_cats);
					$level = 0;
					$cats_added = array();
					$last_parent = 0;
					$cat_parent = 0;
					foreach ($categories as $category) {
						$category->cat_name = esc_html($category->cat_name);
						if (false === in_array($category->cat_ID, $excluded)) {
							$ex_ischecked = '';
						} else {
							$ex_ischecked = 'checked';
						}
						if (false === in_array($category->cat_ID, $included)) {
							$in_ischecked = '';
						} else {
							$in_ischecked = 'checked';
						}
						$last_parent = $cat_parent;
						$cat_parent = $category->category_parent;
						if ($cat_parent == 0) {
							$level = 0;
						} elseif ($last_parent != $cat_parent) {
							if (in_array($cat_parent, $cats_added)) {
								$level = $level - 1;
							} else {
								$level = $level + 1;
							}
							$cats_added[] = $cat_parent;
						}
						$pad = '';
						if($level >= 0){
							$pad = str_repeat('&nbsp;', 3*$level);
						}
						
						echo "\n\t<tr valign=\"top\"><td>".esc_html($pad).esc_html($category->cat_name)."</td><td><input type=\"checkbox\" name=\"excluded_cats[]\" value=".esc_attr($category->cat_ID)." ".esc_attr($ex_ischecked)." /></td><td><input type=\"checkbox\" name=\"included_cats[]\" value=".esc_attr($category->cat_ID)." ".esc_attr($in_ischecked)." /></td></tr>";
					}
				}
			?>
			</table>
		</td>
	</tr>
	<?php
}

function srpp_display_age($age, $sort_by, $num) {
	
	?>
	<tr valign="top" <?php if($sort_by != 'popular') { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="age<?php echo esc_attr($num); ?>-direction"><?php echo esc_html__('Ignore posts:', 'super-related-posts') ?></label></th>
		<td>
				<select name="age<?php echo esc_attr($num); ?>-direction" id="age<?php echo esc_attr($num); ?>-direction">
					<option <?php if(!empty($age['direction']) && $age['direction'] == 'before') { echo 'selected="selected"'; } ?> value="before"><?php echo esc_html__( 'less than' , 'super-related-posts') ?></option>
					<option <?php if(!empty($age['direction']) && $age['direction'] == 'after') { echo 'selected="selected"'; } ?> value="after"><?php echo esc_html__( 'more than' , 'super-related-posts') ?></option>
					<option <?php if(!empty($age['direction']) && $age['direction'] == 'none') { echo 'selected="selected"'; } ?> value="none"><?php echo esc_html__( '-----' , 'super-related-posts') ?></option>
				</select>
				<input  name="age<?php echo esc_attr($num); ?>-length" id="age<?php echo esc_attr($num); ?>-length" value="<?php if( !empty($age['length']) ) echo esc_attr($age['length'])   ?>" style="vertical-align: middle; width: 60px;" type="number" size="4" min="1" />
				<select name="age<?php echo esc_attr($num); ?>-duration" id="age<?php echo esc_attr($num); ?>-duration">
					<option <?php if(!empty($age['duration']) && $age['duration'] == 'day') { echo 'selected="selected"'; } ?> value="day"><?php echo esc_html__( 'day(s)' , 'super-related-posts') ?></option>
					<option <?php if(!empty($age['duration']) && $age['duration'] == 'month') { echo 'selected="selected"'; } ?> value="month"><?php echo esc_html__( 'month(s)' , 'super-related-posts') ?></option>
					<option <?php if(!empty($age['duration']) && $age['duration'] == 'year') { echo 'selected="selected"'; } ?> value="year"><?php echo esc_html__( 'year(s)' , 'super-related-posts') ?></option>
				</select>
				<?php echo esc_html__( 'old' , 'super-related-posts') ?>
		</td>
	</tr>
	<?php
}

function srpp_display_custom($custom) {
	?>
	<tr valign="top">
		<th scope="row"><?php echo esc_html__('Match posts by custom field:', 'super-related-posts') ?></th>
		<td>
			<table>
			<tr><td style="border-bottom-width: 0"><?php echo esc_html__( 'Field Name' , 'super-related-posts') ?></td><td style="border-bottom-width: 0"></td><td style="border-bottom-width: 0"><?php echo esc_html__( 'Field Value' , 'super-related-posts') ?></td></tr>
			<tr>
			<td style="border-bottom-width: 0"><input name="custom-key" type="text" id="custom-key" value="<?php echo esc_attr($custom['key']); ?>" size="20" /></td>
			<td style="border-bottom-width: 0">
				<select name="custom-op" id="custom-op">
					<option <?php if($custom['op'] == '=') { echo 'selected="selected"'; } ?> value="=">=</option>
					<option <?php if($custom['op'] == '!=') { echo 'selected="selected"'; } ?> value="!=">!=</option>
					<option <?php if($custom['op'] == '>') { echo 'selected="selected"'; } ?> value=">">></option>
					<option <?php if($custom['op'] == '>=') { echo 'selected="selected"'; } ?> value=">=">>=</option>
					<option <?php if($custom['op'] == '<') { echo 'selected="selected"'; } ?> value="<"><</option>
					<option <?php if($custom['op'] == '<=') { echo 'selected="selected"'; } ?> value="<="><=</option>
					<option <?php if($custom['op'] == 'LIKE') { echo 'selected="selected"'; } ?> value="LIKE"><?php echo esc_html__( 'LIKE' , 'super-related-posts') ?></option>
					<option <?php if($custom['op'] == 'NOT LIKE') { echo 'selected="selected"'; } ?> value="NOT LIKE"><?php echo esc_html__( 'NOT LIKE' , 'super-related-posts') ?></option>
					<option <?php if($custom['op'] == 'REGEXP') { echo 'selected="selected"'; } ?> value="REGEXP"><?php echo esc_html__( 'REGEXP' , 'super-related-posts') ?></option>
					<option <?php if($custom['op'] == 'EXISTS') { echo 'selected="selected"'; } ?> value="EXISTS"><?php echo esc_html__( 'EXISTS' , 'super-related-posts') ?></option>
				</select>
			</td>
				<td style="border-bottom-width: 0"><input name="custom-value" type="text" id="custom-value" value="<?php echo esc_attr($custom['value']); ?>" size="20" /></td>
			</tr>
			</table>
		</td>
	</tr>
	<?php
}

// now for recent_comments

function srpp_display_show_type($show_type) {
	?>
	<tr valign="top">
		<th scope="row" title=""><label for="show_type"><?php echo esc_html__('Type of comment to show:', 'super-related-posts') ?></label></th>
		<td>
			<select name="show_type" id="show_type">
				<option <?php if($show_type == 'all') { echo 'selected="selected"'; } ?> value="all"><?php echo esc_html__( 'All kinds of comment' , 'super-related-posts') ?></option>
				<option <?php if($show_type == 'comments') { echo 'selected="selected"'; } ?> value="comments"><?php echo esc_html__( 'Just plain comments' , 'super-related-posts') ?></option>
				<option <?php if($show_type == 'trackbacks') { echo 'selected="selected"'; } ?> value="trackbacks"><?php echo esc_html__( 'Just trackbacks and pingbacks' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_group_by($group_by) {
	?>
	<tr valign="top">
		<th scope="row" title=""><?php echo esc_html__('Type of grouping:', 'super-related-posts') ?></th>
		<td>
			<select name="group_by" id="group_by">
				<option <?php if($group_by == 'post') { echo 'selected="selected"'; } ?> value="post"><?php echo esc_html__( 'By Post' , 'super-related-posts') ?></option>
				<option <?php if($group_by == 'none') { echo 'selected="selected"'; } ?> value="none"><?php echo esc_html__( 'Ungrouped' , 'super-related-posts') ?></option>
				<option <?php if($group_by == 'author') { echo 'selected="selected"'; } ?> value="author"><?php echo esc_html__( 'By Commenter' , 'super-related-posts') ?></option>
			</select>
			<?php echo esc_html__( '(overrides the sort criteria above)' , 'super-related-posts') ?>
		</td>
	</tr>
	<?php
}

function srpp_display_no_author_comments($no_author_comments) {
	?>
	<tr valign="top">
		<th scope="row"><label for="no_author_comments"><?php echo esc_html__('Omit comments by the post author?', 'super-related-posts') ?></label></th>
		<td>
			<select name="no_author_comments" id="no_author_comments">
				<option <?php if($no_author_comments == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
				<option <?php if($no_author_comments == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_no_user_comments($no_user_comments) {
	?>
	<tr valign="top">
		<th scope="row"><label for="no_user_comments"><?php echo esc_html__('Omit comments by registered users?', 'super-related-posts') ?></label></th>
		<td>
			<select name="no_user_comments" id="no_user_comments">
				<option <?php if($no_user_comments == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
				<option <?php if($no_user_comments == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function srpp_display_date_modified($date_modified) {
	?>
	<tr valign="top">
		<th scope="row"><?php echo esc_html__('Order by date of last edit rather than date of creation?', 'super-related-posts') ?></th>
		<td>
			<select name="date_modified" id="date_modified">
				<option <?php if($date_modified == 'false') { echo 'selected="selected"'; } ?> value="false"><?php echo esc_html__( 'No' , 'super-related-posts') ?></option>
				<option <?php if($date_modified == 'true') { echo 'selected="selected"'; } ?> value="true"><?php echo esc_html__( 'Yes' , 'super-related-posts') ?></option>
			</select>
		</td>
	</tr>
	<?php
}

// 'borrowed', with adaptations, from Stephen Rider at http://striderweb.com/nerdaphernalia/
function srpp_get_plugin_data($plugin_file) {
	// You can optionally pass a specific value to fetch, e.g. 'Version' -- but it's inefficient to do that multiple times
	// As of WP 2.5.1: 'Name', 'Title', 'Description', 'Author', 'Version'
	// As of WP 2.7-bleeding: 'Name', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version', 'TextDomain', 'DomainPath'
	if(!function_exists( 'get_plugin_data' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php');
	static $plugin_data;
	if(!$plugin_data) {
		$plugin_data = get_plugin_data($plugin_file);
		if (!isset($plugin_data['Title'])) {
			if ('' != $plugin_data['PluginURI'] && '' != $plugin_data['Name']) {
				$plugin_data['Title'] = '<a href="' . esc_url($plugin_data['PluginURI']) . '" title="'. __('Visit plugin homepage', 'super-related-posts') . '">' . esc_html($plugin_data['Name']) . '</a>';
			} else {
				$plugin_data['Title'] = $name;
			}
		}
	}
	return $plugin_data;
}