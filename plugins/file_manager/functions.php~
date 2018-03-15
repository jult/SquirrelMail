<?php

/*********************************************************
 *
 * File Manager Support Functions
 *
 */




   // include compatibility plugin
   //
   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/compatibility/functions.php');
   else if (file_exists('../plugins/compatibility/functions.php'))
      include_once('../plugins/compatibility/functions.php');
   else if (file_exists('./plugins/compatibility/functions.php'))
      include_once('./plugins/compatibility/functions.php');



   // look for the current user's entry (or lack thereof) in the user configuration settings
   //
   function getFileManagerUserConfig()
   {

      global $file_manager_quota, $file_manager_adminMail, $file_manager_allowLinks,
             $file_manager_allowChmod, $file_manager_allowEditBinary, $file_manager_baseDirs, 
             $username,
             $file_manager_config;


      if (compatibility_check_sm_version(1, 3))
         include_once(SM_PATH . 'plugins/file_manager/config.php');
      else
         include_once('../plugins/file_manager/config.php');


      // get global variables for versions of PHP < 4.1
      //
      if (!compatibility_check_php_version(4, 1)) {
         global $HTTP_SESSION_VARS;
         $_SESSION = $HTTP_SESSION_VARS;
      }


      $file_manager_user_config = array();
      $numberOfBaseDirs = 0;


      // first, check session to see if we've been here before
      //
      // (instead of just checking if file manager settings exist
      // in the session, check username so that we can avoid a
      // nasty side effect of SquirrelMail installations with 
      // session problems where File Manager permissions get 
      // "left over" in the same browser even after logout)
      //
      if (isset($_SESSION['file_manager_username']) 
             && $_SESSION['file_manager_username'] == $_SESSION['username'])
      {


         // only load settings for users with file_manager access
         //
         if ($_SESSION['file_manager_baseDirs'] != 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED')
         {

            $file_manager_user_config[$username]['quota'] = $_SESSION['file_manager_quota'];
            $file_manager_user_config[$username]['adminMail'] = $_SESSION['file_manager_adminMail'];
            $file_manager_user_config[$username]['allowLinks'] = $_SESSION['file_manager_allowLinks'];
            $file_manager_user_config[$username]['allowChmod'] = $_SESSION['file_manager_allowChmod'];
            $file_manager_user_config[$username]['allowEditBinary'] = $_SESSION['file_manager_allowEditBinary'];

            $baseDirList = explode('##&&##', $_SESSION['file_manager_baseDirs']);
            for ($i = 0; $i < sizeof($baseDirList); $i++)
            {
               if (empty($baseDirList[$i])) 
                  continue;
   
               $file_manager_user_config[$username]['baseDir' . ($i + 1)] = $baseDirList[$i];
            }

         }

      }

      // otherwise, go look in the file manager users file...
      //
      else
      {


         $foundUserConfig = 0;
         if ($CONFIG = @fopen ('../plugins/file_manager/file_manager.users', "r"))
         {

            while (!feof ($CONFIG))
            {
               $userConfig = fgets($CONFIG, 4096);
               $userConfig = trim($userConfig);


               // skip blank lines and comment lines
               // (sometimes trim() doesn't get newlines, so just check for abnormally short lines)
               //
               if (strpos($userConfig, '#') === 0 || strlen($userConfig) < 3)
                  continue;

   
               // parse fields out of line
               //
               preg_match_all('/(\S+)\s*/', $userConfig, $configSettings, PREG_PATTERN_ORDER);
   
   
               // if we find the current user...
               //
               if ($configSettings[1][0] == $username)
               {
   
                  // number of fields given for this user
                  //
                  $numberOfFields = sizeof($configSettings[1]);


                  // start from end of array, grabbing edit binary, chmod, symlink, 
                  // admin email and quota settings
                  //
                  $file_manager_user_config[$username]['allowEditBinary'] = $configSettings[1][$numberOfFields - 1];
                  $file_manager_user_config[$username]['allowChmod'] = $configSettings[1][$numberOfFields - 2];
                  $file_manager_user_config[$username]['allowLinks'] = $configSettings[1][$numberOfFields - 3];
                  $file_manager_user_config[$username]['adminMail'] = $configSettings[1][$numberOfFields - 4];


                  // get all base directories
                  //
                  $numberOfBaseDirs = 1;
                  for ( ; $numberOfBaseDirs < $numberOfFields - 5; $numberOfBaseDirs++)
                  {

                     $file_manager_user_config[$username]['baseDir' . $numberOfBaseDirs] = $configSettings[1][$numberOfBaseDirs];

                  }

   
                  // figure out if there is a quota given or it is one more base directory
                  // (with unlimited quota for this user)
                  //
                  $quota = $configSettings[1][$numberOfFields - 5];
                  if (is_numeric($quota) || is_numeric(get_real_size($quota)))
                  {
                     $file_manager_user_config[$username]['quota'] = $quota;
                     $numberOfBaseDirs--;
                  }
                  else
                  {
                     $file_manager_user_config[$username]['baseDir' . $numberOfBaseDirs] = $quota;
                     $file_manager_user_config[$username]['quota'] = '';
                  }


                  // set flag indicating user was found
                  //
                  $foundUserConfig = 1;
   

                  // break out of loop reading file - we found all we need
                  //
                  break;


               }

            }

            fclose($CONFIG);

         }



         // even if username wasn't found, we still want to store info in
         // session saying so, so we don't have to do this again
         //
         if (!$foundUserConfig)
         {
            $file_manager_quota = 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED';
            $file_manager_adminMail = 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED';
            $file_manager_allowLinks = 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED';
            $file_manager_allowChmod = 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED';
            $file_manager_allowEditBinary = 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED';
            $file_manager_baseDirs = 'USER NOT FOUND - FILE MANAGER ACCESS NOT GRANTED';
         }
         else
         {
            $baseDirs = '';
            for ($i = 0; $i < $numberOfBaseDirs; $i++)
               $baseDirs .= $file_manager_user_config[$username]['baseDir' . ($i + 1)] . '##&&##';

            $file_manager_quota = $file_manager_user_config[$username]['quota'];
            $file_manager_adminMail = $file_manager_user_config[$username]['adminMail'];
            $file_manager_allowLinks = $file_manager_user_config[$username]['allowLinks'];
            $file_manager_allowChmod = $file_manager_user_config[$username]['allowChmod'];
            $file_manager_allowEditBinary = $file_manager_user_config[$username]['allowEditBinary'];
            $file_manager_baseDirs = $baseDirs;
         }


         // store in session
         //
         // (also store our own copy of the username so that we 
         // can avoid a nasty side effect of SquirrelMail 
         // installations with session problems where File Manager 
         // permissions get "left over" in the same browser even 
         // after logout)
         //
         compatibility_sqsession_register($_SESSION['username'], 'file_manager_username');
         compatibility_sqsession_register($file_manager_quota, 'file_manager_quota');
         compatibility_sqsession_register($file_manager_adminMail, 'file_manager_adminMail');
         compatibility_sqsession_register($file_manager_allowLinks, 'file_manager_allowLinks');
         compatibility_sqsession_register($file_manager_allowChmod, 'file_manager_allowChmod');
         compatibility_sqsession_register($file_manager_allowEditBinary, 'file_manager_allowEditBinary');
         compatibility_sqsession_register($file_manager_baseDirs, 'file_manager_baseDirs');
   
      }


      // merge old/legacy config settings with any found here
      //
      if (sizeof($file_manager_config) > 0)
         $file_manager_config = array_merge($file_manager_config, $file_manager_user_config);
      else
         $file_manager_config = $file_manager_user_config;

   }



   // includes email content in an HTML form if user was already writing an email
   //
   function include_current_email_info()
   {

         // get global variables for versions of PHP < 4.1
         //
         if (!compatibility_check_php_version(4, 1)) {
            global $HTTP_POST_VARS;
            $_POST = $HTTP_POST_VARS;
         }


         if (isset($_POST['body']))
            echo '<input type="hidden" name="body" value="' . htmlspecialchars($_POST['body']) . '">';
         if (isset($_POST['mailprio']))
            echo '<input type="hidden" name="mailprio" value="' . $_POST['mailprio'] . '">';
         if (isset($_POST['variable_sent_folder']))
            echo '<input type="hidden" name="variable_sent_folder" value="' . $_POST['variable_sent_folder'] . '">';
         if (isset($_POST['identity']))
            echo '<input type="hidden" name="identity" value="' . $_POST['identity'] . '">';

         // theoretically, we might want to check !empty() on all of these,
         // but delete_draft is the only one that seems to cause problems
         // if we specifically do not do this:
         //
         if (isset($_POST['delete_draft']) && !empty($_POST['delete_draft']))
            echo '<input type="hidden" name="delete_draft" value="' . $_POST['delete_draft'] . '">';
         if (isset($_POST['session']))
            echo '<input type="hidden" name="session" value="' . $_POST['session'] . '">';
         if (isset($_POST['action']))
            echo '<input type="hidden" name="action" value="' . $_POST['action'] . '">';
         if (isset($_POST['reply_id']))
            echo '<input type="hidden" name="reply_id" value="' . $_POST['reply_id'] . '">';
         if (isset($_POST['forward_id']))
            echo '<input type="hidden" name="forward_id" value="' . $_POST['forward_id'] . '">';
         if (isset($_POST['request_mdn']))
            echo '<input type="hidden" name="request_mdn" value="' . $_POST['request_mdn'] . '">';
         if (isset($_POST['request_dr']))
            echo '<input type="hidden" name="request_dr" value="' . $_POST['request_dr'] . '">';
         if (isset($_POST['fm_send_to']))
            echo '<input type="hidden" name="fm_send_to" value=\'' . htmlspecialchars($_POST['fm_send_to'], ENT_NOQUOTES) . '\'>';
         if (isset($_POST['fm_send_to_cc']))
            echo '<input type="hidden" name="fm_send_to_cc" value=\'' . htmlspecialchars($_POST['fm_send_to_cc'], ENT_NOQUOTES) . '\'>';
         if (isset($_POST['fm_send_to_bcc']))
            echo '<input type="hidden" name="fm_send_to_bcc" value=\'' . htmlspecialchars($_POST['fm_send_to_bcc'], ENT_NOQUOTES) . '\'>';
         if (isset($_POST['subject']))
            echo '<input type="hidden" name="subject" value=\'' . htmlspecialchars($_POST['subject'], ENT_QUOTES) . '\'>';

         if (isset($_POST['passed_id']))
            echo '<input type="hidden" name="passed_id" value="' . $_POST['passed_id'] . '">';
         if (isset($_POST['mailbox']))
            echo '<input type="hidden" name="mailbox" value="' . $_POST['mailbox'] . '">';
   }



   function format_size($rawSize) 
   {

      if ($rawSize == 0 || empty($rawSize))
         return _("Unlimited");

      if (!is_numeric($rawSize))
         return $rawSize;

      if ($rawSize / 1073741824 > 1)
         return round($rawSize/1073741824, 1) . _("GB");
      else if ($rawSize / 1048576 > 1)
         return round($rawSize/1048576, 1) . _("MB");
      else if ($rawSize / 1024 > 1)
         return round($rawSize/1024, 1) . _("KB");
      else
         return round($rawSize, 1) . ' ' . _("bytes");

   }

   function get_max_upload() 
   {

      if (!ini_get("file_uploads")) 
      {
         return FALSE;
      }

      $upload_max_filesize = get_real_size(ini_get("upload_max_filesize"));
      $post_max_size = get_real_size(ini_get("post_max_size")); 
      $memory_limit = round(get_real_size(ini_get("memory_limit")) / 2);

      if ($upload_max_filesize > $post_max_size) 
      {
         $max = $post_max_size;
      } 
      else 
      {
         $max = $upload_max_filesize;
      }

      if (($memory_limit != "") && ($memory_limit < $max)) 
      {
         $max = $memory_limit;
      }


      return $max;

   }

   function get_real_size($size)
   {

      if ($size=="") return 0; 

      $scan['GB'] = 1073741824;
      $scan['G'] = 1073741824;
      $scan['MB'] = 1048576;
      $scan['M'] = 1048576;
      $scan['KB'] = 1024;
      $scan['K'] = 1024;

      while (list($key) = each($scan)) 
      {

         if ((strlen($size) > strlen($key)) && (substr($size, strlen($size) - strlen($key)) == $key)
            && (is_numeric(substr($size, 0, strlen($size) - strlen($key))))) 
         {
            $size = substr($size, 0, strlen($size) - strlen($key)) * $scan[$key];
            break;
         }

      }

      return $size;

   }


   function sanitizeFileName($filename)
   {

      $filename = str_replace('//', '/', $filename);


      // shouldn't need it, but just for the paranoid, keep looping
      // till we're rid of evil double dots...
      //
      while (strstr($filename, '..'))
         $filename = str_replace('..', '', $filename);


      return $filename;

   }


   function get_perms($file) {

      $p_bin = substr(decbin(fileperms($file)), -9) ;
      $p_arr = explode(".", substr(chunk_split($p_bin, 1, "."), 0, 17)) ;
      $perms = ""; $i = 0;

      foreach ($p_arr as $value)
            {
                     $p_char = ( $i%3==0 ? "r" : ( $i%3==1 ? "w" : "x" ) );
                              $perms .= ( $value=="1" ? $p_char : "-" ) . ( $i%3==2 ? " " : "" );
                                       $i++;
                                             }

      return $perms;

   }


   // returns true if successful, false otherwise
   //
   function dir_copy($source, $dest, $folderPerms='0755', $overwrite=false, $recursive=true)
   {

      if (!is_dir($source)) return false;

      if (!file_exists($dest))
         if (!mkdir($dest, intval($folderPerms, 8)))
            return false;

      $DIR = opendir($source);
      while (($dirfile = readdir($DIR)) !== false)
      {

         $sourceFile = $source . '/' . $dirfile;
         $destFile = $dest . '/' . $dirfile;


         if ($dirfile == '.' || $dirfile == '..')
            continue;


         if (is_dir($sourceFile) && $recursive)
         {
            if (!dir_copy($sourceFile, $destFile, $folderPerms, $overwrite, $recursive))
               return false;
         }


         else if (is_file($sourceFile))
         {
            if (!$overwrite && file_exists($destFile))
               return false;
            else if (!copy($sourceFile, $destFile))
               return false;
         }
      }

      closedir($DIR);
      return true;

   }



   // returns the total size, in bytes, of all files
   // contained in the directory tree beginning at the
   // specified directory
   // returns -1 if any problem occured (such as if the
   // directory given was nonexistent)
   //
   function dirsize($directory)
   {

      if (!is_dir($directory)) return -1;

      $size = 0;

      if ($DIR = @opendir($directory))
      {

         while (($dirfile = readdir($DIR)) !== false)
         {

            if (is_link($directory . '/' . $dirfile) || $dirfile == '.' || $dirfile == '..') 
               continue;


            if (is_file($directory . '/' . $dirfile)) 
               $size += filesize($directory . '/' . $dirfile);


            else if (is_dir($directory . '/' . $dirfile))
            {

               $dirSize = dirsize($directory . '/' . $dirfile);
               if ($dirSize >= 0) $size += $dirSize;
               else return -1;

            }

         }

         closedir($DIR);

      }

      return $size;

   }



?>
