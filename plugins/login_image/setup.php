<?php

/**
  * SquirrelMail Random Login Image Plugin
  *
  * Copyright (c) 2011-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002 Tracy McKibben <tracy@mckibben.d2g.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage login_image
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_login_image()
{

   global $squirrelmail_plugin_hooks;


   // show login image on login page (1.4.x)
   //
   $squirrelmail_plugin_hooks['login_top']['login_image']
      = 'show_login_image_stub';


   // show login image on login page (1.5.x)
   //
   $squirrelmail_plugin_hooks['template_construct_login.tpl']['login_image']
      = 'show_login_image_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['login_image']
      = 'login_image_check_configuration_stub';

}



/**
  * Returns info about this plugin
  *
  */
function login_image_info()
{

   return array(
                  'english_name' => 'Random Login Image',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                     'Tracy McKibben' => array(
                        'email' => 'tracy@mckibben.d2g.com',
                     ),
                  ),
                  'version' => '1.0',
                  'required_sm_version' => '0.5',
                  'requires_configuration' => 0,
                  'summary' => 'Displays random images on the login page',
                  'details' => 'This plugin chooses an image randomly from one of several possible sources to display on the login page.  Both local and remote image sources are supported, all of which may be configured by the administrator if desired.  The image produced by this plugin may be used to replace the default SquirrelMail "org logo" or may be displayed in addition to (above) it.',
                  'requires_source_patch' => 0,
                  'per_version_requirements' => array(
                     '1.5.2' => array(
                        'required_plugins' => array()
                     ),
                     '1.5.0' => array(
                        'required_plugins' => array(
                           'compatibility' => array(
                              'version' => '2.0.10',
                              'activate' => FALSE,
                           )
                        )
                     ),
                     '1.4.12' => array(
                        'required_plugins' => array()
                     ),
                     '0.5' => array(
                        'required_plugins' => array(
                           'compatibility' => array(
                              'version' => '2.0.10',
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
function login_image_version()
{
   $info = login_image_info();
   return $info['version'];
}



/**
  * Show login image on login page
  *
  */
function show_login_image_stub()
{
   include_once(SM_PATH . 'plugins/login_image/functions.php');
   return show_login_image();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function login_image_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/login_image/functions.php');
   return login_image_check_configuration();
}



