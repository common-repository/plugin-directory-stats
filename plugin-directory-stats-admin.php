<?php
/*
For dashboard
by Redcocker
Last modified: 2012/3/6
License: GPL v2
http://www.near-mint.com/blog/
*/

if(!function_exists('current_user_can') || !current_user_can('manage_options')){
	die(__('Cheatin&#8217; uh?'));
}

add_action('in_admin_footer', array(&$this, 'plugin_dir_stats_add_admin_footer'));

// Update setting options
if (isset($_POST['PLUGIN_Dir_Stats_Setting_Submit']) && $_POST['plugin_dir_stats_hidden_value'] == "true" && check_admin_referer("plugin_dir_stats_update_options", "_wpnonce_update_options")) {
	// Delete cache data when mode changed
	if ($this->plugin_dir_stats_setting_opt['mode'] != $_POST['mode']) {
		$result = $wpdb->query($wpdb->prepare("DELETE FROM $this->plugin_dir_stats_table_name")); 
	}
	// Get new value
	$this->plugin_dir_stats_setting_opt['mode'] = $_POST['mode'];
	$this->plugin_dir_stats_setting_opt['expire_time'] = stripslashes($_POST['expire_time']);
	// Transforming
	$this->plugin_dir_stats_setting_opt['expire_time']  = intval(strip_tags($this->plugin_dir_stats_setting_opt['expire_time']));

	// Store in DB
	update_option('plugin_dir_stats_setting_opt', $this->plugin_dir_stats_setting_opt);
	update_option('plugin_dir_stats_updated', 'false');

	// Show message for admin
	echo "<div id='setting-error-settings_updated' class='updated fade'><p><strong>".__("Settings saved.","plugin_dir_stats")."</strong></p></div>";
}

if (isset($_POST['PLUGIN_Dir_Stats_Clear_Cache']) && $_POST['plugin_dir_stats_clear_cache_hidden_value'] == "true" && check_admin_referer("plugin_dir_stats_clear_cache", "_wpnonce_clear_cache")) {
	$result = $wpdb->query($wpdb->prepare("DELETE FROM $this->plugin_dir_stats_table_name")); 

	// Show message for admin
	echo "<div id='setting-error-settings_updated' class='updated fade'><p><strong>".__("All cached data were deleted.", "plugin_dir_stats")."</strong></p></div>";
}

?>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Plugin Directory Stats</h2>
	<form method="post" action="">
	<?php wp_nonce_field("plugin_dir_stats_update_options", "_wpnonce_update_options"); ?>
	<input type="hidden" name="plugin_dir_stats_hidden_value" value="true" />
	<h3><?php _e("1. Settings", "plugin_dir_stats") ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Data request mode", "plugin_dir_stats") ?></th> 
				<td>
					<select name="mode">
						<option value="api" <?php if ($this->plugin_dir_stats_setting_opt['mode'] == "api") {echo 'selected="selected"';} ?>><?php _e("API", "plugin_dir_stats") ?></option>
						<option value="directly" <?php if ($this->plugin_dir_stats_setting_opt['mode'] == "directly") {echo 'selected="selected"';} ?>><?php _e("Directly", "plugin_dir_stats") ?></option>
					</select>
					<p><small><?php _e("When API is chosen, this plugin requests data using plugins_api() function.<br />When Directly is chosen, this plugin requests data to individual plugin page directly.<br />Therefore, after <a href=\"http://wordpress.org/extend/plugins/\">Plugin Directory</a> will be changed, this plugin may yet fail to get data.", "plugin_dir_stats") ?></small></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Keep data for', 'plugin_dir_stats') ?></th>
				<td>
					<input type="text" name="expire_time" size="20" value="<?php echo esc_html($this->plugin_dir_stats_setting_opt['expire_time']); ?>" /> <?php _e("seconds", "plugin_dir_stats") ?><br />
					<p><small><?php _e("Enter cache data TTL.", "plugin_dir_stats") ?></small></p>
				</td>
			</tr>
		</table>
		<p class="submit">
		<input type="submit" name="PLUGIN_Dir_Stats_Setting_Submit" value="<?php _e("Save Changes", "plugin_dir_stats") ?>" />
		</p>
	</form>
	<h3><?php _e("2. Clear Cache", "plugin_dir_stats") ?></h3>
	<form method="post" action="" onsubmit="return confirmcache()">
	<?php wp_nonce_field("plugin_dir_stats_clear_cache", "_wpnonce_clear_cache"); ?>
		<p class="submit">
		<input type="hidden" name="plugin_dir_stats_clear_cache_hidden_value" value="true" />
		<input type="submit" name="PLUGIN_Dir_Stats_Clear_Cache" value="<?php _e("Clear Cache", "plugin_dir_stats") ?>" />
		</p>
	</form>
	<h3><a href="javascript:showhide('id1');" name="system_info"><?php _e("3. Your System Info", "plugin_dir_stats") ?></a></h3>
	<div id="id1" style="display:none; margin-left:20px">
	<p>
	<?php _e("Server OS:", "plugin_dir_stats") ?> <?php echo php_uname('s').' '.php_uname('r'); ?><br />
	<?php _e("PHP version:", "plugin_dir_stats") ?> <?php echo phpversion(); ?><br />
	<?php _e("MySQL version:", "plugin_dir_stats") ?> <?php echo mysql_get_server_info(); ?><br />
	<?php _e("WordPress version:", "plugin_dir_stats") ?> <?php bloginfo("version"); ?><br />
	<?php _e("Site URL:", "plugin_dir_stats") ?> <?php if(function_exists("home_url")) { echo home_url(); } else { echo get_option('home'); } ?><br />
	<?php _e("WordPress URL:", "plugin_dir_stats") ?> <?php echo site_url(); ?><br />
	<?php _e("WordPress language:", "plugin_dir_stats") ?> <?php bloginfo("language"); ?><br />
	<?php _e("WordPress character set:", "plugin_dir_stats") ?> <?php bloginfo("charset"); ?><br />
	<?php _e("WordPress theme:", "plugin_dir_stats") ?> <?php $plugin_dir_stats_theme = get_theme(get_current_theme()); echo $plugin_dir_stats_theme['Name'].' '.$plugin_dir_stats_theme['Version']; ?><br />
	<?php _e("Plugin Directory Stats version:", "plugin_dir_stats") ?> <?php echo $this->plugin_dir_stats_ver; ?><br />
	<?php _e("Plugin Directory Stats DB version:", "plugin_dir_stats") ?> <?php echo get_option('plugin_dir_stats_checkver_stamp'); ?><br />
	<?php _e("Plugin Directory Stats URL:", "plugin_dir_stats") ?> <?php echo $this->plugin_dir_stats_plugin_url; ?><br />
	<?php _e("Your browser:", "plugin_dir_stats") ?> <?php echo esc_html($_SERVER['HTTP_USER_AGENT']); ?>
	</p>
	</div>
	<p>
	<?php _e("To report a bug ,submit requests and feedback, ", "plugin_dir_stats") ?><?php _e("Use <a href=\"http://wordpress.org/tags/plugin-directory-stats?forum_id=10\">Forum</a> or <a href=\"http://www.near-mint.com/blog/contact\">Mail From</a>", "plugin_dir_stats") ?>
	</p>
	</div>
<?php 