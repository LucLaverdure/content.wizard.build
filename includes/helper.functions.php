<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><?php

function save_options() {
	
	var_dump($_POST['apikey']);
	
	if (isset($_POST['apikey'])) {
		update_option('wb_apikey', serialize($_POST['apikey']));
	}
	if (isset($_POST['whitelist'])) {
		update_option('wb_whitelist', serialize($_POST['whitelist']));
	}
	if (isset($_POST['blacklist'])) {
		update_option('wb_blacklist', serialize($_POST['blacklist']));
	}
	if (isset($_POST['mappings'])) {
		update_option('wb_mappings', serialize($_POST['mappings']));
	}
	
}

function getDirContents($dir, &$results = array()){
	$files = array();
	$files = @scandir($dir);
	if (count($files) > 0 && $files != false) {
		foreach($files as $value) {
			$path = realpath($dir.DIRECTORY_SEPARATOR.$value);
			if(!is_dir($path)) {
				$results[] = $path;
			} else if($value != "." && $value != "..") {
				getDirContents($path, $results);
			}
		}
	}
	return $results;
}


function delete_cache_dir() {
	$dir = __DIR__ . "/cache";
	$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($it,
				 RecursiveIteratorIterator::CHILD_FIRST);
	foreach($files as $file) {
		if ($file->isDir()){
			rmdir($file->getRealPath());
		} else {
			unlink($file->getRealPath());
		}
	}
	rmdir($dir);	
}


function wizbui_get_acf_fields() {
	$ret = array();
	$groups = apply_filters( 'acf/get_field_groups', array() );
	if ( is_array( $groups ) ) {
		foreach ( $groups as $group ) {
			$fields = apply_filters( 'acf/field_group/get_fields', array(), $group['id'] );
			foreach($fields as $field) {
				$build = $field['label']." (".$field['key'].";ACF)";
				$ret[] = $build;
			}
		}
	}
	return $ret;
}

function wizbui_get_default_fields() {
	$keys = array();
	$post_type_features = get_all_post_type_supports('post');
	foreach($post_type_features as $k => $v) {
		$keys[] = $k;
	}
	return $keys;
}

function wizbui_get_custom_fields() {
	$f_all = array();
	$posts_array = get_posts( array('post_type' => 'any', 'posts_per_page' => '999999999999999') );
	foreach($posts_array as $post) {
		$f_custom = get_post_custom($post->ID);
		foreach($f_custom as $name => $f_single) {
			$f_all[$name] = $name;
		}
	}
	return $f_all;
}

function get_all_posts_fields() {
	$fields_acf = wizbui_get_acf_fields();
	$fields_def = wizbui_get_default_fields();
	$f_all = wizbui_get_custom_fields();
	
	// get all posts
	$fields = array_merge($fields_def, $fields_acf, $f_all);
	sort($fields);
	
	return $fields;
}


function whitelist_check($urls_to_add, $path_origin) {
	// verify whitelist
	$urls_to_add_parsed = array();
	foreach ($urls_to_add as $urlX) {
		if (trim($path_origin) != "") {
			$urlX = pathme($path_origin, $urlX);
		}
		$pass = array();
		$whitelist = get_whitelist();
		if (count($whitelist) > 0) {
			foreach ($whitelist as $val) {
				if ((trim($urlX) != "") && (trim($val) != false)) {
					if (strrpos(trim($urlX), trim($val)) !== false) {
						$pass[] = $val;
					}
				}
			}
			if (count($pass) == count($whitelist)) {
				$urls_to_add_parsed[] = trim($urlX);
			}
		} else {
			$urls_to_add_parsed[] = trim($urlX);
		}
	}
	return $urls_to_add_parsed;
}

function blacklist_check($urls_to_add, $path_origin) {
	// verify blacklist
	$urls_to_add_parsed = array();
	foreach ($urls_to_add as $urlX) {
		if (trim($path_origin) != "") {
			$urlX = pathme($path_origin, $urlX);
		}
		$pass = true;
		$blacklist = get_blacklist();
		if (count($blacklist) > 0) {
			foreach ($blacklist as $val) {
				if ((trim($urlX) != "") && (trim($val) != false)) {
					if (strrpos(trim($urlX), trim($val)) !== false) {
						$pass = false;
					}
				}
			}
			if ($pass == true) {
				$urls_to_add_parsed[] = trim($urlX);
			}
		} else {
			$urls_to_add_parsed[] = trim($urlX);
		}
	}
	return $urls_to_add_parsed;
}

function get_blacklist() {
	$opt = get_option('wb_blacklist', null);
	return explode("\n", unserialize($opt));
	/*
	$blacklist = $_POST["blacklist"];
	$blacklist = explode("\n", $blacklist);
	if (count($blacklist) > 0) {
		$list = array();
		foreach($blacklist as $listed) {
			if (trim($listed) != "") $list[] = $listed;
		}
		return $list;
	} else {
		return array();
	}
	*/
}


function get_whitelist() {
	$opt = get_option('wb_whitelist', null);
	return explode("\n", unserialize($opt));
	
	/*
	$whitelist = $_POST["whitelist"];
	$whitelist = explode("\n", $whitelist);
	if (count($whitelist) > 0) {
		$list = array();
		foreach($whitelist as $listed) {
			if (trim($listed) != "") $list[] = $listed;
		}
		return $list;
	} else {
		return array();
	}
	*/
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

function get_real_dirs() {
	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	if(!file_exists(dirname($path_init))) {
		@mkdir(dirname($path_init), 0777, true);
	}
	$dirs = getDirContents($path_init);
	$dir_array = array();
	foreach($dirs as $dirX) {
		$dir_array[] = str_replace(WIZBUI_PLUGIN_PATH."cache/", '', $dirX);
	}
	return $dir_array;
}

function get_crawled_list() {
	$list = explode("\n", @file_get_contents(__DIR__ . "/cache/crawled.txt"));
	if (count($list)>0) {
		$filtered = array();
		foreach ($list as $line) {
			if (trim($line) != "") $filtered[] = trim($line);
		}
		return $filtered;
	} 
	
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

function runmap($offset) {
	
}

?>