<?php


function print_save_locally_link(&$Args)
{

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/functions.php');
   else
      include_once('../plugins/file_manager/functions.php');


   // get global variables for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4, 1)) {
      global $HTTP_SESSION_VARS;
      $_SESSION = $HTTP_SESSION_VARS;
   }


   global $file_manager_config;
   getFileManagerUserConfig();


   if (!in_array($_SESSION['username'], array_keys($file_manager_config)))
      return;


   // put "save locally" link for attachment
   //
   $Args[1]['file_manager']['href'] = '../plugins/file_manager/save_attachment.php?'
      . 'passed_id=' . $Args[3] . '&mailbox=' . $Args[4] . '&passed_ent_id=' . $Args[5];

   bindtextdomain('file_manager', '../plugins/file_manager/locale');
   textdomain('file_manager');
   $Args[1]['file_manager']['text'] = _("save locally");
   bindtextdomain('squirrelmail', '../locale');
   textdomain('squirrelmail');

}


?>
