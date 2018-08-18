<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once __DIR__ . '/../lib/xlsx/simplexlsx.class.php';

function convertToNumberingScheme($number) {
	$baseChar = 66;//"A";
	$letters  = "";

	do {
		$number -= 1;
		$letters = chr($baseChar + ($number % 26)) . $letters;
		$number = ($number / 26) >> 0; // floor
	} while($number > 0);

  return $letters;
}

function parse_xlsx($file) {
	if ( $xlsx = SimpleXLSX::parse($file)) {
		$sheets = $xlsx->sheetNames();
		foreach ($sheets as $sheetnum => $sheet) {
			 list( $num_cols, $num_rows ) = $xlsx->dimension( $sheetnum );
			 $ret = $xlsx->rows($sheetnum);
			 foreach ($ret as $key => $row) {
				for ( $col = 0; $col < $num_cols; $col++ ) {
					echo "sheet:".$sheet.", col:".$col.", row:".$key.', value:'. $row[$col]."<br>";
				}
			 }
		}
		
	} else {
		echo SimpleXLSX::parse_error();
	}
}

function preview_xlsx($file) {

	$header_col_names = array();

	if ( $xlsx = SimpleXLSX::parse($file)) {
		$sheets = $xlsx->sheetNames();
		foreach ($sheets as $sheetnum => $sheet) {
			echo '<h3 data-sheetname="%sheetname%">'.$sheet."</h3>";
			list( $num_cols, $num_rows ) = $xlsx->dimension( $sheetnum );
			$ret = $xlsx->rows($sheetnum);
			echo '<table class="xlsx-preview" style="width:100%;border:1px solid #000;">';
			foreach ($ret as $key => $row) {
				echo '<tr>';
				for ( $col = 0; $col < $num_cols; $col++ ) {
					
					if ($key==0) {
						// 1st line
						$header_col_names[$col] = $row[$col];
					}
					
					echo '<td data-letterscol="'.convertToNumberingScheme($col).'" data-colname="'.$header_col_names[$col].'" data-colnum="'.$col.'" style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$row[$col]."</td>";
				}
				echo '</tr>';
			}
			echo '</table>';
		}
		
	} else {
		echo SimpleXLSX::parse_error();
	}
}

function preview_csv($file) {
	$csv = array_map('str_getcsv', file($file));

	$header_col_names = array();

	echo '<table class="xlsx-preview" style="width:100%;border:1px solid #000;">';

	foreach($csv as $i => $row) {
		echo '<tr>';
		foreach ($row as $col => $cell) {
			if ($i==0) {
				// 1st line
				$header_col_names[$col] = $row[$col];
			}
			echo '<td data-letterscol="'.convertToNumberingScheme($col).'" data-colname="'.$header_col_names[$col].'" data-colnum="'.$col.'" style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$row[$col]."</td>";					
			echo "\n";
		}
		echo '</tr>';
	}

	echo '</table>';
}

function preview_db($file) {

	$this_query = str_replace(WIZBUI_PLUGIN_PATH."cache/", "", $file);
	$this_query = str_replace(".dboquery", "", $this_query);

	$opt = get_option('wb_mappings', null);
	if ($opt !==  null) { 
		$opts = unserialize($opt);
		$opts = json_decode(stripslashes($opts), true);

		$found_i = 0;
		foreach ($opts as $opt) {
			if ($opt[0] == "sql") {
				// select sql item
				if ($this_query == $found_i) {
					// query db
					// 12: user, 11: host, 14: db name, 15: query
					$servername = $opt[11];
					$username = $opt[12];
					$password = $opt[13];
					$dbname = $opt[14];
					$query = $opt[15];
					
					// Create connection
					$conn = new mysqli($servername, $username, $password, $dbname);
					// Check connection
					if ($conn->connect_error) {
						die("Connection failed: " . $conn->connect_error);
					}
					
					$sql = $query;
					$result = $conn->query($sql);
					
					if ($result->num_rows > 0) {

						echo '<table class="xlsx-preview" style="width:100%;border:1px solid #000;">';

						$row_count = 0;
						// output data of each row
						while($row = $result->fetch_assoc()) {
							//echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";

							if ($row_count == 0) {
								echo '<tr>';
								$col_count = 0;
								foreach ($row as $k => $item) {
									echo '<td data-colname="'.$k.'" data-colnum="'.$col_count.'" style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$k."</td>";
									$col_count++;
								}
								echo '</tr>';
							}

							echo '<tr>';
							$col_count = 0;
							foreach ($row as $k => $item) {
								echo '<td data-colname="'.$k.'" data-colnum="'.$col_count.'" style="border-right:1px solid #000;border-bottom:1px solid #000;">'.$item."</td>";
								$col_count++;
							}
							
							$row_count++;
							echo '</tr>';
						}

						echo '</table>';

					} else {
						echo "0 results";
					}
					$conn->close();

				}
				$found_i++;
			}
		}

	}
}

?>