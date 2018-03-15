<?php


/**
  * SquirrelMail Quick Save Plugin
  * Copyright (c) 2001-2002 Ray Black <allah@accessnode.net>
  * Copyright (c) 2003-2007 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage quicksave
  *
  */


/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_quicksave()
{

   global $squirrelmail_plugin_hooks;

   $squirrelmail_plugin_hooks['compose_bottom']['quicksave']
      = 'quicksave_compose_functions';
   
   $squirrelmail_plugin_hooks['generic_header']['quicksave']
      = 'quicksave_clear';
   
   $squirrelmail_plugin_hooks['configtest']['quicksave']
      = 'quicksave_check_configuration';



   // SquirrelMail 1.4.x (although the first two hooks here 
   // also have a touch of 1.5.x code in them too)
   //
   $squirrelmail_plugin_hooks['right_main_after_header']['quicksave']
      = 'quicksave_check_recovery_upon_login';

   $squirrelmail_plugin_hooks['compose_send']['quicksave']
      = 'quicksave_message_sent';

   $squirrelmail_plugin_hooks['compose_button_row']['quicksave']
      = 'quicksave_cancel_button';

   $squirrelmail_plugin_hooks['optpage_loadhook_display']['quicksave']
      = 'quicksave_options_14';



   // SquirrelMail 1.5.x
   //
   $squirrelmail_plugin_hooks['template_construct_motd.tpl']['quicksave']
      = 'quicksave_check_recovery_upon_login';

   $squirrelmail_plugin_hooks['compose_send_after']['quicksave']
      = 'quicksave_message_sent';

   $squirrelmail_plugin_hooks ['template_construct_compose_buttons.tpl']['quicksave']
      = 'quicksave_cancel_button';

   $squirrelmail_plugin_hooks['optpage_loadhook_compose']['quicksave']
      = 'quicksave_options_15';

}



/** @ignore */
if (!defined('SM_PATH'))
   define('SM_PATH', '../');



/**
  * Returns info about this plugin
  *
  */
function quicksave_info()
{

   return array(
             'english_name' => 'Quick Save',
             'authors' => array(
                'Paul Lesniewski' => array(
                   'email' => 'paul@squirrelmail.org',
                   'sm_site_username' => 'pdontthink',
                ),
             ),
             'summary' => 'Automatically saves messages as they are being composed to prevent accidental loss due to leaving the compose screen or browser/computer crashes.',
             'details' => 'This plugin automatically saves messages as they are being composed in order to prevent accidental loss of message content due to having browsed away from the compose screen or more serious problems such as browser or computer crashes.  When a message appears to have been lost and is available for recovery, the user will be prompted about whether or not the recovery should proceed.',
             'version' => '2.4.2',
             'required_sm_version' => '1.2.9',
             'requires_configuration' => 0,
             'requires_source_patch' => 0,
// NOTE: could just require Compatibility 2.0.7 for all SM versions,
//       but the following is intended as an example for how plugin
//       authors can indicate different requirements for different 
//       SM versions.  Also note that other requirements such as
//       "requires_configuration" may also be overridden in the same
//       fashion.  
//       FYI, when SM looks for one of these requirement values, it 
//       takes the highest one it can find (so for example, when 
//       running SM 1.5.2, and the needed requirement value is only 
//       listed here under 1.2.9 and 1.4.10, it will take the value 
//       from the 1.4.10 settings), or will use the "global" value
//       above if not found herein.
             'per_version_requirements' => array(
                '1.5.2' => array(
                   'requires_source_patch' => 0,
                   'required_plugins' => array(
                      'compatibility' => array(
                         'version' => '2.0.5',
                         'activate' => FALSE,
                      )
                   ) 
                ),
                '1.5.0' => array(
                   'requires_source_patch' => 0,
                   'required_plugins' => array(
                      'compatibility' => array(
                         'version' => '2.0.7',
                         'activate' => FALSE,
                      )
                   ) 
                ),
                '1.4.10' => array(
                   'requires_source_patch' => 0,
                   'required_plugins' => array(
                      'compatibility' => array(
                         'version' => '2.0.5',
                         'activate' => FALSE,
                      )
                   ) 
                ),
                '1.2.9' => array(
                   'requires_source_patch' => 0,
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
function quicksave_version()
{

   $info = quicksave_info();
   return $info['version'];

}



/**
  * Determine whether or not to offer message 
  * recovery upon login
  *
  */
function quicksave_check_recovery_upon_login($args)
{

   include_once(SM_PATH . 'plugins/quicksave/login_functions.php');
   return quicksave_check_recovery_upon_login_do($args);

}



/**
  * Present user quicksave preferences on display/compose
  * options page for SquirrelMail 1.5.x
  *
  */
function quicksave_options_15()
{

   include_once(SM_PATH . 'plugins/quicksave/functions.php');
   quicksave_options_15_do();

}



/**
  * Present user quicksave preferences on display/compose
  * options page for SquirrelMail 1.4.x
  *
  */
function quicksave_options_14()
{

   include_once(SM_PATH . 'plugins/quicksave/functions.php');
   quicksave_options_14_do();

}



/**
  * Set flag indicating message was sent
  *
  */
function quicksave_message_sent($args) 
{

   include_once(SM_PATH . 'plugins/quicksave/functions.php');
   quicksave_message_sent_do($args);

}



/**
  * Turn off quicksave if "message sent" flag is set
  *
  */
function quicksave_clear() 
{

   include_once(SM_PATH . 'plugins/quicksave/functions.php');
   quicksave_clear_do();

}



/**
  * Add "cancel" button to the compose page
  *
  */
function quicksave_cancel_button()
{

   include_once(SM_PATH . 'plugins/quicksave/functions.php');
   return quicksave_cancel_button_do();

}



/**
  * Add the code that does all the work onto the compose page
  *
  */
function quicksave_compose_functions()
{

   include_once(SM_PATH . 'plugins/quicksave/functions.php');
   quicksave_compose_functions_do();

}



/**
  * Validate that this plugin is configured correctly
  *
  */
function quicksave_check_configuration()
{

   include_once(SM_PATH . 'plugins/quicksave/common_functions.php');
   return quicksave_check_configuration_do();

}



