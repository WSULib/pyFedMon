pyFedMon
========

Utility for monitoring Fedora logs (currently focusing on connector to Omeka front-end).

<h3>Launch Fedora-side Monitor</h3>
nohup python pyFedMon.py &
  - this ensures the script runs as a process in the background and does not exit between detections
  - might be worth considering adding as Cron Job to ensure always running

Update:
omeka_connectorv2.php (for Omeka 2.x installations) is stable and functional

omeka_connector.php (for Omeka 1.x installations) is a work-in-progress
