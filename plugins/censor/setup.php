<?php

/**
  * SquirrelMail Censor Plugin
  * Copyright (c) 2007 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage censor
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_censor() 
{

   global $squirrelmail_plugin_hooks;

   $squirrelmail_plugin_hooks['configtest']['censor']
      = 'censor_configtest';

   $squirrelmail_plugin_hooks['compose_send']['censor'] 
      = 'censor_compose_form';

   $squirrelmail_plugin_hooks['generic_header']['censor']
      = 'censor_error_on_compose_screen';

}



/**
  * Returns info about this plugin
  *
  */
function censor_info()
{

   return array(
                  'english_name' => 'Censor',
                  'authors' => array(
                     'Paul Lesniewski' => array(
                        'email' => 'paul@squirrelmail.org',
                        'sm_site_username' => 'pdontthink',
                     ),
                  ),
                  'version' => '1.0',
                  'required_sm_version' => '1.4.2',
                  'requires_configuration' => 0,
                  'requires_source_patch' => 0,
                  'summary' => 'Allows censoring of emails before they are sent.',
                  'details' => 'This plugin allows you to create a list of words that are considered unacceptable in outgoing emails.  Emails containing such words can be blocked, or can have such words removed before sending.',
                  'per_version_requirements' => array(
                     '1.5.2' => array(
                        'required_plugins' => array(),
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
                        'required_plugins' => array(),
                     ),
                     '1.2' => array(
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
function censor_version() 
{

   $info = censor_info();
   return $info['version'];

}



/**
  * Validate that this plugin is configured correctly
  *
  */
function censor_configtest()
{

   include_once(SM_PATH . 'plugins/censor/functions.php');
   censor_configtest_do();

}



/**
  * Checks outgoing mail content for banned words.
  *
  */
function censor_compose_form(&$args)
{

   include_once(SM_PATH . 'plugins/censor/functions.php');
   return censor_compose_form_do($args);

}



/**
  * Displays any errors that blocked email from
  * sending back on top of compose screen
  *
  */
function censor_error_on_compose_screen()
{

   include_once(SM_PATH . 'plugins/censor/functions.php');
   censor_error_on_compose_screen_do();

}



