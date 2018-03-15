<?php

/**
  * SquirrelMail Custom From Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage custom_from
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_custom_from() 
{

   global $squirrelmail_plugin_hooks;


   // Show options on display preferences page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_display']['custom_from']
      = 'custom_from_display_options_stub';


   // Make SquirrelMail use the correct From address
   //
   $squirrelmail_plugin_hooks['abook_init']['custom_from']
      = 'cf_before_send_stub';


   // Begin buffering so we can modify compose page content later
   //
   $squirrelmail_plugin_hooks['compose_form']['custom_from']
      = 'cf_start_buffering_stub';


   // Modify the compose page, adding our custom input
   //
   $squirrelmail_plugin_hooks['compose_bottom']['custom_from']
      = 'cf_modify_compose_page_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['custom_from']
      = 'custom_from_check_configuration_stub';

}



/**
  * Returns info about this plugin
  *
  */
function custom_from_info()
{ 
  
   return array(
                  'english_name' => 'Custom From',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                  ),
                  'version' => '2.0',
                  'required_sm_version' => '1.4.0',
                  'requires_configuration' => 0,
                  'summary' => 'Allows users to directly edit the From: field when composing.',
                  'details' => 'This plugin adds a "From:" text field to the compose screen.  This allows the user to specify an arbitrary From address (that will also be used as the "Reply-To").  If this "From:" field is left blank, the normal identity selector (just above it) is used.<br /><br />The administrator may provide a list of users who are allowed to use this feature, perhaps making this a super-user or administrative tool.',
                  'requires_source_patch' => 0,
                  'required_plugins' => array(),
               );
   
}



/**
  * Returns version info about this plugin
  *
  */
function custom_from_version()
{
   $info = custom_from_info();
   return $info['version'];
}



/**
  * Show options on display preferences page
  *
  */
function custom_from_display_options_stub()
{
   include_once(SM_PATH . 'plugins/custom_from/functions.php');
   custom_from_display_options();
}



/**
  * Make SquirrelMail use the correct From address
  *
  * Fire on the abook_init hook so that we can change
  * incoming data that the compose page makes use of
  *
  */
function cf_before_send_stub()
{
   include_once(SM_PATH . 'plugins/custom_from/functions.php');
   cf_before_send();
}



/**
  * Begin buffering so we can modify compose page content later
  *
  */
function cf_start_buffering_stub()
{
   include_once(SM_PATH . 'plugins/custom_from/functions.php');
   cf_start_buffering();
}



/**
  * Modify the compose page, adding our custom input
  *
  */
function cf_modify_compose_page_stub()
{
   include_once(SM_PATH . 'plugins/custom_from/functions.php');
   cf_modify_compose_page();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function custom_from_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/custom_from/functions.php');
   return custom_from_check_configuration();
}



