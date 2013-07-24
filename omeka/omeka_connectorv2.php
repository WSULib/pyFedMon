<?php
include 'sensitive.php';
$dc_identifier = $_REQUEST['dc_identifier'];
$pid = $_REQUEST['pid'];
$type = $_REQUEST['type'];

//variables
// $FedoraLoc = "";
// $OmekaLocation = "";
$thumbLoc = "files/thumbnails";
$sqthumbLoc = "files/square_thumbnails";
$fullsizeLoc = "files/fullsize";
$fileLoc = "files/original";

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
global $conV2V2;

		//connect to MySQL db
	// $conV2=mysqli_connect("hostname","username","passwd","db_name");

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$response = mysqli_query($conV2V2,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($ri = $response->fetch_array()) 
	{
		$record_id = $ri[0];
	}

	$response = mysqli_query($conV2,"SELECT filename FROM omeka_files WHERE item_id='$record_id'");
	while ($af = $response->fetch_array())
	{
		$archive_filename = $af[0];
	}

	$response = mysqli_query($conV2,"SELECT value FROM omeka_options WHERE name='thumbnail_constraint'");
	while ($tc = $response->fetch_array())
	{
		$thumbnail_constraint = $tc[0];
	}

	$response = mysqli_query($conV2,"SELECT value FROM omeka_options WHERE name='square_thumbnail_constraint'");
	while ($stc = $response->fetch_array())
	{
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
global $conV2;

		//connect to MySQL db
	// $conV2=mysqli_connect("hostname","username","passwd","db_name");

	if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	$response = mysqli_query($conV2,"SELECT record_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
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
	$response = mysqli_query($conV2,"SELECT record_type_id FROM omeka_element_texts WHERE element_id=43 and text='{$dc_identifier}'");
	while ($rti = $response->fetch_array()) 
	{
		$record_type_id = $rti[0];
	}

	//purge rows associated with record_id--this is only to update xml not anything to do with other datastreams
	mysqli_query($conV2,"DELETE FROM omeka_element_texts WHERE record_id=(SELECT * from (SELECT record_id FROM omeka_element_texts WHERE element_id=43 AND text='{$dc_identifier}' LIMIT 1)as t)");

	//iterate through DC elements and insert into table
	//title
	foreach ($titleArray as $title) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',50,'$record_type_id','{$title}')");
	}
	//identifier
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',43,'$record_type_id','{$dc_identifier}')");
	
	//subjects
	foreach ($subjArray as $subject) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',49,'$record_type_id','{$subject}')");
	}

	//description
	foreach ($descripArray as $description) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',41,'$record_type_id','{$description}')");
	}	

	//creator
	foreach ($creatorArray as $creator) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',39,'$record_type_id','{$creator}')");
	}	

	//date
	foreach ($dateArray as $date) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',40,'$record_type_id','{$date}')");
	}	

	//type
	foreach ($typeArray as $type) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',51,'$record_type_id','{$type}')");
	}	

	//format
	foreach ($formatArray as $format) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',42,'$record_type_id','{$format}')");
	}	

	//language
	foreach ($langArray as $language) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',44,'$record_type_id','{$language}')");
	}	

	//relation
	foreach ($relatArray as $relation) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',46,'$record_type_id','{$relation}')");
	}	

	//coverage
	foreach ($coverArray as $coverage) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',38,'$record_type_id','{$coverage}')");
	}	

	//rights
	foreach ($rightsArray as $rights) {
	mysqli_query($conV2,"INSERT INTO omeka_element_texts (record_id, element_id, record_type_id, text) VALUES ('$record_id',47,'$record_type_id','{$right}')");
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

?>
