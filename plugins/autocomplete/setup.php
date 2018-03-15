<?php

/**
  * SquirrelMail Autocomplete Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2005 Graham <gsm-smpi@soundclashchampions.com>
  * Copyright (c) 2001 Tyler Akins
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage autocomplete
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_autocomplete()
{

   global $squirrelmail_plugin_hooks;


   // 1.4.x - 1.5.0:  options go on display options page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_display']['autocomplete']
      = 'ac_show_options_stub';


   // 1.5.1 and up:  options go on compose options page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_compose']['autocomplete']
      = 'ac_show_options_stub';


   // 1.4.x - 1.5.1:  Insert JavaScript for Autocomplete functionality
   //
   $squirrelmail_plugin_hooks['compose_bottom']['autocomplete']
      = 'ac_compose_bottom_stub';


   // 1.5.2 and up:  Insert JavaScript for for Autocomplete functionality
   //
   $squirrelmail_plugin_hooks['template_construct_compose_form_close.tpl']['autocomplete']
      = 'ac_compose_bottom_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['autocomplete']
      = 'autocomplete_check_configuration_stub';

}   



/**
  * Returns info about this plugin
  *
  */
function autocomplete_info()
{

   return array(
                 'english_name' => 'Autocomplete',
                 'authors' => array(
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                    'Graham' => array(
                       'email' => 'gsm-smpi@soundclashchampions.com',
                    ),
                    'Tyler Akins' => array(),
                 ),
                 'version' => '3.0',
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 0,
                 'requires_source_patch' => 0,
                 'summary' => 'Searches user contacts while the user types into the To/Cc/Bcc fields.',
                 'details' => 'This plugin searches user contacts as the user types into the To/Cc/Bcc fields on the compose screen, displaying those that match for quick selection.  Various forms of matching and other behaviors are configurable by the administrator and/or each user.',
                 'required_plugins' => array(
                    'compatibility' => array(
                       'version' => '2.0.7',
                       'activate' => FALSE,
                    )
                 )
               );

}



/**
  * Returns version info about this plugin
  *
  */
function autocomplete_version()
{
   $info = autocomplete_info();
   return $info['version'];
}



/**
  * Integrate options into SM options page
  *
  */
function ac_show_options_stub($args)
{
   include_once(SM_PATH . 'plugins/autocomplete/functions.php');
   include_once(SM_PATH . 'plugins/autocomplete/main.php');
   ac_show_options($args);
}



/**
  * Insert JavaScript for Autocomplete functionality
  *
  */
function ac_compose_bottom_stub()
{
   include_once(SM_PATH . 'plugins/autocomplete/functions.php');
   include_once(SM_PATH . 'plugins/autocomplete/main.php');
   return ac_compose_bottom();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  * 
  */
function autocomplete_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/autocomplete/functions.php');
   include_once(SM_PATH . 'plugins/autocomplete/main.php');
   return autocomplete_check_configuration();
}



