<?php

   // FILE:          functions.php
   // TITLE:         Omeka/Fedora Commons Connector Functions
   // AUTHOR:  		 Cole Hudson, WSULS Digital Publishing Librarian
   // CREATED:       August 2013
   //
   // PURPOSE:
   // This file contains the XML and Database Functions used omeka update application;
   //
   // OVERALL METHOD:
   // none - function based
   //
   // FUNCTIONS:
   //
   // INCLUDED FILES:
   //	None
   //
   // DATA FILES:
   //   None


function updateID3data($eventInfo) {
	$eventInfo['mt']['mime_type'] = $eventInfo['exif']['FILE']['MimeType'];

	if ($eventInfo['mt']['mime_type'] == "image/jpeg") { $eventInfo['vid']['video']['dataformat'] = "jpg"; }
	else if ($eventInfo['mt']['mime_type'] == "image/png") { $eventInfo['vid']['video']['dataformat'] = "png"; }
	else if ($eventInfo['mt']['mime_type'] == "image/gif") { $eventInfo['vid']['video']['dataformat'] = "gif"; }
	else if ($eventInfo['mt']['mime_type'] == "image/bmp") { $eventInfo['vid']['video']['dataformat'] = "bmp"; }
	else if ($eventInfo['mt']['mime_type'] == "image/tiff") { $eventInfo['vid']['video']['dataformat'] = "tiff"; }
	else if ($eventInfo['mt']['mime_type'] == "image/tif") { $eventInfo['vid']['video']['dataformat'] = "tif"; }
	else { $eventInfo['video']['dataformat'] == "unknown";}

	if ($eventInfo['mt']['mime_type'] == "image/jpeg") { $eventInfo['vid']['video']['lossless'] = "false"; }
	else if ($eventInfo['mt']['mime_type'] == "image/png") { $eventInfo['vid']['video']['lossless'] = "false"; }
	else if ($eventInfo['mt']['mime_type'] == "image/gif") { $eventInfo['vid']['video']['lossless'] = "false"; }
	else if ($eventInfo['mt']['mime_type'] == "image/bmp") { $eventInfo['vid']['video']['lossless'] = "false"; }
	else if ($eventInfo['mt']['mime_type'] == "image/tiff") { $eventInfo['vid']['video']['lossless'] = "true"; }
	else if ($eventInfo['mt']['mime_type'] == "image/tif") { $eventInfo['vid']['video']['lossless'] = "true"; }
	else { $eventInfo['vid']['video']['lossless'] = "unknown";}

	if (ISSET($eventInfo['exif']['IFD0']['BitsPerSample'])) {
		$eventInfo['vid']['video']['bits_per_sample'] = array_sum($eventInfo['exif']['IFD0']['BitsPerSample']);		
	}

	$eventInfo['vid']['video']['pixel_aspect_ratio'] = (float) 1;

	if (ISSET($eventInfo['exif']['THUMBNAIL']['XResolution'])) {
		$eventInfo['vid']['video']['resolution_x'] = $eventInfo['exif']['THUMBNAIL']['XResolution'];
	}

	if (ISSET($eventInfo['exif']['THUMBNAIL']['YResolution'])) {
		$eventInfo['vid']['video']['resolution_y'] = $eventInfo['exif']['THUMBNAIL']['YResolution'];
	}

	if (ISSET($eventInfo['exif']['THUMBNAIL']['Compression'])) {
		$eventInfo['vid']['video']['compression_ratio'] = $eventInfo['exif']['THUMBNAIL']['Compression'];
	}
	else {
		$eventInfo['vid']['video']['compression_ratio'] = 0;
	}

	$updatedMetadata = json_encode($eventInfo['mt'] + $eventInfo['vid']);
	// print_r($eventInfo);


	//connect to MySQL db
	$con = mysqli_connect($eventInfo['host'],$eventInfo['username'],$eventInfo['password'],$eventInfo['omeka2db']);

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	//Update Metadata, size, mime_type, type_os from omeka_files
	mysqli_query($con, "UPDATE omeka_files SET metadata = '$updatedMetadata' WHERE item_id='{$eventInfo['record_id']}'");
	mysqli_query($con, "UPDATE omeka_files SET size = '{$eventInfo['exif']['FILE']['FileSize']}' WHERE item_id='{$eventInfo['record_id']}'");
	mysqli_query($con, "UPDATE omeka_files SET mime_type = '{$eventInfo['mt']['mime_type']}' WHERE item_id='{$eventInfo['record_id']}'");
	if ($eventInfo['mt']['mime_type'] == "image/jpeg") {
		// $type_os = "stuff2";
		$type_os = "JPEG image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	elseif ($eventInfo['mt']['mime_type'] == "image/png") {
		$type_os = "PNG image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	elseif ($eventInfo['mt']['mime_type'] == "image/gif") {
		$type_os = "GIF image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	elseif ($eventInfo['mt']['mime_type'] == "image/bmp") {
		$type_os = "BMP image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	elseif ($eventInfo['mt']['mime_type'] == "image/tiff") {
		$type_os = "TIFF image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	elseif ($eventInfo['mt']['mime_type'] == "image/tif") {
		$type_os = "TIFF image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	else { $type_os = "unknown image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";}
	mysqli_query($con, "UPDATE omeka_files SET type_os = '$type_os' WHERE item_id='{$eventInfo['record_id']}'");



	//update file title to where original_filename from omeka_files matches dc:title
	$xml = simplexml_load_file($eventInfo['xmlURL']);

	$titleArray = array();
	$titles = $xml->xpath('//dc:title');
	foreach ($titles as $title) {
		$titleArray[] = $title;
	}
	mysqli_query($con, "UPDATE omeka_files SET original_filename = '$title' WHERE item_id='{$eventInfo['record_id']}'");
}

function updateImage($eventInfo)
{
	$outputfile = "$eventInfo[OmekaLocation]/$eventInfo[fileLoc]/$eventInfo[oldFilename]";
	$eventInfo['outputfile'] = $outputfile;
	
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

//Set character set/encoding
	mysqli_set_charset($con, "utf8");
	// $charset = mysqli_character_set_name($con);
	// printf ("Current character set is %s\n",$charset);

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


?>