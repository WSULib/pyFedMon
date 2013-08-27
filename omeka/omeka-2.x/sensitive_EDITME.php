<?php

   // FILE:          sensitivie_EDITME.php
   // TITLE:         Omeka v.2/Fedora Commons Connector sensitive data
   // AUTHOR:  		 Cole Hudson, WSULS Digital Publishing Librarian
   // CREATED:       August 2013
   //
   // PURPOSE:
   // This file populates the sensitive data for omeka_connector.php
   // it does not depend on any other files
   //
   // OVERALL METHOD:
   // 1. Supplies database information and locations of Fedora and Omeka installs

   //
   // FUNCTIONS:
   //   None
   //
   // INCLUDED FILES:
   //	None
   //
   // DATA FILES:
   //   None

//Rename this file to sensitive.php
$host = "Omeka DB host usually localhost";
$username = "Omeka DB username";
$password = "Omeka DB passwd";
$omekaDB = "Add the Omeka DB name here";
$FedoraLocation = "Location of Fedora instance aka http://FQDN";
$OmekaLocation = "Path for Omeka install; should be on same server as this file; for example, /var/www/omeka_name_here";
?>