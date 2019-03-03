<?php

/**
  * SquirrelMail Variable Sent Folder Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002-2003 Robin Rainton <robin@rainton.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage variable_sent_folder
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_variable_sent_folder()
{  

   global $squirrelmail_plugin_hooks;


   // Show the custom folder selector on the compose screen (1.4.x)
   //
   $squirrelmail_plugin_hooks['compose_button_row']['variable_sent_folder']
      = 'variable_sent_folder_show_custom_sent_selector_stub';


   // Show the custom folder selector on the compose screen (1.5.2+)
   //
   $squirrelmail_plugin_hooks['template_construct_compose_buttons.tpl']['variable_sent_folder']
      = 'variable_sent_folder_show_custom_sent_selector_stub';


   // Change the folder sent messages will be saved to
   //
   $squirrelmail_plugin_hooks['compose_send']['variable_sent_folder']
      = 'variable_sent_folder_override_sent_folder_stub';


   // In the case of replies, move the original message
   // to the sent folder if needed
   //
   $squirrelmail_plugin_hooks['compose_send_after']['variable_sent_folder']
      = 'variable_sent_folder_move_orig_to_sent_folder_stub';


   // Show options on folder preferences page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_folder']['variable_sent_folder']
      = 'variable_sent_folder_options_stub';


   // Make sure this plugin runs after the sent_subfolders plugin
   // on the compose_send hook (loading_constants is not a good
   // place for this, but other choices aren't better: prefs_backend,
   // get_pref, abook_init
   //
   $squirrelmail_plugin_hooks['loading_constants']['variable_sent_folder']
      = 'variable_sent_folder_reorder_compose_send_hook_stub';


   // Configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['variable_sent_folder']
      = 'variable_sent_folder_check_configuration_stub';

}



/**
  * Returns info about this plugin
  *
  */
function variable_sent_folder_info()
{  

   return array(  
                  'english_name' => 'Variable Sent Folder',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                     'Robin Rainton' => array(
                        'email' => 'robin@rainton.com',
                     ),
                  ),
                  'version' => '1.0',
                  'required_sm_version' => '1.4.6', // uses compose_send_after hook
                  'requires_configuration' => 0,
                  'summary' => 'Allows sent messages to be saved in the folder of one\'s choice.',
                  'details' => 'This plugin places a drop-down selector on the message compose screen that allows users to select which folder to save the sent message into.  When replying, it also allows the user to move the original message to the same folder if desired.<br /><br />The default value for the folder selection drop-down can be modified by other plugins, such as the Per Recipient Sent Folders plugin.',
                  'requires_source_patch' => 0,
                  // we are using reposition_plugin_on_hook()
                  // so Compatibility is always required
                  'required_plugins' => array(
                     'compatibility' => array(
                        'version' => '2.0.5',
                        'activate' => FALSE,
                     )
                  ),
                  'per_version_requirements' => array(),
               );

}



/**
  * Returns version info about this plugin
  *
  */
function variable_sent_folder_version()
{
   $info = variable_sent_folder_info();
   return $info['version'];
}



/**
  * Show the custom folder selector on the compose screen
  *                       
  */ 
function variable_sent_folder_show_custom_sent_selector_stub()
{                   
   include_once(SM_PATH . 'plugins/variable_sent_folder/functions.php');
   return variable_sent_folder_show_custom_sent_selector();
}



/**
  * Change the folder sent messages will be saved to
  *                       
  */ 
function variable_sent_folder_override_sent_folder_stub()
{                   
   include_once(SM_PATH . 'plugins/variable_sent_folder/functions.php');
   variable_sent_folder_override_sent_folder();
}



/**
  * In the case of replies, move the original message to the
  * sent folder if needed
  *
  */
function variable_sent_folder_move_orig_to_sent_folder_stub()
{
   include_once(SM_PATH . 'plugins/variable_sent_folder/functions.php');
   variable_sent_folder_move_orig_to_sent_folder();
}



/**
  * Display user configuration options on folder preferences page
  *
  */
function variable_sent_folder_options_stub()
{
   include_once(SM_PATH . 'plugins/variable_sent_folder/functions.php');
   variable_sent_folder_options();
}



/**
  * Make sure this plugin runs after the sent_subfolders plugin
  * on the compose_send hook (loading_constants is not a good
  * place for this, but other choices aren't better: prefs_backend,
  * get_pref, abook_init
  *
  */
function variable_sent_folder_reorder_compose_send_hook_stub()
{
   include_once(SM_PATH . 'plugins/variable_sent_folder/reorder_compose_send_hook.php');
   variable_sent_folder_reorder_compose_send_hook();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function variable_sent_folder_check_configuration_stub()
{  
   include_once(SM_PATH . 'plugins/variable_sent_folder/functions.php');
   return variable_sent_folder_check_configuration();
}



