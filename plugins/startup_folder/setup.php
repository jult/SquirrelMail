<?php

/**
  * SquirrelMail Spam Buttons Plugin
  * Copyright (C) 2003-2004 Scott Heavner <sdh@po.cwru.edu>
  * Copyright (c) 2004-2007 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage startup_folder
  *
  */


/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_startup_folder() 
{

   global $squirrelmail_plugin_hooks;


   // override startup location
   //
   $squirrelmail_plugin_hooks['webmail_top']['startup_folder']
      = 'startup_folder_override_stub';


   // show options on folder preferences page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_folder']['startup_folder']
      = 'startup_folder_options_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['startup_folder']
      = 'startup_folder_check_configuration_stub';

}



/**
  * Returns info about this plugin
  *
  */
function startup_folder_info()
{

   return array(
                 'english_name' => 'Startup Folder',
                 'authors' => array(
                    'Scott Heavner' => array(
                       'email' => 'sdh@po.cwru.edu',
                       'sm_site_username' => '',
                    ),
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                 ),
                 'version' => '2.1',
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 0,
                 'requires_source_patch' => 0,
                 'summary' => 'Allows the user to change the mail folder that is displayed when first logging in.',
                 'details' => 'This plugin allows the user to change the mail folder that is displayed when first logging in.  It also allows certain plugin pages to be displayed instead of a mail folder.',
                 'per_version_requirements' => array(
                    '1.5.2' => array(
                       'required_plugins' => array()
                    ),
                    '1.5.0' => array(
                       'required_plugins' => array(
                          'compatibility' => array(
                             'version' => '2.0.7',
                             'activate' => FALSE,
                          )
                       )
                    ),
                    '1.4.10' => array(
                       'required_plugins' => array()
                    ),
                    '1.4.0' => array(
                       'required_plugins' => array(
                          'compatibility' => array(
                             'version' => '2.0.7',
                             'activate' => FALSE,
                          )
                       )
                    ),
                 ),
               );

}  
   
   
      
/**
  * Returns version info about this plugin
  *
  */
function startup_folder_version()
{
   $info = startup_folder_info();
   return $info['version'];
} 



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function startup_folder_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/startup_folder/functions.php');
   return startup_folder_check_configuration();
}



/**
  * Override Startup Location
  *
  */
function startup_folder_override_stub() 
{
   include_once(SM_PATH . 'plugins/startup_folder/functions.php');
   startup_folder_override();
}



/**
  * Display user configuration options on folder preferences page
  *
  */
function startup_folder_options_stub() 
{
   include_once(SM_PATH . 'plugins/startup_folder/functions.php');
   startup_folder_options();
}



