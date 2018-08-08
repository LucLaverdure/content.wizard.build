<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once __DIR__ . '/simplexlsx.class.php';

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
	//var_dump($csv);

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

?>