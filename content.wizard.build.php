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

// core Wizard.Build.Content admin panel
add_action('admin_menu', 'wizbui_setup_menu');
function wizbui_setup_menu(){
	add_menu_page( 'Content Wizard Build', 'Wizard.Build', 'manage_options', 'content-wizard-build', 'wizbui_callback', plugin_dir_url( __FILE__ ) ."/wizard.png" );
}
function wizbui_callback(){
	include_once(__DIR__ . "/lib/phpQuery.php");
	include(__DIR__ . "/wizbui-admin-page.php");
}

// core Wizard.Build.Content File Saver
add_action( 'admin_post_wb_save_hook', 'admin_post_wb_save_hook_callback' );
function admin_post_wb_save_hook_callback() {
	include_once(__DIR__ . "/lib/phpQuery.php");
	include(__DIR__ . "/data.save.php");
}

// core Wizard.Build.Content get new urls to crawl
add_action( 'admin_post_wb_get_hook', 'admin_post_wb_get_hook_callback' );
function admin_post_wb_get_hook_callback() {
	$crawl_file = __DIR__ . "/cache/crawl.me.txt";
	if (file_exists($crawl_file)) {
		echo file_get_contents($crawl_file);
	} else {
		echo "";
	}
}