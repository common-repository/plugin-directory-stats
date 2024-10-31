<?php
/*
Plugin Name: Plugin Directory Stats
Plugin URI: http://www.near-mint.com/blog/
Description: This plugin gets plugin's download count and other stats from WordPress.ORG and allows you to show them on your blog using shortcode.
Version: 0.1.4
Author: redcocker
Author URI: http://www.near-mint.com/blog/
Text Domain: plugin_dir_stats
Domain Path: /languages
*/
/*
Last modified: 2012/5/15
License: GPL v2
*/
/*
    Copyright 2011 M. Sumitomo

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class PLUGIN_DIRECTORY_STATS {
	var $plugin_dir_stats_plugin_url;
	var $plugin_dir_stats_ver = "0.1.4";
	var $plugin_dir_stats_db_ver = "0.1";
	var $plugin_dir_stats_setting_opt;
	var $plugin_dir_stats_table_name;

	function __construct() {
		global $wpdb;

		load_plugin_textdomain('plugin_dir_stats', false, dirname(plugin_basename(__FILE__)).'/languages');
		$this->plugin_dir_stats_plugin_url = plugin_dir_url(__FILE__);
		$this->plugin_dir_stats_setting_opt = get_option('plugin_dir_stats_setting_opt');
		$this->plugin_dir_stats_table_name = $wpdb->prefix."plugin_dir_stats";

		register_activation_hook(__FILE__, array(&$this, 'plugin_dir_stats_create_db_table'));
		add_action('plugins_loaded', array(&$this, 'plugin_dir_stats_check_db_ver'));

		// Register shortcode
		add_shortcode('plugin_name', array(&$this, 'plugin_dir_stats_name_shortcode_handler'));
		add_shortcode('plugin_version', array(&$this, 'plugin_dir_stats_version_shortcode_handler'));
		add_shortcode('plugin_last_updated', array(&$this, 'plugin_dir_stats_last_upd_shortcode_handler'));
		add_shortcode('plugin_requires', array(&$this, 'plugin_dir_stats_requires_shortcode_handler'));
		add_shortcode('plugin_tested', array(&$this, 'plugin_dir_stats_tested_shortcode_handler'));
		add_shortcode('plugin_dl', array(&$this, 'plugin_dir_stats_dl_shortcode_handler'));
		add_shortcode('plugin_rating', array(&$this, 'plugin_dir_stats_rating_shortcode_handler'));
		add_shortcode('plugin_num_ratings', array(&$this, 'plugin_dir_stats_num_ratings_shortcode_handler'));
		add_shortcode('plugin_download_link', array(&$this, 'plugin_dir_stats_download_link_shortcode_handler'));

		if ($this->plugin_dir_stats_setting_opt['mode'] == "api") {
			add_shortcode('plugin_author', array(&$this, 'plugin_dir_stats_author_shortcode_handler'));
			add_shortcode('plugin_site', array(&$this, 'plugin_dir_stats_site_shortcode_handler'));
			add_shortcode('plugin_hp', array(&$this, 'plugin_dir_stats_hp_shortcode_handler'));
			add_shortcode('plugin_added', array(&$this, 'plugin_dir_stats_added_shortcode_handler'));
			add_shortcode('plugin_compatibility', array(&$this, 'plugin_dir_stats_compatibility_shortcode_handler'));
		}

		if ($this->plugin_dir_stats_setting_opt['mode'] == "directly") {
			add_shortcode('plugin_today', array(&$this, 'plugin_dir_stats_today_shortcode_handler'));
			add_shortcode('plugin_yesterday', array(&$this, 'plugin_dir_stats_yesterday_shortcode_handler'));
			add_shortcode('plugin_last_week', array(&$this, 'plugin_dir_stats_last_week_shortcode_handler'));
		}

		add_shortcode('plugin_stats', array(&$this, 'plugin_dir_stats_basic_stats_shortcode_handler'));
		add_shortcode('plugin_list_all', array(&$this, 'plugin_dir_stats_list_all_shortcode_handler'));
		add_shortcode('plugin_list', array(&$this, 'plugin_dir_stats_list_all_shortcode_handler'));

		// Apply shortcode parser to content in widgets.
		add_filter('widget_text', 'do_shortcode');

		// For setting panel
		add_action('admin_menu', array(&$this, 'plugin_dir_stats_register_menu_item'));
		add_filter('plugin_action_links', array(&$this, 'plugin_dir_stats_setting_link'), 10, 2);
	}

	// Create DB table for cached data
	function plugin_dir_stats_create_db_table() {
		global $wpdb;

		if($wpdb->get_var("SHOW TABLES LIKE '".$this->plugin_dir_stats_table_name."'") != $this->plugin_dir_stats_table_name) {
			$sql = "CREATE TABLE ".$this->plugin_dir_stats_table_name." (
					id BIGINT(11) NOT NULL AUTO_INCREMENT,
					updated BIGINT(11) DEFAULT '0' NOT NULL,
					name VARCHAR(255) NOT NULL,
					value LONGTEXT,
					UNIQUE KEY id (id)
				);";

			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}
	}

	// Create settings array
	function plugin_dir_stats_setting_array() {

		$this->plugin_dir_stats_setting_opt = array(
			"mode" => 'api',
			"expire_time" => 10800,
			);

		// Store in DB
		add_option('plugin_dir_stats_setting_opt', $this->plugin_dir_stats_setting_opt);
		add_option('plugin_dir_stats_updated', 'false');

	}

	// Check DB table version and create table
	function plugin_dir_stats_check_db_ver() {
		$current_checkver_stamp = get_option('plugin_dir_stats_checkver_stamp');
		if (!$current_checkver_stamp || version_compare($current_checkver_stamp, $this->plugin_dir_stats_db_ver, "!=")) {
			$updated_count = 0;
			// For new installation
			if (!$current_checkver_stamp) {
				// Register array
				$this->plugin_dir_stats_setting_array();

				$updated_count = $updated_count + 1;
			}

			update_option('plugin_dir_stats_checkver_stamp', $this->plugin_dir_stats_db_ver);
			// Stamp for showing messages
			if ($updated_count != 0) {
				update_option('plugin_dir_stats_updated', 'true');
			}

		}
	}

	// Register the setting panel
	function plugin_dir_stats_register_menu_item() {
		$post2pdf_conv_page_hook = add_options_page('Plugin Directory Stats Options', 'Plugin Dir Stats', 'manage_options', 'plugin-dir-stats-options', array(&$this, 'plugin_dir_stats_options_panel'));

		if ($plugin_dir_stats_page_hook != null) {
			$plugin_dir_stats_page_hook = '-'.$plugin_dir_stats_page_hook;
		}
		add_action('admin_print_scripts'.$plugin_dir_stats_page_hook, array(&$this, 'plugin_dir_stats_load_jscript_for_admin'));

		if (get_option('plugin_dir_stats_updated') == "true" && !(isset($_POST['PLUGIN_Dir_Stats_Setting_Submit']) && $_POST['plugin_dir_stats_hidden_value'] == "true")) {
			add_action('admin_notices', array(&$this, 'plugin_dir_stats_admin_updated_notice'));
		}

	}

	// Message for admin when DB table updated
	function plugin_dir_stats_admin_updated_notice(){
		echo '<div id="message" class="updated"><p>'.__("Plugin Directory Stats has successfully created new DB table.<br />If you upgraded to this version, some setting options may be added or reset to the default values.<br />Go to the <a href=\"options-general.php?page=plugin-dir-stats-options\">setting panel</a> and configure Plugin Directory Stats now. Once you save your settings, this message will be cleared.", "plugin_dir_stats").'</p></div>';
	}

	function plugin_dir_stats_load_jscript_for_admin(){
		wp_enqueue_script('rc_admin_js', $this->plugin_dir_stats_plugin_url.'rc-admin-js.js', false, '1.3');
	}

	// Show plugin info in the footer
	function plugin_dir_stats_add_admin_footer() {
		$plugin_dir_stats_plugin_data = get_plugin_data(__FILE__);
		printf('%1$s by %2$s<br />', $plugin_dir_stats_plugin_data['Title'].' '.$plugin_dir_stats_plugin_data['Version'], $plugin_dir_stats_plugin_data['Author']);
	}

	// Register the setting panel
	function plugin_dir_stats_setting_link($links, $file) {
		static $this_plugin;
		if (! $this_plugin) $this_plugin = plugin_basename(__FILE__);
		if ($file == $this_plugin){
			$settings_link = '<a href="options-general.php?page=plugin-dir-stats-options">'.__("Settings", "plugin_dir_stats").'</a>';
			array_unshift($links, $settings_link);
		}  
		return $links;
	}

	// Shortcode handler
	function plugin_dir_stats_name_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['name'];
	}

	function plugin_dir_stats_version_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['version'];
	}

	function plugin_dir_stats_author_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['author'];
	}

	function plugin_dir_stats_site_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['site'];
	}

	function plugin_dir_stats_hp_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['homepage'];
	}

	function plugin_dir_stats_last_upd_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['last_updated'];
	}

	function plugin_dir_stats_added_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['added'];
	}

	function plugin_dir_stats_requires_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['requires'];
	}

	function plugin_dir_stats_tested_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['tested'];
	}

	function plugin_dir_stats_dl_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['downloaded'];
	}

	function plugin_dir_stats_today_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['today'];
	}

	function plugin_dir_stats_yesterday_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['yesterday'];
	}

	function plugin_dir_stats_last_week_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['last_week'];
	}

	function plugin_dir_stats_rating_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['rating'];
	}

	function plugin_dir_stats_num_ratings_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['num_ratings'];
	}

	function plugin_dir_stats_compatibility_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['compatibility'];
	}

	function plugin_dir_stats_download_link_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		return $plugin_stats['download_link'];
	}

	function plugin_dir_stats_basic_stats_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$plugin_stats = $this->plugin_dir_stats_get_stats_by_url($src);

		if ($this->plugin_dir_stats_setting_opt['mode'] == "directly") {
			$download_detail = "<small>(".__('Today: ', 'plugin_dir_stats').$plugin_stats['today']." ".__('Yesterday: ', 'plugin_dir_stats').$plugin_stats['yesterday']." ".__('Last Week: ', 'plugin_dir_stats').$plugin_stats['last_week'].")</small><br />";
			$date_detail = "";
		} else {
			$download_detail = "";
			$date_detail = "<strong>".__('Relase: ', 'plugin_dir_stats')."</strong>".$plugin_stats['added']."<br />";
		}

		$stats = "<p class=\"plugin-basic-stats\"><strong>".__('Version: ', 'plugin_dir_stats')."</strong>".$plugin_stats['version']."<br /><strong>".__('Last Updated: ', 'plugin_dir_stats')."</strong>".$plugin_stats['last_updated']."<br />".$date_detail."<strong>".__('Requires: ', 'plugin_dir_stats')."</strong>".$plugin_stats['requires']."<br /><strong>".__('Tested up to: ', 'plugin_dir_stats')."</strong>".$plugin_stats['tested']."<br />"."<strong>".__('Downloads: ', 'plugin_dir_stats')."</strong>".$plugin_stats['downloaded']."<br />".$download_detail."<strong>".__('Average Rating: ', 'plugin_dir_stats')."</strong>".$plugin_stats['rating']."/5 (".$plugin_stats['num_ratings'].__(' ratings', 'plugin_dir_stats').")</p>";

		return $stats;
	}

	function plugin_dir_stats_list_all_shortcode_handler($atts) {
		extract(shortcode_atts(array(
			'src' => '',
		), $atts));

		$slug = str_replace('http://profiles.wordpress.org/', '', $src);
		$slug = str_replace('/', '', $slug);
		$slug = sanitize_title($slug);

		$plugin_list = $this->plugin_dir_stats_get_list_all_by_url($src);
		$plugin_list = $plugin_list[$slug];
		$stats = "";

		foreach ($plugin_list as $key => $val) {
			$stats = $stats."<li><strong>".$val[0]."</strong><br /><small>".__('Downloads: ', 'plugin_dir_stats').$val[1]."</small></li>";
		}

		return "<p class=\"plugin-list-all\"><ul>".$stats."</ul></p>";
	}

	// Get plugin stats by URL
	function plugin_dir_stats_get_stats_by_url($address) {
		global $wpdb;

		if (strpos($address, 'http://wordpress.org/extend/plugins/') === false) {
			$address = "http://wordpress.org/extend/plugins/".$address;
		}

		$address = esc_url($address);
		$chached_d_date = '';
		$elapsed_time = 0;
		$expire_time = intval($this->plugin_dir_stats_setting_opt['expire_time']);

		if ($expire_time != 0) {
			$slug = str_replace('http://wordpress.org/extend/plugins/', '', $address);
			$slug = str_replace('/', '', $slug);
			$slug = sanitize_title($slug);

			$chached_d_date = $wpdb->get_var($wpdb->prepare(
				"SELECT updated FROM $this->plugin_dir_stats_table_name WHERE name = %s",
				$slug));

			$current_u_time = time();
			$elapsed_time = $current_u_time - $chached_d_date;
			$y = date("Y", $current_u_time);
			$m = date("m", $current_u_time);
			$d = date("d", $current_u_time);
			$midnight = mktime(0, 0, 0, $m, $d, $y);
			$next_day = $midnight - $chached_d_date;
		}

		if ($expire_time == 0 || empty($chached_d_date) || (!empty($chached_d_date) && ($elapsed_time > $expire_time || $next_day > 0))) {
			if ($this->plugin_dir_stats_setting_opt['mode'] == "directly") { 
				$address = rtrim($address, '/');
				$address = $address."/stats/";

				$plugin_stats = $this->plugin_dir_stats_get_element($this->plugin_dir_stats_get_body($address));
			} else {
				$plugin_stats = $this->plugin_dir_stats_get_stats($this->plugin_dir_stats_get_info($address));
			}
			
			if ($expire_time != 0) {
				if (empty($chached_d_date)) {
					$result = $wpdb->query($wpdb->prepare("
						INSERT INTO $this->plugin_dir_stats_table_name
						(updated, name, value)
						VALUES ( %d, %s, %s )", 
        					$current_u_time, $slug, maybe_serialize($plugin_stats)));
				} else {
					$result = $wpdb->query($wpdb->prepare("
						UPDATE $this->plugin_dir_stats_table_name
						SET updated =%d, value= %s
						WHERE name = %s",
						$current_u_time, maybe_serialize($plugin_stats), $slug));
				}
			}
		} else {
			$plugin_stats = $wpdb->get_var($wpdb->prepare(
				"SELECT value FROM $this->plugin_dir_stats_table_name WHERE name = %s",
				$slug));
			$plugin_stats = maybe_unserialize($plugin_stats);
		}

		return $plugin_stats;
	}

	// Get plugin list all by URL
	function plugin_dir_stats_get_list_all_by_url($address) {
		global $wpdb;

		if (strpos($address, 'http://profiles.wordpress.org/') === false) {
			$address = "http://profiles.wordpress.org/".$address ."/";
		}

		$address = esc_url($address);
		$elapsed_time = 0;
		$expire_time = intval($this->plugin_dir_stats_setting_opt['expire_time']);
		$slug = str_replace('http://profiles.wordpress.org/', '', $address);
		$slug = str_replace('/', '', $slug);
		$slug = sanitize_title($slug);

		if ($expire_time != 0) {
			$chached_l_date = $wpdb->get_var($wpdb->prepare(
				"SELECT updated FROM $this->plugin_dir_stats_table_name WHERE name = %s",
				$slug."_list_all"));

			$current_u_time = time();
			$elapsed_time = $current_u_time - $chached_l_date;
			$y = date("Y", $current_u_time);
			$m = date("m", $current_u_time);
			$d = date("d", $current_u_time);
			$midnight = mktime(0, 0, 0, $m, $d, $y);
			$next_day = $midnight - $chached_l_date;
		}

		if ($expire_time == 0 || empty($chached_l_date) || (!empty($chached_l_date) && ($elapsed_time > $expire_time || $next_day > 0))) {
			$plugin_list = $this->plugin_dir_stats_get_list_all($this->plugin_dir_stats_get_body($address));
			$plugin_list[$slug] = $plugin_list;

			if ($expire_time != 0) {
				if (empty($chached_l_date)) {
					$result = $wpdb->query($wpdb->prepare("
						INSERT INTO $this->plugin_dir_stats_table_name
						(updated, name, value)
						VALUES ( %d, %s, %s )", 
        					$current_u_time, $slug."_list_all", maybe_serialize($plugin_list)));
				} else {
					$result = $wpdb->query($wpdb->prepare("
						UPDATE $this->plugin_dir_stats_table_name
						SET updated =%d, value= %s
						WHERE name = %s",
						$current_u_time, maybe_serialize($plugin_list), $slug."_list_all"));
				}
			}
		} else {
			$plugin_list = $wpdb->get_var($wpdb->prepare(
				"SELECT value FROM $this->plugin_dir_stats_table_name WHERE name = %s",
				$slug."_list_all"));
			$plugin_list = maybe_unserialize($plugin_list);
		}

		return $plugin_list;
	}

	// Abstract plugin stats from plugins API data
	function plugin_dir_stats_get_stats($api) {
		$plugin_stats = array();

		if (!empty($api) && !is_wp_error($api)) {
			if (!empty($api->name)) {
				$plugin_stats['name'] = $api->name;
			}

			if (!empty($api->version)) {
				$plugin_stats['version'] = $api->version;
			}

			if (!empty($api->author)) {
				$plugin_stats['author'] = $api->author;
				preg_match("@<a href=\"([^\"]+?)\">.+?</a>@", $plugin_stats['author'], $matches);
				$plugin_stats['site'] = esc_url($matches[1]);
			}

			if (!empty($api->homepage)) {
				$plugin_stats['homepage'] = esc_url($api->homepage);
			}

			if (!empty($api->last_updated)) {
				$plugin_stats['last_updated'] = $api->last_updated;
			}

			if (!empty($api->added)) {
				$plugin_stats['added'] = $api->added;
			}

			if (!empty($api->requires)) {
				$plugin_stats['requires'] = $api->requires;
			}

			if (!empty($api->tested)) {
				$plugin_stats['tested'] = $api->tested;
			}

			if (!empty($api->downloaded)) {
				$plugin_stats['downloaded'] = $api->downloaded;
				$plugin_stats['downloaded'] = number_format_i18n($plugin_stats['downloaded']);
			}

			$plugin_stats['today'] = '';
			$plugin_stats['yesterday'] = '';
			$plugin_stats['last_week'] = '';

			if (!empty($api->rating)) {
				$plugin_stats['rating'] = $api->rating;
				$plugin_stats['rating'] = round($plugin_stats['rating']/20, 2);
			}

			if (!empty($api->num_ratings)) {
				$plugin_stats['num_ratings'] = $api->num_ratings;
				$plugin_stats['num_ratings'] = number_format($plugin_stats['num_ratings']);
			}

			if (!empty($api->compatibility)) {
				$compatibility = $api->compatibility;
				$plugin_stats['compatibility'] = $compatibility[$GLOBALS['wp_version']][$plugin_stats['version']][0];
			}

			if (!empty($api->download_link)) {
				$plugin_stats['download_link'] = esc_url($api->download_link);
			}

		}

		return $plugin_stats;
	}

	// Abstract plugin stats from plguin page content
	function plugin_dir_stats_get_element($data) {
		$plugin_stats = array();

		if (!empty($data) && !is_wp_error($data)) {
			preg_match("@<h2>((?!<a href=\"/extend/plugins\">Plugin Directory</a>).+?)</h2>@", $data, $matches);
			$plugin_stats['name'] = $matches[1];
			unset($matches);

			preg_match("@<p class=\"button\"><a href='http://downloads\.wordpress\.org/plugin/.+?\.zip'>Download Version (.+?)</a></p>@", $data, $matches);
			$plugin_stats['version'] = $matches[1];
			unset($matches);

			$plugin_stats['author'] = '';
			$plugin_stats['site'] = '';
			$plugin_stats['homepage'] = '';

			preg_match("@<strong>Last Updated: </strong> ([0-9]{4}-[0-9]{1,2}-[0-9]{1,2})@", $data, $matches);
			$plugin_stats['last_updated'] = $matches[1];
			unset($matches);

			$plugin_stats['added'] = '';

			preg_match("@<strong>Requires: </strong>(.+?) or higher<br />@", $data, $matches);
			$plugin_stats['requires'] = $matches[1];
			unset($matches);

			preg_match("@<strong>Compatible up to: </strong>(.+?)<br />@", $data, $matches);
			$plugin_stats['tested'] = $matches[1];
			unset($matches);

			preg_match("@<strong>Downloads: </strong>(.*?)<br />@", $data, $matches);
			$plugin_stats['downloaded'] = str_replace(',','',$matches[1]);
			unset($matches);
			settype($plugin_stats['downloaded'], 'integer');
			$plugin_stats['downloaded'] = number_format_i18n($plugin_stats['downloaded']);

			preg_match("@<th scope=\"row\">Today</th>.*?<td>(.+?)</td>@s", $data, $matches);
			$plugin_stats['today'] = str_replace(',','',$matches[1]);
			unset($matches);
			settype($plugin_stats['today'], 'integer');
			$plugin_stats['today'] = number_format_i18n($plugin_stats['today']);

			preg_match("@<th scope=\"row\">Yesterday</th>.*?<td>(.+?)</td>@s", $data, $matches);
			$plugin_stats['yesterday'] = str_replace(',','',$matches[1]);
			unset($matches);
			settype($plugin_stats['yesterday'], 'integer');
			$plugin_stats['yesterday'] = number_format_i18n($plugin_stats['yesterday']);

			preg_match("@<th scope=\"row\">Last Week</th>.*?<td>(.+?)</td>@s", $data, $matches);
			$plugin_stats['last_week'] = str_replace(',','',$matches[1]);
			unset($matches);
			settype($plugin_stats['last_week'], 'integer');
			$plugin_stats['last_week'] = number_format_i18n($plugin_stats['last_week']);

			preg_match("@<div class=\"star star-rating\" style=\"width: (.+?)px\"></div>@", $data, $matches);
			$plugin_stats['rating'] = $matches[1];
			unset($matches);
			settype($plugin_stats['rating'], 'float');
			$plugin_stats['rating'] = round($plugin_stats['rating']/20, 2);

			preg_match("@<span>\((.+?) ratings\)</span>@", $data, $matches);
			$plugin_stats['num_ratings'] = str_replace(',','',$matches[1]);
			unset($matches);
			settype($plugin_stats['num_ratings'], 'integer');
			$plugin_stats['num_ratings'] = number_format_i18n($plugin_stats['num_ratings']);

			$plugin_stats['compatibility'] = '';

			preg_match("@<p class=\"button\"><a href='(http://downloads.wordpress.org/plugin/.+?\.zip)'>Download Version .+?</a></p>@", $data, $matches);
			$plugin_stats['download_link'] = esc_url($matches[1]);
			unset($matches);

		}

		return $plugin_stats;
	}

	// Abstract plugin stats from WordPress.COM profile page
	function plugin_dir_stats_get_list_all($data) {
		$match_num_name = preg_match_all("@<h3>(<a href=\"http://wordpress\.org/extend/plugins/.+?/\">(.+?)</a>)</h3>@", $data, $matches_name);
		$match_num_dl = preg_match_all("@<p class=\"downloads\">(.+?) downloads</p>@", $data, $matches_dl);

		if ($match_num_name == $match_num_dl && $match_num_name != 0 && $match_num_name != false) {
			for ($i = 0; $i < $match_num_name; $i++) {
				$plugin_name = $matches_name[2][$i];
				$plugin_link = $matches_name[1][$i];
				$plugin_dl = str_replace(',', '', $matches_dl[1][$i]);
				settype($plugin_dl, 'integer');
				$plugin_dl = number_format_i18n($plugin_dl);

				$plugins[$plugin_name][0] = $plugin_link;
				$plugins[$plugin_name][1] = $plugin_dl;
			}
		} else {
			$plugins['none'][0] = 'none';
			$plugins['none'][1] = 'none';
		}
		
		return $plugins;
	}

	// Abstract plugin stats from plugins profile page
	function plugin_dir_stats_get_list($data) {
		$match_num_name = preg_match_all("@<h3>(<a href=\"http://wordpress\.org/extend/plugins/.+?/\">(.+?)</a>)</h3>@", $data, $matches_name);
		$match_num_ver = preg_match_all("@<li><span class=\"info-marker\">Version</span> (.+?)</li>@", $data, $matches_ver);
		$match_num_upd = preg_match_all("@<li><span class=\"info-marker\">Updated</span> (.+?)</li>@", $data, $matches_upd);
		$match_num_dl = preg_match_all("@<li><span class=\"info-marker\">Downloads</span> (.+?)</li>@", $data, $matches_dl);
		$match_num_rating = preg_match_all("@<div class=\"star star-rating\" style=\"width: (.+?)px\"></div>@", $data, $matches_rating);

		if ($match_num_name == $match_num_ver && $match_num_ver == $match_num_upd && $match_num_upd == $match_num_dl && $match_num_dl == $match_num_rating && $match_num_name != 0 && $match_num_name != false) {
			for ($i = 0; $i < $match_num_name; $i++) {
				$plugin_name = $matches_name[2][$i];
				$plugin_link = $matches_name[1][$i];
				$plugin_ver = $matches_ver[1][$i];
				$plugin_upd = $matches_upd[1][$i];
				$plugin_dl = str_replace(',', '', $matches_dl[1][$i]);
				settype($plugin_dl, 'integer');
				$plugin_dl = number_format_i18n($plugin_dl);

				$plugin_rating = $matches_rating[1][$i];
				settype($plugin_rating, 'float');
				$plugin_rating = round($plugin_rating/20, 2);

				$plugins[$plugin_name][0] = $plugin_link;
				$plugins[$plugin_name][1] = $plugin_ver;
				$plugins[$plugin_name][2] = $plugin_upd;
				$plugins[$plugin_name][3] = $plugin_dl;
				$plugins[$plugin_name][4] = $plugin_rating;
			}
		} else {
			$plugins['none'][0] = 'none';
			$plugins['none'][1] = 'none';
			$plugins['none'][2] = 'none';
			$plugins['none'][3] = 'none';
			$plugins['none'][4] = 'none';
		}
		
		return $plugins;
	}

	// Get content body by HTTP request
	function plugin_dir_stats_get_body($address) {
		$body = -1;
		$response_data = wp_remote_get($address);

		if (is_wp_error($response_data) || $response_data['response']['code'] !== 200) {
			return $body;
		}

		$body = $response_data['body'];

		return $body;
	}

	// Get plugin info by API
	function plugin_dir_stats_get_info($address) {
		$slug = str_replace('http://wordpress.org/extend/plugins/', '', $address);
		$slug = str_replace('/', '', $slug);
		$slug = sanitize_title($slug);

		require_once(ABSPATH.'wp-admin/includes/plugin-install.php');

		$api = plugins_api('plugin_information', array('slug' => $slug));

		return $api;
	}

	// Setting panel
	function plugin_dir_stats_options_panel(){
		global $wpdb;

		if (is_admin()) {
			include_once('plugin-directory-stats-admin.php');
		}

	}

}

// Start this plugin
$PLUGIN_DIRECTORY_STATS = new PLUGIN_DIRECTORY_STATS();

?>