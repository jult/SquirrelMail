<?php


function squirrelmail_plugin_init_file_manager() 
{

   global $squirrelmail_plugin_hooks;
   global $mimetypes;


   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/mime_types.php');
   else
      include_once('../plugins/file_manager/mime_types.php');


   $squirrelmail_plugin_hooks['menuline']['file_manager'] = 'file_manager_link';
   $squirrelmail_plugin_hooks['compose_bottom']['file_manager'] = 'file_manager_attach';


   // install "save local" handler for all known mime types
   //
   foreach (array_values($mimetypes) as $mimeType)
   {

      $squirrelmail_plugin_hooks['attachment ' . $mimeType]['file_manager'] = 'file_manager_save_attachment_link';

   }

}

function file_manager_link() 
{

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/print_main_file_manager_link.php');
   else
      include_once('../plugins/file_manager/print_main_file_manager_link.php');

   print_main_file_manager_link();

}


function file_manager_attach() 
{

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/print_attach_from_file_manager_link.php');
   else
      include_once('../plugins/file_manager/print_attach_from_file_manager_link.php');

   print_attach_from_file_manager_link();

}


function file_manager_save_attachment_link(&$Args)   
{     

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/print_save_locally_link.php');
   else
      include_once('../plugins/file_manager/print_save_locally_link.php');

   print_save_locally_link($Args);

}   


?>
