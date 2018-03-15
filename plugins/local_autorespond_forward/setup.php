<?php

/**
  * SquirrelMail Local User Autoresponder and Mail Forwarder Plugin
  * Copyright (c) 2004-2009 Jonathan Bayer <jbayer@spamcop.net>,
  *                         Paul Lesniewski <paul@squirrelmail.org>,
  *                         Dan Astoorian 
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage local_autorespond_forward
  *
  */


   
/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_local_autorespond_forward() 
{

   global $squirrelmail_plugin_hooks;

   $squirrelmail_plugin_hooks['optpage_register_block']['local_autorespond_forward'] 
      = 'laf_option_link';

   $squirrelmail_plugin_hooks['configtest']['local_autorespond_forward']
      = 'local_autorespond_forward_check_configuration';

}



/**
  * Returns info about this plugin
  *
  */
function local_autorespond_forward_info()
{

   return array(
                 'english_name' => 'Local User Autoresponder and Mail Forwarder',
                 'authors' => array(
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                    'Jonathan Bayer' => array(
                       'email' => 'jbayer@spamcop.net',
                    ),
                    'Dan Astoorian' => array(
                    ),
                 ),
                 'version' => '3.0.1',
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 1,
                 'requires_source_patch' => 0,
                 'summary' => 'Allows you to create an auto-reply message to all incoming email while you\'re away.  Also allows you to forward incoming email to other addresses.',
                 'details' => 'This plugin allows users to set an auto-reply message to incoming email, which is most commonly used to notify the sender of one\'s absence.  This plugin also allows users to specify that mail be forwarded to (an)other email address(es).  You can use the autoresponder or forwarding components independently of one another, or use them both.<br /><br />This plugin is limited to use with mail systems where mail users have real local accounts, or at least where users have FTP access to "home" directories that the mail server knows about when delivering mail.  Please note that this plugin is ONLY a front-end for configuring autoreplies and/or forwarding addresses and IS NOT responsible for executing those functionalities during the mail delivery process.  Before installing this plugin, you need to have a fully functioning autoresponder/forwarder system in place on your mail server.<br /><br />This plugin is capable of managing vacation and/or .forward files on your server via either FTP or a local SUID program.  If you use the SUID program, it will only work on a local system (or possibly via a NFS mount or similar).  Also supported are lookups of FTP server name in LDAP.',
                 'per_version_requirements' => array(
                    '1.5.2' => array(
                       'required_plugins' => array()
                    ),
                    '1.4.0' => array(
                       'required_plugins' => array(
                          'compatibility' => array(
                             'version' => '2.0.14',
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
function local_autorespond_forward_version()
{

   $info = local_autorespond_forward_info();
   return $info['version'];
}



/**
  * Inserts link to the configuration page
  * in main SM options page
  *
  */
function laf_option_link() 
{

   include_once(SM_PATH . 'plugins/local_autorespond_forward/functions.php');
   laf_option_link_do();

}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function local_autorespond_forward_check_configuration()
{

   include_once(SM_PATH . 'plugins/local_autorespond_forward/functions.php');
   return local_autorespond_forward_check_configuration_do();

}



