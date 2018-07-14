<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$root = "";
$_POST['dir'] = urldecode($_POST['dir']);

if( file_exists($root . $_POST['dir']) ) {
	$files = scandir($root . $_POST['dir']);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {

				$filed = scandir($root . $_POST['dir'] . $file);
				$counted = count($filed) - 2; // less "." and ".."
				$short_path = str_replace("../wp-content/plugins/content.wizard.build/cache/","",$_POST['dir'].$file);
			
				echo "<li class=\"directory collapsed\"><input type=\"checkbox\" class=\"chkfile\" value=\"".htmlentities($short_path)."\"> <a onclick=\"$(this).prev().click();\" href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . ' <span style="color:#ccc;font-size:16px;">('.$counted.')</span></a></li>';
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
				$short_path = str_replace("../wp-content/plugins/content.wizard.build/cache/","",$_POST['dir'].$file);
				$ext = preg_replace('/^.*\./', '', $file);
				echo "<li class=\"file ext_$ext\"><input type=\"checkbox\" class=\"chkfile\" value=\"".htmlentities($short_path)."\"> <a onclick=\"$(this).prev().click();\" href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
			}
		}
		echo "</ul>";	
	}
}

?>