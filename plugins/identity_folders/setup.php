<?php

/**
  * SquirrelMail Identity Folders Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage identity_folders
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_identity_folders()
{  

   global $squirrelmail_plugin_hooks;

   
   // Override custom sent folder (requires Variable Sent Folder plugin)
   //
   $squirrelmail_plugin_hooks['custom_sent_folder']['identity_folders']
      = 'if_custom_sent_folder_stub';


   // Add folder selector on advanced identities page
   //
   $squirrelmail_plugin_hooks['options_identities_table']['identity_folders']
      = 'if_add_identity_inputs_stub';


   // Process server configuration inputs on advanced identities page
   //
   $squirrelmail_plugin_hooks['options_identities_process']['identity_folders']
      = 'if_process_identity_inputs_stub';


   // Synchronize plugin preference settings with identities reorder
   //
   $squirrelmail_plugin_hooks['options_identities_renumber']['identity_folders']
      = 'if_renumber_identites_stub';

}



/**
  * Returns info about this plugin
  *
  */
function identity_folders_info()
{

   return array(
                  'english_name' => 'Identity Folders',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                  ),
                  'version' => '1.0',
                  'required_sm_version' => '1.4.0',
                  'requires_configuration' => 0,
                  'required_plugins' => array(
                     'variable_sent_folder' => array(
                        'version' => '1.0',
                        'activate' => TRUE,
                     ),
                  ),
                  'summary' => 'Allows users to specify a folder that will serve as the default sent message location when replying to messages sent to any of their identities.',
                  'details' => 'This plugin allows users to specify a folder that will serve as the default sent message location when replying to messages sent to any of the identities configured on the advanced identities screen.  Keep in mind that this plugin only sets the default folder selection for the custom sent folder drop-down list (which is provided by the Variable Sent Folder plugin), and only does so when replying to messages that were sent to a pre-configured identity.  The Per Recipient Sent Folders plugin may be more useful depending on your needs.',
                  'requires_source_patch' => 0,
                  'per_version_requirements' => array(),
               );

}  
   
      
      
/**
  * Returns version info about this plugin
  *
  */ 
function identity_folders_version()
{                   
   $info = identity_folders_info();
   return $info['version'];
}  
   
      
      
/**
  * Override custom sent folder (requires Variable Sent Folder plugin)
  *
  */ 
function if_custom_sent_folder_stub($args)
{
   include_once(SM_PATH . 'plugins/identity_folders/functions.php'); 
   return if_custom_sent_folder($args);
}



/**
  * Add folder selector on advanced identities page
  *
  */
function if_add_identity_inputs_stub($args)
{
   include_once(SM_PATH . 'plugins/identity_folders/options.php');
   return if_add_identity_inputs($args);
}



/**
  * Process server configuration inputs on advanced identities page
  *
  */
function if_process_identity_inputs_stub($args)
{
   include_once(SM_PATH . 'plugins/identity_folders/options.php');
   if_process_identity_inputs($args);
}



/**
  * Synchronize plugin preference settings with identities reorder
  *
  */
function if_renumber_identites_stub($args)
{
   include_once(SM_PATH . 'plugins/identity_folders/options.php');
   if_renumber_identites($args);
}



