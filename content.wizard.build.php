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

define("WIZBUI_PLUGIN_PATH", ABSPATH . 'wp-content/plugins/content.wizard.build/');

@wp_enqueue_style("wiz.css", plugin_dir_url( __FILE__ )."/wiz.css");

// core Wizard.Build.Content admin panel
add_action('admin_menu', 'wizbui_setup_menu');
function wizbui_setup_menu(){
	include_once(WIZBUI_PLUGIN_PATH . "queue.php");
	add_menu_page( 'Content Wizard Build', 'Wizard.Build', 'manage_options', 'content-wizard-build', 'wizbui_callback', plugin_dir_url( __FILE__ ) ."/wizard-white.png" );
}
function wizbui_callback(){
	include_once(WIZBUI_PLUGIN_PATH . "queue.php");
	include_once(WIZBUI_PLUGIN_PATH . "lib/phpQuery.php");
	include_once(WIZBUI_PLUGIN_PATH . 'includes/helper.functions.php');
	include_once(WIZBUI_PLUGIN_PATH . 'includes/upload.save.php');
	include(WIZBUI_PLUGIN_PATH . "wizbui-admin-page.php");
}

// core Wizard.Build.Content File Saver
add_action( 'admin_post_wb_save_hook', 'admin_post_wb_save_hook_callback' );
function admin_post_wb_save_hook_callback() {
	include_once(WIZBUI_PLUGIN_PATH . "queue.php");
	include_once(WIZBUI_PLUGIN_PATH . "lib/phpQuery.php");
	include_once(WIZBUI_PLUGIN_PATH . 'includes/helper.functions.php');
	include(WIZBUI_PLUGIN_PATH . "data.save.php");
}

// core Wizard.Build.Content Bulk Mappings scripts
add_action( 'admin_post_wb_mappings_hook', 'admin_post_wb_mappings_hook_callback' );
function admin_post_wb_mappings_hook_callback() {
	include_once(WIZBUI_PLUGIN_PATH . "queue.php");
	include_once(WIZBUI_PLUGIN_PATH . 'includes/helper.functions.php');
	include_once(WIZBUI_PLUGIN_PATH . "lib/phpQuery.php");
	include(WIZBUI_PLUGIN_PATH . "mappings.php");
}

