<?php
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {exit();}
delete_option('plugin_dir_stats_updated');
delete_option('plugin_dir_stats_checkver_stamp');
delete_option('plugin_dir_stats_setting_opt');
global $wpdb;
$plugin_dir_stats_table_name = $wpdb->prefix."plugin_dir_stats";
if($wpdb->get_var("SHOW TABLES LIKE '".$plugin_dir_stats_table_name."'") == $plugin_dir_stats_table_name) {
	$wpdb->query("DROP TABLE IF EXISTS $plugin_dir_stats_table_name");
}
?>
