<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include_once ABSPATH . 'wp-content/plugins/content.wizard.build/includes/helper.functions.php';

function whitelist_check($urls_to_add, $path_origin) {
	// verify domain whitelist
	$urls_to_add_parsed = array();
	foreach ($urls_to_add as $urlX) {
		$urlX = pathme($path_origin, $urlX);
		$pass = array();
		$domains = get_whitelist();
		if (count($domains) > 0) {
			foreach($domains as $val) {
				if ( strrpos($urlX, $val) !== false) $pass[] = $val;
			}
			if (count($pass) == count($domains)) {
				$urls_to_add_parsed[] = $urlX;
			}
		} else {
			$urls_to_add_parsed[] = $urlX;
		}
	}
	return $urls_to_add_parsed;
}

function get_whitelist() {
	$whitelist = $_REQUEST["whitelist"];
	$whitelist = explode(";", $whitelist);
	if (count($whitelist) > 0)
		return $whitelist;
	else
		return array();
}

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

function get_crawled_list() {
	$list = explode("\n", @file_get_contents(__DIR__ . "/cache/crawled.txt"));
	if (count($list)>0)
		return $list;
	else
		return array();
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

	
	update_option('wb_domains', serialize(implode("\n", get_whitelist())));
	
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

	// save crawl file
	file_put_contents($path, $data);

	// add to list of crawled urls
	file_put_contents(__DIR__ . "/cache/crawled.txt", pathme($path, $_REQUEST["url"])."\n", FILE_APPEND);
	
	//try {
		
		$ext = substr($path, strrpos($path, '.') + 1);
		
		$urls_to_add = array();
		
		if ($ext=="css") {
			// TODO: get image urls
		} elseif ($ext=="js") {
			// TODO: nothing
		} elseif (in_array($ext, array("php", ".php", ".php3", ".php4", ".php5", ".phtml"))) {
			// TODO: secure file
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

		// verify domain whitelist
		$urls_to_add_parsed = whitelist_check($urls_to_add, $path_origin);

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
		$crawled = get_crawled_list();
		
		$cache_data = array_merge($crawled, $cache_data);
		
		$urls_ret_final = array();
		foreach ($urls_ret as $url) {
			if (!in_array($url, $cache_data))
				$urls_ret_final[$url] = $url;
		}

		// write urls to crawl to disk
		file_put_contents($urlsfile_path, implode("\n", $urls_ret_final), FILE_APPEND);
	//} catch (Exception $e) {
		// meh, was prolly binary...
	//}
}

?>