<?php
/**
 * Class SUPER_Gutenberg
 *
 * @author   Magazine3
 * @category Backend
 * @path  modules/gutenberg/includes/class-gutenberg
 * @Since Version 1.9.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class SUPER_Gutenberg {

        /**
         * Static private variable to hold instance this class
         * @var type 
         */
        private static $instance;
        private $service;
        private $render;
        
        private $blocks = array(
            'relatedposts' => array(            
                'handler'      => 'super-related-posts-js-reg',                                
                'block_name'   => 'related-post-block',
                'render_func'  => 'render_related_posts_data',
                'style'        => 'super-g-related-posts-css',
                'editor'       => 'super-gutenberg-css-reg-editor',
                'local_var'    => 'superGutenbergRelatedPost',
                'local'        => array()
            ),
        );

        /**
         * This is class constructer to use all the hooks and filters used in this class
         */
        private function __construct() {
                   
            foreach ($this->blocks as $key => $value) {
                $this->blocks[$key]['path'] = SRPP_PLUGIN_URI. 'includes/gutenberg/assets/blocks/'.$key.'.js'; 
            }
           
            if($this->render == null){
                require_once SRPP_DIR_NAME.'/includes/gutenberg/includes/render.php';
                $this->render = new SUPER_Gutenberg_Render();
            }
            
            
            add_action( 'init', array( $this, 'register_super_blocks' ) );                    
            add_action( 'enqueue_block_editor_assets', array( $this, 'register_admin_assets' ) ); 
        }

        /**
         * Function to enqueue admin assets for gutenberg blocks
         * @Since Version 1.9.7
         */
        public function register_admin_assets() {

            global $pagenow;
          
            if ( !function_exists( 'register_block_type' ) ) {
                    // no Gutenberg, Abort
                    return;
            }		
             
            if($this->blocks){
            
                foreach($this->blocks as $key => $block){  

                    if ( $pagenow != 'widgets.php' ){
                        wp_register_script(
                            $block['handler'],
                            $block['path'],
                            array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor' )                                 
                        );
                    }
                
                    wp_localize_script( $block['handler'], $block['local_var'], $block['local'] );
                    wp_enqueue_script( $block['handler'] );
                }
                
            } 
                                                 
        }
        
        /**
         * Register a how to block
         * @return type
         * @since version 1.9.7
         */
        public function register_super_blocks() {
                
            if ( !function_exists( 'register_block_type' ) ) {
                    // no Gutenberg, Abort
                    return;
            }		                  		    
            
            if($this->blocks){
                
                foreach($this->blocks as $block){
                    register_block_type( 'super/'.$block['block_name'], array(
                        'style'           => $block['style'],
                        'editor_style'    => $block['editor'],
                        'editor_script'   => $block['handler'],
                        'render_callback' => array( $this, $block['render_func'] ),
                ) );
                    
                }
                                
            }                                        
        }

        public function render_related_posts_data($attributes){
          
             ob_start();
            
            if ( !isset( $attributes ) ) {
                ob_end_clean();                                     
                return '';
            }
            
            echo $this->render->related_posts_block_data($attributes);
            
            return ob_get_clean();
            
        }

	        
        /**
         * Return the unique instance 
         * @return type instance
         * @since version 1.9.7
         */
        public static function get_instance() {
            if ( null == self::$instance ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

}

if ( class_exists( 'SUPER_Gutenberg') ) {
	SUPER_Gutenberg::get_instance();
}