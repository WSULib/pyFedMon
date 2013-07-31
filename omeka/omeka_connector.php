<?php
include 'sensitive.php';
$dc_identifier = $_REQUEST['dc_identifier'];
$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];

//variables
$FedoraLoc = "http://fedoratest.lib.wayne.edu";
$OmekaLoc = "/var/www/omeka1";
$thumbLoc = "archive/thumbnails";
$sqthumbLoc = "archive/square_thumbnails";
$fullsizeLoc = "archive/fullsize";
$fileLoc = "archive/files";

<<<<<<< HEAD
//Testing
// $pid = 'fedomeka:3';
// $dc_identifier = 'fedomeka:3';
// $type = '';


// echo "this is the dc_identifier.....$dc_identifier";


//NOTES:
//Change to where the file is converted to jpg and rename all of the extensions to jpg.  Get the hash not the extension too.
//Put version on github; takeout all the passwords; make a 2.0 version

=======

// echo "this is the dc_identifier.....$dc_identifier";

>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
function updateImage()
{

global $FedoraLoc;
global $OmekaLoc;
global $thumbLoc;
global $sqthumbLoc;
global $fullsizeLoc;
global $fileLoc;
global $pid;
global $dc_identifier;
global $type;
<<<<<<< HEAD
=======
global $con;
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

		//connect to MySQL db
	// $con=mysqli_connect("hostname","username","passwd","db_name");

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
		$record_id = $ri[0];
	}

	$response = mysqli_query($con,"SELECT archive_filename FROM omeka_files WHERE item_id='$record_id'");
	while ($af = $response->fetch_array())
	{
		$archive_filename = $af[0];
	}

	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='thumbnail_constraint'");
	while ($tc = $response->fetch_array())
	{
		$thumbnail_constraint = $tc[0];
	}

	$response = mysqli_query($con,"SELECT value FROM omeka_options WHERE name='square_thumbnail_constraint'");
	while ($stc = $response->fetch_array())
	{
		$square_thumbnail_constraint = $stc[0];
	}

	$url = "$FedoraLoc/fedora/objects/$pid/datastreams/ACCESS/content";
	$outputfile = "$OmekaLoc/$fileLoc/$archive_filename";
	$cmd = "wget \"$url\" --output-document=$outputfile";
	exec($cmd);

	$cmd = "echo $OmekaLoc/$fullsizeLoc/$archive_filename $OmekaLoc/$thumbLoc/$archive_filename $OmekaLoc/$sqthumbLoc/$archive_filename | xargs -n 1 cp $outputfile && chown www-data:www-data $OmekaLoc/$fullsizeLoc/$archive_filename $OmekaLoc/$thumbLoc/$archive_filename $OmekaLoc/$sqthumbLoc/$archive_filename $outputfile";
	exec($cmd);

<<<<<<< HEAD
//	$cmd = "convert $OmekaLoc/archive/thumbnails/$archive_filename -resize $thumbnail_constraint . "x" . $thumbnail_constraint\> $archive_filename";
	$cmd = "/usr/bin/convert $OmekaLoc/$thumbLoc/$archive_filename -resize " . escapeshellarg($thumbnail_constraint.'x'.$thumbnail_constraint.'>'). " $OmekaLoc/$thumbLoc/$archive_filename";
	// echo $cmd;
	// echo "\n";
	exec($cmd);

	//	$cmd = "convert $OmekaLoc/archive/square_thumbnails/$archive_filename -thumbnail" . escapeshellarg('x' . $square_thumbnail_constraint*2) "-resize" $stcVar . "x"\< "-resize 50% -gravity center -crop 200x200+0+0 +repage $archive_filename";
//convert 43886080d2206b5a9dc71e171fd725b2.jpg -thumbnail x200*2 -resize 200*2x\< -resize 50% -gravity center -crop 200x200+0+0 +repage stuff_square.jpg
	$cmd = "/usr/bin/convert $OmekaLoc/$sqthumbLoc/$archive_filename -thumbnail " . escapeshellarg('x' . $square_thumbnail_constraint*2). " -resize " . escapeshellarg($square_thumbnail_constraint*2 . 'x<'). " -resize 50% -gravity center -crop " . escapeshellarg($square_thumbnail_constraint . 'x' . $square_thumbnail_constraint . '+0+0')." +repage $OmekaLoc/$sqthumbLoc/$archive_filename";
	// echo $cmd;
=======
	$cmd = "/usr/bin/convert $OmekaLoc/$thumbLoc/$archive_filename -resize " . escapeshellarg($thumbnail_constraint.'x'.$thumbnail_constraint.'>'). " $OmekaLoc/$thumbLoc/$archive_filename";
	exec($cmd);

	$cmd = "/usr/bin/convert $OmekaLoc/$sqthumbLoc/$archive_filename -thumbnail " . escapeshellarg('x' . $square_thumbnail_constraint*2). " -resize " . escapeshellarg($square_thumbnail_constraint*2 . 'x<'). " -resize 50% -gravity center -crop " . escapeshellarg($square_thumbnail_constraint . 'x' . $square_thumbnail_constraint . '+0+0')." +repage $OmekaLoc/$sqthumbLoc/$archive_filename";
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	exec($cmd);

}

function updateDC()
{

global $FedoraLoc;
global $OmekaLoc;
global $thumbLoc;
global $sqthumbLoc;
global $fullsizeLoc;
global $fileLoc;
global $pid;
global $dc_identifier;
global $type;
<<<<<<< HEAD

		//connect to MySQL db
	$con=mysqli_connect("localhost","omekajoe","jagwire","coleomekatest");
=======
global $con;

		//connect to MySQL db
	// $con=mysqli_connect("hostname","username","passwd","db_name");
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$response = mysqli_query($con,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
		$record_id = $ri[0];
	}

	$metaArray = array();

	//download the updated records and iterate through them
	$url = 'http://fedoratest.lib.wayne.edu/fedora/objects/'.$pid.'/datastreams/DC/content';
	$xml = simplexml_load_file($url);

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
	//Get record_type_id
	$response = mysqli_query($con,"SELECT record_type_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($rti = $response->fetch_array()) 
	{
		$record_type_id = $rti[0];
	}

	//purge rows associated with record_id--this is only to update xml not anything to do with other datastreams
<<<<<<< HEAD
	//the one below works
=======
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	mysqli_query($con,"DELETE FROM omeka_element_texts WHERE record_id=(SELECT * from (SELECT record_id FROM omeka_element_texts WHERE element_id=43 AND text='{$dc_identifier}' LIMIT 1)as t)");

	//iterate through DC elements and insert into table
	//title
	foreach ($titleArray as $title) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',50,'$record_type_id','{$title}')");
	}
<<<<<<< HEAD

=======
	//identifier
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',43,'$record_type_id','{$dc_identifier}')");
	
	//subjects
	foreach ($subjArray as $subject) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',49,'$record_type_id','{$subject}')");
	}

	//description
	foreach ($descripArray as $description) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',41,'$record_type_id','{$description}')");
	}	

	//creator
	foreach ($creatorArray as $creator) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',39,'$record_type_id','{$creator}')");
	}	

	//date
	foreach ($dateArray as $date) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',40,'$record_type_id','{$date}')");
	}	

	//type
	foreach ($typeArray as $type) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',51,'$record_type_id','{$type}')");
	}	

	//format
	foreach ($formatArray as $format) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',42,'$record_type_id','{$format}')");
	}	

	//language
	foreach ($langArray as $language) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',44,'$record_type_id','{$language}')");
	}	

	//relation
	foreach ($relatArray as $relation) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',46,'$record_type_id','{$relation}')");
	}	

	//coverage
	foreach ($coverArray as $coverage) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',38,'$record_type_id','{$coverage}')");
	}	

	//rights
	foreach ($rightsArray as $rights) {
	mysqli_query($con,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',47,'$record_type_id','{$right}')");
	}
}

// echo "this is the record id....$record_id";
if ($type == "initialize")
{
	updateImage();
	updateDC();
}

else {
	$datastreamId = $_REQUEST['datastreamId'];
	//Uncomment below line to test and comment out line above
	// $datastreamId = 'ACCESS';
}



//Datastream ID
if ($datastreamId == "ACCESS")
{
	updateImage();
}

if ($datastreamId == "DC") 
{
	updateDC();
}

<<<<<<< HEAD
?>
=======
?>
>>>>>>> 52a2ba386c7f0b429e8a4829c8b133eaa0768e76
