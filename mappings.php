<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';


if (is_admin()) {

	$offset = $_POST["offset"];
	$queue_size = 15;
	$json_config = $_POST["config"];

	runmap($offset, $queue_size, parseJsonConfig($json_config));

}

?>