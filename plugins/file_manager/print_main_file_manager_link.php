<?php


function print_main_file_manager_link()
{

   if (defined('SM_PATH'))
   {
      include_once(SM_PATH . 'functions/i18n.php');
      include_once(SM_PATH . 'plugins/file_manager/functions.php');
   }
   else
   {
      include_once('../functions/i18n.php');
      include_once('../plugins/file_manager/functions.php');
   }


   // get global variables for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4, 1)) {
      global $HTTP_SESSION_VARS;
      $_SESSION = $HTTP_SESSION_VARS;
   }


   global $file_manager_config;
   getFileManagerUserConfig();


   // set to 0 for no debug (regular operation)
   // set to 1 to display the current username in
   //    place of the regular 'File Manager' link
   // set to 2 to dump out the contents of File
   //    Manager's config settings at run time
   //
   $debug = 0;


   // show actual username -- this must match config/users file exactly
   //
   if ($debug == 1)
   {
      displayInternalLink('plugins/file_manager/file_manager.php', _($_SESSION['username']), '');
      echo '&nbsp;&nbsp;' . (in_array($_SESSION['username'], array_keys($file_manager_config)));
      echo '&nbsp;&nbsp;';
   }


   // dump size and contents of config file for debugging...
   //
   else if ($debug == 2)
   {
      echo '<br><br>Number of users configured to use File Manager is ' . sizeof($file_manager_config) . '<br><br>';
      print_r($file_manager_config);
      echo '<hr>';
   }


   // non-debug (normal) functionality:
   //
   // only users who are allowed get to see the link...
   //
   else
   {
      if (!in_array($_SESSION['username'], array_keys($file_manager_config)))
         return;

      bindtextdomain('file_manager', '../plugins/file_manager/locale');
      textdomain('file_manager');
      displayInternalLink('plugins/file_manager/file_manager.php', _("File Manager"), '');
      bindtextdomain('squirrelmail', '../locale');
      textdomain('squirrelmail');
      echo '&nbsp;&nbsp;';

   }


}


?>
