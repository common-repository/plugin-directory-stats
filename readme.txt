=== Plugin Directory Stats ===
Contributors: redcocker
Donate link: http://www.near-mint.com/blog/donate
Tags: plugin, directory, download, stat
Requires at least: 2.8
Tested up to: 3.3.1
Stable tag: 0.1.4

This plugin gets plugin's download count and other stats from WordPress.ORG and allows you to show them on your blog using shortcode.

== Description ==

This plugin gets plugin's download count and other stats from WordPress.ORG and allows you to show them on your blog using shortcode.

= Features =

* Easy and flexible.
* Caching stats data.
* Localization: English(Default), 日本語(Japanese, UTF-8).

== Installation ==

= Installation =

1. Upload plugin folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. If you need, go to "Settings" -> "Plugin Dir Stats" to configure.

Note: Don't set "Keep data for" option to very short time. Frequent data requests may cause overload on your site and it isn't a good thing for WordPress.ORG.

= Usage =

Insert following shortcode to your post/page/widget.

**Formatted stats**

* Individual plugin stats: `[plugin_stats src="PLUGIN_SLUG"]`
* All plugins list by author: `[plugin_list src="AUTHOR'S_DISPLAY_NAME"]`

Here are examples.

If plugin page's url is `http://wordpress.org/extend/plugins/plugin-directory-stats/`, `PLUGIN_SLUG` is `plugin-directory-stats`.

* e.g. `[plugin_stats src="plugin-directory-stats"]`

You can find `AUTHOR'S_DISPLAY_NAME` in indivisual plugin pages or author's profile pages.

* e.g. `[plugin_list src="redcocker"]`

Note: "All plugins list by author" is always run in "Directly" mode.

**Individual values**

You can show individual values.

For "API" mode

* Author: `[plugin_author src="PLUGIN_SLUG"]`
* Author's site: `[plugin_site src="PLUGIN_SLUG"]`
* Plugin page: `[plugin_hp src="PLUGIN_SLUG"]`
* Release date: `[plugin_added src="PLUGIN_SLUG"]`
* Compatibility with installed WP: `[plugin_compatibility src="PLUGIN_SLUG"]`

For "Directly" mode

* Downloads today: `[plugin_today src="PLUGIN_SLUG"]`
* Downloads yesterday: `[plugin_yesterday src="PLUGIN_SLUG"]`
* Downloads last week: `[plugin_last_week src="PLUGIN_SLUG"]`

For both mode

* Plugin name: `[plugin_name src="PLUGIN_SLUG"]`
* Current version: `[plugin_version src="PLUGIN_SLUG"]`
* Last Updated: `[plugin_last_updated src="PLUGIN_SLUG"]`
* Requires: `[plugin_requires src="PLUGIN_SLUG"]`
* Tested up to: `[plugin_tested src="PLUGIN_SLUG"]`
* Downloads all time: `[plugin_dl src="PLUGIN_SLUG"]`
* Average rating: `[plugin_rating src="PLUGIN_SLUG"]`
* Number of ratings: `[plugin_num_ratings src="PLUGIN_SLUG"]`
* Download link: `[plugin_download_link src="PLUGIN_SLUG"]`

== Screenshots ==

1. This is a plugin stats.
2. This is a list by author.
3. This is setting panel.

== Changelog ==

= 0.1.4 =
* Compatible with updated Plugin Directory.

= 0.1.3 =
* Removed unnecessary loops.
* Fix a bug: The database error occurs when this plugin is actevated for the first time.

= 0.1.2 =
* Removed unnecessary loops.

= 0.1.1 =
* Fix a bug: "Plugin Directory Stats version" doesn't be shown in "3. Your System Info" setting section.
* Fix a bug: "Plugin Directory Stats DB version" doesn't be shown in "3. Your System Info" setting section.
* Fix a bug: "Plugin Directory Stats URL" doesn't be shown in "3. Your System Info" setting section.

= 0.1 =
* This is the initial release.

== Upgrade Notice ==

= 0.1.4 =
This version is compatible with updated Plugin Directory.

= 0.1.3 =
This version has a change and bug fix.

= 0.1.2 =
This version has a change.

= 0.1.1 =
This version has bug fixes.

= 0.1 =
This is the initial release.
