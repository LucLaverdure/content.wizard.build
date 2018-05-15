<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';

function get_dirs() {
	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	if(!file_exists(dirname($path_init))) {
		@mkdir(dirname($path_init), 0777, true);
	}
	$dirs = getDirContents($path_init);
	$dir_array = array();
	foreach($dirs as $dirX) {
		$dir_array[] = "http://".str_replace(WIZBUI_PLUGIN_PATH."cache/", '', $dirX."\n");
	}
	return $dir_array;
}

function pathme($fromURL, $relURL) {
	if (strpos($relURL, 'http') === false) {
		if (strpos($fromURL, 'http') === false) {
			$fromURL = explode("/", $fromURL);
			$relURL = "http://".$fromURL[0]."/".$relURL;
		} else {
			$fromURL = explode("/", $fromURL);
			$relURL = $fromURL[0]."/".$relURL;
		}
	}
	
	$relURL = str_replace("//", "/", $relURL);
	$relURL = str_replace("http:/", "http://", $relURL);
	$relURL = str_replace("https:/", "https://", $relURL);
	return $relURL;
}

if (is_admin()) {

	$path = $_REQUEST["path"];
	$path_origin = $_REQUEST["path"];

	
	update_option('wb_domains', serialize($_REQUEST["whitelist"]));
	
	$path = str_replace("../", "", $_REQUEST["path"]);
	try {	
		chmod(__DIR__ . "/cache", 0777);
	} catch (Exception $e) { 
		// meh
	}
	
	$path = __DIR__ . "/cache/" . $path;
	
	$data = base64_decode($_REQUEST["data"]);

	if(!file_exists(dirname($path)))
		mkdir(dirname($path), 0777, true);

	file_put_contents($path, $data);

	try {
		
		$ext = substr($path, strrpos($path, '.') + 1);
		
		$urls_to_add = array();
		
		if ($ext=="css") {
			// TODO: get image urls
		} elseif ($ext=="js") {
			// TODO: nothing
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

			// get A SCRIPT SRC links
			foreach(pq("img") as $links) {
				$urls_to_add[] = pq($links)->attr("src");
			}
			
		}

		
		$urls_to_add_parsed = array();
		
		// verify domain whitelist
		foreach ($urls_to_add as $urlX) {
			$urlX = pathme($path_origin, $urlX);
			$pass = false;
			$domains = split("\n", trim($_REQUEST["whitelist"]));
			foreach($domains as $val) {
				if ( strrpos($urlX, $val) !== false) $pass = true;
			}
			if ($pass) {
				$urls_to_add_parsed[] = $urlX;
			}
		}

		$urlsfile_path = __DIR__ . "/cache/crawl.me.txt";
		$contents = @file_get_contents($urlsfile_path);
		$urls_ret = array();
		// remove duplicates in crawl.me.txt
		if ($contents) {
			$alreadyin = explode("\n", $contents);
			foreach ($urls_to_add_parsed as $url) {
				if (!in_array($url, $alreadyin)) {
					$urls_ret[$url] = $url;
				}
			}
		} else {
			// remove duplicates
			foreach ($urls_to_add_parsed as $url) {
				$urls_ret[$url] = $url;
			}
		}
		
		$cache_data = get_dirs();
		$urls_ret_final = array();
		foreach ($urls_ret as $url) {
			if (!in_array($url, $cache_data))
				$urls_ret_final[$url] = $url;
		}
			
		// write urls to crawl to disk
		file_put_contents($urlsfile_path, implode("\n", $urls_ret_final), FILE_APPEND);
	} catch (Exception $e) {
		// meh, was prolly binary...
	}
}

?>