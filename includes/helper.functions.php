<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><?php

function save_options() {
	
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

function parseJsonConfig($jsonConfig) {

	$decodeConfig = json_decode(stripslashes($jsonConfig), true);
	$outputConfig = array();

	// load data
	foreach ($decodeConfig as $k => $main) { // main
		$this_config = array();
		$inc = 0;
		foreach ($main as $kkk => $field) { // fields
			$inc++;
			
			switch ($inc) {
				case 1:
					$this_config["inputmethod"] = $field; // scraper
					break;
				case 2:
					$this_config["postType"] = $field; // post / page ...
					break;
				case 3:
					$this_config["containerInstance"] = $field;
					break;
				case 4:
					$this_config["containerop"] = $field;
					break;
				case 5:
					$this_config["containeropeq"] = $field;
					break;					
				case 6:
					$this_config["validator"] = $field; // expression
					break;
				case 7:
					$this_config["op"] = $field; // contains / equals
					break;
				case 8:
					$this_config["opeq"] = $field; // expression of op
					break;
				case 9:
					$this_config["idsel"] = $field; // id expression
					break;
				case 10:
					$this_config["idop"] = $field; // text / image src / html
					break;
				case 11:
					$this_config["idopeq"] = $field; // expression of id op
					break;
			}
			if ($inc > 11) {
				if (is_array($field)) {
					$this_config["fields"] = array();
					foreach ($field as $ka => $dig) {
						$row_counter = 0;
						$single_field = array();
						foreach ($dig as $kar => $row) { // row
							$row_counter++;
							switch ($row_counter) {
								case 1:
									$single_field["field-map"] = $row;
									break;
								case 2:
									$single_field["fieldsel"] = $row;
									break;
								case 3:
									$single_field["fieldop"] = $row;
									break;
								case 4:
									$single_field["fieldopeq"] = $row;
									break;
							}
						}
						$this_config["fields"][] = $single_field;
					} // field
				} // fields array
			} // fields row
			
		} // fields looper
		$outputConfig[] = $this_config;
	} // json config

	return $outputConfig;
	
} // function

function parseEntry($query, $url, $ht, $isContainer = false) {
	
	$container_array = array();
	
	// parse regex expressions (triple brackets)
	$q = array();
	preg_match_all('/{{{.*}}}/U', $query, $q, PREG_SET_ORDER, 0);

	foreach ($q as $n => $qq) {
		$qq = $qq[0];
		
		$newregex = str_replace("{{{", '', $qq);
		$newregex = str_replace("}}}", '', $newregex);

		$newq = array();
		preg_match_all($newregex, $ht, $newq, PREG_SET_ORDER, 0);

		$getzeros = array();
		foreach ($newq as $z => $zero) {
			$getzeros[] = $zero[0];
		}
		
		$getzeros = implode("", $getzeros);
		
		if ($isContainer) {
			$matches = array();
			preg_match_all($newregex, $ht, $matches, PREG_SET_ORDER, 0);
			foreach($matches as $b => $found_match) {
				$container_array[] = $found_match;
			}
		}
		
		$query = str_replace($qq, $getzeros, $query);
	}
	
	// parse jquery expressions (double brackets)
	$q = array();
	preg_match_all('/{{.*}}/U', $query, $q, PREG_SET_ORDER, 0);
	
	foreach ($q as $n => $qq) {
		$qq = $qq[0];
		
		$newjq = str_replace("{{", '', $qq);
		$newjq = str_replace("}}", '', $newjq);
		$doc = phpQuery::newDocument('<div>'.$ht.'</div>'); 
		
		$code = $doc->find($newjq);
		$appendHTML = '';
		foreach (pq($code) as $k => $thisf) {
			$to_push = pq($thisf)->html();
			if ($isContainer) {
				$container_array[] = $to_push;
			} else {
				$appendHTML .= $to_push;
			}
		}
		$query = str_replace($qq, $appendHTML, $query);
	}
	

	// parse %url%
	$ret = str_replace("%url%", $url, $query);
	
	// ret remaining
	if ($isContainer) {
		return $container_array;
	}
	return $ret;
}

function parseAfterOp($html, $op, $opeq) {
	// Apply first transformation
	switch ($op) {
		case "text":
			$html = strip_tags($html);
			break;
		case "html":
			// nothing to do here
			break;
		case "imgsrc":
			$doc = phpQuery::newDocument('<div>'.$html.'</div>'); 
			$code = $doc->find("img");
			$img = "";
			foreach (pq($code) as $k => $thisf) {
				$to_push = pq($thisf)->html();
				$img = pq($thisf)->attr("src");
				$img = str_replace("//", "http://", $img);
				$html = $img;
			}
			break;
		case "imgcss":
			// TODO: css funk
			break;
	}

	
	// Apply second transformation
	switch ($opeq) {
		case "String (Text)":
			// nothing to do here
			break;
		case "Date":
			$html = strtotime($html);
			break;
		case "Price":
			$matches = array();
			$re = '/[0-9,.]+/';
			$matched = array();
			preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$matched[] = $match[0];
			}
			$html = (float) implode("", $matched);
			break;
		case "Alpha":
			$matches = array();
			$re = '/[a-zA-Z,.]+/';
			$matched = array();
			preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$matched[] = $match[0];
			}
			$html = implode("", $matched);
			break;
		case "Numeric (int)":
			$matches = array();
			$re = '/[0-9,.]+/';
			$matched = array();
			preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$matched[] = $match[0];
			}
			$html = (int) implode("", $matched);
			break;
		case "Numeric (float)":
			$matches = array();
			$re = '/[0-9,.]+/';
			$matched = array();
			preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$matched[] = $match[0];
			}
			$html = (float) implode("", $matched);
			break;
		case "Alpha &amp; Numeric":
			$matches = array();
			$re = '/[a-zA-Z0-9]+/';
			$matched = array();
			preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
			foreach ($matches as $match) {
				$matched[] = $match[0];
			}
			$html = implode("", $matched);
			break;
		case "URL Encode":
			$html = urlencode(trim($html));
			break;
		case "Capitalize":
			$html = ucwords(trim($html));
			break;
		case "UPPERCASE":
			$html = strtoupper(trim($html));
			break;
		case "lowercase":
			$html = strtolower(trim($html));
			break;
		case "MD5 Hash":
			$html = md5(trim($html));
			break;
		case "SHA1 Hash":
			$html = sha1(trim($html));
			break;
	}
	
	return $html;
}

function validateOp($query, $url, $html, $op, $opeq) {
	$ret = parseEntry($query, $url, $html);	
	switch ($op) {
		case "notnull":
			if (trim($ret) != "") return true;
			break;
		case "contains":
			if (strpos($ret, $opeq) !== false) return true;
			break;
		case "equals":
			if ($ret == $opeq) return true;
			break;
		case "numgt":
			$matches = array();
			$re = '/[0-9,.]+/';
			preg_match_all($re, $ret, $matches, PREG_SET_ORDER, 0);
			$html = implode("", $matches);
			break;
		case "numlt":
			$matches = array();
			$re = '/[0-9,.]+/';
			preg_match_all($re, $ret, $matches, PREG_SET_ORDER, 0);
			$html = implode("", $matches);
			break;
	}
	
	return false;
}

function runmap($offset, $mapCount, $json_config) {
	// get crawled files

	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	if(!file_exists(dirname($path_init))) {
		@mkdir(dirname($path_init), 0777, true);
	}
	$filez = getDirContents($path_init);
	
	for ($i = $offset; $i <= ($offset + $mapCount - 1); ++$i) {

		if (isset($filez[$i])) {

			$file_contents = file_get_contents($filez[$i]);
		
			// for each Mappings Group
			foreach($json_config as $jc_key => $jc_val) {
				

				$containers = array(); //  instance containers
				
				// Scraper Method
				if ($jc_val["inputmethod"] == "scraper") {
					
					// get containers
					// 		function parseEntry($query, $url, $ht, $isContainer = false) {
					$containers = parseEntry($jc_val["containerInstance"], $filez[$i], $file_contents, true);
					foreach ($containers as $container) {

						// adjust container
						// 		function parseAfterOp($html, $op, $opeq) {
						$container = parseAfterOp($container, $jc_val["containerop"], $jc_val["containeropeq"]);
						// validate mapping
						//		function validateOp($query, $url, $html, $op, $opeq) {
						$valid = validateOp($jc_val["validator"], $filez[$i], $container, $jc_val["op"], $jc_val["opeq"]);
						
						// when validation passed
						if ($valid) {
							// get id
							// 		function parseEntry($query, $url, $ht, $isContainer = false) {
							$id = parseEntry($jc_val["idsel"], $filez[$i], $container);

							// 		function parseAfterOp($html, $op, $opeq) {
							$id = parseAfterOp($id, $jc_val["idop"], $jc_val["idopeq"]);
							
							// build fields
							$build_fields = array();
							$raw_fields = $jc_val["fields"];

							foreach ($raw_fields as $keyin => $field) {
								$this_field = "";
								// function parseEntry($query, $url, $ht, $isContainer = false) {
								$this_field = parseEntry($raw_fields[$keyin]["fieldsel"], $filez[$i], $container);

								// function parseAfterOp($html, $op, $opeq) {
								$this_field = parseAfterOp($this_field, $raw_fields[$keyin]["fieldop"], $raw_fields[$keyin]["fieldopeq"]);

								$build_fields[$raw_fields[$keyin]["field-map"]] = $this_field;
							}

							
							// query current id

							$args = array(
								'posts_per_page'   => -1,
								'post_type'     => $jc_val["postType"],
								'meta_query'    => array(
									array(
										'key'       => 'wizard_build_id',
										'value'     => array($id),
										'compare'   => 'IN'
									)
								)
							);
							$the_query = new WP_Query($args);

							// verify if id exists
							if ( $the_query->have_posts() ) {

								// if id exists, update item
								while ( $the_query->have_posts() ) : $the_query->the_post();
									$my_post = array(
										'ID' => get_the_ID(),
										'post_type' => $jc_val["postType"]
									);
									$meta = array();
									foreach ($build_fields as $kname => $val) {
										if (substr($kname, 0, 1) != "_") {
											$my_post[$kname] = $val;
										} else {
											$meta[$kname] = $val;
										}
									}
									
									// create product category if doesn`t exist
									if (isset($my_post["product_cat"])) {
										wp_insert_term(
										  $my_post["product_cat"], // the term 
										  'product_cat', // the taxonomy
										  array(
											'description'=> $my_post["post_category"]
										  )
										);								
									}
									
									// create post category if doesn`t exist
									if (isset($my_post["post_category"])) {
										wp_insert_term(
										  $my_post["post_category"], // the term 
										  'post_category', // the taxonomy
										  array(
											'description'=> $my_post["post_category"]
										  )
										);								
									}
									
									
									// Update the post into the database
									wp_update_post( $my_post );
									foreach($meta as $mk => $mv) {
										update_post_meta(get_the_ID(), $mk, $mv);
									}
									
									if (isset($my_post["thumbnail"])) {
										add_image(get_the_ID(), $my_post["thumbnail"], basename($my_post["thumbnail"]));
									}
									
								endwhile;
								wp_reset_postdata();

							} else {
								// if id doesnt exist, create item
								// REQUIRED: post_title and post_content
								$my_post = array(
									'post_type' => $jc_val["postType"]
								);
								
								$meta = array();
								foreach ($build_fields as $kname => $val) {
									if (substr($kname, 0, 1) != "_") {
										$my_post[$kname] = $val;
									} else {
										$meta[$kname] = $val;
									}
								}

								$pid = wp_insert_post($my_post);

								// create category if doesn`t exist
								if (isset($my_post["product_cat"])) {
									wp_insert_term(
									  $my_post["product_cat"], // the term 
									  'product_cat', // the taxonomy
									  array(
										'description'=> $my_post["post_category"]
									  )
									);								
								}
								
								// create post category if doesn`t exist
								if (isset($my_post["post_category"])) {
									wp_insert_term(
									  $my_post["post_category"], // the term 
									  'post_category', // the taxonomy
									  array(
										'description'=> $my_post["post_category"]
									  )
									);								
								}
								
								// Update the post into the database
								update_post_meta($pid, 'wizard_build_id', $id);
								foreach($meta as $mk => $mv) {
									update_post_meta($pid , $mk, $mv);
								}
							
								if (isset($my_post["thumbnail"])) {								
									add_image($pid, $my_post["thumbnail"], basename($my_post["thumbnail"]));
								}
								
							}

						} // valid
						
					} // containers
					
				} // scraper / sql
				
			} // mappings group

		} else {
			echo "EOQ";
		}
	}
}

function add_image($post_id, $image_url, $image_name) {
// Add Featured Image to Post
    //$image_url        = 'http://s.wordpress.org/style/images/wp-header-logo.png'; // Define the image URL here
    //$image_name       = 'wp-header-logo.png';
	$image_url = strtok($image_url, '?');
	$image_name = strtok($image_name, '?');
    $upload_dir       = wp_upload_dir(); // Set upload folder
    $image_data       = file_get_contents($image_url); // Get image data
    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
    $filename         = basename( $unique_file_name ); // Create image file name

    // Check folder permission and define file location
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    // Create the image  file on the server
    file_put_contents( $file, $image_data );

    // Check image file type
    $wp_filetype = wp_check_filetype( $filename, null );

    // Set attachment data
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
		'guid'			 => $unique_file_name,
        'post_status'    => 'inherit'
    );

    // Create the attachment
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

    // Assign metadata to attachment
    wp_update_attachment_metadata( $attach_id, $attach_data );

    // And finally assign featured image to post
    set_post_thumbnail( $post_id, $attach_id );
	
	update_post_meta($post_id, '_thumbnail_id', $attach_id);
}

?>