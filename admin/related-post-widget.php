<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Adds Suprp_Related_Post_Widget widget.
 */
class Suprp_Related_Post_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'Suprp_Related_Post_Widget', // Base ID
			esc_html__( 'Related Posts List', 'super-related-posts' ), // Name
			array( 'description' => esc_html__( 'Widget to display Related Post', 'super-related-posts' ), ) // Args
		);
	}


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo html_entity_decode(esc_attr($args['before_widget']));
		extract($args, EXTR_SKIP);
		$related_post = empty($instance['related_post']) ? '' : $instance['related_post'];
	
		if(!empty($related_post) && $related_post == 'related_post1'){
			echo do_shortcode("[super-related-posts related_post='1']");
		}
		
		if(!empty($related_post) && $related_post == 'related_post2'){
			echo do_shortcode("[super-related-posts related_post='2']");
		}

		if(!empty($related_post) && $related_post == 'related_post3'){
			echo do_shortcode("[super-related-posts related_post='3']");
		}
		echo html_entity_decode(esc_attr($args['after_widget']));	
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
       
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$related_post = $instance['related_post']; 	?>
		<p>
			<label for="<?php echo $this->get_field_id('related_post'); ?>"><?php esc_attr_e( 'Related Posts type:', 'super-related-posts' ); ?> 
			<select class='widefat' id="<?php echo $this->get_field_id('related_post'); ?>" name="<?php echo $this->get_field_name('related_post'); ?>" >
				<option value='related_post1'<?php echo ($related_post=='related_post1')?'selected':''; ?>>Related Post1</option>
				<option value='related_post2'<?php echo ($related_post=='related_post2')?'selected':''; ?>>Related Post2</option> 
				<option value='related_post3'<?php echo ($related_post=='related_post3')?'selected':''; ?>>Related Post3</option> 
			</select>                
		</label>
	    </p>
       <?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['related_post'] = $new_instance['related_post'];
		return $instance;
	}

} // class Suprp_Related_Post_Widget