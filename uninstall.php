<?php

	if (defined('ABSPATH') && defined('WP_UNINSTALL_PLUGIN')) {
		global $wpdb, $table_prefix;

		delete_option('super-related-posts');
		delete_option('super-related-posts-feed');
		delete_option('widget_rrm_super_related_posts');
		delete_option('srp_posts_offset');
		delete_option('srp_posts_caching_status');

		$table_name = $table_prefix . 'super_related_posts';
		$wpdb->query("DROP TABLE `$table_name`");

		$cached_table = $table_prefix . 'super_related_cached';
		$wpdb->query("DROP TABLE `$cached_table`");
	}