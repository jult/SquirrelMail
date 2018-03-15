<?php

/**
  * SquirrelMail Compose Extras Plugin
  *
  * Copyright (c) 2005-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003-2004 Justus Pendleton <justus@ryoohki.net>
  * Copyright (c) 2003 Bruce Richardson <itsbruce@uklinux.net>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage compose_extras
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_compose_extras() 
{

   global $squirrelmail_plugin_hooks;


   // 1.4.x - 1.5.0:  options go on display options page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_display']['compose_extras']
      = 'ce_show_options_stub';


   // 1.5.1 and up:  options go on compose options page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_compose']['compose_extras']
      = 'ce_show_options_stub';


   // 1.4.x - 1.5.1:  Insert JavaScript for tab fixes (and more)
   //
   $squirrelmail_plugin_hooks['compose_bottom']['compose_extras']
      = 'ce_compose_bottom_stub';


   // 1.5.2 and up:  Insert JavaScript for tab fixes (and more)
   //
   $squirrelmail_plugin_hooks['template_construct_compose_form_close.tpl']['compose_extras']
      = 'ce_compose_bottom_stub';


   // Adjusts body text as needed and add checks to the page
   // submit action (subject warning and prevent multiple form submit)
   //
   $squirrelmail_plugin_hooks['compose_form']['compose_extras']
      = 'ce_fix_body_stub';


   // Include JavaScript that accomplishes the rewrap functionality.
   // Also, make sure this plugin comes before all others on the
   // compose_form hook so it can prevent other onsubmit handlers
   // from firing multiple times.
   //
   $squirrelmail_plugin_hooks['generic_header']['compose_extras']
      = 'rewrap_add_script_stub';


   // Add the "Rewrap" button to the compose screen (1.4.x)
   //
   $squirrelmail_plugin_hooks['compose_button_row']['compose_extras']
      = 'rewrap_add_button_stub';


   // Add the "Rewrap" button to the compose screen (1.5.x)
   //
   $squirrelmail_plugin_hooks['template_construct_compose_buttons.tpl']['compose_extras']
      = 'rewrap_add_button_stub';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['compose_extras']
      = 'compose_extras_check_configuration_stub';

}	



/**
  * Returns info about this plugin
  *
  */
function compose_extras_info()
{

   return array(
                 'english_name' => 'Compose Extras',
                 'authors' => array(
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                    'Justus Pendleton' => array(
                       'email' => 'justus@ryoohki.net',
                    ),
                    'Bruce Richardson' => array(
                       'email' => 'itsbruce@uklinux.net',
                    ),
                 ),
                 'version' => '0.10',
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 0,
                 'requires_source_patch' => 0,
                 'summary' => 'Adds usability enhancements to the compose screen.',
                 'details' => 'This plugin addresses some usability issues with the SquirrelMail compose screen: the tab order can be changed so that the user does not have to tab through all the buttons between the subject line and the message body, access keys can be added to most common elements on the compose page, the user can choose to have a few blank lines inserted at the top of the message body for replies and forwards, the user can be prevented from clicking the "Send" button more than once, pressing Enter in any text field won\'t invoke any form action (such as auto-clicking the Signature button) and the user can choose to have buttons on the compose screen that remove reply citations and rewrap the message body text.',
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
function compose_extras_version()
{
   $info = compose_extras_info();
   return $info['version'];
}



/**
  * Integrate options into SM options page
  * 
  */
function ce_show_options_stub($args)
{
   include_once(SM_PATH . 'plugins/compose_extras/functions.php');
   ce_show_options($args);
}



/**
  * Inserts javascript for tab fixes (and more)
  * 
  */
function ce_compose_bottom_stub()
{
   include_once(SM_PATH . 'plugins/compose_extras/functions.php');
   return ce_compose_bottom();
}



/**
  * Adjusts body text as needed and add checks
  * to the page submit action (subject warning
  * and prevent multiple form submit)
  * 
  */
function ce_fix_body_stub()
{
   include_once(SM_PATH . 'plugins/compose_extras/functions.php');
   ce_fix_body();
}



/**
  * Include JavaScript that accomplishes the rewrap functionality.
  * Also, make sure this plugin comes before all others on the
  * compose_form hook so it can prevent other onsubmit handlers
  * from firing multiple times.
  *
  */
function rewrap_add_script_stub($args)
{
   include_once(SM_PATH . 'plugins/compose_extras/functions.php');
   rewrap_add_script($args);
}



/**
  * Add the "Rewrap" button to the compose screen
  *
  */
function rewrap_add_button_stub()
{
   include_once(SM_PATH . 'plugins/compose_extras/functions.php');
   return rewrap_add_button();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function compose_extras_check_configuration_stub()
{
   include_once(SM_PATH . 'plugins/compose_extras/functions.php');
   return compose_extras_check_configuration();
}



