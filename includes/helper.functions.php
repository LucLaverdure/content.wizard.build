<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><?php

function save_options() {
	if (isset($_POST['save'])) {
		update_option('wb_apikey', serialize($_POST['apikey']));
		update_option('wb_jsenabled', serialize($_POST['jsenabled']));
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

?>