<?php
$eventInfo = array();
include 'sensitive.php';
include 'updateID3data.php';

//variables
$dc_identifier = $_REQUEST['dc_identifier'];
$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];
$thumbLoc = "files/thumbnails";
$sqthumbLoc = "files/square_thumbnails";
$fullsizeLoc = "files/fullsize";
$fileLoc = "files/original";


$eventInfo['pid'] = $pid;
$eventInfo['dc_identifier'] = $dc_identifier;
$eventInfo['type'] = $type;
$eventInfo['thumbLoc'] = $thumbLoc;
$eventInfo['sqthumbLoc'] = $sqthumbLoc;
$eventInfo['fullsizeLoc'] = $fullsizeLoc;
$eventInfo['fileLoc'] = $fileLoc;
$eventInfo['host'] = $host;
$eventInfo['username'] = $username;
$eventInfo['password'] = $password;
$eventInfo['omeka1db'] = $omeka1db;
$eventInfo['omeka2db'] = $omeka2db;
$eventInfo['FedoraLocation'] = $FedoraLocation;
$eventInfo['OmekaLocation'] = $OmekaLocation;
$url = "$eventInfo[FedoraLocation]/fedora/objects/$eventInfo[pid]/datastreams/ACCESS/content";
$eventInfo['xmlURL'] = "$eventInfo[FedoraLocation]/fedora/objects/$eventInfo[pid]/datastreams/DC/content";
$eventInfo['url'] = $url;

		//connect to MySQL db
		$con = mysqli_connect($eventInfo['host'],$eventInfo['username'],$eventInfo['password'],$eventInfo['omeka2db']);

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
		$eventInfo['record_id'] = $ri[0];
	}

	//Get record_type
	$response = mysqli_query($con,"SELECT record_type FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($rti = $response->fetch_array()) 
	{
		$eventInfo['record_type'] = $rti[0];
	}

	$response = mysqli_query($con,"SELECT filename FROM omeka_files WHERE item_id='{$eventInfo['record_id']}'");
	while ($af = $response->fetch_array())
	{
		$eventInfo['oldFilename'] = $af[0];
	}

	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='thumbnail_constraint'");
	while ($tc = $response->fetch_array())
	{
		$eventInfo['thumbnail_constraint'] = $tc[0];
	}

	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='square_thumbnail_constraint'");
	while ($stc = $response->fetch_array())
	{
		$eventInfo['square_thumbnail_constraint'] = $stc[0];
	}

$outputfile = "$eventInfo[OmekaLocation]/$eventInfo[fileLoc]/$eventInfo[oldFilename]";
$eventInfo['outputfile'] = $outputfile;

function updateImage($eventInfo)
{
	$cmd = "wget $eventInfo[url] --output-document=$eventInfo[outputfile]";
	exec($cmd);

	//retrieve exif data from image
	$exif = exif_read_data($eventInfo['outputfile'], 0, true);
	$eventInfo['exif'] = $exif;

	$filename = preg_replace("/\.[^$]*/","",$eventInfo['oldFilename']);
	if ($eventInfo['exif']['FILE']['MimeType'] == "image/jpeg") { $eventInfo['updatedFilename'] = "$filename.jpg"; }
	else if ($eventInfo['exif']['FILE']['MimeType'] == "image/png") { $eventInfo['updatedFilename'] = "$filename.png"; }
	else if ($eventInfo['exif']['FILE']['MimeType'] == "image/gif") { $eventInfo['updatedFilename'] = "$filename.gif"; }
	else if ($eventInfo['exif']['FILE']['MimeType'] == "image/bmp") { $eventInfo['updatedFilename'] = "$filename.bmp"; }	
	else if ($eventInfo['exif']['FILE']['MimeType'] == "image/tiff") { $eventInfo['updatedFilename'] = "$filename.tiff"; }
	else if ($eventInfo['exif']['FILE']['MimeType'] == "image/tif") { $eventInfo['updatedFilename'] = "$filename.tif"; }
	
//connect to MySQL db
	$con = mysqli_connect($eventInfo['host'],$eventInfo['username'],$eventInfo['password'],$eventInfo['omeka2db']);

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	mysqli_query($con, "UPDATE omeka_files SET filename = '{$eventInfo['updatedFilename']}' WHERE item_id='{$eventInfo['record_id']}'");	

	$newOutputfile = "$eventInfo[OmekaLocation]/$eventInfo[fileLoc]/$eventInfo[updatedFilename]";
	$eventInfo['newOutputfile'] = $newOutputfile;

	//Copy, rename, change permissions, and convert images to folders in files directory
	$cmd = "echo $eventInfo[OmekaLocation]/$eventInfo[fullsizeLoc]/$eventInfo[oldFilename] $eventInfo[OmekaLocation]/$eventInfo[thumbLoc]/$eventInfo[oldFilename] $eventInfo[OmekaLocation]/$eventInfo[sqthumbLoc]/$eventInfo[oldFilename] | xargs -n 1 cp $eventInfo[outputfile]";
	exec($cmd);

	$cmd = "mv $eventInfo[OmekaLocation]/$eventInfo[fullsizeLoc]/$eventInfo[oldFilename] $eventInfo[OmekaLocation]/$eventInfo[fullsizeLoc]/$eventInfo[updatedFilename] && mv $eventInfo[OmekaLocation]/$eventInfo[thumbLoc]/$eventInfo[oldFilename] $eventInfo[OmekaLocation]/$eventInfo[thumbLoc]/$eventInfo[updatedFilename] && mv $eventInfo[OmekaLocation]/$eventInfo[sqthumbLoc]/$eventInfo[oldFilename] $eventInfo[OmekaLocation]/$eventInfo[sqthumbLoc]/$eventInfo[updatedFilename] && mv $eventInfo[outputfile] $eventInfo[newOutputfile]";
	exec($cmd);

	$cmd = "chown www-data:www-data $eventInfo[OmekaLocation]/$eventInfo[fullsizeLoc]/$eventInfo[updatedFilename] $eventInfo[OmekaLocation]/$eventInfo[thumbLoc]/$eventInfo[updatedFilename] $eventInfo[OmekaLocation]/$eventInfo[sqthumbLoc]/$eventInfo[updatedFilename] $eventInfo[newOutputfile]";
	exec($cmd);

	$cmd = "/usr/bin/convert $eventInfo[OmekaLocation]/$eventInfo[thumbLoc]/$eventInfo[updatedFilename] -background white -flatten -thumbnail " . escapeshellarg($eventInfo[thumbnail_constraint].'x'.$eventInfo[thumbnail_constraint].'>'). " $eventInfo[OmekaLocation]/$eventInfo[thumbLoc]/$eventInfo[updatedFilename]";
	exec($cmd);

	$cmd = "/usr/bin/convert $eventInfo[OmekaLocation]/$eventInfo[sqthumbLoc]/$eventInfo[updatedFilename] -thumbnail " . escapeshellarg('x' . $eventInfo[square_thumbnail_constraint]*2). " -resize " . escapeshellarg($eventInfo[square_thumbnail_constraint]*2 . 'x<'). " -resize 50% -background white -flatten -gravity center -crop " . escapeshellarg($eventInfo[square_thumbnail_constraint] . 'x' . $eventInfo[square_thumbnail_constraint] . '+0+0')." +repage $eventInfo[OmekaLocation]/$eventInfo[sqthumbLoc]/$eventInfo[updatedFilename]";
	exec($cmd);

	updateID3data($eventInfo);
}

function updateDC($eventInfo)
{
	//connect to MySQL db
	$con = mysqli_connect($eventInfo['host'],$eventInfo['username'],$eventInfo['password'],$eventInfo['omeka2db']);

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}


	$metaArray = array();

	//download the updated records and iterate through them
	$xml = simplexml_load_file($eventInfo['xmlURL']);

	$titleArray = array();
	$titles = $xml->xpath('//dc:title');
	foreach ($titles as $title) {
		$titleArray[] = $title;
	}

	$creatorArray = array();
	$creators = $xml->xpath('//dc:creator');
	foreach ($creators as $creator) {
		$creatorArray[] = $creator;
	}

	$subjArray = array();
	$subjects = $xml->xpath('//dc:subject');
	foreach ($subjects as $subject) {
		$subjArray[] = $subject;
	}

	$descripArray = array();
	$descriptions = $xml->xpath('//dc:description');
	foreach ($descriptions as $description) {
		$descripArray[] = $description;
	}

	$dateArray = array();
	$dates = $xml->xpath('//dc:date');
	foreach ($dates as $date) {
		$dateArray[] = $date;
	}

	$typeArray = array();
	$types = $xml->xpath('//dc:type');
	foreach ($types as $type) {
		$typeArray[] = $type;
	}	

	$formatArray = array();
	$formats = $xml->xpath('//dc:format');
	foreach ($formats as $format) {
		$formatArray[] = $format;
	}

	$langArray = array();
	$languages = $xml->xpath('//dc:language');
	foreach ($languages as $language) {
		$langArray[] = $language;
	}

	$relatArray = array();
	$relations = $xml->xpath('//dc:relation');
	foreach ($relations as $relation) {
		$relatArray[] = $relation;
	}

	$coverArray = array();
	$coverages = $xml->xpath('//dc:coverage');
	foreach ($coverages as $coverage) {
		$coverArray[] = $coverage;
	}

	$rightsArray = array();
	$rights = $xml->xpath('//dc:rights');
	foreach ($rights as $right) {
		$rightsArray[] = $right;
	}


	//purge rows associated with record_id--this is only to update xml, not anything to do with other datastreams
	mysqli_query($con,"DELETE FROM omeka_element_texts WHERE record_id=(SELECT * from (SELECT record_id FROM omeka_element_texts WHERE element_id=43 AND text='{$eventInfo['dc_identifier']}' LIMIT 1)as t)");

	//iterate through DC elements and insert into table
	//title
	foreach ($titleArray as $title) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',50,'{$eventInfo['record_type']}','{$title}')");
	}
		// $response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	//identifier
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',43,'{$eventInfo['record_type']}','{$eventInfo['dc_identifier']}')");
	
	//subjects
	foreach ($subjArray as $subject) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',49,'{$eventInfo['record_type']}','{$subject}')");
	}

	//description
	foreach ($descripArray as $description) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',41,'{$eventInfo['record_type']}','{$description}')");
	}	

	//creator
	foreach ($creatorArray as $creator) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',39,'{$eventInfo['record_type']}','{$creator}')");
	}	

	//date
	foreach ($dateArray as $date) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',40,'{$eventInfo['record_type']}','{$date}')");
	}	

	//type
	foreach ($typeArray as $type) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',51,'{$eventInfo['record_type']}','{$type}')");
	}	

	//format
	foreach ($formatArray as $format) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',42,'{$eventInfo['record_type']}','{$format}')");
	}	

	//language
	foreach ($langArray as $language) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',44,'{$eventInfo['record_type']}','{$language}')");
	}	

	//relation
	foreach ($relatArray as $relation) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',46,'{$eventInfo['record_type']}','{$relation}')");
	}	

	//coverage
	foreach ($coverArray as $coverage) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',38,'{$eventInfo['record_type']}','{$coverage}')");
	}	

	//rights
	foreach ($rightsArray as $rights) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',47,'{$eventInfo['record_type']}','{$right}')");
	}
}

if (ISSET($type)) {
	if ($type == "initialize"){
	updateImage($eventInfo);
	updateDC($eventInfo);
}
}

else {
	$datastreamId = $_REQUEST['datastreamId'];
	//Uncomment below line to test and comment out line above
	// $datastreamId = 'ACCESS';
	//Datastream ID
if ($datastreamId == "ACCESS")
{
	updateImage($eventInfo);
}

if ($datastreamId == "DC") 
{
	updateDC($eventInfo);
}
}


//FOR TESTING
// var_dump(get_defined_vars());


?>
