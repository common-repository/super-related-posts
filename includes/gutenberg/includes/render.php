<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SUPER_Gutenberg_Render {
    
    public function related_posts_block_data($attributes){ 
   
        if(!empty($attributes)){
            if( 'related_post_1' === $attributes['related_post'] ){ 
                return do_shortcode("[super-related-posts related_post='1']");
            }
            if( 'related_post_2' === $attributes['related_post'] ){ 
                return do_shortcode("[super-related-posts related_post='2']");
            }
            if( 'related_post_3' === $attributes['related_post'] ){ 
                return do_shortcode("[super-related-posts related_post='3']");
            }
            
        }
        
    }
    
}