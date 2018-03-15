<?php


   chdir('..');
   define('SM_PATH', '../');


   if (file_exists(SM_PATH . 'include/validate.php'))
   {
      include_once(SM_PATH . 'include/validate.php');
      include_once(SM_PATH . 'functions/i18n.php');
      include_once(SM_PATH . 'include/load_prefs.php');
      include_once(SM_PATH . 'functions/page_header.php');
      include_once(SM_PATH . 'functions/imap.php');
   }
   else
   {
      include_once(SM_PATH . 'src/validate.php');
      include_once(SM_PATH . 'functions/i18n.php');
      include_once(SM_PATH . 'src/load_prefs.php');
      include_once(SM_PATH . 'functions/page_header.php');
      include_once(SM_PATH . 'functions/imap.php');
   }


   global $key, $imapServerAddress, $imapPort, $onetimepad;


   global $file_manager_config, $defaultFilePerms, $chmodOK;
   include_once(SM_PATH . 'plugins/file_manager/functions.php');
   getFileManagerUserConfig();
   $baseDir = $file_manager_config[$username]['baseDir1'];
   $adminMail = $file_manager_config[$username]['adminMail'];
   


   // determine which base dir to use, if specified
   //
   if (isset($_POST['baseDir']))
   {

      if (isset($file_manager_config[$username]['baseDir' . $_POST['baseDir']])
        && !empty($file_manager_config[$username]['baseDir' . $_POST['baseDir']]))

         $baseDir = $file_manager_config[$username]['baseDir' . $_POST['baseDir']];

   }



   // make sure user is allowed access; baseDir will be empty if not
   //
   if (empty($baseDir))
      return;


   // get global variables for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4, 1)) {
      global $HTTP_GET_VARS, $HTTP_SESSION_VARS, $HTTP_COOKIE_VARS;
      $_GET = $HTTP_GET_VARS;
      $_SESSION = $HTTP_SESSION_VARS;
      $_COOKIE = $HTTP_COOKIE_VARS;
   }


   $username = $_SESSION['username'];
   $onetimepad = $_SESSION['onetimepad'];
   $key = $_COOKIE['key'];


   // extract the attachment...
   //
   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   sqimap_mailbox_select($imapConnection, $_GET['mailbox']);
   $message = sqimap_get_message($imapConnection, $_GET['passed_id'], $_GET['mailbox']);
   $entity = getEntity($message, $_GET['passed_ent_id']);
   $entityHeader = $entity->header;



   // determine target file name (store in user's base dir)
   // (SM v1.3 and above hold file name in "parameters" array in the entity header)
   //
   if (!empty($entityHeader->parameters['name']))
      $targetFile = $baseDir . (substr($baseDir, -1) == '/' ? '' : '/') . $entityHeader->parameters['name'];
   else if (!empty($entityHeader->filename))
      $targetFile = $baseDir . (substr($baseDir, -1) == '/' ? '' : '/') . $entityHeader->filename;
   else
      $targetFile = $baseDir . (substr($baseDir, -1) == '/' ? '' : '/') . 'unnamed_file_from_email_attachment';


   if (!($FILE = fopen ($targetFile, 'w')))
   {

      displayPageHeader($color, 'None');
      textdomain('file_manager');
      echo '<hr><h4>' . _("Error Occurred Attempting To Save File.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';
      textdomain('squirrelmail');
      return;

   }



   // save file locally... 
   // this is a modified rip-off of /functions/mime.php
   //
   $ent_id = $_GET['passed_ent_id'];


   // do a bit of error correction.  If we couldn't find the entity id, just guess
   // that it is the first one.  That is usually the case anyway.
   if (!$ent_id) {
      $ent_id = 1;
   }


   // saving attachments is different depending on SM versions...
   // first, 1.3 and above
   //
   if (compatibility_check_sm_version(1, 3))
   {

      global $uid_support;

      $sid = sqimap_session_id($uid_support);
      /* Don't kill the connection if the browser is over a dialup
       * and it would take over 30 seconds to download it.
       * Don´t call set_time_limit in safe mode.
       */

      if (!ini_get('safe_mode')) {
          set_time_limit(0);
      }
      if ($uid_support) {
         $sid_s = substr($sid,0,strpos($sid, ' '));
      } else {
         $sid_s = $sid;
      }

      $attachment = mime_fetch_body ($imapConnection, $_GET['passed_id'], $_GET['passed_ent_id']);
      fwrite($FILE, decodeBody($attachment, $entityHeader->encoding));

   }


   // save attachment for older SM versions
   //
   else 
   {

      $sid = sqimap_session_id();
      // Don't kill the connection if the browser is over a dialup
      // and it would take over 30 seconds to download it.

      // don t call set_time_limit in safe mode.
      if (!ini_get('safe_mode')) {
         set_time_limit(0);
      }

      fputs ($imapConnection, "$sid FETCH " . $_GET['passed_id'] . " BODY[$ent_id]\r\n");
      $cnt = 0;
      $continue = true;
      $read = fgets ($imapConnection, 4096);
      // This could be bad -- if the section has sqimap_session_id() . ' OK'
      // or similar, it will kill the download.
      while (!ereg("^".$sid." (OK|BAD|NO)(.*)$", $read, $regs)) {
         if (trim($read) == ')==') {
            $read1 = $read;
            $read = fgets ($imapConnection, 4096);
            if (ereg("^".$sid." (OK|BAD|NO)(.*)$", $read, $regs)) {
               return;
            } else {
               fwrite($FILE, decodeBody($read1, $entityHeader->encoding));
               fwrite($FILE, decodeBody($read, $entityHeader->encoding));
            }
         } else if ($cnt) {
            fwrite($FILE, decodeBody($read, $entityHeader->encoding));
         }
         $read = fgets ($imapConnection, 4096);
         $cnt++;
      }

   }

   fclose($FILE);


   // change permissions to default file_manager file permissions
   //
   if ($chmodOK) chmod($targetFile, intval($defaultFilePerms, 8));



   // go to file manager where user can see saved file
   //
   header('Location: ../../plugins/file_manager/file_manager.php');
   //header('Location: ../../src/right_main.php');


?>
