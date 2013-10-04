pyFedMon
========

Utility for monitoring Fedora logs (currently focusing on connector to Omeka front-end).

<h3>How to Install</h3>

<h4>Fedora Initial Config</h4>
<ol>
	<li>Place a copy of the code in a directory on your Fedora Commons server.</li>
	<li>Edit sensitive_EDITME.py per its instructions.</li>
	<li>Edit the altID field of every Fedora Object you wish to place into Omeka to reflect the FQDN of the omeka_connector.php as found your Omeka server.</li>
<ul>
	<li>For example: http://www.example.org/pyFedMon/omeka-2.x/omeka_connector.php</li>
</ul>
</ol>

<h4>Omeka Initial Config</h4>
<ol>
	<li>Place a copy of the code in a web-accessible directory on your Omeka server.</li>
	<li>Choose which version of Omeka you are using, and edit sensitive_EDITME.php in the relevant directory.</li>
	<li>Make your location of omeka_connector.php matches the URI found in the altID fields of the objects you are importing from Fedora Commons to Omeka.</li>
</ol>

<h3>Run CSV Creator</h3>
<ol>
	<li>Run csv_creator.py, using as an argument the PID of the collection Object that contains all the items you want to be in Omeka</li>
	<li>CSV will be generated in directory.</li>
	<li>Install and run <a href="http://omeka.org/add-ons/plugins/csv-import/">CSV Import plugin</a> using the csv that is generated from csv_creator.</li>
<ul>
	<li>Import should create an placeholder omeka items with correct pids and a placeholder image and metadata.</li>
</ul> 
</ol>

<h3>Initial Sync</h3>
<ol>
	<li>Run updateAll.py using as an argument the same PID of the relevant collection Object.</li>
	<li>Content should now be synced with Fedora Commons collection, thereby replacing placeholder image and metadata.</li>
</ol>

<h3>Launch Fedora-side Monitor</h3>
Run the below command to start the Fedora monitor.  This will automatically propagate changes in Fedora over to Omeka.
command: <em>nohup python pyFedMon.py &</em>
  - this ensures the script runs as a process in the background and does not exit between detections
  - might be worth considering adding as Cron Job to ensure always running

<h3>Update:</h3>
omeka-2.x (for Omeka 2.x installations) is stable and functional

omeka-1.x (for Omeka 1.x installations) is stable and functional
