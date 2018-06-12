<?php
   /*
   Plugin Name: Content.Wizard.Build
   Plugin URI: http://content.wizard.build
   description: Dynamic Content. Here. Now.
   Version: 1.00
   Author: Luc Laverdure
   Author URI: http://LucLaverdure.com
   License: GPL2
   */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

wp_enqueue_style("wiz.css", plugin_dir_url( __FILE__ )."/wiz.css");
wp_enqueue_style("jqueryui-struct", plugin_dir_url( __FILE__ )."/lib/jquery-ui.structure.min.css");
wp_enqueue_style("jqueryui-theme", plugin_dir_url( __FILE__ )."/lib/jquery-ui.theme.min.css");
wp_enqueue_script("jqueryui-src", plugin_dir_url( __FILE__ )."/lib/jquery-ui.min.js");
wp_enqueue_script("jqueryui-combobox", plugin_dir_url( __FILE__ )."/lib/jquery-ui.combobox.js");

// core Wizard.Build.Content admin panel
add_action('admin_menu', 'wizbui_setup_menu');
function wizbui_setup_menu(){
	add_menu_page( 'Content Wizard Build', 'Wizard.Build', 'manage_options', 'content-wizard-build', 'wizbui_callback', plugin_dir_url( __FILE__ ) ."/wizard-white.png" );
}
function wizbui_callback(){
	include_once(__DIR__ . "/lib/phpQuery.php");
	include(__DIR__ . "/wizbui-admin-page.php");
}

// core Wizard.Build.Content File Saver
add_action( 'admin_post_wb_save_hook', 'admin_post_wb_save_hook_callback' );
function admin_post_wb_save_hook_callback() {
	include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';
	include_once(__DIR__ . "/lib/phpQuery.php");
	include(__DIR__ . "/data.save.php");
}

// core Wizard.Build.Content get new urls to crawl
add_action( 'admin_post_wb_get_hook', 'admin_post_wb_get_hook_callback' );
function admin_post_wb_get_hook_callback() {
	
	include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';
	define("WIZBUI_PLUGIN_PATH", ABSPATH . 'wp-content/plugins/content.wizard.build/');
	
	// has been crawled
	$crawled = array();
	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	if(file_exists(dirname($path_init))) {
		$files = getDirContents($path_init);
		foreach($files as $dirX) {
			$thisurl = "http://".str_replace(WIZBUI_PLUGIN_PATH."cache/", '', $dirX);
			$crawled[$thisurl] = $thisurl;
		}
	}
	$crawled_file = __DIR__ . "/cache/crawled.txt";
	if(file_exists($crawled_file)) {
		$crawled_file = explode("\n", @file_get_contents($crawled_file));
		foreach($crawled_file as $url) {
			$crawled[$url] = $url;
		}
	}
	// to crawl
	$crawl_file = __DIR__ . "/cache/crawl.me.txt";
	$to_crawl = array();
	$to_crawl_ret = array();
	if (file_exists($crawl_file)) {
		$to_crawl = explode("\n", file_get_contents($crawl_file));
		foreach ($to_crawl as $crawl_url) {
			if (!in_array($crawl_url, $crawled)) {
				$to_crawl_ret[] = $crawl_url;
			}
		}
	}

	if ( (isset($_GET["path"])) && (trim($_GET["path"]) != "") ) {
		$to_crawl_ret = whitelist_check($to_crawl_ret, urldecode($_GET["path"]));
		$to_crawl_ret = blacklist_check($to_crawl_ret, urldecode($_GET["path"]));
	} elseif ( (isset($_POST["path"])) && (trim($_POST["path"]) != "") ) {
		$to_crawl_ret = whitelist_check($to_crawl_ret, urldecode($_POST["path"]));
		$to_crawl_ret = blacklist_check($to_crawl_ret, urldecode($_POST["path"]));
	} else {
		$to_crawl_ret = whitelist_check($to_crawl_ret, "");
		$to_crawl_ret = blacklist_check($to_crawl_ret, "");
	}
	echo json_encode(array( "to_crawl" => $to_crawl_ret, "crawled" => $crawled));
	
}