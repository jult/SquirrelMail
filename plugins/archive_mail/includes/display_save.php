<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

      global $username, $data_dir;
      global $archivefilenames, $archiveattachments, $archivetype;
      if (isset($_POST['archivefilenames'])) {
         $archivefilenames = $_POST['archivefilenames'];
         setPref($data_dir, $username, 'archivefilenames', $archivefilenames);
      }
      if (isset($_POST['archiveattachments'])) {
         $archiveattachments = $_POST['archiveattachments'];
         setPref($data_dir, $username, 'archiveattachments', $archiveattachments);
      }
      if (isset($_POST['archivetype'])) {
         $archivetype = $_POST['archivetype'];
         setPref($data_dir, $username, 'archivetype', $archivetype);
      }
      if (isset($_POST['archiveent'])) {
         $archiveent = $_POST['archiveent'];
         setPref($data_dir, $username, 'archiveent', $archiveent);

         if ($archiveent != 2) {
            $filename = getHashedFile($username, $data_dir, '');
            $at = getPref($filename, $username, 'archivetype');
            if ($at == 2){
               $archivetype = 0;
               setPref($data_dir, $username, 'archivetype', $archivetype);
            }
         }
      }
?>