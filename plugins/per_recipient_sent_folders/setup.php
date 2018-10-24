<?php

/**
  * SquirrelMail Per Recipient Sent Folders Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage per_recipient_sent_folders
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_per_recipient_sent_folders()
{  

   global $squirrelmail_plugin_hooks;

   
   // Override custom sent folder (requires Variable Sent Folder plugin)
   //
   $squirrelmail_plugin_hooks['custom_sent_folder']['per_recipient_sent_folders']
      = 'prsf_custom_sent_folder_stub';

   
   // Detect the sent folder used when sending
   //
   $squirrelmail_plugin_hooks['compose_send_after']['per_recipient_sent_folders']
      = 'prsf_store_used_sent_folder_stub';


   // Display user configuration options on folder preferences page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_folder']['per_recipient_sent_folders']
      = 'prsf_options_stub';


   // Configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['per_recipient_sent_folders']
      = 'prsf_check_configuration_stub';

}



/**
  * Returns info about this plugin
  *
  */
function per_recipient_sent_folders_info()
{

   return array(
                  'english_name' => 'Per Recipient Sent Folders',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                  ),
                  'version' => '1.0.1',
                  'required_sm_version' => '1.4.23',
                  'requires_configuration' => 0,
                  'summary' => 'Associates recipients with custom sent folder selections.',
                  'details' => 'This plugin helps associate recipients with the folders that messages sent to them should be stored in.  When replying to such a recipient, the custom sent folder selection (which is provided by the Variable Sent Folder plugin) is defaulted to the appropriate folder.  Recipient-to-folder associations can be created manually on the Folder Preferences screen or automatically by remembering what sent folder was used when sending messages.',
                  'requires_source_patch' => 0,
                  'per_version_requirements' => array(),
                  'required_plugins' => array(
                     'variable_sent_folder' => array(
                        'version' => '1.0',
                        'activate' => TRUE,
                     ),
                  ),
               );

}  
   
      
      
/**
  * Returns version info about this plugin
  *
  */ 
function per_recipient_sent_folders_version()
{                   
   $info = per_recipient_sent_folders_info();
   return $info['version'];
}  
   
      
      
/**
  * Override custom sent folder (requires Variable Sent Folder plugin)
  *
  */ 
function prsf_custom_sent_folder_stub()
{
   include_once(SM_PATH . 'plugins/per_recipient_sent_folders/functions.php'); 
   prsf_custom_sent_folder();
}



/**
  * Detect the sent folder used when sending
  *
  */ 
function prsf_store_used_sent_folder_stub()
{
   include_once(SM_PATH . 'plugins/per_recipient_sent_folders/functions.php'); 
   prsf_store_used_sent_folder();
}



/**
  * Display user configuration options on folder preferences page
  *
  */ 
function prsf_options_stub()
{
   include_once(SM_PATH . 'plugins/per_recipient_sent_folders/options.php'); 
   prsf_options();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function prsf_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/per_recipient_sent_folders/functions.php');
   return prsf_check_configuration();
}



