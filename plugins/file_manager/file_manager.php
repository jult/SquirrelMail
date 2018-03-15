<?php

   // NOTE - unless ownership of file in question and the containing directory
   // is apache:apache (or nobody:nobody, etc. as the case may be), most file 
   // operations will fail



   chdir('..');
   define('SM_PATH', '../');
   require_once(SM_PATH . 'functions/global.php');
   require_once(SM_PATH . 'functions/strings.php');
   include_once(SM_PATH . 'plugins/file_manager/functions.php');


   if (compatibility_check_sm_version(1, 3))
   {
      include_once(SM_PATH . 'include/validate.php');
      include_once(SM_PATH . 'functions/i18n.php');
   }
   else
   {
      include_once(SM_PATH . 'include/validate.php');
      include_once(SM_PATH . 'functions/i18n.php');
   }


   // get global variables for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4, 1)) {
      global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_SERVER_VARS, 
             $HTTP_POST_FILES, $HTTP_SESSION_VARS;
      $_POST = $HTTP_POST_VARS;
      $_GET = $HTTP_GET_VARS;
      $_SERVER = $HTTP_SERVER_VARS;
      $_FILES = $HTTP_POST_FILES;
      $_SESSION = $HTTP_SESSION_VARS;
   }


   if (!isset($_GET['downloadFile']))
   {

      include_once(SM_PATH . 'functions/page_header.php');
      include_once(SM_PATH . 'functions/imap.php');
      if (compatibility_check_sm_version(1, 3))
         include_once(SM_PATH . 'include/load_prefs.php');
      else
         include_once(SM_PATH . 'src/load_prefs.php');

   }


   getFileManagerUserConfig();



   if (!isset($_GET['downloadFile']) && !isset($_POST['attachmentFile']))
      displayPageHeader($color, 'None');


   bindtextdomain('file_manager', '../plugins/file_manager/locale');
   textdomain('file_manager');
   $username = $_SESSION['username'];

   compatibility_sqextractGlobalVar('session');

   if ( isset($_SESSION['composesession']) ) {
      $composesession = $_SESSION['composesession'];
   }
   if ( isset($_SESSION['attachments']) ) {
      $attachments = $_SESSION['attachments'];
   }


   global $file_manager_config, $defaultFilePerms, $defaultFolderPerms,
          $systemUmask, $symlinkColor, $fileEditStyle, $fileLinkColor, 
          $chmodOK, $color, $newlineChar, $globalAllowEditBinary,
          $antiVirusCommand, $antiVirusCommandFoundNoVirusReturnCode,
          $checkUploadsForVirii, $showAntiVirusErrorDetails,
          $showGoodAntiVirusResults,
          $sharedDirectoryQuotas, $viewFilesBehavior, $data_dir;
   $quota = $file_manager_config[$username]['quota'];
   $adminMail = $file_manager_config[$username]['adminMail'];
   $allowLinks = $file_manager_config[$username]['allowLinks'];
   $allowChmod = $file_manager_config[$username]['allowChmod'];
   $userIsAllowedToOpenBinary = $file_manager_config[$username]['allowEditBinary'] && $globalAllowEditBinary; 
   umask(intval($systemUmask, 8));


   // no longer used...
   // store the name of this file - don't want to delete ourselves!
   //
   //preg_match('/(.*\/|)(.*)/', $_SERVER['PHP_SELF'], $matches);
   //$thisFile = strtoupper($matches[2]);



   // determine which base dir to use
   //
   $baseDirFromRequest = "";
   if (isset($_POST['baseDir']))
   {
      $baseDirFromRequest = $_POST['baseDir'];
   }
   else if (isset($_GET['baseDir']) && (isset($_GET['downloadFile'])))
   {
      $baseDirFromRequest = $_GET['baseDir'];
   }

   $baseDirNo = "1";
   if (!empty($baseDirFromRequest))
   {

      if (isset($file_manager_config[$username]['baseDir' . $baseDirFromRequest])
        && !empty($file_manager_config[$username]['baseDir' . $baseDirFromRequest]))
      {
         $baseDirNo = $baseDirFromRequest;
      }

   }
   $baseDir = $file_manager_config[$username]['baseDir' . $baseDirNo];


   // compatibility with old config file versions...
   //
   if (empty($baseDir) && isset($file_manager_config[$username]['baseDir']) 
      && !empty($file_manager_config[$username]['baseDir']))

      $baseDir = $file_manager_config[$username]['baseDir'];



   // get current working directory, if specified
   //
   if (isset($_POST['cwd']))
   {
      $cwd = $baseDir . '/' . $_POST['cwd'];
      $cwd = str_replace('//', '/', $cwd);
      $cwd = str_replace('//', '/', $cwd);
      
      // do not allow user to go above their base directory
      //
      if (!strstr($cwd . '/', $baseDir))
         $cwd = $baseDir;

   }
   else if (isset($_GET['cwd']) && (isset($_GET['downloadFile'])))
   {
      $cwd = $baseDir . '/' . $_GET['cwd'];
      $cwd = str_replace('//', '/', $cwd);
      $cwd = str_replace('//', '/', $cwd);

      // do not allow user to go above their base directory
      //
      if (!strstr($cwd . '/', $baseDir))
         $cwd = $baseDir;

   }
   else
      $cwd = $baseDir;

   if (strlen($cwd) > 1 && substr($cwd, strlen($cwd) - 1) == '/')
      $cwd = substr($cwd, 0, strlen($cwd) - 1);


   // shouldn't need it, but just for the paranoid, keep looping 
   // till we're rid of evil double dots...
   //
   while (strstr($cwd, '..'))
      $cwd = str_replace('..', '', $cwd);


   // don't display full path, only what user is allowed to access
   //
   $temp = str_replace('/', '\/', $baseDir);
   preg_match("/$temp(.*)/", $cwd, $matches);
   $displayCWD = '/' . $matches[1];
   $displayCWD = str_replace('//', '/', $displayCWD);


   // figure out parent directory path
   //
   if (preg_match('/(.+)\/(.*)/', $cwd, $matches) == 0)
      $parentDir = '/';
   else 
      $parentDir = $matches[1];


   // don't display full path for parent, either
   //
   $displayParentDir = '/';
   if (preg_match("/$temp(.*)/", $parentDir, $matches) > 0)
      $displayParentDir .= $matches[1];
   $displayParentDir = str_replace('//', '/', $displayParentDir);


   // load all base dirs
   //
   $userBaseDirs = array();
   foreach ($file_manager_config[$username] as $configParam => $path)
   {

      if (strstr($configParam, 'baseDir'))
      {

         $displayPath = substr($path, strrpos($path, '/') + 1);
         if (empty($displayPath))
            $displayPath = "/";

         $userBaseDirs[substr($configParam, strpos($configParam, 'baseDir') + strlen('baseDir'))] 
            = $displayPath;

      }

   }


   // check for quota override...
   //
   $inSharedDir = 0;
   foreach (array_keys($sharedDirectoryQuotas) as $sharedDir)
   {
      if (strstr($cwd, $sharedDir)) 
      {
         $quota = $sharedDirectoryQuotas[$sharedDir];
         $inSharedDir = $sharedDir;
      }
   }



// debug stuff...  
// we aren't using getcwd ...  echo "<hr>getcwd() = " . getcwd() . 
// debug stuff...  echo "<hr>constructed cwd = $cwd<br>parent dir = $parentDir<hr>";
// debug stuff...  echo "<hr>umask = 0" . base_convert(umask(), 10, 8) . "<hr>";
   // ############################################################
   // check for file download request
   //
   if (isset($_GET['downloadFile']) && isset($_GET['downloadFileName']))
   {

      $downloadFile = $cwd . '/' . $_GET['downloadFileName'];
      $downloadFile = sanitizeFileName($downloadFile);
      $absolute_dl = $_GET['absolute_dl'];
      if (empty($absolute_dl)) $absolute_dl = 'false';

      include_once(SM_PATH . 'plugins/file_manager/download.php');

      doDownload($_GET['downloadFileName'], $downloadFile, $absolute_dl);

   }


   // ############################################################
   // check for send file as attachment request
   //
   if (isset($_POST['attachmentFile']) && isset($_POST['attachmentFileName']))
   {

      $attachmentFile = $cwd . '/' . $_POST['attachmentFileName'];
      $attachmentFile = sanitizeFileName($attachmentFile);


      //
      // this bit of code is modified from /src/compose.php...
      //


      // generate random file name for attachment and copy 
      // the file to attachments directory
      //
      $hashed_attachment_dir = getHashedDir($username, $attachment_dir);
      $localfilename = GenerateRandomString(32, '', 7);
      $full_localfilename = "$hashed_attachment_dir/$localfilename";
      while (file_exists($full_localfilename)) 
      {
         $localfilename = GenerateRandomString(32, '', 7);
         $full_localfilename = "$hashed_attachment_dir/$localfilename";
      }
      if (!@copy($attachmentFile, $full_localfilename))
      {

         bindtextdomain('squirrelmail', '../locale');
         textdomain('squirrelmail');
         displayPageHeader($color, 'None');
         bindtextdomain('file_manager', '../plugins/file_manager/locale');
         textdomain('file_manager');
         echo '<hr><h4>' . _("Error Occurred Attempting To Send File As Attachment.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }


      // put file attachment info together for compose form
      // and go to compose screen
      //
      else
      {

         include_once(SM_PATH . 'plugins/file_manager/download.php');

         // determine mime type
         //
         list($type0, $type1) = explode('/', getMimeType($_POST['attachmentFileName']));
         if (empty($type0)) $type0 = "application";
         if (empty($type1)) $type1 = "octet-stream";


         // make sure we have a compose session set up
         //
         if (!isset($composesession)) 
         {
            $composesession = 0;
            compatibility_sqsession_register($composesession, 'composesession');
         }

         if (!isset($session)) 
         {
            compatibility_sqsession_unregister('composesession');
   
            $session = "$composesession" +1;
            $composesession = $session;

            compatibility_sqsession_register($composesession, 'composesession');
         }


         // constructing the message and its attachment is different 
         // depending on SM versions... first 1.3 and greater:
         //
         if (compatibility_check_sm_version(1, 3))
         {

            compatibility_sqextractGlobalVar('compose_messages');

            if (!isset($compose_messages)) {
              $compose_messages = array();
            }

            if (!isset($compose_messages[$session]) || ($compose_messages[$session] == NULL)) {
            /* if (!array_key_exists($session, $compose_messages)) {  /* We can only do this in PHP >= 4.1 */
              $composeMessage = new Message();
              $rfc822_header = new Rfc822Header();
              $composeMessage->rfc822_header = $rfc822_header;
              $composeMessage->reply_rfc822_header = '';
              $compose_messages[$session] = $composeMessage;
              sqsession_register($compose_messages,'compose_messages');
            } else {
              $composeMessage=$compose_messages[$session];
            }


            $type = $type0 . '/' . $type1;
            $composeMessage->initAttachment($type, $_POST['attachmentFileName'], $full_localfilename);
            $compose_messages[$session] = $composeMessage;
            compatibility_sqsession_register($compose_messages , 'compose_messages');

         }


         // add attachment for older SM versions
         //
         else
         {

            $newAttachment['localfilename'] = $localfilename;
            $newAttachment['remotefilename'] = $_POST['attachmentFileName'];
            $newAttachment['type'] = $type0 . '/' . $type1;
            $newAttachment['session'] = $session;
   

            if ($newAttachment['type'] == "") 
               $newAttachment['type'] = 'application/octet-stream';


            compatibility_sqsession_unregister('attachments');


            $attachments[] = $newAttachment;


            compatibility_sqsession_register($attachments, 'attachments');

         }



         // Note - the following can only be used with the 
         // replacement compose.php included with File Manager.
         // This is the only way to make attachments work from
         // File Manager with register_globals=Off for SM versions
         // 1.2.x
         //
         // This doesn't work with register_globals = Off because compose.php 
         // looks for "attachedmessages" as a GET variable... so instead, we
         // replace compose.php with a hack that skips the clearing of the
         // attachments array
         //
         // Get ready to go to compose page - because there are possibly
         // line feeds in the contents of the body text of the email
         // the user may have already started, we can't pass these 
         // parameters back using the GET method, thus we cannot use
         // the header("Location...") function.  Instead, we have to return an
         // intermediate HTML page here that just has a form on it, nothing
         // more - as soon as it loads, it redirects back to the compose page.
         //
         echo "\n";
         echo "\n";

         // Several browsers couldn't figure out what sendAttachmentForm was before it was declared...
         // echo '<html><body bgcolor="' . $color[4] . '" onLoad="sendAttachmentForm.submit()">'

//echo '<html><body bgcolor="' . $color[4] . '">';
         echo '<html><body bgcolor="' . $color[4] . '" onLoad="document.forms[0].submit()">'
            . '<form action="../../src/compose.php" name="sendAttachmentForm" method="POST">';

         echo '<input type="hidden" name="file_manager_attachment" value="1">';
         //doesn't work with register_globals=off  echo '<input type="hidden" name="attachedmessages" value="true">';



         // if user was already writing an email, the contents
         // of that mail should be in $_POST....
         //
         if (isset($_POST['body']))
            echo '<input type="hidden" name="body" value="' . htmlspecialchars($_POST['body']) . '">';
         if (isset($_POST['mailprio']))
            echo '<input type="hidden" name="mailprio" value="' . $_POST['mailprio'] . '">';
         if (isset($_POST['variable_sent_folder']))
            echo '<input type="hidden" name="variable_sent_folder" value="' . $_POST['variable_sent_folder'] . '">';
         if (isset($_POST['identity']))
            echo '<input type="hidden" name="identity" value="' . $_POST['identity'] . '">';
         if (isset($_POST['delete_draft']))
            echo '<input type="hidden" name="delete_draft" value="' . $_POST['delete_draft'] . '">';
         if (isset($_POST['session']))
            echo '<input type="hidden" name="session" value="' . $_POST['session'] . '">';
         else
            echo '<input type="hidden" name="session" value="' . $composesession . '">';
         if (isset($_POST['action']))
            echo '<input type="hidden" name="action" value="' . $_POST['action'] . '">';
//  this keeps the reply id, but you lose any text that the user had been
//  typing in the reply up to now.  but by not forwarding this id back to
//  compose.php, this message is not flagged as "sent reply".  i have hacked
//  compose.php to take the body given here instead of starting from scratch.
         if (isset($_POST['reply_id']))
            echo '<input type="hidden" name="reply_id" value="' . $_POST['reply_id'] . '">';
         if (isset($_POST['forward_id']))
            echo '<input type="hidden" name="forward_id" value="' . $_POST['forward_id'] . '">';
         if (isset($_POST['request_mdn']))
            echo '<input type="hidden" name="request_mdn" value="' . $_POST['request_mdn'] . '">';
         if (isset($_POST['request_dr']))
            echo '<input type="hidden" name="request_dr" value="' . $_POST['request_dr'] . '">';
         if (isset($_POST['fm_send_to']))
            echo '<input type="hidden" name="send_to" value="' . htmlspecialchars(str_replace('"', '\'', $_POST['fm_send_to'])) . '">';
         if (isset($_POST['fm_send_to_cc'])) 
            echo '<input type="hidden" name="send_to_cc" value="' . htmlspecialchars(str_replace('"', '\'', $_POST['fm_send_to_cc'])) . '">';
         if (isset($_POST['fm_send_to_bcc']))
            echo '<input type="hidden" name="send_to_bcc" value="' . htmlspecialchars(str_replace('"', '\'', $_POST['fm_send_to_bcc'])) . '">';
         if (isset($_POST['subject']))
            echo '<input type="hidden" name="subject" value="' . htmlspecialchars(str_replace('"', '\'', $_POST['subject'])) . '">';
         if (isset($_POST['passed_id']))
            echo '<input type="hidden" name="passed_id" value="' . $_POST['passed_id'] . '">';
         if (isset($_POST['mailbox']))
            echo '<input type="hidden" name="mailbox" value="' . $_POST['mailbox'] . '">';
         else
            echo '<input type="hidden" name="mailbox" value="INBOX">';


         echo '</form></body></html>';



         // This is in my opinion the best way to do this, but
         // it won't work because once we return to the compose
         // page, all relative links will try to construct their
         // base href from the file_manager directory
         //
         // "attachedmessages" is a hack, because the compose page
         // will wipe the attachments array if it thinks this is a
         // new message - it doesn't expect someone else to start
         // the message as we are doing here...
         // TODO: 1.3.x no longer uses "attachedmessages" -- need
         // to fix... but it looks like it doesn't clear the attachments
         // array for new messages, either - need to double check, but
         // keep my fingers crossed
         // 
         /*
         $attachedmessages = "true";
         include_once(SM_PATH . 'src/compose.php');
         */



         // for posterity, here is the way we used to do it, but newlines get
         // scrunched in the body text...
         //
         /*
         $composeURL = 'Location: ../../src/compose.php?mailbox=INBOX&startMessage=1&attachedmessages=true&session='
            . $composesession;


         // if user was already writing an email, the contents
         // of that mail should be in $_POST....
         //
         if (isset($_POST['mailprio']))
            $composeURL .= '&mailprio=' . $_POST['mailprio'];
         if (isset($_POST['variable_sent_folder']))
            $composeURL .= '&variable_sent_folder=' . $_POST['variable_sent_folder'];
         if (isset($_POST['identity']))
            $composeURL .= '&identity=' . $_POST['identity'];
         if (isset($_POST['delete_draft']))
            $composeURL .= '&delete_draft=' . $_POST['delete_draft'];
         if (isset($_POST['session']))
            $composeURL .= '&session=' . $_POST['session'];
         if (isset($_POST['action']))
            $composeURL .= '&action=' . $_POST['action'];
         if (isset($_POST['reply_id']))
            $composeURL .= '&reply_id=' . $_POST['reply_id'];
         if (isset($_POST['forward_id']))
            $composeURL .= '&forward_id=' . $_POST['forward_id'];
         if (isset($_POST['request_mdn']))
            $composeURL .= '&request_mdn=' . $_POST['request_mdn'];
         if (isset($_POST['request_dr']))
            $composeURL .= '&request_dr=' . $_POST['request_dr'];
         if (isset($_POST['send_to']))
            $composeURL .= '&send_to=' . $_POST['send_to'];
         if (isset($_POST['send_to_cc']))
            $composeURL .= '&send_to_cc=' . $_POST['send_to_cc'];
         if (isset($_POST['send_to_bcc']))
            $composeURL .= '&send_to_bcc=' . $_POST['send_to_bcc'];
         if (isset($_POST['subject']))
            $composeURL .= '&subject=' . $_POST['subject'];
         if (isset($_POST['passed_id']))
            $composeURL .= '&passed_id=' . $_POST['passed_id'];
         if (isset($_POST['mailbox']))
            $composeURL .= '&mailbox=' . $_POST['mailbox'];
         if (isset($_POST['body']))
            $composeURL .= '&body=' . preg_replace("/(\015\012)|(\015)|(\012)/", " ", $_POST['body']);



         header($composeURL);
         */


         exit;

 
      }

   }


// ###################################################################
?>

   <script type="text/javascript" language="javascript">
   <!--

      function isPosInteger(inputValue)
      {

         stringValue = inputValue.toString();

         for (i = 0; i < stringValue.length; i++)
         {

            aChar = stringValue.charAt(i);
            if (aChar < "0" || aChar > "9")
               return false;

         }

         return true;

      }



      function verifyUpload()
      {

         fileList = new Array();
         dirList = new Array();

         // only need to do any verification if overwrite is not turned on
         //
         if (document.uploadForm.allowOverwrite.value == "no")
         {
         
            // build file and dir lists (from current directory listing)
            //
            index = 0;
            fileList[0] = "";
            for (i = 1; i < document.deleteForm.fileList.value.length; i++)
            {
               if (document.deleteForm.fileList.value.substring(i, i+1) == ":")
               {
                  index++;
                  fileList[index] = "";
                  continue;
               }
               fileList[index] += document.deleteForm.fileList.value.substring(i, i+1);
            }

            index = 0;
            dirList[0] = "";
            for (i = 1; i < document.deleteForm.dirList.value.length; i++)
            {
               if (document.deleteForm.dirList.value.substring(i, i+1) == ":")
               {
                  index++;
                  dirList[index] = "";
                  continue;
               }
               dirList[index] += document.deleteForm.dirList.value.substring(i, i+1);
            }
            
         }


         var message = "";
         var haveAtLeastOneUploadFile = false;
         var setAllowOverwriteToTrue = false;
         

         // look for upload file names in list of files for
         // this directory and alert for overwrite if the
         // that checkbox is not checked
         //
         for (j = 1; j <= uploadForm.numberOfFiles.value; j++)
         {
         
            verifyFileField = eval("uploadForm.uploadFile" + j);
            verifyFileName = verifyFileField.value;
            displayFileName = verifyFileField.value;
            
            if (verifyFileName == "") continue;
            
            haveAtLeastOneUploadFile = true;
            
            
            // only need to do any verification if overwrite is not turned on
            //
            if (document.uploadForm.allowOverwrite.value == "no")
            {
            
               for (i = 0; i < dirList.length; i++)
               {
                  if (dirList[i] != ""
                     && (verifyFileName.length
                         - verifyFileName.indexOf(dirList[i]) == dirList[i].length))
                  {
                     message = dirList[i] + "\n\n<?php echo _("You can't upload a file that has the same name as a folder.  Try anyway?"); ?>";
                  }
               }

               if (message == "") for (i = 0; i < fileList.length; i++)
               {
                  if (fileList[i] != "" 
                     && (verifyFileName.length
                         - verifyFileName.indexOf(fileList[i]) == fileList[i].length))
                  {
                     message = fileList[i] + "\n\n<?php echo _("It looks like you want to upload a file that already exists in this directory.  Do you want to overwrite it?"); ?>";
                  }
               }

               // if we caught an error, warn user
               //
               if (message != "")
               {
                  if (confirm(message))
                  {
                     setAllowOverwriteToTrue = true;
                     message = "";
                  }
                  else
                     return false;
               }
               
            }
            
         }


         if (!haveAtLeastOneUploadFile)
         {
            alert('<?php echo _("Click the browse button to select a file to upload."); ?>');
            return false;
         }


         if (setAllowOverwriteToTrue)
            document.uploadForm.allowOverwrite.value = "ok";
         

         return true;

      }



      function getRenameFile()
      {

         isOneChecked = false;

         for (i = 0; i < document.deleteForm.elements.length; i++)
            if (document.deleteForm.elements[i].type == 'checkbox'
                && document.deleteForm.elements[i].checked)
            {
               isOneChecked = true;
               document.renameFileForm.oldFileName.value = document.deleteForm.elements[i].value;
               break;
            }


         if (!isOneChecked)
         {

            alert('<?php echo _("Select a file or folder to move or rename."); ?>');
            return false;

         }


         newFileName = prompt("<?php echo _("Please enter new file name (with optional folder where file will be moved):"); ?>", "");
         newFileName = trim(newFileName);
         if (newFileName != null && newFileName != "")
         {
//TODO: check before form submission if the new file name matches any of the 
//      files/folders in the current listing and stop it here (reuse code above)
            document.renameFileForm.newFileName.value = newFileName;
            return true;
         }

         return false;

      }


      function getNewFile()
      {

         newFileName = prompt("<?php echo _("Please enter new file name:"); ?>", "");
         newFileName = trim(newFileName);
         if (newFileName != null && newFileName != "")
         {
            document.newFileForm.newFileName.value = newFileName;
            return true;
         }

         return false;
 
      }


      function getNewFolder()
      {

         newFolderName = prompt("<?php echo _("Please enter new folder name:"); ?>", "");
         newFolderName = trim(newFolderName);
         if (newFolderName != null && newFolderName != "")
         {
            document.newFolderForm.newFolderName.value = newFolderName;
            return true;
         }

         return false;
 
      }


      function getChmodFile()
      {

         for (i = 0; i < document.deleteForm.elements.length; i++)
            if (document.deleteForm.elements[i].type == 'checkbox'
                && document.deleteForm.elements[i].checked)
            {

//if (document.deleteForm.elements[i].name.indexOf("File") == -1)
//message = "You cannot download a folder.  Please select a file to download.";

               chmodMode = prompt("<?php echo _("Please enter desired permission:"); ?>", "644");

               chmodMode = trim(chmodMode);
               if (chmodMode == "") return false;

               //document.chmodForm.chmodFileName.value = document.deleteForm.elements[i].value;
               document.deleteForm.chmodMode.value = chmodMode;
               return true;

            }

         alert("<?php echo _("Select a file to change permissions on."); ?>");
         return false;

      }


      // no longer used...
      //
      function setViewFile()
      {

         message = "<?php echo _("Select a file to view."); ?>";

         for (i = 0; i < document.deleteForm.elements.length; i++)
            if (document.deleteForm.elements[i].type == 'checkbox'
                && document.deleteForm.elements[i].checked)
            {

               if (document.deleteForm.elements[i].name.indexOf("File") == -1)
                  message = '<?php echo _("You cannot edit folders.  Please select a file to edit."); ?>';

               else
               {
                  document.viewForm.viewFile.value = document.deleteForm.elements[i].value;
                  return true;
               }

            }

         alert(message);
         return false;
 
      }


      function setCopyFile()
      {
 

         isOneChecked = false;

         for (i = 0; i < document.deleteForm.elements.length; i++)
            if (document.deleteForm.elements[i].type == 'checkbox'
                && document.deleteForm.elements[i].checked)
            {
               isOneChecked = true;
               document.copyForm.copyFile.value = document.deleteForm.elements[i].value;
               break;
            }


         if (!isOneChecked)
         {

            alert("<?php echo _("Select a file or folder to copy."); ?>");
            return false;

         }


         newFileName = prompt("<?php echo _("Please enter target name (with optional folder where file will be moved):"); ?>", "");
         newFileName = trim(newFileName);
         if (newFileName != null && newFileName != "")
         {
//TODO: check before form submission if the new file name matches any of the 
//      files/folders in the current listing and stop it here (reuse code above)
            document.copyForm.newFileName.value = newFileName;
            return true;
         }

         return false;

      }


      function verifyDelete()
      {

         isOneChecked = false; 

         for (i = 0; i < document.deleteForm.elements.length; i++) 
            if (document.deleteForm.elements[i].type == 'checkbox' 
                && document.deleteForm.elements[i].checked) 

               isOneChecked = true; 


         if (!isOneChecked) 
         { 

            alert('<?php echo _("Select a file to delete."); ?>'); 
            return false; 

         } 
         else 
            return confirm("<?php echo _("You sure?  There's no turning back once you delete any files or folders!"); ?>");

      }

   // take off whitespace from front and tail of string, also removes all newlines
   function trim(stringToTrim)
   {
      for (i = 0; i < stringToTrim.length; i++)
      {
         while (stringToTrim.charAt(0) == ' '
                 || stringToTrim.charAt(0) == '\n'
                 || stringToTrim.charAt(0) == '\t'
                 || stringToTrim.charAt(0) == '\f'
                 || stringToTrim.charAt(0) == '\r')
                 stringToTrim = stringToTrim.substring(1, stringToTrim.length);

         while (stringToTrim.charAt(stringToTrim.length - 1) == ' '
                 || stringToTrim.charAt(stringToTrim.length - 1) == '\n'
                 || stringToTrim.charAt(stringToTrim.length - 1) == '\t'
                 || stringToTrim.charAt(stringToTrim.length - 1) == '\f'
                 || stringToTrim.charAt(stringToTrim.length - 1) == '\r')
                 stringToTrim = stringToTrim.substring(0, stringToTrim.length - 1);
      }

      return stringToTrim;

   }


   // -->
   </script>


   <br>
   <table align=center width="100%" border="0" cellpadding="2" cellspacing="0">
      <tr>
         <td align=center bgcolor="<?php echo $color[0]; ?>" colspan="2">
            <b><?php echo _("File Manager"); ?></b>
         </td>
      </tr>
   </table>


<?php // ###################################################################


   // ############################################################
   // check for view link behavior change
   //
   if (isset($_POST['viewFileBehavior']))
   {

      setPref($data_dir, $username, 'file_manager_view_files_behavior', $_POST['viewFileBehavior']);

   }



   // ############################################################
   // check for new file request
   //
   if (isset($_POST['newFile']) && isset($_POST['newFileName']))
   {

      $newFile = $cwd . '/' . $_POST['newFileName'];
      $newFile = sanitizeFileName($newFile);


      if (touch($newFile) && (!$chmodOK || ($chmodOK && chmod($newFile, intval($defaultFilePerms, 8)) )))
      {

         echo '<h4>' . _("New File Successfully Created") . '</h4><hr>';

      }
      else
      {

         echo '<h4>' . _("Error Occurred Attempting To Create File.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }
      
   }


   // ############################################################
   // check for chmod request
   //
   if ($chmodOK)
   if (isset($_POST['delete']) && $_POST['chmod'] == '1'
     && isset($_POST['chmodMode']) && $allowChmod)
   {

      $chmodMode = $_POST['chmodMode'];


      $success = 0;


      if (substr($chmodMode, 0, 2) != '-R')
         if (substr($chmodMode, 0, 1) != '0') $chmodMode = '0' . $chmodMode;


      if (!$success && (is_numeric($chmodMode) || substr($chmodMode, 0, 2) == '-R')) 
      {

         // loop thru, chmod'ing each file
         //
         $numberFilesChmoded = 0;
         $erroneousFiles = array();
         foreach (array_keys($_POST) as $fileKey)
         {
         
            if (strstr($fileKey, 'deleteFile') || strstr($fileKey, 'deleteFolder'))
            {

               $filename = $cwd . '/' . $_POST[$fileKey];
               $filename = sanitizeFileName($filename);

               if (substr($chmodMode, 0, 2) == '-R')

                  // a backdoor for doing chmod -R using a system call
                  // 
                  // when prompted for the mode, you must enter syntax
                  // similar to   -R 0644
                  // 
                  // uncomment at your own risk!
                  // (and comment out the semicolon below)
                  //
                  //system("chmod $chmodMode $filename");
                  ;

               else
               {
                  if (chmod($filename, intval($chmodMode, 8)))
                     $numberFilesChmoded++;
                  else
                     array_push($erroneousFiles, substr($filename, strrpos($filename, '/') + 1));
               }

            }

         }

         if (sizeof($erroneousFiles) > 0)
         {

            echo '<h4>' . _("Error Occurred Attempting To Change Permissions On:");
            foreach (array_values($erroneousFiles) as $file)
               echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;' . $file;
            echo '<br><br><small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

         }

         else if ($numberFilesChmoded > 0)
            $success = 1;

      }

      // bad formatting for the permissions string
      //
// TODO: check this BEFORE form submission in the javascript!!
      else
      {

         echo '<h4>' . _("Error - Bad Syntax For CHMOD Permissions.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';
         $success = 0;

      }


      if ($success)
      {

         echo '<h4>' . _("Permissions Successfully Changed") . '</h4><hr>';

      }
      else
      {

         //echo '<h4>' . _("Error Occurred Attempting To Change File/Folder Permissions.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }
      
   }


   // ############################################################
   // check for new folder request
   //
   if (isset($_POST['newFolder']) && isset($_POST['newFolderName']))
   {

      $newFolder = $cwd . '/' . $_POST['newFolderName'];
      $newFolder = sanitizeFileName($newFolder);


      if (mkdir($newFolder, intval($defaultFolderPerms, 8)))
      {

         echo '<h4>' . _("New Folder Successfully Created") . '</h4><hr>';

      }
      else
      {

         echo '<h4>' . _("Error Occurred Attempting To Create Folder.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }
      
   }


   // ############################################################
   // check for rename file/folder request
   //
   if (isset($_POST['renameFile']) && isset($_POST['newFileName']) && isset($_POST['oldFileName']))
   {

      $newFilename = $cwd . '/' . $_POST['newFileName'];
      $newFilename = str_replace('//', '/', $newFilename);
      $oldFilename = $cwd . '/' . $_POST['oldFileName'];
      $oldFilename = sanitizeFileName($oldFilename);

      // a little tricky, but here's what we'll do:  allow as many
      // pairs of .. as there are / in the current working directory
      // (unless cwd is just "/", then no double dots allowed)
      // (only for the new file name) 
      //
      $numberOfSlashes = substr_count($displayCWD, '/');
      if ($displayCWD == '/')
         $numberOfSlashes = 0;
      $numberOfDoubleDots = substr_count($newFilename, '..');
      for ($i = $numberOfSlashes; $i < $numberOfDoubleDots; $i++)
      {
         $newFilename = preg_replace('/\.\./', '//', $newFilename, 1);
      }
 

      if (is_file($newFilename))
      {

         echo '<h4>' . _("Cannot Rename Or Move File Or Folder - Destination Already Exists") . '</h4>';

      }


      else if ((is_dir($newFilename) && rename($oldFilename, $newFilename . '/' 
                     . substr($oldFilename, strrpos($oldFilename, '/') + 1)))
           || (!is_dir($newFilename) && rename($oldFilename, $newFilename)))
      {

         echo '<h4>' . _("Success!") . '</h4><hr>';

      }


      else
      {

         echo '<h4>' . _("Error Occurred Attempting To Move Or Rename File Or Folder.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }
      
   }


   // ############################################################
   // check for file copy request
   //
   if (isset($_POST['copy']) && isset($_POST['copyFile']) && isset($_POST['newFileName']))
   {

      $newFilename = $cwd . '/' . $_POST['newFileName'];
      $newFilename = str_replace('//', '/', $newFilename);
      $oldFilename = $cwd . '/' . $_POST['copyFile'];
      $oldFilename = sanitizeFileName($oldFilename);

      // a little tricky, but here's what we'll do:  allow as many
      // pairs of .. as there are / in the current working directory
      // (unless cwd is just "/", then no double dots allowed)
      // (only for the new file name)
      //
      $numberOfSlashes = substr_count($displayCWD, '/');
      if ($displayCWD == '/')
         $numberOfSlashes = 0;
      $numberOfDoubleDots = substr_count($newFilename, '..');
      for ($i = $numberOfSlashes; $i < $numberOfDoubleDots; $i++)
      {
         $newFilename = preg_replace('/\.\./', '//', $newFilename, 1);
      }


      if (is_dir($newFilename) || is_file($newFilename))
      {

         echo '<h4>' . _("Cannot Copy File Or Folder - Destination Already Exists") . '</h4>';

      }


      else if ((is_dir($oldFilename) && dir_copy($oldFilename, $newFilename, $defaultFolderPerms)) 
            || (is_file($oldFilename) && copy($oldFilename, $newFilename) 
                && (!$chmodOK || ($chmodOK && chmod($newFilename, intval($defaultFilePerms, 8))))))
      {

         echo '<h4>' . _("Success!") . '</h4><hr>';

      }


      else
      {

         echo '<h4>' . _("Error Occurred Attempting To Copy File Or Folder.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }

   }


   // ############################################################
   // check for save file request
   //
   if (isset($_POST['save']) && $_POST['save'] == '1')
   {

      $file = $cwd . '/' . $_POST['editFile'];
      $file = sanitizeFileName($file);


      // convert to proper newlines and preserve html special chars
      //
      $_POST['fileContents'] = preg_replace("/(\015\012)|(\015)|(\012)/", $newlineChar, $_POST['fileContents']);
      // this is too strong.. we lose things like + signs...  $_POST['fileContents'] = preg_replace("/(\015\012)|(\015)|(\012)/", $newlineChar, urldecode($_POST['fileContents']));


      if (is_writable($file) && ($FILE = fopen ($file, "w"))
         && (fwrite($FILE, $_POST['fileContents'])))
      {

         echo '<h4>' . _("File Saved") . '</h4><hr>';

      }
      else
      {

         echo '<h4>' . _("Error Occurred Attempting To Save File.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }

   }


   // ############################################################
   // check for edit request
   //
   if ((isset($_POST['view']) && isset($_POST['viewFile']))
    || (isset($_POST['continueEditing']) && $_POST['continueEditing'] == '1'))
   {

      $file = $cwd . '/' . $_POST['viewFile'];
      $file = sanitizeFileName($file);

      $openBinaryOK = 0;
      if (isset($_POST['openBinary'])) 
         $openBinaryOK = $_POST['openBinary'];
      if (isset($_POST['continueEditing']) && $_POST['continueEditing'] == '1') 
         $openBinaryOK = 1;

      $isBinary = 0;


      if (!is_dir($file) && is_readable($file) && ($FILE = fopen ($file, "r")))
      {

         // check if file is binary or not (unless $openBinaryOK is turned on)
         //
         if (!$openBinaryOK)
         {

            $test = fread($FILE, 1024);
            fclose($FILE);
            for ($i = 0; $i < strlen($test); $i++)
            {

               // test for any characters in the first 1KB of the 
               // file that have an ASCII value greater than 127
               // (which excludes most non-English characters, 
               // unfortunately - if someone has a better idea,
               // I'm all ears...  I tried this with 255 which 
               // worked for Japanese characters (except the Japanese
               // characters came out as junk), however, it allowed
               // binary files to open as well)
               //
               if (ord(substr($test, $i, $i + 1)) > 127)
               {

                  echo '<h4>"' . $_POST['viewFile'] . '" ' . _("appears to be binary.");
                  if ($userIsAllowedToOpenBinary)
                  {
                     echo '&nbsp;&nbsp;' . _("Click") 
                        . ' <a href="" onClick="document.viewForm.openBinary.value = \'1\';'
                        . 'document.viewForm.viewFile.value = \''
                        . $_POST['viewFile']
                        . '\';document.viewForm.submit(); return false">' 
                        . _("here") . '</a> ' . _("to open anyway.");
                  }
                  else
                  {
                     echo '&nbsp;&nbsp;' . _("You can only open text files.");
                  }
                  echo '</h4>';
                  echo '<hr>';


                  $isBinary = 1;
                  break;

               }

            }

         }


         // if the file wasn't binary (or we were told to open it anyway),
         // reopen file and continue...
         //
         if (!$isBinary || ($openBinaryOK && $userIsAllowedToOpenBinary))
         {

            @fclose($FILE);


            // if user changed size of the text area, save in user prefs
            //
            if (isset($_POST['changeFileEditTextAreaSize']) && $_POST['changeFileEditTextAreaSize'] == '1')
            {

               if (!is_numeric($_POST['fileEditTextAreaCols']) || !is_numeric($_POST['fileEditTextAreaRows']))
               {  
         
                  echo '<h4>' . _("Error - Size Must Be Numeric.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';
            
               }
               else
               {

                  setPref($data_dir, $username, 'file_manager_edit_box_cols', $_POST['fileEditTextAreaCols']);
                  setPref($data_dir, $username, 'file_manager_edit_box_rows', $_POST['fileEditTextAreaRows']);

               }

            }


            $FILE = fopen($file, "r");


            echo '<form name="fileEditForm" action="' . $_SERVER['PHP_SELF'] . '" method="POST">';

            echo '<input type="hidden" name="cwd" value="' . $displayCWD . '">';
            echo '<input type="hidden" name="baseDir" value="' . $baseDirNo . '">';
            include_current_email_info();
            echo '<input type="hidden" name="editFile" value="' . $_POST['viewFile'] . '">';
            echo '<input type="hidden" name="viewFile" value="' . $_POST['viewFile'] . '">';
            echo '<input type="hidden" name="save" value="1">';
            echo '<input type="hidden" name="continueEditing" value="0">';
            echo '<input type="hidden" name="changeFileEditTextAreaSize" value="0">';

            echo '<table cellspacing=0 cellpadding=0><tr><td>&nbsp;&nbsp;';
            echo $_POST['viewFile'] . '</td>';

            echo '<td align="right"><input type="button" value="' . _("Save") . '" onClick="document.fileEditForm.continueEditing.value=\'1\'; document.fileEditForm.submit()">&nbsp;&nbsp;';
            echo '&nbsp;&nbsp;&nbsp;<input type="button" value="' . _("Save And Close") . '" onClick="document.fileEditForm.submit()">&nbsp;&nbsp;';
            echo '&nbsp;&nbsp;&nbsp;<input type="button" value="' . _("Cancel") . '" onClick="document.refreshForm.submit()"></td></tr>';


            // get user-specific sizing for edit box
            //
            $cols = getPref($data_dir, $username, 'file_manager_edit_box_cols', '70');
            $rows = getPref($data_dir, $username, 'file_manager_edit_box_rows', '10');

            echo '<tr><td colspan=3><textarea name="fileContents" cols="' . $cols . '" rows="' . $rows . '">';

            while (!feof ($FILE)) 
            {
               $buffer = fgets($FILE, 4096);
               echo htmlspecialchars($buffer);  // preserve things like &nbsp; when editing html files
            }

            echo '</textarea></td></tr><tr><td colspan="2" align="right">';
            echo _("Columns") . ': <input type="text" name="fileEditTextAreaCols" value="' . $cols . '" size="1">';
            echo '&nbsp;&nbsp;' . _("Rows") . ': <input type="text" name="fileEditTextAreaRows" value="' . $rows . '" size="1">';
            echo '&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="changeFileEditTextAreaSizeButton" value="' 
               . _("Change Size") . '" onClick="if (!isPosInteger(document.fileEditForm.fileEditTextAreaRows.value) || !isPosInteger(document.fileEditForm.fileEditTextAreaCols.value)) { alert(\'' . _("Error - Size Must Be Numeric.") . '\'); } else { document.fileEditForm.continueEditing.value=\'1\'; document.fileEditForm.save.value=\'0\'; document.fileEditForm.changeFileEditTextAreaSize.value=\'1\'; document.fileEditForm.submit(); }">';
            echo '</table></form>';
            echo '<hr>';

            fclose($FILE);

         }

      }

   }


   // ############################################################
   // check for file upload and attempt to move it to
   // the local directory if found
   //
   if (isset($_POST['upload']) && isset($_POST['numberOfFiles']) && isset($_POST['allowOverwrite']))
   {

      $numberOfFiles = $_POST['numberOfFiles'];
      if (is_numeric($numberOfFiles) 
         && ($_POST['allowOverwrite'] == 'no' || $_POST['allowOverwrite'] == 'ok'))
      {

         $successfulFileCount = 0;
         $errorCount = 0;
         $goodAntiVirusResults = array();

         for ($i = 1; $i <= $numberOfFiles; $i++)
         {

            if (!isset($_FILES['uploadFile' . $i]['name']) 
               || empty($_FILES['uploadFile' . $i]['name'])) 
               continue;


            $destinationFileName = $cwd . '/' . $_FILES['uploadFile' . $i]['name'];
            $destinationFileName = sanitizeFileName($destinationFileName);
         

            // run anti virus check now, but save results for later
            // (first insert filename in command string)
            //
            $actualAntiVirusCommand = preg_replace('/%filename%/', $_FILES['uploadFile' . $i]['tmp_name'], $antiVirusCommand);
            $antiVirusCommandResult = $antiVirusCommandFoundNoVirusReturnCode;
            $antiVirusOutput = array();
            if ($checkUploadsForVirii) exec($actualAntiVirusCommand, $antiVirusOutput, $antiVirusCommandResult);


            // check size of user's base directory and see if this upload
            // will put them over their limit (if we are in a "shared directory",
            // then only check the size on it, and not the user's base dir)
            //
            $dirToCheck = $baseDir;
            if ($inSharedDir) $dirToCheck = $inSharedDir;

            $baseDirSize = dirsize($dirToCheck);
            if (!empty($quota) && $baseDirSize == -1)
            {
         
               echo '<h4>' . _("Error determining upload quota.") . '&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4>';
               $errorCount++;
               break;
        
            }
            else if (!empty($quota) && $baseDirSize + $_FILES['uploadFile' . $i]['size'] > get_real_size($quota))
            {
       
               echo '<h4>' . _("Error - Quota Exceeded.") . '&nbsp;&nbsp;' . _("Please delete other files first.") . '&nbsp;&nbsp;<small>' . _("Quota =") . ' ' . format_size($quota) . '.&nbsp;&nbsp;' . _("Current usage =") . ' ' . format_size($baseDirSize) . '<br>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4>';
               $errorCount++;
               break;
         
            }
            else if (file_exists($destinationFileName) && $_POST['allowOverwrite'] == 'no')
            {
         
               echo '<h4>' . $_FILES['uploadFile' . $i]['name'] . ' ' . _("already exists!  Check the \"Allow Overwrite\" checkbox to replace it.") . '</h4>';
               $errorCount++;
        
            }
            else if (is_dir($destinationFileName))
            {
         
               echo '<h4>' . _("Cannot overwrite a directory with a file.") . ' (' . $_FILES['uploadFile' . $i]['name'] . ')&nbsp;&nbsp;' . _("Please rename one or the other.") . '</h4>';
               $errorCount++;
         
            }
            else if ($checkUploadsForVirii &&
               ($antiVirusCommandResult !== $antiVirusCommandFoundNoVirusReturnCode))
            {

               echo '<h4>' . _("Error - Virus found in") . ' ' . $_FILES['uploadFile' . $i]['name'] . '.</h4>';
               if ($showAntiVirusErrorDetails)
               {
                  echo '<h4>Error Details:</h4>';
                  foreach ($antiVirusOutput as $line) echo $line . '<br>';
                  echo '<br>';
               }
               $errorCount++;

            }
            else if (move_uploaded_file($_FILES['uploadFile' . $i]['tmp_name'], $destinationFileName))
            {

               if ($chmodOK) chmod($destinationFileName, intval($defaultFilePerms, 8));
               {
                  $successfulFileCount++;
                  if ($showGoodAntiVirusResults) array_push($goodAntiVirusResults, $antiVirusOutput);
               }
         
            }
            else
            {
         
               echo '<h4>' . _("Error Occurred Attempting To Upload") . ' ' . $_FILES['uploadFile' . $i]['name'] . '.&nbsp;&nbsp;<small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4>';
               $errorCount++;
         
            }

         }
         
         
         // print upload stats
         //
         if (!($successfulFileCount == 0 && $errorCount == 0))
         {

            if ($successfulFileCount > 0)
               echo "<h4>$successfulFileCount " . _("File(s) Successfully Uploaded") . '</h4>';

            if ($showGoodAntiVirusResults)
            {
               echo '<h4>AntiVirus Scan Results:</h4>';
               foreach ($goodAntiVirusResults as $oneResultSet)
               {
                  foreach ($oneResultSet as $line)
                     echo $line . '<br>';
                  echo '<br>';
               }
            }

            echo '<hr>';

         }


      }
   }


   // ############################################################
   // check for file deletion request
   //
   if (isset($_POST['delete']) && $_POST['chmod'] == '0')
   {
      
      // loop thru, deleting each file
      //
      $numberFilesDeleted = 0;
      $erroneousFiles = array();
      foreach (array_keys($_POST) as $fileKey)
      {
         
         if (strstr($fileKey, 'deleteFile') || strstr($fileKey, 'deleteFolder'))
         {

            $filename = $cwd . '/' . $_POST[$fileKey];
            $filename = sanitizeFileName($filename);

            if (is_dir($filename))
            {
               if (!rmdir($filename)) 
                  array_push($erroneousFiles, substr($filename, strrpos($filename, '/') + 1));
               else $numberFilesDeleted++;
            }
            else
            {
               if (!unlink($filename)) 
                  array_push($erroneousFiles, substr($filename, strrpos($filename, '/') + 1));
               else $numberFilesDeleted++;
            }


         }

      }

      if (sizeof($erroneousFiles) > 0)
      {

         echo '<h4>' . _("Error Occurred Attempting To Delete:");
         foreach (array_values($erroneousFiles) as $file)
            echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;' . $file;
         echo '<br><br><small>' . _("If this problem persists, please send a message to") . ' <a href="../../src/compose.php?send_to=' . $adminMail . '">' . $adminMail . '</a></small></h4><hr>';

      }

      else if ($numberFilesDeleted > 0)
      {

         echo "<h4>$numberFilesDeleted " . _("File(s) Successfully Deleted") . '</h4><hr>';

      }

   }



   // ############################################################
   // set up form form for multiple uploads
   //

   echo '<h4>' . _("File Upload") . '&nbsp;&nbsp; <small><font color="' . $color[2] . '">' 
      . _("Max Size") . ' = ' . format_size(get_max_upload()) . _("(per file)") . '&nbsp;&nbsp;&nbsp;&nbsp;' . _("Quota =") . ' ' . format_size($quota) . '</font></small></h4>';


   if (isset($_POST['multipleUpload']) && isset($_POST['numberOfFiles']))
   {

      $numberOfFiles = $_POST['numberOfFiles'];
      if (is_numeric($numberOfFiles))
      {

         echo '<form name="uploadForm" action="' . $_SERVER['PHP_SELF'] 
            . '" method="POST" enctype="multipart/form-data" onSubmit="return verifyUpload()">'

            . '<input type="hidden" name="cwd" value="' . $displayCWD . '">';

         include_current_email_info();

         echo '<input type="hidden" name="baseDir" value="' . $baseDirNo . '">'

            . '<input type="hidden" name="numberOfFiles" value="' . $numberOfFiles . '">'

            . '<input type="hidden" name="allowOverwrite" value="no">'

            . '<input type="hidden" name="upload" value="1">'

            . '<input type="submit" value="' . _("Upload") . '">'

            . '&nbsp;&nbsp;<input type="checkbox" name="overwrite" onClick="if (document.uploadForm.overwrite.checked) document.uploadForm.allowOverwrite.value = \'ok\'; else document.uploadForm.allowOverwrite.value = \'no\';">'
            . _("Allow Overwrite")

            . '<br><br><table cellspacing=0 cellpadding=0>';

         for ($i = 1; $i <= $numberOfFiles; $i++)
         {

            echo '<tr><td>'
               . _("File:") . '<input name="uploadFile' . $i . '" size=48 type="file">'
               . '</td><td>'
               . '</td></tr>';

         }

         echo '</table><br>'
            . '<input type="submit" value="' . _("Upload") 
            . '"></form><hr><br>';


         // could return here, but may as well show file listing as ususal...
         //
         //return;

      }

   }


   // otherwise just one upload input field
   //
   else
   {

      ?>

   <table cellspacing=0 cellpadding=0>

      <tr><td>

         <form name="uploadForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data" onSubmit="return verifyUpload()">
   
            <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
            <?php include_current_email_info(); ?>
            <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
            <input type="hidden" name="numberOfFiles" value="1">
            <input type="hidden" name="allowOverwrite" value="no">

            File: <input name="uploadFile1" size=48 type="file">
            &nbsp;&nbsp;<input type="submit" name="upload" value="<?php echo _("Upload"); ?>">

         </form>

      </td>
      <td>

         <form name="overwriteForm">&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="overwrite" onClick="if (document.overwriteForm.overwrite.checked) document.uploadForm.allowOverwrite.value = 'ok'; else document.uploadForm.allowOverwrite.value = 'no';"><?php echo _("Allow Overwrite"); ?></form>

      </td></tr></table>

      <?php

   }
   // ############################################################


   ?>


   <table cellspacing=0 cellpadding=0>

      <tr>

         <td><form name="multipleUploadForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input type="hidden" name="multipleUpload" value="1">
            <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
            <?php include_current_email_info(); ?>
            <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
            <select name="numberOfFiles">
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
               <option value="5">5</option>
               <option value="6">6</option>
               <option value="7">7</option>
               <option value="8">8</option>
               <option value="9">9</option>
               <option value="10">10</option>
               <option value="11">11</option>
               <option value="12">12</option>
               <option value="13">13</option>
               <option value="14">14</option>
               <option value="15">15</option>
               <option value="16">16</option>
               <option value="17">17</option>
               <option value="18">18</option>
               <option value="19">19</option>
               <option value="20">20</option>
               <option value="21">21</option>
               <option value="22">22</option>
               <option value="23">23</option>
               <option value="24">24</option>
               <option value="25">25</option>
               <option value="26">26</option>
               <option value="27">27</option>
               <option value="28">28</option>
               <option value="29">29</option>
               <option value="30">30</option>
               <option value="31">31</option>
               <option value="32">32</option>
               <option value="33">33</option>
               <option value="34">34</option>
               <option value="35">35</option>
               <option value="36">36</option>
               <option value="37">37</option>
               <option value="38">38</option>
               <option value="39">39</option>
               <option value="40">40</option>
               <option value="41">41</option>
               <option value="42">42</option>
               <option value="43">43</option>
               <option value="44">44</option>
               <option value="45">45</option>
               <option value="46">46</option>
               <option value="47">47</option>
               <option value="48">48</option>
               <option value="49">49</option>
               <option value="50">50</option>
            </select>
            &nbsp;&nbsp;<input type="submit" value="<?php echo _("Upload Multiple Files"); ?>">
         </form></td>

      </tr>

   </table>


   <hr>


   <form name="changeBaseDirForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <table cellpadding=0 cellspacing=0 width="90%">

         <tr>
            <td><h4><?php echo _("Listing for") . '&nbsp;&nbsp;<small><font color="' . $color[2] 
               . '">' . $displayCWD ?></font></small></h4></td>
            <td valign="top" align="right">
<?php

   if (sizeof($userBaseDirs) > 1) 
   {

      include_current_email_info();
      echo '<select name = "baseDir">';

      foreach ($userBaseDirs as $dirName => $dirPath)
      {

         echo '<option ';

         if ($baseDirNo == $dirName) echo 'SELECTED ';

         echo 'value = "' . $dirName . '">' . $dirPath . '</option>';

      }

      echo '</select>';

      echo '&nbsp;&nbsp;<input type="submit" value="' . _("Go") . '">';

   }

?>
            </td>
         </tr>

      </table>
      <table cellpadding=0 cellspacing=0>

         <tr>

         <!--td>&nbsp;&nbsp;<input type="button" value="<?php echo _("Edit"); ?>" onClick="if (setViewFile()) document.viewForm.submit()"></td-->

         <!--td>&nbsp;&nbsp;<input type="button" value="<?php echo _("Move"); ?>" onClick="if (setMoveFolder()) document.moveForm.submit()"></td-->

<?php  

   if ($allowChmod && $chmodOK) 
      echo '<td>&nbsp;&nbsp;&nbsp;<input type="button" value="' . _("CHMOD") . '" '
         . 'onClick="if (getChmodFile()) {document.deleteForm.chmod.value=\'1\'; document.deleteForm.submit();}"></td>';
   else
      echo '<td></td>';

?>

         <td>&nbsp;&nbsp;<input type="button" value="<?php echo _("Copy"); ?>" onClick="if (setCopyFile()) document.copyForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("Move/Rename"); ?>" onClick="if (getRenameFile()) document.renameFileForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("New Folder"); ?>" onClick="if (getNewFolder()) document.newFolderForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("New File"); ?>" onClick="if (getNewFile()) document.newFileForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("Delete"); ?>" onClick="if (verifyDelete()) document.deleteForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("Refresh"); ?>" onClick="document.refreshForm.submit()"></td></tr>

      </table>

   </form>


   <form name="deleteForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="delete" value="1">
      <input type="hidden" name="chmod" value="0">
      <input type="hidden" name="chmodMode" value="">


<?php


   // ############################################################
   // create file listing...
   //


   // begin file and directory list table...
   //
   echo '<table cellpadding="0" cellspacing="0" cols="5" width="90%">';


   // put "<--BACK" link at top of list unless at root
   //
   if ($cwd !== '/') 
   {

      // do not allow user to go above their base directory
      //
      if (strstr($parentDir . '/', $baseDir))

         echo '<tr><td>&nbsp;&nbsp;<a href="" onClick="document.refreshForm.cwd.value = \'' 
            . $displayParentDir .  '\'; document.refreshForm.submit(); return false;">'
            . _("<--BACK") . '</a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';

   }


   // loop thru all the files and dirs in the cwd
   //
   $count = 0;
   $fileList = array();
   $dirList = array();
   if ($DIR = opendir($cwd))
   {

      // grab all listings for this directory
      //
      while (($filename = readdir($DIR)) !== false)
      { 
         $filenames[]=$filename; 
      } 


      // then sort and loop thru the sorted list
      //
      sort($filenames);
      while (list ($junk, $filename) = each ($filenames)) 
      {

         $fullPath = $cwd . '/' . $filename;
         $fullPath = str_replace('//', '/', $fullPath);
         $displayFullPath = $displayCWD . '/' . $filename;
         $displayFullPath = str_replace('//', '/', $displayFullPath);


         if ($filename == '.' || $filename == '..') continue;
         // no longer used  if (strstr(strtoupper($filename), $thisFile)) continue;


         // don't allow user to follow symlinks (depends on config)
         //
         if (is_link($fullPath) && !$allowLinks)
            continue;


         $count++;


         if (is_dir($fullPath))
         {

            if ($TEMP_DIR = @opendir($fullPath)) 
            {

               closedir($TEMP_DIR);
               array_push($dirList, $filename);

               echo '<tr><td>&nbsp;&nbsp;'
                  . '<input type="checkbox" name="deleteFolder'
                  . $count . '" value="' . $filename 
                  . '">&nbsp;&nbsp;';
               echo '<a href="" ';

               if (is_link($fullPath) && !empty($symlinkColor)) 
                  echo 'style="color:' . $symlinkColor . ';" ';

               echo 'onClick="document.refreshForm.cwd.value = \'' 
                  . $displayFullPath 
// TODO: allow download of dirs too?  tar/zip up the whole dir... ???
                  . '\'; document.refreshForm.submit(); return false;">' 
                  . $filename
                  . '</a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;';
               if ($allowChmod && $chmodOK) echo '&nbsp;&nbsp;&nbsp;' . get_perms($fullPath);
               echo '</td></tr>';
               continue;

            }
            else
            {
               continue;
            }

         }


         $stats = stat($fullPath);

         array_push($fileList, $filename);


         echo '<tr><td>&nbsp;&nbsp;'
            . '<input type="checkbox" name="deleteFile' 
            . $count . '" value="' . $filename . '">&nbsp;';
         if ($fileEditStyle == 'hyperlink') {
            echo '<a href="" ';
            if (!empty($fileLinkColor)) 
               echo 'style="color:' . $fileLinkColor . ';" ';
            echo 'onClick="document.viewForm.viewFile.value = \''
               . $filename
               . '\';document.viewForm.submit(); return false">' . $filename 
               . '</a>';
         }
         else echo $filename;
         echo '&nbsp;&nbsp;</td><td><small>['
            . '<a href="" onClick="document.downloadForm.downloadFileName.value=\''
            . $filename 
            . '\';document.downloadForm.absolute_dl.value=\'true\';'
            . 'document.downloadForm.submit();return false">'
            . _("download") . '</a> | '
            . '<a href="" onClick="document.showFileForm.downloadFileName.value=\''
            . $filename
            . '\';document.showFileForm.absolute_dl.value=\'false\';'
            . 'document.showFileForm.submit();return false">'
            . _("view") . '</a>';
         if ($fileEditStyle == 'edit link')
            echo ' | <a href="" onClick="document.viewForm.viewFile.value = \''
               . $filename
               . '\';document.viewForm.submit(); return false">' . _("edit") . '</a>';
         echo ' | <a href="" onClick="document.sendAttachmentForm.attachmentFileName.value = \'' 
            . $filename 
            . '\';document.sendAttachmentForm.submit(); return false">'
            . _("send") . '</a>]</small>'
            . '&nbsp;&nbsp;&nbsp;&nbsp;</td><td>' . $stats[7] 
            . '&nbsp;&nbsp;&nbsp;&nbsp;</td><td>' 
            . date('l, F dS', $stats[9]) . '</td>'
            . '<td>&nbsp;';
         if ($allowChmod && $chmodOK) echo '&nbsp;&nbsp;&nbsp;' . get_perms($fullPath);
         echo '</td></tr>';

      }

      // put "<--BACK" link at bottom of list unless at root 
      // or only one file (plus two for . and ..)
      //
      if ($cwd !== '/' && count($filenames) > 3) 
      {

         // do not allow user to go above their base directory
         //
         if (strstr($parentDir . '/', $baseDir))

            echo '<tr><td>&nbsp;&nbsp;<a href="" onClick="document.refreshForm.cwd.value = \'' 
               . $displayParentDir .  '\'; document.refreshForm.submit(); return false;">'
               . _("<--BACK") . '</a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';

      }


      echo '</table>';
      closedir($DIR);

   }

   // hidden inputs just to hold file and directory lists for use in
   // page javascript (see verifyUpload javascript function)
   //?>
   <input type="hidden" name="fileList" value="<?php echo ':'; 
                                                 foreach ($fileList as $oneFile) echo $oneFile . ':'; ?>">
   <input type="hidden" name="dirList" value="<?php echo ':'; 
                                                 foreach ($dirList as $oneDir) echo $oneDir . ':'; ?>">



      <br><br>

   </form>


   <form>

      <table cellpadding=0 cellspacing=0>

         <tr>

         <!--td>&nbsp;&nbsp;<input type="button" value="<?php echo _("Edit"); ?>" onClick="if (setViewFile()) document.viewForm.submit()"></td-->

         <!--td>&nbsp;&nbsp;<input type="button" value="<?php echo _("Move"); ?>" onClick="if (setMoveFolder()) document.moveForm.submit()"></td-->

<?php  

   if ($allowChmod && $chmodOK) 
      echo '<td>&nbsp;&nbsp;&nbsp;<input type="button" value="' . _("CHMOD") . '" '
         . 'onClick="if (getChmodFile()) {document.deleteForm.chmod.value=\'1\'; document.deleteForm.submit();}"></td>';
   else
      echo '<td></td>';

?>

         <td>&nbsp;&nbsp;<input type="button" value="<?php echo _("Copy"); ?>" onClick="if (setCopyFile()) document.copyForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("Move/Rename"); ?>" onClick="if (getRenameFile()) document.renameFileForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("New Folder"); ?>" onClick="if (getNewFolder()) document.newFolderForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("New File"); ?>" onClick="if (getNewFile()) document.newFileForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("Delete"); ?>" onClick="if (verifyDelete()) document.deleteForm.submit()"></td>

         <td>&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo _("Refresh"); ?>" onClick="document.refreshForm.submit()"></td></tr>

      </table>

   </form>



   <!-- ############################################################ -->
   <!-- View link behavior -->

   <form name="viewBehaviorForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

   <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
   <?php include_current_email_info(); ?>
   <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">

   <input type="hidden" name="viewFileBehavior" value="<?php 

      // if user is allowed to override, get setting from user prefs
      //
      if ($viewFilesBehavior < 3) 
      { 
         $viewFilesBehavior = getPref($data_dir, $username, 'file_manager_view_files_behavior', $viewFilesBehavior);
         if ($viewFilesBehavior == 'separateWindow') $viewFilesBehavior = 1;
         if ($viewFilesBehavior == 'thisWindow') $viewFilesBehavior = 2;
      }


      // note that these values are backward since they are what the 
      // setting would be changed to if user decides to change it
      // 
      switch ($viewFilesBehavior)
      {
         case 2:
         case 4:  echo 'separateWindow';
                  break;
         case 1:
         case 3:  echo 'thisWindow';
                  break;
      }
   ?>">

   <?php

      if ($viewFilesBehavior < 3)
      {
         echo '<br><table cellpadding=0 cellspacing=0 width="90%">'

            . '<tr><td align="right"><b><small>';

         echo '<a href="" onClick="document.viewBehaviorForm.submit(); return false">'
            . _("Change view links to show in") . ' ';
         if ($viewFilesBehavior == 1) echo _("this window");
         if ($viewFilesBehavior == 2) echo _("a separate window");
         echo '</a></small></b></td></tr></table>';
      }

   ?>

   </form>



   <!-- ############################################################ -->
   <form name="refreshForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="refresh" value="1">


   </form>


   <!-- ############################################################ -->
   <form name="copyForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="copy" value="1">
      <input type="hidden" name="copyFile" value="">
      <input type="hidden" name="newFileName" value="">

   </form>


   <!-- ############################################################ -->
   <form name="viewForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="view" value="1">
      <input type="hidden" name="openBinary" value="0">
      <input type="hidden" name="viewFile" value="">

   </form>


   <!-- ############################################################ -->
   <form name="newFolderForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="newFolder" value="1">
      <input type="hidden" name="newFolderName" value="">

   </form>


   <!-- ############################################################ -->
   <form name="sendAttachmentForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="attachmentFile" value="1">
      <input type="hidden" name="attachmentFileName" value="">
      <?php include_current_email_info(); ?>

   </form>


   <!-- ############################################################ -->
   <form name="newFileForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="newFile" value="1">
      <input type="hidden" name="newFileName" value="">

   </form>


   <!-- ############################################################ -->
   <form name="renameFileForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="renameFile" value="1">
      <input type="hidden" name="newFileName" value="">
      <input type="hidden" name="oldFileName" value="">

   </form>


   <!-- ############################################################ -->
   <form name="chmodForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="chmodFile" value="1">
      <input type="hidden" name="chmodFileName" value="">
      <input type="hidden" name="chmodMode" value="">

   </form>


   <!-- ############################################################ -->
   <form name="showFileForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" <?php 
      if ($viewFilesBehavior == 1 || $viewFilesBehavior == 3) echo 'target="_blank"'; ?>>

      <!-- very frustrating... Netscape won't display inline
           images, etc unless method is GET -->

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="downloadFile" value="1">
      <input type="hidden" name="downloadFileName" value="">
      <input type="hidden" name="absolute_dl" value="">

   </form>


   <!-- ############################################################ -->
   <!-- this is a duplicate of the above form, but always default target because downloads don't need separate window -->
   <form name="downloadForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">

      <!-- very frustrating... Netscape won't display inline
           images, etc unless method is GET -->

      <input type="hidden" name="cwd" value="<?php echo $displayCWD ?>">
      <?php include_current_email_info(); ?>
      <input type="hidden" name="baseDir" value="<?php echo $baseDirNo ?>">
      <input type="hidden" name="downloadFile" value="1">
      <input type="hidden" name="downloadFileName" value="">
      <input type="hidden" name="absolute_dl" value="">

   </form>


<?php 
   bindtextdomain('squirrelmail', '../locale');
   textdomain('squirrelmail'); 
?>
</body>
</html>
