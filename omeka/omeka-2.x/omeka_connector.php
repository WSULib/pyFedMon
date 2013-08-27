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
$thumbLoc = "files/thumbnails";
$sqthumbLoc = "files/square_thumbnails";
$fullsizeLoc = "files/fullsize";
$fileLoc = "files/original";

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
$url = "$eventInfo[FedoraLocation]/fedora/objects/$eventInfo[pid]/datastreams/ACCESS/content";
$eventInfo['xmlURL'] = "$eventInfo[FedoraLocation]/fedora/objects/$eventInfo[pid]/datastreams/DC/content";
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





if (ISSET($type)) {
	if ($type == "initialize"){
	updateImage($eventInfo);
	updateDC($eventInfo);
}
}

else {
	$datastreamId = $_REQUEST['datastreamId'];
	// ***********************
	// FOR TESTING
	// Uncomment below one or both of the lines and comment out line above
	// $datastreamId = 'DC';
	// $datastreamId = 'ACCESS';
	// ***********************
if ($datastreamId == "ACCESS")
{
	updateImage($eventInfo);
}

if ($datastreamId == "DC") 
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
