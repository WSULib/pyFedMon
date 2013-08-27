pyFedMon
========

Utility for monitoring Fedora logs (currently focusing on connector to Omeka front-end).

<h3>Launch Fedora-side Monitor</h3>
command: <em>nohup python pyFedMon.py &</em>
  - this ensures the script runs as a process in the background and does not exit between detections
  - might be worth considering adding as Cron Job to ensure always running

<h3>Update:</h3>
omeka-2.x (for Omeka 2.x installations) is stable and functional

omeka-1.x (for Omeka 1.x installations) is stable and functional
