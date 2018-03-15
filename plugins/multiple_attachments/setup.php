<?php

/**
  * SquirrelMail Multiple Attachments Plugin
  *
  * Copyright (c) 2012-2012 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage multiple_attachments
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_multiple_attachments() 
{

   global $squirrelmail_plugin_hooks;


   // 1.4.x - 1.5.0:  options go on display options page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_display']['multiple_attachments']
      = 'ma_show_options_stub';


   // 1.5.1 and up:  options go on compose options page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_compose']['multiple_attachments']
      = 'ma_show_options_stub';


   // Start output caching so we can alter it before sending to client
   //
   $squirrelmail_plugin_hooks['compose_form']['multiple_attachments']
      = 'ma_start_output_cache_stub';


   // Add multiple attachment inputs to output for SquirrelMail versions 1.4.x
   //
   $squirrelmail_plugin_hooks['compose_bottom']['multiple_attachments']
      = 'ma_add_attachment_inputs_1_4_stub';


   // Add multiple attachment inputs directly to template
   // output for SquirrelMail versions 1.5.x
   //
   $squirrelmail_plugin_hooks['template_construct_compose_attachments.tpl']['multiple_attachments']
      = 'ma_add_attachment_inputs_1_5_stub';


   // Make sure multiple attachments aren't skipped if first upload input is blank
   //
   $squirrelmail_plugin_hooks['prefs_backend']['multiple_attachments']
      = 'ma_multiple_uploads_fix_stub';


   // Make sure multiple attachments aren't skipped if first upload input is blank
   //
// NOTE NOTE NOTE NOTE -----> For SquirrelMail version 1.4.x, it's more
// appropriate to use the loading_constants hook, since it's only executed
// once, but 1.5.x doesn't have that hook, and we have to use prefs_backend
// (above).  That hook is executed a number of times, which is less ideal.
// If you really want to tweak performance in 1.4.x, uncomment the following
// and comment out the prefs_backend hook registration above
//   $squirrelmail_plugin_hooks['loading_constants']['multiple_attachments']
//      = 'ma_multiple_uploads_fix_stub';


   // Handle multiple attachment file uploads
   //
   $squirrelmail_plugin_hooks['save_attached_files']['multiple_attachments']
      = 'ma_handle_multiple_uploads_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['multiple_attachments']
      = 'multiple_attachments_check_configuration_stub';

}	



/**
  * Returns info about this plugin
  *
  */
function multiple_attachments_info()
{

   return array(
                 'english_name' => 'Multiple Attachments',
                 'authors' => array(
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                 ),
                 'version' => '1.0',
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 0,
                 'requires_source_patch' => 1,
                 'summary' => 'Adds multiple attachment uploads to the compose screen.',
                 'details' => 'This plugin adds the ability to place more than one attachment upload input on the compose screen and optionally add more on the fly.',
                 'required_plugins' => array(
                    'compatibility' => array(
                       'version' => '2.0.5',
                       'activate' => FALSE,
                    )
                 )
               );

}



/**
  * Returns version info about this plugin
  *
  */
function multiple_attachments_version()
{
   $info = multiple_attachments_info();
   return $info['version'];
}



/**
  * Integrate options into SM options page
  * 
  */
function ma_show_options_stub($args)
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   ma_show_options($args);
}



/**
  * Start output caching so we can alter it before sending to client
  * 
  */
function ma_start_output_cache_stub()
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   ma_start_output_cache();
}



/**
  * Add multiple attachment inputs to output for SquirrelMail versions 1.5.x
  * 
  */
function ma_add_attachment_inputs_1_5_stub()
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   return ma_add_attachment_inputs_1_5();
}



/**
  * Add multiple attachment inputs to output for SquirrelMail versions 1.4.x
  * 
  */
function ma_add_attachment_inputs_1_4_stub()
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   ma_add_attachment_inputs_1_4();
}



/**
  * Make sure multiple attachments aren't skipped if first upload input is blank
  *
  */
function ma_multiple_uploads_fix_stub()
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   ma_multiple_uploads_fix();
}



/**
  * Handle multiple attachment file uploads
  * 
  * @return boolean TRUE only if an error occurred trying
  *                 to process an attachment file upload;
  *                 FALSE under normal operation
  *
  */
function ma_handle_multiple_uploads_stub($args)
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   return ma_handle_multiple_uploads($args);
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function multiple_attachments_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/multiple_attachments/functions.php');
   return multiple_attachments_check_configuration();
}



