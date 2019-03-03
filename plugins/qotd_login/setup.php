<?php

/**
  * SquirrelMail Quote of the Day at Login Plugin
  *
  * Copyright (c) 2003-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002 Tracy McKibben <tracy@mckibben.d2g.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage qotd_login
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_qotd_login()
{

   global $squirrelmail_plugin_hooks;


   // show quote on login page
   //
   $squirrelmail_plugin_hooks['login_bottom']['qotd_login']
      = 'qotd_login_get_quote_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['qotd_login']
      = 'qotd_login_check_configuration_stub';

}



/**
  * Returns info about this plugin
  * 
  */
function qotd_login_info()
{

   return array(
                 'english_name' => 'Quote of the Day at Login',
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
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 0,
                 'summary' => 'Displays a random "quote of the day" on the login page',
                 'details' => 'This plugin displays a random "quote of the day" on the login page beneath the username/password fields.',
                 'requires_source_patch' => 0, 
               );

}



/**
  * Returns version info about this plugin
  *
  */
function qotd_login_version()
{
   $info = qotd_login_info();
   return $info['version'];
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function qotd_login_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/qotd_login/functions.php');
   return qotd_login_check_configuration();
}



/**
  * Show quote on login page
  *
  */
function qotd_login_get_quote_stub()
{
   include_once(SM_PATH . 'plugins/qotd_login/functions.php');
   qotd_login_get_quote();
}



