<?php

/**
  * SquirrelMail Change SQL Password Plugin
  * Copyright (C) 2001-2002 Tyler Akins
  *               2002 Thijs Kinkhorst <kink@users.sourceforge.net>
  *               2002-2005 Paul Lesneiwski <paul@openguild.net>
  * This program is licensed under GPL. See COPYING for details
  *
  * @package plugins
  * @subpackage Change SQL Password
  *
  */


/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_change_sqlpass() 
{
   global $squirrelmail_plugin_hooks;

   $squirrelmail_plugin_hooks['webmail_bottom']['change_sqlpass']          = 'csp_check_for_https';

//   $squirrelmail_plugin_hooks['optpage_set_loadinfo']['change_sqlpass']    = 'csp_show_success';
   $squirrelmail_plugin_hooks['options_save']['change_sqlpass']            = 'csp_show_success';

   $squirrelmail_plugin_hooks['optpage_register_block']['change_sqlpass']  = 'csp_show_optblock';
   $squirrelmail_plugin_hooks['right_main_after_header']['change_sqlpass'] = 'csp_password_force';

}



/** @ignore */
if (!defined('SM_PATH'))
   define('SM_PATH', '../');



/**
  * Returns version info about this plugin
  *
  */
function change_sqlpass_version() 
{

   return '3.3-1.2';

}



/**
  * Check if session is HTTPS originally
  *
  * Called in webmail_bottom so the plugins that manage SSL logins 
  * can do their thing in webmail_top, but before this plugin possibly
  * redirects the page during the right_main_after_header hook.
  *
  */
function csp_check_for_https()
{

   include_once(SM_PATH . 'plugins/change_sqlpass/functions.php');
   csp_check_for_https_do();

}



/**
  * Forces user into change password screen if needed
  *
  */
function csp_password_force()
{

   include_once(SM_PATH . 'plugins/change_sqlpass/functions.php');
   csp_password_force_do();

}



/**
  * Show change password option section on options page
  *
  */
function csp_show_optblock()
{

   include_once(SM_PATH . 'plugins/change_sqlpass/functions.php');
   csp_show_optblock_do();

}



/**
  * Shows status message at top of options screen
  * after password was changed
  *
  */
function csp_show_success() 
{

   include_once(SM_PATH . 'plugins/change_sqlpass/functions.php');
   csp_show_success_do();

}



?>
