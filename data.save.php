<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';


if (is_admin()) {

	save_options();

	if (isset($_POST["quicksave"])) {
		$ret = parseJsonConfig(stripslashes($_POST['mappings']));
		var_dump($ret);
		die();
	}

	$path = "";
	$path_origin = "";
	if (isset($_POST["path"])) {
		$path = $_POST["path"];
		$path_origin = $_POST["path"];
	} elseif (isset($_GET["path"])) {
		$path = $_GET["path"];
		$path_origin = $_GET["path"];
	}

	// prevent path hacks
	$path = str_replace("../", "", $path);
	
	// fix filenames for get query parameters
	$path = str_replace("?", "_", $path);

	// fix filenames for php files
	$ext = substr($path, strrpos($path, '.') + 1);
	$path = str_replace(array(".php", ".php3", ".php4", ".php5", ".phtml"), ".html", $path);

	try {	
		chmod(__DIR__ . "/cache", 0777);
	} catch (Exception $e) { 
		// meh
	}
	
	$path = __DIR__ . "/cache/" . $path;
	
	$data = base64_decode($_POST["data"]);

	if(!file_exists(dirname($path)))
		mkdir(dirname($path), 0777, true);

	// save crawl file
	if(!file_exists($path))
		file_put_contents($path, $data);

	// add to list of crawled urls
	file_put_contents(__DIR__ . "/cache/crawled.txt", pathme($path, $_POST["url"])."\n", FILE_APPEND);
	
	//try {
		
		$ext = substr($path, strrpos($path, '.') + 1);
		
		$urls_to_add = array();
		
		if ($ext=="css") {
			// TODO: get image urls
		} elseif ($ext=="js") {
			// TODO: nothing
		} elseif (in_array($ext, array("php", ".php", ".php3", ".php4", ".php5", ".phtml"))) {
			// secure file, fixed at save level
		} else {
			/* get links (a href)
			*  get meta links (link href)
			*  get scripts (script src)
			*  get images (img src)
			**************************/

			$doc = phpQuery::newDocument($data);

			// get A HREF links
			foreach(pq("a") as $links) {
				$urls_to_add[] = pq($links)->attr("href");
			}

			// get LINK HREF links
			foreach(pq("link") as $links) {
				$urls_to_add[] = pq($links)->attr("href");
			}

			// get A SCRIPT SRC links
			foreach(pq("script") as $links) {
				$urls_to_add[] = pq($links)->attr("src");
			}

			// get A IMG SRC links
			foreach(pq("img") as $links) {
				$urls_to_add[] = pq($links)->attr("src");
			}
			
		}

		// verify whitelist
		$urls_to_add = whitelist_check($urls_to_add, $path_origin);
		// verify blacklist
		$urls_to_add = blacklist_check($urls_to_add, $path_origin);

		$urlsfile_path = __DIR__ . "/cache/crawl.me.txt";
		$contents = @file_get_contents($urlsfile_path);
		$urls_ret = array();
		
		// remove duplicates in crawl.me.txt
		if (trim($contents) != "") {
			$alreadyin = explode("\n", $contents);
			foreach ($urls_to_add as $url) {
				if (!in_array(trim($url), $alreadyin)) {
					$urls_ret[trim($url)] = trim($url);
				}
			}
		} else {
			// remove duplicates
			foreach ($urls_to_add as $url) {
				$urls_ret[trim($url)] = trim($url);
			}
		}
		
		// already crawled urls
		$cache_data = get_dirs();
		$crawled = get_crawled_list();
		$cache_data = array_merge($crawled, $cache_data);
		
		$urls_ret_final = array();
		foreach ($urls_ret as $url) {
			if (!in_array(trim($url), $cache_data))
				$urls_ret_final[trim($url)] = trim($url);
		}

		// write urls to crawl to disk
		file_put_contents($urlsfile_path, implode("\n", $urls_ret_final), FILE_APPEND);
	//} catch (Exception $e) {
		// meh, was prolly binary...
	//}
	
}

?>