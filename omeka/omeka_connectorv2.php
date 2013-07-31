<?php
<<<<<<< HEAD
include 'sensitive.php';
$dc_identifier = $_REQUEST['dc_identifier'];
$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];

//variables
// $FedoraLoc = "";
// $OmekaLocation = "";
=======
$eventInfo = array();
include 'sensitive.php';
include 'updateID3data.php';
//variables
$dc_identifier = $_REQUEST['dc_identifier'];
$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
$thumbLoc = "files/thumbnails";
$sqthumbLoc = "files/square_thumbnails";
$fullsizeLoc = "files/fullsize";
$fileLoc = "files/original";

<<<<<<< HEAD
// echo "this is the dc_identifier.....$dc_identifier";

function updateImage()
{

global $FedoraLoc;
global $OmekaLocation;
global $thumbLoc;
global $sqthumbLoc;
global $fullsizeLoc;
global $fileLoc;
global $pid;
global $dc_identifier;
global $type;

		//connect to MySQL db
	// $con=mysqli_connect("hostname","username","passwd","db_name");
=======
// $dc_identifier = "wayne:MOTA_PH_19871988_5t_029";
// $pid = "wayne:MOTA_PH_19871988_5t_029";
// $type = "initialize";

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
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
<<<<<<< HEAD
		$record_id = $ri[0];
	}

	$response = mysqli_query($con,"SELECT filename FROM omeka_files WHERE item_id='$record_id'");
	while ($af = $response->fetch_array())
	{
		$archive_filename = $af[0];
=======
		$eventInfo['record_id'] = $ri[0];
		// echo $eventInfo['record_id'];
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
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}

	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='thumbnail_constraint'");
	while ($tc = $response->fetch_array())
	{
<<<<<<< HEAD
		$thumbnail_constraint = $tc[0];
=======
		$eventInfo['thumbnail_constraint'] = $tc[0];
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}

	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='square_thumbnail_constraint'");
	while ($stc = $response->fetch_array())
	{
<<<<<<< HEAD
		$square_thumbnail_constraint = $stc[0];
	}

	$url = "$FedoraLoc/fedora/objects/$pid/datastreams/ACCESS/content";
	$outputfile = "$OmekaLocation/$fileLoc/$archive_filename";
	$cmd = "wget \"$url\" --output-document=$outputfile";
	exec($cmd);

	$cmd = "echo $OmekaLocation/$fullsizeLoc/$archive_filename $OmekaLocation/$thumbLoc/$archive_filename $OmekaLocation/$sqthumbLoc/$archive_filename | xargs -n 1 cp $outputfile && chown www-data:www-data $OmekaLocation/$fullsizeLoc/$archive_filename $OmekaLocation/$thumbLoc/$archive_filename $OmekaLocation/$sqthumbLoc/$archive_filename $outputfile";
	exec($cmd);

	$cmd = "/usr/bin/convert $OmekaLocation/$thumbLoc/$archive_filename -background white -flatten -thumbnail " . escapeshellarg($thumbnail_constraint.'x'.$thumbnail_constraint.'>'). " $OmekaLocation/$thumbLoc/$archive_filename";
	exec($cmd);

	$cmd = "/usr/bin/convert $OmekaLocation/$sqthumbLoc/$archive_filename -thumbnail " . escapeshellarg('x' . $square_thumbnail_constraint*2). " -resize " . escapeshellarg($square_thumbnail_constraint*2 . 'x<'). " -resize 50% -background white -flatten -gravity center -crop " . escapeshellarg($square_thumbnail_constraint . 'x' . $square_thumbnail_constraint . '+0+0')." +repage $OmekaLocation/$sqthumbLoc/$archive_filename";
	exec($cmd);

}

function updateDC()
{

global $FedoraLoc;
global $OmekaLocation;
global $thumbLoc;
global $sqthumbLoc;
global $fullsizeLoc;
global $fileLoc;
global $pid;
global $dc_identifier;
global $type;

		//connect to MySQL db
	// $con=mysqli_connect("hostname","username","passwd","db_name");
=======
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
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

<<<<<<< HEAD
	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
		$record_id = $ri[0];
	}
=======
	// $response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	// while ($ri = $response->fetch_array()) 
	// {
	// 	$record_id = $ri[0];
	// }
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

	$metaArray = array();

	//download the updated records and iterate through them
<<<<<<< HEAD
	$url = 'http://fedoratest.lib.wayne.edu/fedora/objects/'.$pid.'/datastreams/DC/content';
	$xml = simplexml_load_file($url);
=======
	$xml = simplexml_load_file($eventInfo['xmlURL']);
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

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

// var_dump(get_defined_vars());
<<<<<<< HEAD
	//Get record_type_id
	$response = mysqli_query($con,"SELECT record_type_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($rti = $response->fetch_array()) 
	{
		$record_type_id = $rti[0];
	}

	//purge rows associated with record_id--this is only to update xml not anything to do with other datastreams
	mysqli_query($con,"DELETE FROM omeka_element_texts WHERE record_id=(SELECT * from (SELECT record_id FROM omeka_element_texts WHERE element_id=43 AND text='{$dc_identifier}' LIMIT 1)as t)");
=======


	//purge rows associated with record_id--this is only to update xml not anything to do with other datastreams
	mysqli_query($con,"DELETE FROM omeka_element_texts WHERE record_id=(SELECT * from (SELECT record_id FROM omeka_element_texts WHERE element_id=43 AND text='{$eventInfo['dc_identifier']}' LIMIT 1)as t)");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

	//iterate through DC elements and insert into table
	//title
	foreach ($titleArray as $title) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',50,'$record_type_id','{$title}')");
	}
	//identifier
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',43,'$record_type_id','{$dc_identifier}')");
	
	//subjects
	foreach ($subjArray as $subject) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',49,'$record_type_id','{$subject}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',50,'{$eventInfo['record_type']}','{$title}')");
	}
		// $response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	//identifier
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',43,'{$eventInfo['record_type']}','{$eventInfo['dc_identifier']}')");
	
	//subjects
	foreach ($subjArray as $subject) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',49,'{$eventInfo['record_type']}','{$subject}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}

	//description
	foreach ($descripArray as $description) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',41,'$record_type_id','{$description}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',41,'{$eventInfo['record_type']}','{$description}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//creator
	foreach ($creatorArray as $creator) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',39,'$record_type_id','{$creator}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',39,'{$eventInfo['record_type']}','{$creator}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//date
	foreach ($dateArray as $date) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',40,'$record_type_id','{$date}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',40,'{$eventInfo['record_type']}','{$date}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//type
	foreach ($typeArray as $type) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',51,'$record_type_id','{$type}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',51,'{$eventInfo['record_type']}','{$type}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//format
	foreach ($formatArray as $format) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',42,'$record_type_id','{$format}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',42,'{$eventInfo['record_type']}','{$format}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//language
	foreach ($langArray as $language) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',44,'$record_type_id','{$language}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',44,'{$eventInfo['record_type']}','{$language}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//relation
	foreach ($relatArray as $relation) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',46,'$record_type_id','{$relation}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',46,'{$eventInfo['record_type']}','{$relation}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//coverage
	foreach ($coverArray as $coverage) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',38,'$record_type_id','{$coverage}')");
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',38,'{$eventInfo['record_type']}','{$coverage}')");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	}	

	//rights
	foreach ($rightsArray as $rights) {
<<<<<<< HEAD
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',47,'$record_type_id','{$right}')");
	}
}

// echo "this is the record id....$record_id";
if ($type == "initialize")
{
	updateImage();
	updateDC();
=======
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type, text) VALUES ('{$eventInfo['record_id']}',47,'{$eventInfo['record_type']}','{$right}')");
	}
}

// // echo "this is the record id....$record_id";
if (ISSET($type)) {
	if ($type == "initialize"){
	updateImage($eventInfo);
	updateDC($eventInfo);
}
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
}

else {
	$datastreamId = $_REQUEST['datastreamId'];
	//Uncomment below line to test and comment out line above
	// $datastreamId = 'ACCESS';
<<<<<<< HEAD
}



//Datastream ID
if ($datastreamId == "ACCESS")
{
	updateImage();
=======
	//Datastream ID
if ($datastreamId == "ACCESS")
{
	updateImage($eventInfo);
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
}

if ($datastreamId == "DC") 
{
<<<<<<< HEAD
	updateDC();
}

?>
=======
	updateDC($eventInfo);
}
}





?>
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
