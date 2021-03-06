<?php
/**
 * jQuery File Tree PHP Connector
 *
 * Version 1.2.0
 *
 * @author - Cory S.N. LaViska A Beautiful Site (http://abeautifulsite.net/)
 * @author - Dave Rogers - https://github.com/daverogers/jQueryFileTree
 *
 * History:
 *
 * 1.2.0 - adding file filter and file crop- by dmacias (01/24/2015)
 * 1.1.0 - adding multiSelect (checkbox) support (08/22/2014)
 * 1.0.2 - fixes undefined 'dir' error - by itsyash (06/09/2014)
 * 1.0.1 - updated to work with foreign characters in directory/file names (12 April 2008)
 * 1.0.0 - released (24 March 2008)
 *
 * Output a list of files for jQuery File Tree
 */

$filepath = urldecode((isset($_POST['dir']) ? $_POST['dir'] : null ));
$filters = $_POST['filter'];

// set checkbox if multiSelect set to true
$checkbox = ( isset($_POST['multiSelect']) && $_POST['multiSelect'] == 'true' ) ? "<input type='checkbox' />" : null;

if( file_exists($filepath) ) {
	$files = scandir($filepath);
	natcasesort($files);
	if( count($files) > 2 ) { // The 2 accounts for . and ..
		echo "<ul class='jqueryFileTree'>";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($filepath . $file) && $file != '.' && $file != '..' && is_dir($filepath . $file) ) {
				echo "<li class='directory collapsed'>{$checkbox}<a href='#' rel='" .htmlentities($filepath . $file). 
				"/'>" . htmlentities((strlen($file) > 33) ? substr($file,0,33).'...' : $file) . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($filepath . $file) && $file != '.' && $file != '..' && !is_dir($filepath . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
    			foreach ($filters as $filter) {
					if (empty($filter) | $ext==$filter) echo "<li class='file ext_{$ext}'>{$checkbox}<a href='#' rel='" . htmlentities($filepath . $file) . "'>" . htmlentities($file) . "</a></li>";
				}
			}
		}
		echo "</ul>";
	}
}

?>

