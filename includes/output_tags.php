<?php

/*
	Library for the Recent Posts, Random Posts, Recent Comments, and Super Related Posts plugins
	-- provides the routines which evaluate output template tags
*/

define('SRPP_OT_LIBRARY', true);

// Called by the post plugins to match output tags to the actions that evaluate them
function srpp_output_tag_action($tag) {
	return 'srpp_otf_'.$tag;
}

/*
	innards
*/

// To add a new output template tag all you need to do is write a tag function like those below.

// All the tag functions must follow the pattern of 'srpp_otf_title' below.
//	the name is the tag name prefixed by 'srpp_otf_'
//	the arguments are always $option_key, $result and $ext
//		$option_key	the key to the plugin's options
//		$result		the particular row of the query result
//		$ext			some extra data which a tag may use
//	the return value is the value of the tag as a string

function srpp_otf_title ($option_key, $result, $ext) {
	$value = srpp_oth_truncate_text($result->post_title, $ext);
	return apply_filters('the_title', $value);
}

function srpp_otf_url($option_key, $result, $ext) {
	$value = apply_filters('the_permalink', get_permalink($result->ID));
	return srpp_oth_truncate_text($value, $ext);
}

function srp_srpp_otf_author($option_key, $result, $ext) {
	$type = false;
	if ($ext) {
		$s = explode(':', $ext);
		if (count($s) == 1) {
			$type = $s[0];
		}
	}
	switch ($type) {
	case 'display':
		$author = get_the_author_meta('display_name',$result->post_author);
		break;
	case 'full':
		$auth = get_userdata($result->post_author);
		$author = $auth->first_name.' '.$auth->last_name;
		break;
	case 'reverse':
		$auth = get_userdata($result->post_author);
		$author = $auth->last_name.', '.$auth->first_name;
		break;
	case 'first':
		$auth = get_userdata($result->post_author);
		$author = $auth->first_name;
		break;
	case 'last':
		$auth = get_userdata($result->post_author);
		$author = $auth->last_name;
		break;
	default:
		$author = get_the_author_meta('display_name',$result->post_author);
	}
	return $author;
}

function srpp_otf_authorurl($option_key, $result, $ext) {
	return get_author_posts_url($result->post_author);
}

function srpp_otf_date($option_key, $result, $ext) {
	$p_date = isset($result->post_date)?$result->post_date:'';
	if ($ext === 'raw') return $p_date;
	else return srpp_oth_format_date($p_date, $ext);
}

function srpp_otf_dateedited($option_key, $result, $ext) {
	if ($ext === 'raw') return $result->post_modified;
	else return srpp_oth_format_date($result->post_modified, $ext);
}

function srpp_otf_time($option_key, $result, $ext) {
	return srpp_oth_format_time($result->post_date, $ext);
}

function srpp_otf_timeedited($option_key, $result, $ext) {
	return srpp_oth_format_time($result->post_modified, $ext);
}

function srpp_otf_excerpt($option_key, $result, $ext) {
	if (!$ext) {
		$len = 55;
		$type = 'a';
	} else {
		$s = explode(':', $ext);
		if (count($s) == 1) {
			$s[] = 'a';
		}
		$len = $s[0];
		$type = $s[1];
		if ($type === 'b') {
			if (count($s) > 2) {
				$more = $s[2];
			} else {
				$more = ' &hellip;';
			}
			if (count($s) > 3) {
				if ($s[3] === 'link') {
					$url = srpp_otf_url($option_key, $result, '');
					$more = '<a href="'.esc_url($url).'">'.esc_html($more).'</a>';
				}
			}
			if (count($s) > 4) {
				$numsent = $s[4];
			}
		}
	}
	switch ($type) {
	case 'a':
		$value = trim($result->post_excerpt);
		if ($value == '') $value = $result->post_content;
		$value = srpp_oth_trim_excerpt($value, $ext);
		break;
	case 'b':
		$value = trim($result->post_excerpt);
		if ($value === '') {
			$value = $result->post_content;
			$value = convert_smilies($value);
			$value = srpp_oth_trim_extract($value, $len, $more, $numsent);
			$value = apply_filters('get_the_content', $value);
			remove_filter('the_content', 'srpp_content_filter', 5);
			remove_filter('the_content', 'srp_post_filter', 5);
			$value = apply_filters('the_content', $value);
			add_filter('the_content', 'srpp_content_filter', 5);
			add_filter('the_content', 'srp_post_filter', 5);

		} else {
			$value = convert_smilies($value);
			$value = apply_filters('get_the_excerpt', $value);
			remove_filter('the_excerpt', 'srpp_content_filter', 5);
			$value = apply_filters('the_excerpt', $value);
			add_filter('the_excerpt', 'srpp_content_filter', 5);
		}
		break;
	default:
		$value = trim($result->post_excerpt);
		if ($value == '') $value = $result->post_content;
		$value = srpp_oth_trim_excerpt($value, $len);
		break;
	}
	return $value;
}

function srpp_otf_snippet($option_key, $result, $ext) {
	$len = 100;
	$type = 'char';
	$more = '';
	$link = 'nolink';
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[0]) && $s[0]) $len = $s[0];
		if (isset($s[1]) && $s[1]) $type = $s[1];
		if (isset($s[2]) && $s[2]) $more = $s[2];
		if (isset($s[3]) && $s[3]) $link = $s[3];
	}
	if ($link === 'link') {
		$url = srpp_otf_url($option_key, $result, '');
		$more = '<a href="'.esc_url($url).'">'.esc_html($more).'</a>';
	}
	return srpp_oth_format_snippet($result->post_content, $option_key, $type, $len, $more);
}

function srpp_otf_snippetword($option_key, $result, $ext) {
	$len = 100;
	$more = '';
	$link = 'nolink';
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[0]) && $s[0]) $len = $s[0];
		if (isset($s[1]) && $s[1]) $more = $s[1];
		if (isset($s[2]) && $s[2]) $link = $s[2];
	}
	if ($link === 'link') {
		$url = srpp_otf_url($option_key, $result, '');
		$more = '<a href="'.esc_url($url).'">'.esc_html($more).'</a>';
	}
	return srpp_oth_format_snippet($result->post_content, $option_key, 'word', $len, $more);
}

function srpp_otf_fullpost($option_key, $result, $ext) {
	remove_filter( 'the_content', 'srpp_content_filter', 5 );
	remove_filter( 'the_content', 'srp_post_filter', 5 );
	$value = apply_filters('the_content', $result->post_content);
	add_filter( 'the_content', 'srpp_content_filter', 5 );
	add_filter( 'the_content', 'srp_post_filter', 5 );
	return str_replace(']]>', ']]&gt;', $value);
}

function srpp_otf_commentcount($option_key, $result, $ext) {
	$value = $result->comment_count;
	if ($ext) {
		$s = explode(':', $ext);
		if (count($s) == 3) {
			if ($value == 0) $value = $s[0];
			elseif ($value == 1) $value .= ' ' . $s[1];
			else $value .= ' ' . $s[2];
		}
	}
	return $value;
}

function srpp_otf_commentexcerpt($option_key, $result, $ext) {
	if (!$ext) {
		$len = 55;
		$type = 'a';
	} else {
		$s = explode(':', $ext);
		if (count($s) == 1) {
			$s[] = 'a';
		}
		$len = $s[0];
		$type = $s[1];
		if ($type === 'b') {
			if (count($s) > 2) {
				$more = $s[2];
			} else {
				$more = ' &hellip;';
			}
			if (count($s) > 3) {
				if ($s[3] === 'link') {
					$url = srpp_otf_commenturl($option_key, $result, '');
					$more = '<a href="'.esc_url($url).'">'.esc_html($more).'</a>';
				}
			}
		}
	}
	switch ($type) {
	case 'a':
		$value = srpp_oth_trim_comment_excerpt($result->comment_content, $ext);
		break;
	case 'b':
		$value = $result->comment_content;
		$value = convert_smilies($value);

		$text = str_replace(']]>', ']]&gt;', $value);
		if ($len <= count(preg_split('/[\s]+/', strip_tags($text), -1))) {
			// remove html entities for now
			$text = str_replace("\x06", "", $text);
			preg_match_all("/&([a-z\d]{2,7}|#\d{2,5});/i", $text, $ents);
			$text = preg_replace("/&([a-z\d]{2,7}|#\d{2,5});/i", "\x06", $text);
			// now we start counting
			$parts = preg_split('/([\s]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
			$in_tag = false;
			$num_words = 0;
			$text = '';
			foreach($parts as $part) {
				if(0 < preg_match('/<[^>]*$/s', $part)) {
					$in_tag = true;
				} else if(0 < preg_match('/>[^<]*$/s', $part)) {
					$in_tag = false;
				}
				if(!$in_tag && '' != trim($part) && substr($part, -1, 1) != '>') {
					$num_words++;
				}
				$text .= $part;
				if($num_words >= $len && !$in_tag) break;
			}
			// put back the missing html entities
			foreach ($ents[0] as $ent) $text = preg_replace("/\x06/", $ent, $text, 1);
			$text = balanceTags($text, true);
			$value = $text . $more;
		}
		$value = apply_filters('get_comment_text', $value);
		break;
	default:
		$value = srpp_oth_trim_comment_excerpt($result->comment_content, $ext);
		break;
	}
	return $value;
}

function srpp_otf_commentsnippet($option_key, $result, $ext) {
	$len = 100;
	$type = 'char';
	$more = '';
	$link = 'nolink';
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[0]) && $s[0]) $len = $s[0];
		if (isset($s[1]) && $s[1]) $type = $s[1];
		if (isset($s[2]) && $s[2]) $more = $s[2];
		if (isset($s[3]) && $s[3]) $link = $s[3];
	}
	if ($link === 'link') {
		$url = srpp_otf_commenturl($option_key, $result, '');
		$more = '<a href="'.esc_url($url).'">'.esc_html($more).'</a>';
	}
	return srpp_oth_format_snippet($result->comment_content, $option_key, $type, $len, $more);
}

function srp_srpp_otf_commentsnippetword($option_key, $result, $ext) {
	$len = 100;
	$more = '';
	$link = 'nolink';
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[0]) && $s[0]) $len = $s[0];
		if (isset($s[1]) && $s[1]) $more = $s[1];
		if (isset($s[2]) && $s[2]) $link = $s[2];
	}
	if ($link === 'link') {
		$url = srpp_otf_commenturl($option_key, $result, '');
		$more = '<a href="'.esc_url($url).'">'.esc_html($more).'</a>';
	}
	return srpp_oth_format_snippet($result->comment_content, $option_key, 'word', $len, $more);
}

function srpp_otf_commentdate($option_key, $result, $ext) {
	if ($ext === 'raw') return $result->comment_date;
	return srpp_oth_format_date($result->comment_date, $ext);
}

function srpp_otf_commenttime($option_key, $result, $ext) {
	return srpp_oth_format_time($result->comment_date, $ext);
}

function srpp_otf_commentdategmt($option_key, $result, $ext) {
	if ($ext === 'raw') return $result->comment_date_gmt;
	return srpp_oth_format_date($result->comment_date_gmt, $ext);
}

function srpp_otf_commenttimegmt($option_key, $result, $ext) {
	return srpp_oth_format_time($result->comment_date_gmt, $ext);
}

function srpp_otf_commenter($option_key, $result, $ext) {
	$value = $result->comment_author;
	$value = apply_filters('get_comment_author', $value);
	$value = apply_filters('comment_author', $value);
	return srpp_oth_truncate_text($value, $ext);
}

function srpp_otf_commenterurl($option_key, $result, $ext) {
	$value = $result->comment_author_url;
	$value = apply_filters('get_comment_author_url', $value);
	return srpp_oth_truncate_text($value, $ext);
}


function srpp_otf_commenterip($option_key, $result, $ext) {
	return $result->comment_author_IP;
}

function srpp_otf_commenturl($option_key, $result, $ext) {
	$value = apply_filters('the_permalink', get_permalink($result->ID)) . '#comment-' . $result->comment_ID;
	return srpp_oth_truncate_text($value, $ext);
}

function srpp_otf_catnames($option_key, $result, $ext) {
	return srpp_otf_categorynames($option_key, $result, $ext);
}

function srpp_otf_categorynames($option_key, $result, $ext) {
	$cats = get_the_category($result->ID);
	$value = '';
	$n = 0;
	foreach ($cats as $cat) {
		if ($n > 0) $value .= $ext;
		$value .= apply_filters('single_cat_title', $cat->cat_name);
		++$n;
	}
	return $value;
}

function srpp_otf_custom($option_key, $result, $ext) {
	$custom = get_post_custom($result->ID);
	return $custom[$ext][0];
}

function srpp_otf_tags($option_key, $result, $ext) {
	$tags = (array) get_the_tags($result->ID);
	$tag_list = array();
	foreach ( $tags as $tag ) {
		if (isset($tag->name)) {
			$tag_list[] = $tag->name;
		}
	}
	if (!$ext) $ext = ', ';
	$tag_list = join( $ext, $tag_list );
	return $tag_list;
}

function srpp_otf_taglinks($option_key, $result, $ext) {
	
	$tags = (array) get_the_tags($result->ID);
	$tag_list = '';
	$tag_links = array();
	foreach ( $tags as $tag ) {
		$link = get_tag_link($tag->term_id);
		if ( is_wp_error( $link ) )
			return $link;
		$tag_links[] = '<a href="' . esc_url($link) . '" rel="tag">' . esc_html($tag->name) . '</a>';
	}
	if (!$ext) $ext = ' ';
	$tag_links = join( $ext, $tag_links );
	$tag_links = apply_filters( 'the_tags', $tag_links );
	$tag_list .= $tag_links;
	return $tag_list;
}

function srpp_otf_link($option_key, $result, $ext) {
	$ttl = srpp_otf_title($option_key, $result, $ext);
	$pml = srpp_otf_url($option_key, $result, null);
	if(!empty($result->post_excerpt)){
		$excerpt_str = wp_strip_all_tags($result->post_excerpt)."...";
	}else{
		$excerpt_str = "";
	}
	$feature_img_alt_txt = $ttl;
	if(isset($result->ID)){
		$image_id = get_post_thumbnail_id($result->ID);
		if($image_id != 0){
			$feature_img_alt_txt = get_post_meta($image_id , '_wp_attachment_image_alt', true);
			if(empty($feature_img_alt_txt)){
				$feature_img_alt_txt = $ttl;	
			}
		}
	}
	$pdt = srpp_otf_date($option_key, $result, null);
	$img = srpp_otf_imagesrc_shareaholic($option_key, $result, null);
	if(empty($img)){
		$img = SRPP_PLUGIN_URI.'/images/default-image.png';
	}
	return '<div class="sprp-wrpr"><div class="sprp-txt"><a href="'.esc_url($pml).'" rel="bookmark" title="'.esc_attr($ttl).'">'.esc_attr($ttl).'</a><p>'.esc_attr($excerpt_str).'</p></div><div class="sprp-img"><a href="'.esc_url($pml).'" rel="bookmark" title="'.esc_url($ttl).'"><img src="'.esc_url($img).'" width="250" height="175" alt="'.esc_attr($feature_img_alt_txt).'" aria-label="'.esc_attr($ttl).'"></a></div></div>';
}

function srpp_otf_score($option_key, $result, $ext) {
	return sprintf("%.0f", $result->score);
}

function srpp_oth_get_actual_size($imgtag) {
	// first try extracting the width and height attributes
	if (preg_match('/\s+width\s*=\s*[\'|\"](.*?)[\'|\"]/is', $imgtag, $matches)) {
		$current_width = $matches[1];
		if (preg_match('/\s+height\s*=\s*[\'|\"](.*?)[\'|\"]/is', $imgtag, $matches)) {
			$current_height = $matches[1];
		}
	}
	// then try using the GD library
	if (!(($current_width) && ($current_height))) {
		// extract the image src url
		preg_match('/\s+src\s*=\s*[\'|\"](.*?)[\'|\"]/is', $imgtag, $matches);
		
		if (function_exists('getimagesize') && $imagesize = getimagesize($matches[1])) {
			$current_width = $imagesize['0'];
			$current_height = $imagesize['1'];
		} else {
			// if all else fails...
			$current_width = $current_height = 0;
		}
		
	}
	return array($current_width, $current_height);
}

function srpp_oth_image_size_full($w, $h, $imgtag){
	return array(1, 1);
}

function srpp_oth_image_size_scale($w, $h, $imgtag){
	$maxsize = max($w, $h);
	list($current_width, $current_height) = srpp_oth_get_actual_size($imgtag);
	$width_ratio = $height_ratio = 1.0;
	if ($current_width > $maxsize)
		$width_ratio = $maxsize / $current_width;
	if ($current_height > $maxsize)
		$height_ratio = $maxsize / $current_height;
	// the smaller ratio is the one we need to fit it to the constraining box
	$ratio = min( $width_ratio, $height_ratio );
	$w = intval($current_width * $ratio);
	$h = intval($current_height * $ratio);
	return array($w, $h);
}

function srpp_oth_image_size_blank($w, $h, $imgtag){
	return array(0, 0);
}

function srpp_oth_image_size_exact($w, $h, $imgtag){
	return array($w, $h);
}

function srpp_oth_image_size_fixedw($w, $h, $imgtag){
	list($current_width, $current_height) = srpp_oth_get_actual_size($imgtag);
	$h = intval($w * ($current_height / $current_width));
	return array($w, $h);
}

function srpp_oth_image_size_fixedh($w, $h, $imgtag){
	list($current_width, $current_height) = srpp_oth_get_actual_size($imgtag);
	$w = intval($h * ($current_width / $current_height));
	return array($w, $h);
}

function srpp_oth_test($x) {
	if (empty($x)) return 'a';
	if (is_numeric($x)) return 'b';
	return 'c';
}

function srpp_oth_process($w, $h) {
	static $table = array(	'a' => array('a' => 'full', 'b' => 'scale', 'c' => 'blank'),
							'b' => array('a' => 'scale', 'b' => 'exact', 'c' => 'fixedw'),
							'c' => array('a' => 'blank', 'b' => 'fixedh', 'c' => 'blank'));
	return 'srpp_oth_image_size_' . $table[srpp_oth_test($w)][srpp_oth_test($h)];
}

function srpp_otf_image($option_key, $result, $ext) {
	// extract any image tags
	$content = $result->post_content;
	$i = 0;
	$imgtag = '';
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[3]) && $s[3] === 'post') {
			$content = apply_filters('the_content', $content);
		}
	}

	if (isset($s[4]) && $s[4] === 'link') {
		$pattern = '/<a.+?<img.+?>.+?a>/i';
		$pattern2 = '#(<a.+?<img.+?)(/>|>)#is';
	} else {
		$pattern = '/<img.+?>/i';
		$pattern2 = '#(<img.+?)(/>|>)#is';
	}
	if (!preg_match_all($pattern, $content, $matches)) {
		// no <img> tags in content
		if ((isset($s[5]) && $s[5]) && (isset($s[6]) && $s[6])) {
			// a default <img> tag has been given
			return $s[5].':'.$s[6];
		} else {
			return '';
		}
	}
	if (isset($s[0])) {
		$i = $s[0];
	}

	if (isset($matches[0][$i])){
		$imgtag = $matches[0][$i];
	}

	if (!isset($s[1])) {
		$s[1] = null;
	}

	if (!isset($s[2])) {
		$s[2] = null;
	}

	$process = srpp_oth_process($s[1],$s[2]);
	list($w, $h) = $process(intval($s[1]), intval($s[2]), $imgtag);
	if ($w === 0) return '';
	if ($w === 1) return $imgtag;
	// remove height or width if present
	$imgtag = preg_replace('/(width|height)\s*=\s*[\'|\"](.*?)[\'|\"]/is', '', $imgtag);
	// insert the new size
	$imgtag = preg_replace($pattern2, "$1 height=\"$h\" width=\"$w\" $2", $imgtag);
	return $imgtag;
}

function srpp_otf_imagesrc($option_key, $result, $ext) {
	// extract any image tags
	$content = $result->post_content;
	$i = 0;
	$imgsrc = '';
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[1]) && $s[1] === 'post') {
			$content = apply_filters('the_content', $content);
		}
		if (isset($s[2]) && $s[2]) $suffix = $s[2];
	}
	if (isset($s[0])) {
		$i = $s[0];
	}
	$pattern = '/<img.+?src\s*=\s*[\'|\"](.*?)[\'|\"].+?>/i';

	if (!preg_match_all($pattern, $content, $matches)) return '';

	if (isset($matches[1][$i])){
		$imgsrc = $matches[1][$i];
	}

	if (isset($suffix) && $suffix) {
		if ($suffix === '?m') $suffix = '-' . get_option('medium_size_w') . 'x' . get_option('medium_size_h');
		if ($suffix === '?t') $suffix = '-' . get_option('thumbnail_size_w') . 'x' . get_option('thumbnail_size_h');
		$pathinfo = pathinfo($imgsrc);
		$extension = $pathinfo['extension'];
		$imgsrc = str_replace(".$extension", "$suffix.$extension", $imgsrc);
	}
	return $imgsrc;
}

function srpp_otf_imagesrc_shareaholic($option_key, $result, $ext) {

  $thumbnail_src = '';

  if (is_attachment($result->ID)) {
    $thumbnail_src = wp_get_attachment_thumb_url($result->ID);
  }

  $thumbnail_src = srpp_oth_post_featured_image($result->ID);

  if ($thumbnail_src == NULL) {
    $thumbnail_src = srpp_oth_post_first_image($result->ID);
  }

	if ($thumbnail_src != NULL) {
		return $thumbnail_src;
	} else {
		return null;
	}
}

function srpp_otf_imagealt($option_key, $result, $ext) {
	// extract any image tags
	$content = $result->post_content;
	$i = 0;
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[1]) && $s[1] === 'post') {
			$content = apply_filters('the_content', $content);
		}
		if (isset($s[2]) && $s[2]) $suffix = $s[2];
	}
	$pattern = '/<img.+?alt\s*=\s*[\'|\"](.*?)[\'|\"].+?>/i';
	if (!preg_match_all($pattern, $content, $matches)) return '';
	if (isset($s[0])) {
		$i = $s[0];
	}
	if (isset($matches[1][$i])) {
		return $matches[1][$i];
	}
}

function srpp_otf_gravatar($option_key, $result, $ext) {
	$size = 96;
	$rating = '';
	$default = "http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?s=$size"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
	if ($ext) {
		$s = explode(':', $ext);
		if (isset($s[0])) $size = $s[0];
		if (isset($s[1])) $rating = $s[1];
		if (isset($s[3])) {
			$default = 'http:'.$s[3];
		} else {
			if (isset($s[2])) $default = $s[2];
		}
	}
	$email = '';
	if (isset($result->comment_author_email)) {
		$email = $result->comment_author_email;
	} else {
		$user = get_userdata($result->post_author);
		if ($user) $email = $user->user_email;
	}
	if (!empty($email)) {
		$out = 'http://www.gravatar.com/avatar/';
		$out .= md5(strtolower($email));
		$out .= '?s='.$size;
		$out .= '&amp;d=' . urlencode( $default );
		if ('' !== $rating)
			$out .= "&amp;r={$rating}";
		$avatar = '<img alt="" src="'.esc_url($out).'" class="avatar avatar-'.esc_attr($size).'" height="'.esc_attr($size).'" width="'.esc_attr($size).'" />';
	} else {
		$avatar = '<img alt="" src="'.esc_url($default).'" class="avatar avatar-'.esc_attr($size).' avatar-default" height="'.esc_attr($size).'" width="'.esc_attr($size).'" />';
	}
	return apply_filters('get_avatar', $avatar, $email, $size, $default);
}

// returns the principal category id of a post -- if a cats are hierarchical chooses the most specific -- if multiple cats chooses the first (numerically smallest)
function srpp_otf_categoryid($option_key, $result, $ext) {
	$cats = get_the_category($result->ID);
	foreach ($cats as $cat) {
		$parents[] = $cat->category_parent;
	}
	foreach ($cats as $cat) {
		if (!in_array($cat->cat_ID, $parents)) $categories[] = $cat->cat_ID;
	}
	return $categories[0];
}

// fails if parentheses are out of order or nested
function srpp_oth_splitapart($subject) {
	$bits = explode(':', $subject);
	$inside = false;
	$newbits = array();
	$acc = '';
	foreach ($bits as $bit) {
		if (false !== strpos($bit, '{')) {
			$inside = true;
			$acc = '';
		}
		if (false !== strpos($bit, '}')) {
			$inside = false;
			if ($acc !== '') {
				$acc .= ':' . $bit;
			} else {
				$acc = $bit;
			}
		}
		if ($inside) {
			if ($acc !== '') {
				$acc .= ':' . $bit;
			} else {
				$acc = $bit;
			}
		} else {
			if ($acc !== '') {
				$newbits[] = $acc;
				$acc = '';
			} else {
				$newbits[] = $bit;
			}
		}
	}
	return $newbits;
}

// ****************************** Helper Functions *********************************************

function srpp_oth_post_first_image($id) {
  $first_img = '';
  if ($id == NULL)
    return false;
  else {
		$post = get_post($id);
    	$output = preg_match_all('/<img.*?src=[\'"](.*?)[\'"].*?>/i', $post->post_content, $matches);
    if (isset($matches[1][0])) {
      // Exclude base64 images; meta tags require full URLs
      if (strpos($matches[1][0], 'data:') === false) {
          $first_img = $matches[1][0];
      }
    } else {
      	return false;
    }
    	return $first_img;
  }
}

/**
 * This function returns the URL of the featured image for a given post
 *
 * @return returns `false` or a string of the image src
 */
function srpp_oth_post_featured_image($id, $size = "thumbnail") {
  $featured_img = '';
  if ($id == NULL)
    return false;
  else {
		$post = get_post($id);
    if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
      $thumbnail_shareaholic = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'thumbnail');
      $thumbnail_full = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');

      if (($size == "thumbnail") && ($thumbnail_shareaholic[0] !== $thumbnail_full[0])) {
        $featured_img = esc_attr($thumbnail_full[0]);
      } else {
        if ($size == "thumbnail") {
          $thumbnail_large = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
        } else {
          $thumbnail_large = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
        }
        $featured_img = esc_attr($thumbnail_large[0]);
      }
    } else {
      return false;
    }
  }
  return $featured_img;
}

function srpp_oth_truncate_text($text, $ext) {
	if (!$ext) {
		return $text;
	}
	$s = explode(':', $ext);
	if (count($s) > 2) {
		return $text;
	}
	if (count($s) == 1) {
		$s[] = 'wrap';
	}
	$length = $s[0];
	$type = $s[1];
	switch ($type) {
	case 'wrap':
		$length += strlen('<br />');
		if (!function_exists('mb_detect_encoding')) {
			return wordwrap($text, $length, '<br />', true);
		} else {
			$e = mb_detect_encoding($text);
			$formatted = '';
			$position = -1;
			$prev_position = 0;
			$last_line = -1;
			while($position = mb_strpos($text, " ", ++$position, $e)) {
				if($position > $last_line + $length + 1) {
					$formatted.= mb_substr($text, $last_line + 1, $prev_position - $last_line - 1, $e).'<br />';
					$last_line = $prev_position;
				}
				$prev_position = $position;
			}
			$formatted.= mb_substr($text, $last_line + 1, mb_strlen( $text ), $e);
			return $formatted;
		}
	case 'chop':
		if (!function_exists('mb_detect_encoding')) {
			 return substr($text, 0, $length);
		} else {
			$e = mb_detect_encoding($text);
			return mb_substr($text, 0, $length, $e);
		}
	case 'trim':
		if (strlen($text) > $length) {
		} else {
			return $text;
		}
		if (!function_exists('mb_detect_encoding')) {
			$textlen = strlen($text);
			if ($textlen > $length) {
				$text = substr($text, 0, $length-2);
				return rtrim($text,".").'&hellip;';
			} else {
				return $text;
			}
		} else {
			$e = mb_detect_encoding($text);
			$textlen = mb_strlen($text, $e);
			if ($textlen > $length) {
				$text = mb_substr($text, 0, $length-2, $e);
				return rtrim($text,".").'&hellip;';
			} else {
				return $text;
			}
		}
	case 'snip':
		if (!function_exists('mb_detect_encoding')) {
			$textlen = strlen($text);
			if ($textlen > $length) {
				$b = floor(($length - 2)/2);
				$l = $textlen - $b - 1;
				return substr($text, 0, $b).'&hellip;'.substr($text, $l);
			} else {
				return $text;
			}
		} else {
			$e = mb_detect_encoding($text);
			$textlen = mb_strlen($text, $e);
			if ($textlen > $length) {
				$b = floor(($length - 2)/2);
				$l = $textlen - $b - 1;
				return mb_substr($text, 0, $b, $e).'&hellip;'.mb_substr($text, $l, 1000, $e);
			} else {
				return $text;
			}
		}
	default:
		return wordwrap($t, $length, '<br />', true);
	}
}

function srpp_oth_trim_extract($text, $len, $more, $numsent) {
	$text = str_replace(']]>', ']]&gt;', $text);
	if(strpos($text, '<!--more-->')) {
		$parts = explode('<!--more-->', $text, 2);
		$text = $parts[0];
	} else {
		if ($len > count(preg_split('/[\s]+/', strip_tags($text), -1))) return $text;
		// remove html entities for now
		$text = str_replace("\x06", "", $text);
		preg_match_all("/&([a-z\d]{2,7}|#\d{2,5});/i", $text, $ents);
		$text = preg_replace("/&([a-z\d]{2,7}|#\d{2,5});/i", "\x06", $text);
		// now we start counting
		$parts = preg_split('/([\s]+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$in_tag = false;
		$num_words = 0;
		$sentences = array();
		$words = '';
		foreach($parts as $part) {
			if(0 < preg_match('/<[^>]*$/s', $part)) {
				$in_tag = true;
			} else if(0 < preg_match('/>[^<]*$/s', $part)) {
				$in_tag = false;
			}
			if(!$in_tag && '' != trim($part) && substr($part, -1, 1) != '>') {
				$num_words++;
			}
			if(!$in_tag && '' != trim($part) && false !== strpos('.?!', substr($part, -1, 1))) {
				$sentences [] = $words . $part;
				$words = '';
			} else {
				$words .= $part;
			}
			if($num_words >= $len && !$in_tag) break;
		}
		if (!isset($numsent)) {
			$text = implode('', $sentences) . $words;
		} else {
			$numsent = abs($numsent);
			if ($numsent == 0) {
				$text = implode('', $sentences);
			} else {
				$text = implode('', array_slice($sentences, 0, $numsent));
			}
		}
		// put back the missing html entities
	    foreach ($ents[0] as $ent) $text = preg_replace("/\x06/", $ent, $text, 1);
	}
	$text = balanceTags($text, true);
	$text = $text . $more;
	return $text;
}

function srpp_oth_format_snippet($content, $option_key, $trim, $len, $more) {
	$content = strip_tags($content);
	$p = get_option($option_key);
	if ($p['stripcodes']) $content = srpp_oth_strip_special_tags($content, $p['stripcodes']);
	// strip extra whitespace
	$content = preg_replace('/\s+/u', ' ', $content);
	$content = stripslashes($content);
	if (function_exists('mb_detect_encoding')) $enc = mb_detect_encoding($content);
	// grab a maximum number of characters
	if ($enc) {
		mb_internal_encoding($enc);
		if (mb_strlen($content) >= $len) {
			$snippet = mb_substr($content, 0, $len);
			if ($trim == 'word' && mb_strlen($snippet) == $len) {
				// trim back to the last full word--NB if our snippet ends on a word
				// boundary we still have to trim back to the non-word character
				// (the final 's' in the pattern makes sure we match newlines)
				preg_match('/^(.*)\W/su', $snippet, $matches);
				//if we can't get a single full word we use the full snippet
				// (we use $matches[1] because we don't want the white-space)
				if ($matches[1]) $snippet = $matches[1];
			}
			$snippet .= $more;
		} else {
			$snippet = $content;
		}
	} else {
		if (strlen($content) >= $len) {
			$snippet = substr($content, 0, $len);
			if ($trim == 'word' && strlen($snippet) == $len) {
				// trim back to the last full word--NB if our snippet ends on a word
				// boundary we still have to trim back to the non-word character
				// (the final 's' in the pattern makes sure we match newlines)
				preg_match('/^(.*)\W/s', $snippet, $matches);
				//if we can't get a single full word we use the full snippet
				// (we use $matches[1] because we don't want the white-space)
				if ($matches[1]) $snippet = $matches[1];
			}
			$snippet .= $more;
		} else {
			$snippet = $content;
		}
	}
	return $snippet;
}

function srpp_oth_strip_special_tags($text, $stripcodes) {
		$numtags = count($stripcodes);
		for ($i = 0; $i < $numtags; $i++) {
			if (!$stripcodes[$i]['start'] || !$stripcodes[$i]['end']) return $text;
			$pattern = '/('. srpp_oth_regescape($stripcodes[$i]['start']) . '(.*?)' . srpp_oth_regescape($stripcodes[$i]['end']) . ')/i';
			$text = preg_replace($pattern, '', $text);
		}
		return $text;
}

function srpp_oth_trim_excerpt($content, $len) {
	// taken from the wp_trim_excerpt filter
	remove_filter( 'the_content', 'srpp_content_filter', 5 );
	remove_filter( 'the_content', 'srp_post_filter', 5 );
	$text = apply_filters('the_content', $content);
	add_filter( 'the_content', 'srpp_content_filter', 5 );
	add_filter( 'the_content', 'srp_post_filter', 5 );
	$text = str_replace(']]>', ']]&gt;', $text);
	$text = strip_tags($text);
	if (!$len) $len = 55;
	$excerpt_length = $len;
	$words = explode(' ', $text, $excerpt_length + 1);
	if (count($words) > $excerpt_length) {
		array_pop($words);
		$text = implode(' ', $words);
	}
	$text = convert_smilies($text);
	return $text;
}

function srpp_oth_trim_comment_excerpt($content, $len) {
	// adapted from the wp_trim_excerpt filter
	$text = $content;
	$text = apply_filters('get_comment_text', $text);
	$text = str_replace(']]>', ']]&gt;', $text);
	$text = strip_tags($text);
	if (!$len) $len = 55;
	$excerpt_length = $len;
	$words = explode(' ', $text, $excerpt_length + 1);
	if (count($words) > $excerpt_length) {
		array_pop($words);
		$text = implode(' ', $words);
	}
	$text = convert_smilies($text);
	return $text;
}

function srpp_oth_format_date($date, $fmt) {
	if (!$fmt) $fmt = get_option('date_format');
	$d = mysql2date($fmt, $date);
	$d = apply_filters('get_the_time', $d, $fmt);
	return apply_filters('the_time', $d, $fmt);
}

function srpp_oth_format_time($time, $fmt) {
	if (!$fmt) $fmt = get_option('time_format');
	$d = mysql2date($fmt, $time);
	$d = apply_filters('get_the_time', $d, $fmt);
	return apply_filters('the_time', $d, $fmt);
}

function srpp_oth_regescape($s) {
	$s = str_replace('\\', '\\\\', $s);
	$s = str_replace('/', '\\/', $s);
	$s = str_replace('[', '\\[', $s);
	$s = str_replace(']', '\\]', $s);
	return $s;
}