<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';


if (is_admin()) {
	
	// function runmap($offset, $mapCount, $json_config, $file_offset = 0, $preview = false) 
	if ($_POST["preview"]=="true") {
		//function query, $url, $ht, $isContainer = false, $jconfig
		$_POST["config"] = json_decode(stripslashes($_POST["config"]), true);
		echo parseEntry($_POST["query"], $_POST["file"], $_POST["ht"], false, $_POST["config"]);
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