<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//wp_deregister_script('jquery');
//wp_enqueue_script ('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);

wp_enqueue_script ("jqueryui-src", plugin_dir_url( __FILE__ )."/lib/jquery-ui.min.js");
wp_enqueue_script ("jqueryui-combobox", plugin_dir_url( __FILE__ )."/lib/jquery-ui.combobox.js");

wp_enqueue_style("jqueryui-struct", plugin_dir_url( __FILE__ )."/lib/jquery-ui.structure.min.css");
wp_enqueue_style("jqueryui-theme", plugin_dir_url( __FILE__ )."/lib/jquery-ui.theme.min.css");

?>