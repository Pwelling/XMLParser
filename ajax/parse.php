<?php
// ini_set('error_reporting',E_ALL);
// ini_set('display_errors','on');
include('../classes/XmlParser.php');

$file = isset($_POST['file']) ? $_POST['file'] : false;
if($file !== false){
	$XmlParser = new XmlParser($file);
	$return = $XmlParser->returnArray();
} else {
	$return = array('error'=>'NO_FILE');
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($return);
?>