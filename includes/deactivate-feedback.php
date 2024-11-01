<?php 
$reasons = array(
    		1 => '<li><label><input type="radio" name="suprp_disable_reason" value="temporary"/>' . __('It is only temporary', 'super_related_posts') . '</label></li>',
		2 => '<li><label><input type="radio" name="suprp_disable_reason" value="stopped showing super related posts"/>' . __('I stopped showing Super related posts on my site', 'super_related_posts') . '</label></li>',
		3 => '<li><label><input type="radio" name="suprp_disable_reason" value="missing feature"/>' . __('I miss a feature', 'super_related_posts') . '</label></li>
		<li><input type="text" name="suprp_disable_text[]" value="" placeholder="Please describe the feature"/></li>',
		4 => '<li><label><input type="radio" name="suprp_disable_reason" value="technical issue"/>' . __('Technical Issue', 'super_related_posts') . '</label></li>
		<li><textarea name="suprp_disable_text[]" placeholder="' . __('Can we help? Please describe your problem', 'super_related_posts') . '"></textarea></li>',
		5 => '<li><label><input type="radio" name="suprp_disable_reason" value="other plugin"/>' . __('I switched to another plugin', 'super_related_posts') .  '</label></li>
		<li><input type="text" name="suprp_disable_text[]" value="" placeholder="Name of the plugin"/></li>',
		6 => '<li><label><input type="radio" name="suprp_disable_reason" value="other"/>' . __('Other reason', 'super_related_posts') . '</label></li>
		<li><textarea name="suprp_disable_text[]" placeholder="' . __('Please specify, if possible', 'super_related_posts') . '"></textarea></li>',
    );
shuffle($reasons);
?>


<div id="suprp-reloaded-feedback-overlay" style="display: none;">
    <div id="suprp-reloaded-feedback-content">
	<form action="" method="post">
	    <h3><strong><?php _e('If you have a moment, please let us know why you are deactivating:', 'super_related_posts'); ?></strong></h3>
	    <ul>
                <?php 
                foreach ($reasons as $reason){
                    echo $reason;
                }
                ?>
	    </ul>
	    <?php if ($email) : ?>
    	    <input type="hidden" name="suprp_disable_from" value="<?php echo $email; ?>"/>
	    <?php endif; ?>
	    <input id="suprp-reloaded-feedback-submit" class="button button-primary" type="submit" name="suprp_disable_submit" value="<?php _e('Submit & Deactivate', 'super_related_posts'); ?>"/>
	    <a class="button"><?php _e('Only Deactivate', 'super_related_posts'); ?></a>
	    <a class="suprp-feedback-not-deactivate" href="#"><?php _e('Don\'t deactivate', 'super_related_posts'); ?></a>
	    <?php if (function_exists('wp_nonce_field')) wp_nonce_field('srp_feedback_check_nonce', 'srp_feedback_nonce'); ?>
	</form>
    </div>
</div>