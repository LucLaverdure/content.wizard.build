<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';


if (is_admin()) {
	
	if ($_POST["preview"]=="true") {
		$_POST["config"] = json_decode(stripslashes($_POST["config"]), true);
		
		//function query, $url, $ht, $isContainer = false, $jconfig
		echo html_entity_decode(stripslashes(parseEntry($_POST["query"], $_POST["file"], "", false, $_POST["config"], true)));
	} else {
		$offset = $_POST["offset"];
		$file_offset = $_POST["file_offset"];
		$queue_size = 35;
		$json_config = $_POST["config"];
		$parseConfig = parseJsonConfig($json_config);

		// function runmap($offset, $mapCount, $json_config, $file_offset = 0, $preview = false) 
		runmap($offset, $queue_size, $parseConfig, $file_offset);
	}

}

?>