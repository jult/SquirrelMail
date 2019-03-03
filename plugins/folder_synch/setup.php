<?php

/**
  * SquirrelMail Folder Synchronization Plugin
  *
  * Copyright (c) 2011-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003 Nick Bartos <>
  * Copyright (c) 2002 Jimmy Conner <jimmy@advcs.org>
  * Copyright (c) 2002 Jay Guerette <JayGuerette@pobox.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage folder_synch
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_folder_synch()
{

   global $squirrelmail_plugin_hooks;


   // Show options on folder preferences page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_folder']['folder_synch']
      = 'folder_synch_display_options_stub';


   // Insert needed script on folder list page
   //
   $squirrelmail_plugin_hooks['left_main_after']['folder_synch']
      = 'folder_synch_insert_folder_list_script_stub';


   // Insert needed script on message list page
   //
   $squirrelmail_plugin_hooks['right_main_after_header']['folder_synch']
      = 'folder_synch_insert_message_list_script_stub';


   // Insert needed script on message read page
   //
   $squirrelmail_plugin_hooks['read_body_header']['folder_synch']
      = 'folder_synch_insert_message_read_script_stub';

}



/**
  * Returns info about this plugin
  *
  */
function folder_synch_info()
{

   return array(
                  'english_name' => 'Folder Synchronization',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                     'Nick Bartos' => array(
                     ),
                     'Jimmy Conner' => array(
                        'email' => 'jimmy@advcs.org',
                     ),
                     'Jay Guerette' => array(
                        'email' => 'JayGuerette@pobox.com',
                     ),
                  ),
                  'version' => '1.0',
                  'required_sm_version' => '1.4.0',
                  'requires_configuration' => 0,
                  'summary' => 'Keeps the folder list and message list synchronized.',
                  'details' => 'This plugin automatically synchronizes the folder list and the message list.  This can be used, when the folder list is configured to automatically refresh itself (SquirrelMail does this by default), to ensure that the message list always shows newest incoming messages.',
                  'requires_source_patch' => 0,
                  'required_plugins' => array(),
               );

}



/**
  * Returns version info about this plugin
  *
  */
function folder_synch_version()
{
   $info = folder_synch_info();
   return $info['version'];
}



/**
  * Show options on folder preferences page
  *
  */
function folder_synch_display_options_stub()
{
   include_once(SM_PATH . 'plugins/folder_synch/functions.php');
   folder_synch_display_options();
}



/** 
  * Insert needed script on folder list page
  *
  */
function folder_synch_insert_folder_list_script_stub()
{
   include_once(SM_PATH . 'plugins/folder_synch/functions.php');
   folder_synch_insert_folder_list_script();
}



/** 
  * Insert needed script on message list page
  *
  */
function folder_synch_insert_message_list_script_stub()
{
   include_once(SM_PATH . 'plugins/folder_synch/functions.php');
   folder_synch_insert_message_list_script();
}



/** 
  * Insert needed script on message read page
  *
  */
function folder_synch_insert_message_read_script_stub()
{
   include_once(SM_PATH . 'plugins/folder_synch/functions.php');
   folder_synch_insert_message_read_script();
}



