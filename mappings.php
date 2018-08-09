<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';


if (is_admin()) {
	
	// function runmap($offset, $mapCount, $json_config, $file_offset = 0, $preview = false) 
	if ($_POST["preview"]=="true") {
		//function parseEntry($query, $url, $ht, $isContainer = false, $firstLineFields = array()) {
		echo parseEntry($_POST["query"], $_POST["file"], $_POST["ht"], false);
	} else {
		$offset = $_POST["offset"];
		$file_offset = $_POST["file_offset"];
		$queue_size = 35;
		$json_config = $_POST["config"];
		$parseConfig = parseJsonConfig($json_config);
		runmap($offset, $queue_size, $parseConfig, $file_offset);
	}

}

?>