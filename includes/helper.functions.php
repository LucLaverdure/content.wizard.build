<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );

function logme($string) {

	$filename = WIZBUI_PLUGIN_PATH . "logs.txt";

	$log = date("Y-m-d H:i:s") . " - " . str_replace(array("\n", "\r"), "", $string) . "\n";

	file_put_contents($filename, $log, FILE_APPEND);
}


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
	$decodeConfig = "";
	if (!is_array($jsonConfig)) {
		$decodeConfig = stripslashes($jsonConfig);
		$decodeConfig = html_entity_decode($decodeConfig);
	}

	$decodeConfig = json_decode($decodeConfig, true);
	$outputConfig = array();


	if ( (!isset($decodeConfig)) || (count($decodeConfig) == 0) ) return;

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

	if ($is_preview && isset($jconfig[0])) $jconfig = $jconfig[0];

	global $latest_first_line, $csvdata, $sheetsHeads, $current_sheet;
	$csvdata = array();
	$ext = substr($url, strrpos($url, '.'));
	$latest_first_line = array();
	$container_array = array();

	$parts = parse_url($url);
	@parse_str($parts['query'], $urlquery);
	$this_file = @$urlquery['file'];
	if (trim($this_file) == "" ) {
		// passed file
		$this_file = $url;
	} else {
		// passed url, get filename
		$this_file = WIZBUI_PLUGIN_PATH."cache/".$this_file;
	}

	if (!$isContainer) {
		logme("-------at file: ".$this_file);
		logme("-------at url: ".$url);
	}


	// parse regex expressions (triple brackets)
	$q = array();
	preg_match_all('/{{{.*}}}/U', $query, $q, PREG_SET_ORDER, 0);

	$handle = "";

	// csv file
	if ($ext == ".csv") {
		$handle = fopen($this_file, "r");

		if (!isset($jconfig["fielddelimiter"])) $jconfig["fielddelimiter"] = ",";
		if (!isset($jconfig["enclosure"])) $jconfig["enclosure"] = '"';

		$latest_first_line = fgetcsv($handle, 0, $jconfig["fielddelimiter"], $jconfig["enclosure"]);

		if ($offset > 0) {
			for ($i = 0; $i < $offset; ++$i) {
				$dump = fgetcsv($handle, 0, $jconfig["fielddelimiter"], $jconfig["enclosure"]);
			}
		}
		$csvdata = fgetcsv($handle, 0, $jconfig["fielddelimiter"], $jconfig["enclosure"]);

	// DB Query
	} elseif ($ext == ".dboquery") {
		
		// parse field name
		$q = array();
		preg_match_all('/{{.*}}/U', $query, $q, PREG_SET_ORDER, 0);
		
		foreach ($q as $n => $qq) {
			$qq = $qq[0];
			
			$newjq = str_replace("{{", '', $qq);
			$newjq = str_replace("}}", '', $newjq);

			$servername = $jconfig["dbhost"];
			$username = $jconfig["dbuser"];
			$password = $jconfig["dbpass"];
			$dbname = $jconfig["dbname"];
			$sql = $jconfig["dbquery"];
	
			// Create connection
			try {
				@$conn = new mysqli($servername, $username, $password, $dbname);

				// Check connection
				if ($conn->connect_error) {

					//die("Connection failed: " . $conn->connect_error);
					logme("----db error: ".$conn->connect_error);

				} else {
					
					$result = @$conn->query($sql);
					
					if ($result->num_rows > 0) {
			
						$row_count = 0;
						// output data of each row
						while($row = $result->fetch_assoc()) {
			
							for ($i = 0; $i < $offset; ++$i) {
								$row = $result->fetch_assoc();
							}
			
							$col_count = 0;
							foreach ($row as $k => $item) {

								if ($k == $newjq) {
									$appendHTML = $item;
									break 2;
								}
								$col_count++;
							}
							
							$row_count++;
						}
			
					}

					$conn->close();

					$query = str_replace($qq, $appendHTML, $query);
				}
				
			} catch (Exception $e) {
				logme("----db error: ".$e->getMessage());
			}
					
		}
		
		// cols by number
		$q = array();
		preg_match_all('/{.*}/U', $query, $q, PREG_SET_ORDER, 0);

		
		foreach ($q as $n => $qq) {
			// if is_numeric, col number, else col letter
			$qq = $qq[0];
			
			$newjq = str_replace("{", '', $qq);
			$newjq = str_replace("}", '', $newjq);
			$newjq = selector_val($newjq);

			$servername = $jconfig["dbhost"];
			$username = $jconfig["dbuser"];
			$password = $jconfig["dbpass"];
			$dbname = $jconfig["dbname"];
			$sql = $jconfig["dbquery"];

			// Create connection
			@$conn = new mysqli($servername, $username, $password, $dbname);
			// Check connection
			if ($conn->connect_error) {
				logme("----db error: ".$conn->connect_error);
			} else {
				
				$result = $conn->query($sql);
				
				if ($result->num_rows > 0) {
		
					$row_count = 0;
					// output data of each row
					while($row = $result->fetch_assoc()) {
		
						for ($i = 0; $i < $offset; ++$i) {
							$row = $result->fetch_assoc();
						}
		
						$col_count = 0;
						foreach ($row as $k => $item) {

							if ($col_count == $newjq) {
								$appendHTML = $item;
								break 2;
							}
							$col_count++;
						}
						
						$row_count++;
					}
		
				} 
				$conn->close();
					

				$query = str_replace($qq, $appendHTML, $query);
			}
		}	


	} elseif (($ext == ".xlsx") || ($ext == ".xls")) {
		// do nothing
	} else {
		$ht = file_get_contents($url);
	}

	// Regex parsing
	foreach ($q as $n => $qq) {
		$qq = $qq[0];
		
		$newregex = str_replace("{{{", '', $qq);
		$newregex = str_replace("}}}", '', $newregex);

		$newq = array();
		@preg_match_all($newregex, $ht, $newq, PREG_SET_ORDER, 0);

		$getzeros = array();
		foreach ($newq as $z => $zero) {
			$getzeros[] = $zero[0];
		}
		
		$getzeros = implode("", $getzeros);
		
		if ($isContainer) {
			$matches = array();
			@preg_match_all($newregex, $ht, $matches, PREG_SET_ORDER, 0);
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


	} elseif (($ext == ".xlsx") || ($ext == ".xls")) {
		// XLSX: sheetname, {col letter}, {{col by field name}}, {col number}
		$csvdata = array();
		$offset_counter = 0;
		$latest_first_line = array();
		if ( $xlsx = SimpleXLSX::parse($this_file)) {

			$sheets = $xlsx->sheetNames();
			foreach ($sheets as $sheetnum => $sheet) {
				$current_sheet = $sheet;
				list( $num_cols, $num_rows ) = $xlsx->dimension( $sheetnum );
				$ret = $xlsx->rows($sheetnum);
				foreach ($ret as $key => $row) {
					if ( ($key == 0) || ($offset_counter == ($offset + 1)) ) {
						for ( $col = 0; $col < $num_cols; $col++ ) {
							if ($key == 0) {
								// 1st line of sheet
								$latest_first_line[$col] = $row[$col];
								array_walk($latest_first_line, 'selector_val');
							} else {
								if ($offset_counter == ($offset + 1)) {
									$csvdata[$col] = $row[$col];
								}
							}
						}
					}

					if ($offset_counter == ($offset + 1)) {
						break 2;
					}

					$offset_counter++;
				}
			}

		}

		// latest sheet name 
		$query = str_replace("%sheetname%", $current_sheet, $query);

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
				if (isset($csvdata[$search_arr])) {
					 $appendHTML .= $csvdata[$search_arr];
				}
			}

			//replace
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

			//replace
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

			try {

				$doc = @phpQuery::newDocument('<div>'.$ht.'</div>'); 
				if (!$isContainer) {
					logme("----searching jq: ".$newjq);
				}
				$code = @$doc->find($newjq);
				$appendHTML = '';
				foreach (@pq($code) as $k => $thisf) {
					$to_push = @pq($thisf)->html();
					if ($isContainer) {
						$container_array[] = $to_push;
					} else {
						$appendHTML .= $to_push;
					}
				}
				$query = str_replace($qq, $appendHTML, $query);

			} catch (Exception $e) {
				logme("----error processing: ".$newjq);
			}
		}
	}

	// parse %url%
	$query = str_replace("%url%", str_replace(WIZBUI_PLUGIN_PATH."cache/", "", $url), $query);
	

	// parse php expressions
	$q = array();
	preg_match_all('/\<\?php.*\?\>/U', $query, $q, PREG_SET_ORDER, 0);
	
	foreach ($q as $n => $qq) {
		$qq = $qq[0];
		
		$newjq = str_replace("<?php", '', $qq);
		$newjq = str_replace("?>", '', $newjq);

		$newjq = stripslashes($newjq);
		$newjq = html_entity_decode($newjq);

		try {
			$ret_val = @eval("return ".$newjq.";");
		} catch (Exception $e) {
			logme("----error: PHP expression failed:".$newjq);
		}

		$query = str_replace($qq, $ret_val, $query);
	}

	$ret = $query;

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
			try {
				$doc = @phpQuery::newDocument('<div>'.$html.'</div>'); 
				$code = @$doc->find("img");
				$img = "";
				foreach (@pq($code) as $k => $thisf) {
					$to_push = @pq($thisf)->html();
					$img = trim(@pq($thisf)->attr("src"));
					if (substr($img,0,2)=="//") {
						$img = "http://".substr($img, 2);
					}
					$html = $img;
				}
			} catch (Exception $e) {
				logme("phpquery error:". $e->getMessage());
			}
			break;
		case "imgsearch":
			try {

				$max_attempts = 5;
				$attempts = 1;

				while($attempts <= $max_attempts) {

					// select random proxy:
					$f_contents = file(WIZBUI_PLUGIN_PATH."proxy.list.txt"); 
					$proxy = $f_contents[rand(0, count($f_contents) - 1)];

					logme("[img search] - " . $html);
					$url_to_fetch = "https://api.qwant.com/api/search/images?count=1&q=".urlencode($html)."&t=images&safesearch=1&locale=en_CA&uiv=4";
					logme("[img fetch] - " . $url_to_fetch);
					$json = wizbui_curlit($url_to_fetch, $proxy);
					$decoded = json_decode($json, true);
					logme("[img url fetched] - " .print_r($decoded, true));
					if ($decoded["status"] != "error") {
						if (isset($decoded["data"]["result"]["items"][0]["media"])) {
							$html = stripslashes($decoded["data"]["result"]["items"][0]["media"]);
							$attempts = 999;
							logme("[img search result] - " . $html);
						}
					} 
					$attempts++;
				}

			} catch (Exception $e) {
				logme("[img Error] - " . $e->getMessage());
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
		case "Image Download":
			// add thumbnail
			/*
			needs to be added after creating the ID
			*/
			break;
	}
	
	return $html;
}

function validateOp($query, $url, $html, $op, $opeq, $config,  $file_offset = 0) {

	//	function parseEntry($query, $url, $ht, $isContainer = false, $jconfig, $is_preview = false, $offset = 0) {
	$ret = parseEntry($query, $url, $html, false, $config, false, $file_offset);

	if ($html == "") {
		
		$html = $ret;

	}
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


// returns parsed fields to 
function build_fields($jc_val, $url, $file_offset) {
	$build_fields = array();
	$raw_fields = $jc_val["fields"];

	foreach ($raw_fields as $keyin => $field) {
		$this_field = "";
		//	function parseEntry($query, $url, $ht, $isContainer = false, $jconfig, $is_preview = false, $offset = 0) {
		$this_field = parseEntry($raw_fields[$keyin]["fieldsel"], $url, "", false, $jc_val, false, $file_offset);

		// function parseAfterOp($html, $op, $opeq) {
		$this_field = parseAfterOp($this_field, $raw_fields[$keyin]["fieldop"], $raw_fields[$keyin]["fieldopeq"]);

		$build_fields[$raw_fields[$keyin]["field-map"]] = $this_field;
	}

	return $build_fields;
}


function wp_id_exists($id, $jc_val) {
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
		return $the_query;
	}

	return false;

}

function update_item($the_query, $build_fields, $jc_val) {
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
		

		logme("-----Updating [".$jc_val["postType"]."]");
	
		// create product category if doesn`t exist
		ob_start();
		if (isset($my_post["product_cat"])) {
			$cats = explode(",", $my_post["product_cat"]);
			$cats_ids = array();

			logme("-----Cats [".$my_post["product_cat"]."]");
		
			foreach($cats as $data) {
				//$thumb_id = fetch_media($data['thumb']);
				$cid = -1;
				$cid = wp_insert_term(
					$data, // the term 
					'product_cat', // the taxonomy
					array(
						'description'=> $data
						//'slug' => $data['slug'],
						//'parent' => $data['parent']
					)
				);
				$cat_id = -1;
				if (is_wp_error($cid)) {
					$cid = -1;
				} else {
					$cat_id = isset( $cid['term_id'] ) ? $cid['term_id'] : -1;
					logme("-----Created cat id [".$cat_id."]");
				}
				if ($cat_id >= 0) {
					$cats_ids[] = $cat_id;
				} else {
					$term = @get_term_by('name', $data, 'product_cat');
					$cat_id = $term->term_id;
					$cats_ids[] = $cat_id;
				}
			}
			@wp_set_object_terms( get_the_ID(), $cats_ids, 'product_cat' );

		}
		$obout = ob_get_clean();
		logme("----- supressed info: ".$obout);

		// create post category if doesn`t exist
		ob_start();
		if (isset($my_post["post_category"])) {
			$cats = explode(",", $my_post["post_category"]);
			$cats_ids = array();
			foreach($cats as $cat) {
				logme("-----Creating Category: ".$cat);
				$term_id = @term_exists($cat);
				if ($term_id > 0) {
					$cats_ids[] = $term_id;
				} else {
					$cats_ids[] = @wp_create_category($cat);
				}
			}
			@wp_set_post_categories(get_the_ID(), $cats_ids, true);
		}
		$obout = ob_get_clean();
		logme("----- supressed info: ".$obout);

		
		// Update the post into the database
		@wp_update_post( $my_post );
		foreach($meta as $mk => $mv) {
			@update_post_meta(get_the_ID(), $mk, $mv);
		}
		
		if (isset($my_post["thumbnail"])) {
			logme("-----Adding Image: url(".$my_post["thumbnail"].")");
			// add_image($post_id, $image_url, $image_name) {
			add_image(get_the_ID(), $my_post["thumbnail"], basename($my_post["thumbnail"]));
		}
		
	endwhile;
}

function create_item($the_query, $build_fields, $jc_val, $id) {

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

	logme("-----Creating [".$jc_val["postType"]."]");

	$pid = @wp_insert_post($my_post);

	if ($pid == 0) {
		logme("-----Failed to create item: All items need a `post_title` and a `post_content` field...");
		return;
	}

	logme("-----WP_ID generated: ".$pid);

	// create product category if doesn`t exist
	ob_start();
	if (isset($my_post["product_cat"])) {
		$cats = explode(",", $my_post["product_cat"]);
		$cats_ids = array();

		logme("-----Cats [".$my_post["product_cat"]."]");
	
		foreach($cats as $data) {
			//$thumb_id = fetch_media($data['thumb']);
			$cid = -1;
			$cid = wp_insert_term(
				$data, // the term 
				'product_cat', // the taxonomy
				array(
					'description'=> $data
					//'slug' => $data['slug'],
					//'parent' => $data['parent']
				)
			);
			$cat_id = -1;
			if (is_wp_error($cid)) {
				$cid = -1;
			} else {
				$cat_id = isset( $cid['term_id'] ) ? $cid['term_id'] : -1;
				logme("-----Created cat id [".$cat_id."]");
			}
			if ($cat_id >= 0) {
				$cats_ids[] = $cat_id;
			} else {
				$term = @get_term_by('name', $data, 'product_cat');
				$cat_id = $term->term_id;
				$cats_ids[] = $cat_id;
			}
		}
		@wp_set_object_terms( $pid, $cats_ids, 'product_cat' );

	}
	$obout = ob_get_clean();
	logme("----- supressed info: ".$obout);

	

	// create post category if doesn`t exist
	ob_start();
	if (isset($my_post["post_category"])) {
		$cats = explode(",", $my_post["post_category"]);
		$cats_ids = array();
		foreach($cats as $cat) {
			$term_id = @term_exists($cat);
			if ($term_id > 0) {
				$cats_ids[] = $term_id;
			} else {
				$cats_ids[] = @wp_create_category($cat);
			}
		}
		@wp_set_post_categories($pid, $cats_ids, true);
	}
	$obout = ob_get_clean();
	logme("----- supressed info: ".$obout);
	
	logme("-----Updating meta info... ");

	// Update the post into the database
	@update_post_meta($pid, 'wizard_build_id', $id);
	foreach($meta as $mk => $mv) {
		@update_post_meta($pid , $mk, $mv);
	}

	if (isset($my_post["thumbnail"])) {								
		logme("-----Adding Image: url(".$my_post["thumbnail"].")");
		//add_image($post_id, $image_url, $image_name)
		add_image($pid, $my_post["thumbnail"], basename($my_post["thumbnail"]));
	}
}
function runmap($offset, $json_config, $preview = false) {

	$map_params_ret = array();
	$map_params_ret["config"] = $json_config;
	$map_params_ret["offset"] = $offset;
	$map_params_ret["process"] = "next";

	$global_counter = 0;

	// get crawled files
	if ($offset == 0)  {
		logme("----------------------------------------------------------------------------");
		logme("Mappings Thread initialized");

		// get proxy list

		// start fresh
		if (file_exists(WIZBUI_PLUGIN_PATH."proxy.list.txt")) {
			unlink(WIZBUI_PLUGIN_PATH."proxy.list.txt");
		}

		// get free proxy list
		$proxy_page = @file_get_contents("https://free-proxy-list.net/");
		$proxy_list = array();
		try {
			$this_proxy = "";
			$doc = @phpQuery::newDocument($proxy_page); 
			$code = @$doc->find("#proxylisttable tr td:first-child");
			foreach (@pq($code) as $k => $thisf) {
				$proxy_list[$k] = @pq($thisf)->text();
			}
			$code = @$doc->find("#proxylisttable tr td:nth-child(2)");
			foreach (@pq($code) as $k => $thisf) {
				$proxy_list[$k] .= ":". @pq($thisf)->text();
			}
		} catch (Exception $e) {
			logme("proxy phpquery error:". $e->getMessage());
		}

		@file_put_contents(WIZBUI_PLUGIN_PATH."proxy.list.txt", implode("\n", $proxy_list), FILE_APPEND);

	}


	logme("New Thread, Offset: ".$offset);

	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	
	if(!file_exists(dirname($path_init))) {
		@mkdir(dirname($path_init), 0777, true);
	}
	$filez = getDirContents($path_init);

	$filez[] = "placeholder.dboquery";
	logme("Added placeholder file for DB (placeholder.dboquery).");

	// for each file
	for ($i = 0; $i <= count($filez); ++$i) {

		// when file exists
		if (isset($filez[$i])) {
			if ($global_counter >= ($offset)) {
				logme("-File open: ".$filez[$i]);
			}
			// for each row (file offset)
				
				$ext = pathinfo($filez[$i], PATHINFO_EXTENSION);

				if ( (!isset($json_config)) || (!is_array($json_config)) ) {
					logme("-No config found...");
					return;
				} 

				// for each Mappings Group
				foreach($json_config as $jc_key => $jc_val) {

					// get cap of file
					$cap = 0;
					if ($jc_val["inputmethod"] == "scraper") {
						// amount of containers
						/*
						$cap = count(parseEntry($jc_val["containerInstance"], $filez[$i], "", true, $jc_val, false, 0));
						if ($global_counter >= ($offset)) {
							logme("-Scraper containers: ".$cap);
						}
						*/
					} elseif (($jc_val["inputmethod"] == "csv") && ($ext == "csv")) {
						// amount of rows
						$fp = file($filez[$i]);
						$cap = count($fp);
						if ($global_counter >= ($offset)) {
							logme("-CSV count: ".$cap);
						}
					} elseif (($jc_val["inputmethod"] == "xlsx") && ($ext == "xlsx")) {
						// amount of rows
						if ( $xlsx = SimpleXLSX::parse($filez[$i])) {
							$sheets = $xlsx->sheetNames();
							foreach ($sheets as $sheetnum => $sheet) {
								list( $num_cols, $num_rows ) = $xlsx->dimension( $sheetnum );
								$cap += $num_rows;
							}
						}
						if ($global_counter >= ($offset)) {
							logme("-XLSX row count: ".$cap);
						}
					} elseif (($jc_val["inputmethod"] == "sql") && ($ext == "dboquery")) {
						// amount of rows
						$servername = $jc_val["dbhost"];
						$username = $jc_val["dbuser"];
						$password = $jc_val["dbpass"];
						$dbname = $jc_val["dbname"];
						$sql = $jc_val["dbquery"];
							
						// Create connection
						$conn = new mysqli($servername, $username, $password, $dbname);
						// Check connection
						if ($conn->connect_error) {
							die("Connection failed: " . $conn->connect_error);
						}
						
						$result = $conn->query($sql);
						
						$cap = $result->num_rows;
					}
					$cap = (int) $cap;


					// run up to file cap
					for ($fset = 0; $fset <= $cap; ++$fset) {

						if ($global_counter >= ($offset + 35)) {
							$map_params_ret["offset"] = $offset + 35;
							echo json_encode($map_params_ret, JSON_FORCE_OBJECT);
							return;
						}

						if ($global_counter >= ($offset)) {
							logme("---offset:".$global_counter);
						}
						$global_counter++;

						$containers = array(); //  instance containers

						if ($global_counter >= ($offset)) {

							logme("Memory used: ".(memory_get_peak_usage(false)/1024/1024)." MiB");
							logme("--Parsing Row: (".$fset."/".$cap.") file: ".$filez[$i]);
						
							// Scraper Method
							if ($jc_val["inputmethod"] == "scraper") {
								
								// get containers
								// 		function parseEntry($query, $url, $ht, $isContainer = false, $jconfig, $is_preview = false, $offset = 0) {
								$containers = parseEntry($jc_val["containerInstance"], $filez[$i], "", true, $jc_val, false, $fset);
								logme("---Containers count: ".count($containers));
								foreach ($containers as $container) {

									// adjust container
									// 		function parseAfterOp($html, $op, $opeq) {
									$container = parseAfterOp($container, $jc_val["containerop"], $jc_val["containeropeq"]);
									// validate mapping
									//		function validateOp($query, $url, $html, $op, $opeq, $config,  $file_offset = 0) {
									logme("---Validation try: ".$filez[$i]);
									$valid = validateOp($jc_val["validator"], $filez[$i], $container, $jc_val["op"], $jc_val["opeq"], $jc_val, $fset);
									
									// when validation passed
									if ($valid) {
										logme("----success");
										// get id
										// 		function parseEntry($query, $url, $ht, $isContainer = false) {
										$id = parseEntry($jc_val["idsel"], $filez[$i], $container, false, $jc_val, false, $fset);

										// 		function parseAfterOp($html, $op, $opeq) {
										$id = parseAfterOp($id, $jc_val["idop"], $jc_val["idopeq"]);
										
										// build fields
										$build_fields = build_fields($jc_val, $filez[$i], $fset);
										
										$the_query = wp_id_exists($id, $jc_val);

										if ($the_query !== false) {
											update_item($the_query, $build_fields, $jc_val);
										} else {
											create_item($the_query, $build_fields, $jc_val, $id);
										}

										if ($the_query !== false) {
											wp_reset_postdata();
										}
									} else {
										// not valid
										logme("----fail");
									}
									
								} // containers
								
							} elseif (
								(($jc_val["inputmethod"] == "csv") && ($ext == "csv")) ||
								(($jc_val["inputmethod"] == "xlsx") && ($ext == "xlsx")) ||
								(($jc_val["inputmethod"] == "sql") && ($ext == "dboquery"))
							) {

								logme("---Input Type = (".$jc_val["inputmethod"].")");

								// CSV Parsing
								
								// validate mapping

								//	validateOp($query, $url, $html, $op, $opeq, $config,  $file_offset = 0) {
								logme("---Validation try `".$jc_val["validator"]."` is `".$jc_val["op"]."` of `".$jc_val["opeq"]."`)");
								$valid = validateOp($jc_val["validator"], $filez[$i], "", $jc_val["op"], $jc_val["opeq"], $jc_val, $fset);

								// when validation passed
								if ($valid) {

									logme("----Success");

									// get id

									//	function parseEntry($query, $url, $ht, $isContainer = false, $jconfig, $is_preview = false, $offset = 0) {
									$id = parseEntry($jc_val["idsel"], $filez[$i], "", false, $jc_val, false, $fset);

									logme("----id expression: ". $jc_val["idsel"]);

									// 	function parseAfterOp($html, $op, $opeq) {
									$id = parseAfterOp($id, $jc_val["idop"], $jc_val["idopeq"]);

									logme("----id formatted: ". $id);
											
									// build fields
									$build_fields = build_fields($jc_val, $filez[$i], $fset);

									$the_query = wp_id_exists($id, $jc_val);

									if ($the_query !== false) {
										logme("----wiz id exists: Update item.");
										update_item($the_query, $build_fields, $jc_val);
									} else {
										logme("----wiz id doesn't exists: Create item.");
										create_item($the_query, $build_fields, $jc_val, $id);
									}

									if ($the_query !== false) {
										wp_reset_postdata();
									} 


								} else {
									// validation fail
									logme("----Fail");
								}

							} // file type

						} else {
							//logme("--Skipped Row: (".$fset."/".$cap.") file: ".$filez[$i]);
						}// counter offset
				}

			} // mappings group
		} else {
			// if file doesn't exist, end queue
			$map_params_ret["process"] = "stop";
			echo json_encode($map_params_ret, JSON_FORCE_OBJECT);
			return;
		}
	} // foreach file
}


/*
	add image attachment to post
*/
function add_image($post_id, $image_url, $image_name) {
// Add Featured Image to Post
    //$image_url        = 'http://s.wordpress.org/style/images/wp-header-logo.png'; // Define the image URL here
	//$image_name       = 'wp-header-logo.png';
	ob_start();
	
	try {

		if (trim($image_url) == "") {
			logme("Error: No image url provided for thumbnail...");
			return;
		}

		$image_url = strtok($image_url, '?');
		$image_name = strtok($image_name, '?');
		$upload_dir       = wp_upload_dir(); // Set upload folder
		$image_data       = @file_get_contents($image_url); // Get image data
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         = basename( $unique_file_name ); // Create image file name

		// Check folder permission and define file location
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		@file_put_contents( $file, $image_data );

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

	} catch (Exception $e) {
		
		logme("Error adding image: ".$e->getMessage());

	}
	$obout = ob_get_clean();
	logme("supressed image save output: ".$obout);

}

function wizbui_curlit($url, $proxy = null) {

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_PROXY, $proxy);
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
			try {
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
			} catch (Exception $e) {
				logme("Error phpquery:". $e->getMessage());
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