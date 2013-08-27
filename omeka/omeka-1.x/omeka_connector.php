<?php

   // FILE:          omeka_connector.php
   // TITLE:         Omeka v.2/Fedora Commons Connector
   // AUTHOR:  		 Cole Hudson, WSULS Digital Publishing Librarian
   // CREATED:       August 2013
   //
   // PURPOSE:
   // This file receives the PID and identifier  from the Fedora Commons application and uses them to run functions which update the Omeka v.2 program
   // it does not depend on any other files
   //
   // OVERALL METHOD:
   // 1. Receives two variables ($dc_identifier--the identifier from the DC xml on FC--and $pid--the PID from the FC object specified upon ingest)
   // 2. These variables are run through an if test and pushed through the appropriate function(s), i.e. update DC fields or update image bitstream
   // 3. Changes should then be populated in the Omeka database/file structure.
   //
   // FUNCTIONS:
   // updateID3data
   // updateImage
   // updateDC
   //
   // INCLUDED FILES:
   // sensitive.php
   // functions.php
   //
   // DATA FILES:
   // None

$eventInfo = array();
include 'sensitive.php';
include 'functions.php';

//variables received from pyFedmon on Fedora Commons system
$dc_identifier = $_REQUEST['dc_identifier'];
$pid = $_REQUEST['pid'];

//variables
$thumbLoc = "archive/thumbnails";
$sqthumbLoc = "archive/square_thumbnails";
$fullsizeLoc = "archive/fullsize";
$fileLoc = "archive/files";

//Global array which includes needed variables, paths, urls (including variables from sensitive.php)
$eventInfo['pid'] = $pid;
$eventInfo['dc_identifier'] = $dc_identifier;
$eventInfo['thumbLoc'] = $thumbLoc;
$eventInfo['sqthumbLoc'] = $sqthumbLoc;
$eventInfo['fullsizeLoc'] = $fullsizeLoc;
$eventInfo['fileLoc'] = $fileLoc;
$eventInfo['host'] = $host;
$eventInfo['username'] = $username;
$eventInfo['password'] = $password;
$eventInfo['omekaDB'] = $omekaDB;
$eventInfo['FedoraLocation'] = $FedoraLocation;
$eventInfo['OmekaLocation'] = $OmekaLocation;
$eventInfo['ImageContentType'] = $ImageContentType;
$eventInfo['DCContentType'] = $DCContentType;
$url = "$eventInfo[FedoraLocation]/fedora/objects/$eventInfo[pid]/datastreams/$eventInfo[ImageContentType]/content";
$eventInfo['xmlURL'] = "$eventInfo[FedoraLocation]/fedora/objects/$eventInfo[pid]/datastreams/$eventInfo[DCContentType]/content";
$eventInfo['url'] = $url;

	//connect to MySQL db
	$con = mysqli_connect($eventInfo['host'],$eventInfo['username'],$eventInfo['password'],$eventInfo['omekaDB']);

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	//Set character set/encoding
	mysqli_set_charset($con, "utf8");

	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
		$eventInfo['record_id'] = $ri[0];
	}

	//Get record_type
	$response = mysqli_query($con,"SELECT record_type_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($rti = $response->fetch_array()) 
	{
		$eventInfo['record_type'] = $rti[0];
	}

	$response = mysqli_query($con,"SELECT archive_filename FROM omeka_files WHERE item_id='{$eventInfo['record_id']}'");
	while ($af = $response->fetch_array())
	{
		$eventInfo['oldFilename'] = $af[0];
		echo $eventInfo['oldFilename'];
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



if (ISSET($_REQUEST['type'])) {
	$type = $_REQUEST['type'];

	if ($type == "initialize"){
	updateImage($eventInfo);
	updateDC($eventInfo);
}
}

else {
	// $datastreamId = $_REQUEST['datastreamId'];
	// ***********************
	// FOR TESTING
	// Uncomment below one or both of the lines and comment out line above
	// $datastreamId = "$eventInfo[DCContentType]";
	// $datastreamId = "$eventInfo[ImageContentType]";
	// ***********************
if ($datastreamId == "$eventInfo[ImageContentType]")
{
	updateImage($eventInfo);
}

if ($datastreamId == "$eventInfo[DCContentType]") 
{
	updateDC($eventInfo);
}
}

// ***********************
//FOR TESTING
// var_dump(get_defined_vars());
// $charset = mysqli_character_set_name($con);
// printf ("Current character set is %s\n",$charset);
// ***********************

?>














<?php
// include 'sensitive.php';
// $dc_identifier = $_REQUEST['dc_identifier'];
// $pid = $_REQUEST['pid'];
// $type = $_REQUEST['type'];

// //variables
// $FedoraLoc = "http://fedoratest.lib.wayne.edu";
// $OmekaLoc = "/var/www/omeka1";
// $thumbLoc = "archive/thumbnails";
// $sqthumbLoc = "archive/square_thumbnails";
// $fullsizeLoc = "archive/fullsize";
// $fileLoc = "archive/files";


// // echo "this is the dc_identifier.....$dc_identifier";

// function updateImage()
// {

// global $FedoraLoc;
// global $OmekaLoc;
// global $thumbLoc;
// global $sqthumbLoc;
// global $fullsizeLoc;
// global $fileLoc;
// global $pid;
// global $dc_identifier;
// global $type;
// global $con;

// 		//connect to MySQL db
// 	// $con=mysqli_connect("hostname","username","passwd","db_name");

// 	if (mysqli_connect_errno())
// 	{
// 		echo "Failed to connect to MySQL: " . mysqli_connect_error();
// 	}

// 	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
// 	while ($ri = $response->fetch_array()) 
// 	{
// 		$record_id = $ri[0];
// 	}

// 	$response = mysqli_query($con,"SELECT archive_filename FROM omeka_files WHERE item_id='$record_id'");
// 	while ($af = $response->fetch_array())
// 	{
// 		$archive_filename = $af[0];
// 	}

// 	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='thumbnail_constraint'");
// 	while ($tc = $response->fetch_array())
// 	{
// 		$thumbnail_constraint = $tc[0];
// 	}

// 	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='square_thumbnail_constraint'");
// 	while ($stc = $response->fetch_array())
// 	{
// 		$square_thumbnail_constraint = $stc[0];
// 	}

// 	$url = "$FedoraLoc/fedora/objects/$pid/datastreams/ACCESS/content";
// 	$outputfile = "$OmekaLoc/$fileLoc/$archive_filename";
// 	$cmd = "wget \"$url\" --output-document=$outputfile";
// 	exec($cmd);

// 	$cmd = "echo $OmekaLoc/$fullsizeLoc/$archive_filename $OmekaLoc/$thumbLoc/$archive_filename $OmekaLoc/$sqthumbLoc/$archive_filename | xargs -n 1 cp $outputfile && chown www-data:www-data $OmekaLoc/$fullsizeLoc/$archive_filename $OmekaLoc/$thumbLoc/$archive_filename $OmekaLoc/$sqthumbLoc/$archive_filename $outputfile";
// 	exec($cmd);

// 	$cmd = "/usr/bin/convert $OmekaLoc/$thumbLoc/$archive_filename -resize " . escapeshellarg($thumbnail_constraint.'x'.$thumbnail_constraint.'>'). " $OmekaLoc/$thumbLoc/$archive_filename";
// 	exec($cmd);

// 	$cmd = "/usr/bin/convert $OmekaLoc/$sqthumbLoc/$archive_filename -thumbnail " . escapeshellarg('x' . $square_thumbnail_constraint*2). " -resize " . escapeshellarg($square_thumbnail_constraint*2 . 'x<'). " -resize 50% -gravity center -crop " . escapeshellarg($square_thumbnail_constraint . 'x' . $square_thumbnail_constraint . '+0+0')." +repage $OmekaLoc/$sqthumbLoc/$archive_filename";
// 	exec($cmd);

// }

// function updateDC()
// {

// global $FedoraLoc;
// global $OmekaLoc;
// global $thumbLoc;
// global $sqthumbLoc;
// global $fullsizeLoc;
// global $fileLoc;
// global $pid;
// global $dc_identifier;
// global $type;
// global $con;

// 		//connect to MySQL db
// 	// $con=mysqli_connect("hostname","username","passwd","db_name");

// 	if (mysqli_connect_errno())
// 	{
// 		echo "Failed to connect to MySQL: " . mysqli_connect_error();
// 	}

// 	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
// 	while ($ri = $response->fetch_array()) 
// 	{
// 		$record_id = $ri[0];
// 	}

// 	$metaArray = array();

// 	//download the updated records and iterate through them
// 	$url = 'http://fedoratest.lib.wayne.edu/fedora/objects/'.$pid.'/datastreams/DC/content';
// 	$xml = simplexml_load_file($url);

// 	$titleArray = array();
// 	$titles = $xml->xpath('//dc:title');
// 	foreach ($titles as $title) {
// 		$titleArray[] = $title;
// 	}

// 	$creatorArray = array();
// 	$creators = $xml->xpath('//dc:creator');
// 	foreach ($creators as $creator) {
// 		$creatorArray[] = $creator;
// 	}

// 	$subjArray = array();
// 	$subjects = $xml->xpath('//dc:subject');
// 	foreach ($subjects as $subject) {
// 		$subjArray[] = $subject;
// 	}

// 	$descripArray = array();
// 	$descriptions = $xml->xpath('//dc:description');
// 	foreach ($descriptions as $description) {
// 		$descripArray[] = $description;
// 	}

// 	$dateArray = array();
// 	$dates = $xml->xpath('//dc:date');
// 	foreach ($dates as $date) {
// 		$dateArray[] = $date;
// 	}

// 	$typeArray = array();
// 	$types = $xml->xpath('//dc:type');
// 	foreach ($types as $type) {
// 		$typeArray[] = $type;
// 	}	

// 	$formatArray = array();
// 	$formats = $xml->xpath('//dc:format');
// 	foreach ($formats as $format) {
// 		$formatArray[] = $format;
// 	}

// 	$langArray = array();
// 	$languages = $xml->xpath('//dc:language');
// 	foreach ($languages as $language) {
// 		$langArray[] = $language;
// 	}

// 	$relatArray = array();
// 	$relations = $xml->xpath('//dc:relation');
// 	foreach ($relations as $relation) {
// 		$relatArray[] = $relation;
// 	}

// 	$coverArray = array();
// 	$coverages = $xml->xpath('//dc:coverage');
// 	foreach ($coverages as $coverage) {
// 		$coverArray[] = $coverage;
// 	}

// 	$rightsArray = array();
// 	$rights = $xml->xpath('//dc:rights');
// 	foreach ($rights as $right) {
// 		$rightsArray[] = $right;
// 	}

// // var_dump(get_defined_vars());
// 	//Get record_type_id
// 	$response = mysqli_query($con,"SELECT record_type_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
// 	while ($rti = $response->fetch_array()) 
// 	{
// 		$record_type_id = $rti[0];
// 	}

// 	//purge rows associated with record_id--this is only to update xml not anything to do with other datastreams
// 	mysqli_query($con,"DELETE FROM omeka_element_texts WHERE record_id=(SELECT * from (SELECT record_id FROM omeka_element_texts WHERE element_id=43 AND text='{$dc_identifier}' LIMIT 1)as t)");

// 	//iterate through DC elements and insert into table
// 	//title
// 	foreach ($titleArray as $title) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',50,'$record_type_id','{$title}')");
// 	}
// 	//identifier
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',43,'$record_type_id','{$dc_identifier}')");
	
// 	//subjects
// 	foreach ($subjArray as $subject) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',49,'$record_type_id','{$subject}')");
// 	}

// 	//description
// 	foreach ($descripArray as $description) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',41,'$record_type_id','{$description}')");
// 	}	

// 	//creator
// 	foreach ($creatorArray as $creator) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',39,'$record_type_id','{$creator}')");
// 	}	

// 	//date
// 	foreach ($dateArray as $date) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',40,'$record_type_id','{$date}')");
// 	}	

// 	//type
// 	foreach ($typeArray as $type) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',51,'$record_type_id','{$type}')");
// 	}	

// 	//format
// 	foreach ($formatArray as $format) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',42,'$record_type_id','{$format}')");
// 	}	

// 	//language
// 	foreach ($langArray as $language) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',44,'$record_type_id','{$language}')");
// 	}	

// 	//relation
// 	foreach ($relatArray as $relation) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',46,'$record_type_id','{$relation}')");
// 	}	

// 	//coverage
// 	foreach ($coverArray as $coverage) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',38,'$record_type_id','{$coverage}')");
// 	}	

// 	//rights
// 	foreach ($rightsArray as $rights) {
// 	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',47,'$record_type_id','{$right}')");
// 	}
// }

// // echo "this is the record id....$record_id";
// if ($type == "initialize")
// {
// 	updateImage();
// 	updateDC();
// }

// else {
// 	$datastreamId = $_REQUEST['datastreamId'];
// 	//Uncomment below line to test and comment out line above
// 	// $datastreamId = 'ACCESS';
// }



// //Datastream ID
// if ($datastreamId == "ACCESS")
// {
// 	updateImage();
// }

// if ($datastreamId == "DC") 
// {
// 	updateDC();
// }

?>
