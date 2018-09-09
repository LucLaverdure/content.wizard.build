<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';

function wiz_parseInput($input) {
	// gen $standardUrl
	//echo "input"; var_dump($input);
	$standardUrl = wiz_genstandardUrlFromInput($input);
	//echo "standardUrl"; var_dump($standardUrl);
	// 	- remove gets option
	// 	- remove hashes option
	$standardUrl = wiz_setOptions($standardUrl);
	//echo "standardUrl2"; var_dump($standardUrl);
	
	// gen $file
	$filename = wiz_genFileFromstandardUrl($standardUrl);
	//echo "filename"; var_dump($filename);
	
	// remove $input from tocrawl
	wiz_removeInputFromToCrawl($standardUrl);

	// if $standardUrl in crawled skip
	if (wiz_in_crawled($standardUrl)) return;
	// check whitelist
	if (!wiz_validate_whitelist($standardUrl)) return;
	// check blacklist	
	if (!wiz_validate_blacklist($standardUrl)) return;

	// add $input to crawled
	wiz_addInputToCrawled($standardUrl);
	
	// all valid
	// 	check if postjs:
	
	// download $standardUrl to $filename
	$outputz = wiz_download($standardUrl, $filename);
	
	//if (unserialize(get_option('wb_PostJS', 'N')) == 'Y') {
	//}
	//echo "output"; var_dump($output);
	
	// get related files
	$related_files = wiz_related_files($outputz, $filename, $standardUrl);
	
	// save related files
	save_related_files($related_files, $standardUrl);
}

if (is_admin()) {
	
	// save_options
	save_options();

	// if task is only to save options, do it
	if (isset($_POST["quicksave"])) {
		die();
	}
	
	// create folders
	wiz_create_req_folders_and_files();

	// 	$input
	$inputs = $_POST["url"];
	$inputs = explode("\n", $inputs);

	// foreach, max of 15 items
	$inc_me = 0;
	foreach ($inputs as $input) {
		
		$inc_me++;
		
		// max of 15 items
		if ($inc_me > 15) die(); 
		
		// treat input
		wiz_parseInput($input);
		
	}
}

?>