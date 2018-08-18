<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function selector_val(&$item) {
	$ret = "";
	$chars = str_split($item);
	foreach ($chars as $i) {
		if (ctype_alnum($i)) {
			$ret .= $i;
		} else {
			if ($ret != "") $ret .= "_";
		}
	}

	$ret = trim(str_replace("_"," ", $ret)); 
	$ret = str_replace(" ","_", $ret); 
	$item = strtolower($ret);

	return $item;
}

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
	if (isset($_POST['paramRemoveGets'])) {
		update_option('wb_RemoveGets', serialize($_POST['paramRemoveGets']));
	}
	if (isset($_POST['paramRemoveHashes'])) {
		update_option('wb_RemoveHashes', serialize($_POST['paramRemoveHashes']));
	}
	if (isset($_POST['paramPostJS'])) {
		update_option('wb_PostJS', serialize($_POST['paramPostJS']));
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
	$dir = WIZBUI_PLUGIN_PATH . "cache";
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

function get_blacklist() {
	$opt = get_option('wb_blacklist', array());
	return explode("\n", unserialize($opt));
}


function get_whitelist() {
	$opt = get_option('wb_whitelist', array());
	return explode("\n", unserialize($opt));
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
				case 12:
					$this_config["dbhost"] = $field; // expression of id op
					break;
				case 13:
					$this_config["dbuser"] = $field; // expression of id op
					break;
				case 14:
					$this_config["dbpass"] = $field; // expression of id op
					break;
				case 15:
					$this_config["dbname"] = $field; // expression of id op
					break;
				case 16:
					$this_config["dbquery"] = $field; // expression of id op
					break;
				case 17:
					// TODO: ??
					$this_config["line1parsed"] = $field; // expression of id op
					break;
				case 18:
					$this_config["fielddelimiter"] = $field; // expression of id op
					break;
				case 19:
					$this_config["enclosure"] = $field; // expression of id op
					break;
			}

			if ($inc > 19) {
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

// query, url, html (data), return container array?, file offset
function parseEntry($query, $url, $ht, $isContainer = false, $jconfig, $is_preview = false, $offset = 0) {
	$jconfig = $jconfig[0];

	global $latest_first_line, $csvdata, $this_file;
	$csvdata = array();
	$ext = substr($url, strrpos($url, '.'));
	$latest_first_line = array();
	$container_array = array();
	
	// parse regex expressions (triple brackets)
	$q = array();
	preg_match_all('/{{{.*}}}/U', $query, $q, PREG_SET_ORDER, 0);

	$handle = "";

	parse_str($url, $this_file);
	// file open

	// csv file
	if ($ext == ".csv") {
		$handle = fopen(__DIR__."/../cache/".$this_file["file"], "r");
		$latest_first_line = fgetcsv($handle, 0, $jconfig[17], $jconfig[18]);
		if ($offset > 0) {
			for ($i = 0; $i < $offset; ++$i) {
				$dump = fgetcsv($handle, 0, $jconfig[17], $jconfig[18]);
			}
		}
		$csvdata = fgetcsv($handle, 0, $jconfig[17], $jconfig[18]);
	
	// XLSX File
	} elseif ($ext == ".xlsx") {
		$current_sheet = "";
		$offset_counter = 0;
		
//$rustart = getrusage();
		
		if ( $xlsx = SimpleXLSX::parse(__DIR__."/../cache/".$this_file["file"])) {
		
			function rutime($ru, $rus, $index) {
				return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
				 -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
			}
					
			$sheets = $xlsx->sheetNames();
			foreach ($sheets as $sheetnum => $sheet) {
				$current_sheet = $sheet;
				list( $num_cols, $num_rows ) = $xlsx->dimension( $sheetnum );
				$ret = $xlsx->rows($sheetnum);
				foreach ($ret as $key => $row) {
					if ( ($offset_counter==0) || ($offset_counter == ($offset + 1)) ) {
						for ( $col = 0; $col < $num_cols; $col++ ) {
							if ($key == 0) {
								// 1st line
								$latest_first_line[$col] = $row[$col];
								array_walk($latest_first_line, 'selector_val');
							} else {
								if ($offset_counter >= $offset + 1) {
									$csvdata[$col] = $row[$col];
								}
							}
						}
					} 
					$offset_counter++;
				}
			}
		}
/*
$ru = getrusage();
echo "This process used " . rutime($ru, $rustart, "utime") .
" ms for its computations\n";
echo "It spent " . rutime($ru, $rustart, "stime") .
" ms in system calls\n";
*/

	} else {
		$ht = file_get_contents($url);
	}

	// Regex parsing
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

	if ($ext == ".csv") {
		// CSV: filename, {col letter}, {{col by field name}}, {col number}

		array_walk($latest_first_line, 'selector_val');
		
		// cols by field name
		$q = array();
		preg_match_all('/{{.*}}/U', $query, $q, PREG_SET_ORDER, 0);
		
		foreach ($q as $n => $qq) {
			$qq = $qq[0];
			
			$newjq = str_replace("{{", '', $qq);
			$newjq = str_replace("}}", '', $newjq);
			$newjq = selector_val($newjq);

			$search_arr = array_search($newjq, $latest_first_line);
			$appendHTML = "";
			if (in_array($newjq, $latest_first_line)) {
				$appendHTML .= $csvdata[$search_arr];
			}

			$query = str_replace($qq, $appendHTML, $query);
		}

		// cols by number or letters
		$q = array();
		preg_match_all('/{.*}/U', $query, $q, PREG_SET_ORDER, 0);

		
		foreach ($q as $n => $qq) {
			// if is_numeric, col number, else col letter
			$qq = $qq[0];
			
			$newjq = str_replace("{", '', $qq);
			$newjq = str_replace("}", '', $newjq);
			$newjq = selector_val($newjq);

			$appendHTML = "";
			if (is_numeric($newjq)) {
				// col number
				$col_num = $newjq;
				$appendHTML .= $csvdata[$col_num];

			} else {
				// col letter
				$col_num = convert2ColumnIndex($newjq);
				$appendHTML .= $csvdata[$col_num];
			}

			//firstLineFields
			$query = str_replace($qq, $appendHTML, $query);
		}	


	} elseif ($ext == ".xlsx") {
		// XLSX: sheetname, {col letter}, {{col by field name}}, {col number}

		// cols by field name
		$q = array();
		preg_match_all('/{{.*}}/U', $query, $q, PREG_SET_ORDER, 0);

		foreach ($q as $n => $qq) {
			$qq = $qq[0];
			
			$newjq = str_replace("{{", '', $qq);
			$newjq = str_replace("}}", '', $newjq);
			$newjq = selector_val($newjq);
			$appendHTML = "";
			$search_arr = array_search($newjq, $latest_first_line);
			if (in_array($newjq, $latest_first_line)) {
				$appendHTML .= $csvdata[$search_arr];
			}

			$query = str_replace($qq, $appendHTML, $query);

		}

		// letter col: convertToNumberingScheme($col)
		// col name: $header_col_names[$col]
		// col number: $col


		// cols by number or letters
		$q = array();
		preg_match_all('/{.*}/U', $query, $q, PREG_SET_ORDER, 0);

		foreach ($q as $n => $qq) {
			// if is_numeric, col number, else col letter
			$qq = $qq[0];
			
			$newjq = str_replace("{", '', $qq);
			$newjq = str_replace("}", '', $newjq);
			$newjq = selector_val($newjq);

			$appendHTML = "";
			if (is_numeric($newjq)) {
				// col number
				$col_num = $newjq;
				$appendHTML .= $csvdata[$col_num];

			} else {
				// col letter
				$col_num = convert2ColumnIndex($newjq);
				$appendHTML .= $csvdata[$col_num];
			}

			//firstLineFields
			$query = str_replace($qq, $appendHTML, $query);
		}


		
	} else {
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
	}

	// SQL: {{field name}}, {col number}

	// parse %url%
	$ret = str_replace("%url%", $url, $query);
	
	// ret remaining
	if ($isContainer) {
		return $container_array;
	}
	return $ret;
}

// AA to 26
function convert2ColumnIndex($letters) {
    $num = 0;
    $arr = array_reverse(str_split($letters));

    for ($i = 0; $i < count($arr); $i++) {
        $num += (ord(strtolower($arr[$i])) - 96) * (pow(26, $i));
    }
    return $num - 1;
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
				$img = trim(pq($thisf)->attr("src"));
				if (substr($img,0,2)=="//") {
					$img = "http://".substr($img, 2);
				}
				$html = $img;
			}
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
		case "Alphanumeric":
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
			if ( ((float) $html) >  ((float) $opeq) ) return true;
			break;
		case "numlt":
			$matches = array();
			$re = '/[0-9,.]+/';
			preg_match_all($re, $ret, $matches, PREG_SET_ORDER, 0);
			$html = implode("", $matches);
			if ( ((float) $html) <  ((float) $opeq) ) return true;
			break;
	}
	
	return false;
}
function runmap($offset, $mapCount, $json_config, $file_offset = 0, $preview = false) {
	// get crawled files

	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	if(!file_exists(dirname($path_init))) {
		@mkdir(dirname($path_init), 0777, true);
	}
	$filez = getDirContents($path_init);
	
	for ($i = $offset; $i <= ($offset + $mapCount - 1); ++$i) {

		if (isset($filez[$i])) {
			
			$file_contents = file_get_contents($filez[$i]);
			$ext = pathinfo($filez[$i], PATHINFO_EXTENSION);
			
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
					
				} elseif (($jc_val["inputmethod"] == "csv") && ($ext == "csv")) {

					// CSV Parsing

					$row = 0;
					$row_init = $row + $file_offset;
					$line_fields = array();
					if (($handle = fopen($filez[$i], "r")) !== FALSE) {
						while (($data = fgetcsv($handle, 0, $jc_val["separator"], $jc_val["enclosure"])) !== FALSE) {
							
							// get first line fields
							$firstline = $jc_val["line1parsed"];
							if ( ($firstline == "Y") && ($row == 0) )  {
								$line_fields = $data;
							}

							// get each row with offset
							if ($row >= $row_init) {
							
								$fields_count = count($data); // fields count on row $row

								// validate mapping
								//		function validateOp($query, $url, $html, $op, $opeq) {
								$valid = validateOp($jc_val["validator"], $filez[$i], $data, $jc_val["op"], $jc_val["opeq"]);

								// when validation passed
								if ($valid) {
									// get id
									// 		function parseEntry($query, $url, $ht, $isContainer = false) {
									$id = parseEntry($jc_val["idsel"], $filez[$i], $data, false, $line_fields);

									//var_dump($id);
								}	
							}
							
							$row++;
						}
						fclose($handle);
					}
					
				} elseif ($jc_val["inputmethod"] == "xls") {
					
					// old stuffs
					
				} elseif ($jc_val["inputmethod"] == "xlsx") {

					// new stuffs
					
				}
				
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

function wizbui_curlit($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function wiz_genstandardUrlFromInput($input, $origin_file = "") {
	
	$standardUrl = $input;

	// relative path
	if ((substr($standardUrl, 0, 1) == "/") && (substr($standardUrl,1,1) != "/")) {
		if ($origin_file != "") {
			//$base = str_replace(basename($origin_file), "", $origin_file);
			$parse = parse_url($origin_file);
			$parse_url = parse_url($standardUrl);
			
			$standardUrl = $parse["scheme"];
			$standardUrl .= ($parse["host"] != "") ? "://".$parse["host"] : '';
			$standardUrl .= ($parse_url["path"] != "") ? $parse_url["path"] : '';
			$standardUrl .= ($parse_url["query"] != "") ? "?".$parse_url["query"] : '';
			$standardUrl .= ($parse_url["fragment"] != "") ? "#".$parse_url["fragment"] : '';
		}
	}

	// starts with "//" add http
	if (substr($standardUrl, 0, 2) == "//") {
		$standardUrl = "http:".$standardUrl;
	}

	// doesnt starts with "http" add http://
	if (substr($standardUrl, 0, 4) != "http") {
		$standardUrl = "http://".$standardUrl;
	}
	
	// prevent path hacks
	$standardUrl = str_replace("../", "", $standardUrl);

	// return url
	return trim($standardUrl);
}

function wiz_setOptions($standardUrl) {
	// remove get variables
	$rget = unserialize(get_option('wb_RemoveGets', 'N'));
	if ($rget=='Y') {
		$standardUrl = strtok($standardUrl, '?');
	}
	
	// remove hashes variables
	$rhash = unserialize(get_option('wb_RemoveHashes', 'N'));
	if ($rhash=='Y') {
		$standardUrl = strtok($standardUrl, '#');
	}
	return trim($standardUrl);
}

function wiz_genFileFromstandardUrl($standardUrl) {
	$standardUrl = str_replace("http", "http/", $standardUrl);
	$standardUrl = str_replace("http/s", "https/", $standardUrl);
	$standardUrl = str_replace(":/", "", $standardUrl);
	$standardUrl = str_replace("//", "/", $standardUrl);
	
	$filename = explode("/", $standardUrl);
	
	$last = count($filename) - 1;
	// if there is no "." after the last "/" or there is a total of 2 slashes (http://)
	if ((strpos($filename[$last], ".") === false) || (count($filename) <= 3)) {
		// add index file
		$filename[] = "index.html";
	}
	$filename = implode("/", $filename);

	// remove "://" from path, to create http / https folder then domain then files
	$filename = str_replace("://", "", $filename);

	// prevent path hacks
	$filename = str_replace("../", "", $filename);
	
	// fix filenames for get query parameters
	$filename = str_replace("?", "_", $filename);

	// fix filenames for php files
	$filename = str_replace(array(".php", ".php3", ".php4", ".php5", ".phtml"), ".html", $filename);

	$filename = WIZBUI_PLUGIN_PATH . "cache/" . $filename;

	return trim($filename);
}

function wiz_removeInputFromToCrawl($standardUrl) {
	// remove from to crawl
	$tocrawl = file_get_contents(WIZBUI_PLUGIN_PATH . "crawl.me.txt");
	$tocrawl = explode("\n", $tocrawl);
	if (count($tocrawl) > 0) {
		unset($tocrawl[0]);
	}
	$tocrawl = implode("\n", $tocrawl);
	file_put_contents(WIZBUI_PLUGIN_PATH . "crawl.me.txt", $tocrawl);
}

function wiz_addInputToCrawled($standardUrl) {
	file_put_contents(WIZBUI_PLUGIN_PATH . "crawled.txt", $standardUrl."\n", FILE_APPEND);
}
function wiz_in_crawled($standardUrl) {
	// add to list of crawled urls
	$crawled = file_get_contents(WIZBUI_PLUGIN_PATH . "crawled.txt");
	$crawled = explode("\n", $crawled);
	if (in_array($standardUrl, $crawled)) {
		return true;
	}
	return false;
}
function wiz_in_to_crawl($standardUrl) {
	// add to list of crawled urls
	$crawled = file_get_contents(WIZBUI_PLUGIN_PATH . "crawl.me.txt");
	$crawled = explode("\n", $crawled);
	if (in_array($standardUrl, $crawled)) {
		return true;
	}
	return false;
}

function wiz_validate_whitelist($standardUrl) {
	// verify whitelist
	$pass = array();
	$whitelist = get_whitelist();
	if (count($whitelist) > 0) {
		foreach ($whitelist as $white_arg) {
			if (($standardUrl != "") && (trim($white_arg) != false)) {
				if (strrpos($standardUrl, trim($white_arg)) !== false) {
					$pass[] = $white_arg;
				}
			}
		}
		if (count($pass) == count($whitelist)) {
			return true;
		}
	} else {
		return true;
	}
	return false;
}

function wiz_validate_blacklist($standardUrl) {
	$pass = true;
	$blacklist = get_blacklist();
	if (count($blacklist) > 0) {
		foreach ($blacklist as $black_arg) {
			if (($standardUrl != "") && (trim($black_arg) != false)) {
				if (strrpos($standardUrl, trim($black_arg)) !== false) {
					return false;
				}
			}
		}
		return true;
	} else {
		return true;
	}
	return true;
}

function wiz_download($standardUrl, $file) {
	if(!is_dir(dirname($file)))
		mkdir(dirname($file), 0777, true);
	
	$output = wizbui_curlit($standardUrl);
	file_put_contents($file, $output);
	return $output;
}

function wiz_related_files($output, $filename, $url_origin) {
	try {
		
		$urls_to_add = array();
		
		$ext = substr($filename, strrpos($filename, '.'));
		if ($ext==".css") {
			// TODO: get image urls
		} elseif ($ext==".js") {
			// TODO: nothing
		} else {
			/* get links (a href)
			*  get meta links (link href)
			*  get scripts (script src)
			*  get images (img src)
			**************************/

			$doc = phpQuery::newDocument($output);

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
		
		$ret_urls = array();
		foreach ($urls_to_add as $k => $urlta) {
			$urlta = wiz_genstandardUrlFromInput($urlta, $url_origin);
			$urlta = wiz_setOptions($urlta);
			if (!wiz_in_crawled($urlta)) {
				if (wiz_validate_whitelist($urlta)) {
					if (wiz_validate_blacklist($urlta)) {
						$ret_urls[] = $urlta;
					}
				}
			}
			
		}
		
		return $ret_urls;
		
	} catch (Exception $e) {
		// meh, was prolly binary...
		//echo $e->getMessage();
		return array();
		
	}
	
	return array();
}


function save_related_files($related_files) {
	foreach ($related_files as $_input) {
		$urlx = wiz_genstandardUrlFromInput($_input);
		$urlx = wiz_setOptions($urlx);
		if ((!wiz_in_crawled($urlx)) && (!wiz_in_to_crawl($urlx))) {
			file_put_contents(WIZBUI_PLUGIN_PATH . "crawl.me.txt", $urlx."\n", FILE_APPEND);
		}
	}
}

function wiz_recursive_del($dir) {
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

function wiz_del_files($filenames) {
	foreach ($filenames as $k => $file) {
		$long_filename = WIZBUI_PLUGIN_PATH . "cache/".$file;
		$long_filename = str_replace("../", "", $long_filename);
		if (is_dir($long_filename)) {
			wiz_recursive_del($long_filename);
		} elseif(file_exists($long_filename))  {
			unlink($long_filename);
		}
	}
}

function wiz_create_req_folders_and_files() {
	
	// create cache folder if doesn`t exist
	try {
		$touch = WIZBUI_PLUGIN_PATH . "cache";
		if(!is_dir(dirname($touch)))
			mkdir(dirname($touch), 0777, true);
	} catch (Exception $e) { 
		// meh
	}

	try {
		@chmod(WIZBUI_PLUGIN_PATH . "cache", 0777);
	} catch (Exception $e) { 
		// meh
	}

	try {
		// create crawl.me.txt if doesn`t exist
		$touch = WIZBUI_PLUGIN_PATH . "crawl.me.txt";
		if (!file_exists($touch)) {
			if (!file_exists(dirname($touch))) mkdir(dirname($touch), 0777, true);
			touch($touch);
		}
	} catch (Exception $e) { 
		// meh
	}
	
	try {
		// create crawl.me.txt if doesn`t exist
		$touch = WIZBUI_PLUGIN_PATH . "crawled.txt";
		if (!file_exists($touch)) {
			if (!file_exists(dirname($touch))) mkdir(dirname($touch), 0777, true);
			touch($touch);
		}
	} catch (Exception $e) { 
		// meh
	}
	
}
?>