<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );

include_once WIZBUI_PLUGIN_PATH . 'includes/helper.functions.php';


if (is_admin()) {
	
	if (isset($_POST["preview"]) && $_POST["preview"] == "true") {

		//function parseEntry($query, $url, $ht, $isContainer = false, $jconfig, $is_preview = false, $offset = 0) {
		echo html_entity_decode(stripslashes(parseEntry($_POST["query"], $_POST["file"], "", false, parseJsonConfig($_POST["config"]), true)));

	} else {

		//config: mappings,
		//runmappings: true,
		//offset: offset,
		//file_offset: file_offset
		//$file_offset = $_POST["file_offset"];

		$offset = $_POST["offset"];

		$parseConfig = parseJsonConfig($_POST["config"]);
	
		//var_dump($offset, $file_offset, $queue_size, $json_config, $parseConfig);

		//function runmap($offset, $json_config, $preview = false) {
		runmap($offset, $parseConfig, false);
	}

}

?>