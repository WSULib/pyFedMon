<?php	
	
function updateID3data($eventInfo) {
	$eventInfo['mt']['mime_type'] = $eventInfo['exif']['FILE']['MimeType'];

	if ($eventInfo['mt']['mime_type'] == "image/jpeg") { $eventInfo['vid']['video']['dataformat'] = "jpg"; }
	else if ($eventInfo['mt']['mime_type'] == "image/png") { $eventInfo['vid']['video']['dataformat'] = "png"; }
	else if ($eventInfo['mt']['mime_type'] == "image/gif") { $eventInfo['vid']['video']['dataformat'] = "gif"; }
	else if ($eventInfo['mt']['mime_type'] == "image/tiff") { $eventInfo['vid']['video']['dataformat'] = "tiff"; }
	else if ($eventInfo['mt']['mime_type'] == "image/tif") { $eventInfo['vid']['video']['dataformat'] = "tif"; }
	else { $eventInfo['video']['dataformat'] == "unknown";}

	if ($eventInfo['mt']['mime_type'] == "image/jpeg") { $eventInfo['vid']['video']['lossless'] = "false"; }
	else if ($eventInfo['mt']['mime_type'] == "image/png") { $eventInfo['vid']['video']['lossless'] = "false"; }
	else if ($eventInfo['mt']['mime_type'] == "image/gif") { $eventInfo['vid']['video']['lossless'] = "false"; }
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
	// $eventInfo['vid']['video']['resolution_x'] = $eventInfo['exif']['THUMBNAIL']['XResolution'];
	// $eventInfo['vid']['video']['resolution_y'] = $eventInfo['exif']['THUMBNAIL']['YResolution'];
	$eventInfo['vid']['video']['compression_ratio'] = 0;
//	$eventInfo['vid']['video']['compression_ratio'] = $eventInfo['exif']['THUMBNAIL']['Compression'];
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
	elseif ($eventInfo['mt']['mime_type'] == "image/tiff") {
		$type_os = "TIFF image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	elseif ($eventInfo['mt']['mime_type'] == "image/tif") {
		$type_os = "Tiff image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";
	}
	else { $type_os = "unknown image data, JFIF standard 1.01, comment: File source: $eventInfo[url]";}
	mysqli_query($con, "UPDATE omeka_files SET type_os = '$type_os' WHERE item_id='{$eventInfo['record_id']}'");


	//update image metadata from exif data
	//title = original_filename from omeka_files where dc:title
		//download the updated records and iterate through them
	// $url = 'http://silo.lib.wayne.edu/fedora/objects/'.$pid.'/datastreams/DC/content';
	$xml = simplexml_load_file($eventInfo['xmlURL']);

	$titleArray = array();
	$titles = $xml->xpath('//dc:title');
	foreach ($titles as $title) {
		$titleArray[] = $title;
	}
	mysqli_query($con, "UPDATE omeka_files SET original_filename = '$title' WHERE item_id='{$eventInfo['record_id']}'");
}
?>