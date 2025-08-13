<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/


   global $msg, $username, $attachment_dir, $mailbox, $charset,
      $data_dir, $composesession, $uid_support, $sort, $names,
      $msgs, $thread_sort_messages, $allow_server_sort, $show_num,
      $compose_messages, $startMessage, $imapConnection, $archivetype,
      $SQM_INTERNAL_VERSION;

   if (!defined('SM_PATH')) define('SM_PATH','../../../');

   sqgetGlobalVar('archiveButton',$archiveButton);

   if (!isset($archiveButton))
      return;
   if (!isset($msg) || !is_array($msg))
      return;
   include_once(SM_PATH . 'functions/imap.php');
   $hashed_dir = getHashedFile($username, $data_dir, '');
   $archivefilenames = getPref($hashed_dir, $username, 'archivefilenames');
   $archiveattachments = getPref($hashed_dir, $username, 'archiveattachments');
   $archiveent = getPref($hashed_dir, $username, 'archiveent');
   $archivetype = getPref($hashed_dir, $username, 'archivetype');

   $tarray = array('zip','gzip','text','tar');
   $maxarray = array(245,90,245,90);
   $t = $tarray[$archivetype];
   unset ($tarray);
   $names = array();
   include_once(SM_PATH . 'plugins/archive_mail/includes/os.php');
   include_once(SM_PATH . "plugins/archive_mail/includes/compression/$t.php");
   if (!ini_get("safe_mode"))
      set_time_limit(0);
   $zipfile = new zipfile();
   $i = 0;
   $j = 0;
   $mbox = '';
   $c = 0;

   $format = array('separate','eml','mbox','maildir');
   while ($j < count($msg)) {
      if (isset($msg[$i])) {
         $id = $msg[$i];

         if (!isset($SQM_INTERNAL_VERSION) || $SQM_INTERNAL_VERSION[1] != 5) {
            for ($k = 0; $k < count($msgs); $k++) {
               if ($msgs[$k]['ID'] == $id) {
                  break;
               }
            }
         } else {
            $k = $id;
         }

         include (SM_PATH . 'plugins/archive_mail/includes/formats/' . $format[$archiveent] . '.php');
         $j++;
      }
      $i++;
   }

   unset($format);
   $filename = archive_replace_str($mailbox, '-');

   if ($archiveent == 2)
      $zipfile -> addFile($mbox, "$filename");

   sendheader($filename);
   header("Content-Length: " . strlen($zipfile -> file()));
   echo $zipfile -> file();
   exit;

function archive_replace_str($temp, $temp2 = ' ') {
   $temp = str_replace('&#32;',' ',$temp);
   while (strpos($temp,'  ') !== false) {
      $temp = str_replace('  ',' ',$temp);
   }
   return str_replace(array(chr(92),'/',':','>','<','|','?','*',chr(34)), $temp2, $temp);
}

function checkincrement($tempsuffix) {
   global $names;
   if (isset($names[$tempsuffix])) {
      $names[$tempsuffix] = $names[$tempsuffix] + 1;
      $increment = $names[$tempsuffix];
      $increment = ' - ' . $increment;
   } else {
      $names[$tempsuffix] = 1;
      $increment = '';
   }
   return $increment;
}

function archive_names ($archivefilenames, $email, $date, $c, $subject) {
   $tarray = array($c, $date, $date.' - '.$email, $email, $email.' - '.$date, $subject, $email." - ($subject)", 
              $date." - ($subject)", "($subject) - ". $email, "($subject) - ".$date);
   $name = $tarray[$archivefilenames];
   unset($tarray);
   return $name;
}

?>