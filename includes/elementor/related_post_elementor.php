<?php
namespace ElementorSuper\Widgets;

use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Related_Post_Elementor extends Widget_Base {

	public function get_name() {
		return 'related-post';
	}

	public function get_title() {
		return __( 'RELATED POST', 'related-post' );
	}

	public function get_icon() {
		return 'dashicons dashicons-welcome-widgets-menus';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_script_depends() {
		return [ 'elementor-related-post' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'related-post' )
			]
		);

		$this->add_control(
			'seleted_related_post',
			[
				'label' => __( 'Select a Display Option', 'related-post' ),
				'type'  => \Elementor\Controls_Manager::SELECT,
				'default'       => 'related_post_1',
				'options' => [
					'related_post_1'   => esc_html__( 'Related Post1', 'elementor' ),
					'related_post_2'   => esc_html__( 'Related Post2', 'elementor' ),
					'related_post_3'   => esc_html__( 'Related Post3', 'elementor' ),
				],
			]
		);
		
		$this->end_controls_section();
	}

	protected function render() {
	 	$settings = $this->get_settings_for_display();
		 if ( ! empty( $settings['seleted_related_post'] ) ) :
			if( 'related_post_1' === $settings['seleted_related_post'] ){ 
				echo do_shortcode("[super-related-posts related_post='1']");
			}
			if( 'related_post_2' === $settings['seleted_related_post'] ){ 
				echo do_shortcode("[super-related-posts related_post='2']");
			}
			if( 'related_post_3' === $settings['seleted_related_post'] ){ 
				echo do_shortcode("[super-related-posts related_post='3']");
			}
		endif;
		 wp_reset_postdata(); 
	}

	protected function _content_template() {
	?>
		<# 		
		if ( settings.seleted_related_post == 'related_post_1') 
		#>
	 	 <?php echo do_shortcode("[super-related-posts related_post='1']"); ?>
		<# 		
		if ( settings.seleted_related_post == 'related_post_2') 
		#>
	 	<?php echo do_shortcode("[super-related-posts related_post='2']"); ?>
		 <# 		
		if ( settings.seleted_related_post == 'related_post_3') 
		#>
	     <?php echo do_shortcode("[super-related-posts related_post='3']"); ?>
		<?php
	}
}