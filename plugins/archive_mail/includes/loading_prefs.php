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
      $filename = getHashedFile($username, $data_dir, '');
      $archivefilenames = getPref($filename, $username, 'archivefilenames');
      if ($archivefilenames == '') {
         $archivefilenames = 6;
         setPref($data_dir, $username, 'archivefilenames', $archivefilenames);
      }
      $archiveattachments = getPref($filename, $username, 'archiveattachments');
      if ($archiveattachments == '') {
         $archiveattachments = 1;
         setPref($data_dir, $username, 'archiveattachments', $archiveattachments);
      }
      $archivetype = getPref($filename, $username, 'archivetype');
      if ($archivetype == '') {
         $archivetype = 0;
         setPref($data_dir, $username, 'archivetype', $archivetype);
      }
      $archiveent = getPref($filename, $username, 'archiveent');
      if ($archiveent == ''){
         $archiveent = 1;
         setPref($data_dir, $username, 'archiveent', $archiveent);
      }

?>