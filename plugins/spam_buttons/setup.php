<?php

/**
  * SquirrelMail Spam Buttons Plugin
  * Copyright (c) 2005-2009 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage spam_buttons
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_spam_buttons()
{

   global $squirrelmail_plugin_hooks;


   // SM 1.4.x - display buttons on message list
   //
   $squirrelmail_plugin_hooks['mailbox_display_buttons']['spam_buttons']
      = 'sb_mailbox_list_buttons';


   // SM 1.5.x - display buttons on message list
   //
   $squirrelmail_plugin_hooks['message_list_controls']['spam_buttons']
      = 'sb_mailbox_list_buttons';


   // SM 1.5.0/1.5.1 - display buttons on button bar (on read message screen)
   //
   $squirrelmail_plugin_hooks['read_body_menu_top']['spam_buttons']
      = 'sb_read_message_buttons';


   // SM 1.5.2+ - display buttons on button bar (on read message screen) 
//TODO: 1.5.2 should change the API for the buttons on the read message screen, then this code will have to change to suit
   //
   $squirrelmail_plugin_hooks['template_construct_read_menubar_buttons.tpl']['spam_buttons']
      = 'sb_read_message_buttons';


   // display links in "Options" section on single message read screen
   //
   $squirrelmail_plugin_hooks['read_body_header_right']['spam_buttons']
      = 'sb_read_message_links';


   // SM 1.4.x - handle button click from message list page
   //
   $squirrelmail_plugin_hooks['move_before_move']['spam_buttons']
      = 'sb_button_action';


   // SM 1.5.x - handle button click from message list page (although
   //            it is more proper to use "mailbox_display_button_action")
   // SM 1.4.x - print status message at top of message list
   //
   $squirrelmail_plugin_hooks['right_main_after_header']['spam_buttons']
      = 'sb_button_action';


   // SM 1.4.x - handle button click from read message page
   //
   $squirrelmail_plugin_hooks['read_body_header']['spam_buttons']
      = 'sb_button_action';


   // SM 1.5.x - handle button click from read message page
   //
   $squirrelmail_plugin_hooks['template_construct_read_headers.tpl']['spam_buttons']
      = 'sb_button_action';


   // SM 1.5.x - abort message view when message is moved/deleted
   //
   $squirrelmail_plugin_hooks['template_construct_read_message_body.tpl']['spam_buttons']
      = 'sb_abort_message_view';


   // show options on display preferences page
   //
   $squirrelmail_plugin_hooks['optpage_loadhook_display']['spam_buttons']
      = 'spam_buttons_display_options';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['spam_buttons']
      = 'spam_buttons_check_configuration';

}



/**
  * Force the getpot script to pick up these translations
  * (which are in the config file in non-translated form)
  *
  * @ignore
  *
  */
function sb_no_op()
{
   $ignore = _("Spam");
   $ignore = _("Report Spam");
   $ignore = _("Not Spam");
   $ignore = _("Successfully reported as spam");
   $ignore = _("Successfully reported as non-spam");
   $ignore = _("Whitelist");
   $ignore = _("Whitelist Sender");
   $ignore = _("Blacklist");
   $ignore = _("Blacklist Sender");
   $ignore = _("Blacklist Sender");
   $ignore = ngettext("Sender has been blacklisted", "Senders have been blacklisted", 1);
   $ignore = _("Sender has been blacklisted");
   $ignore = _("Senders have been blacklisted");
   $ignore = ngettext("Sender has been whitelisted", "Senders have been whitelisted", 1);
   $ignore = _("Sender has been whitelisted");
   $ignore = _("Senders have been whitelisted");
}



/**
  * Returns info about this plugin
  *
  */
function spam_buttons_info()
{

   return array(
                 'english_name' => 'Spam Buttons',
                 'authors' => array(
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                 ),
                 'version' => '2.3.1',
                 'required_sm_version' => '1.4.0',
                 'requires_configuration' => 1,
                 'summary' => 'Puts Spam/Not Spam buttons on mailbox list and message view pages.',
                 'details' => 'This plugin will place "Spam" and/or "Not Spam" buttons on the mailbox message list page as well as on a single message view page.  The action associated with the buttons (as well as the button text) can be configured to suit most any spam reporting system.  Reporting by email, reporting by executing a command on the server, reporting by moving (or copying) the message to a designated folder and reporting by calling a custom-defined PHP function are all supported.  Any number of custom buttons may also be added, where the associated action is completely customizable (for instance, adding the message sender to a whitelist or blacklist).',
                 'per_version_requirements' => array(
                    '1.5.2' => array(
// we are using get_current_hook_name() and load_config() in functions.php so we always need Compatibility
//                       'required_plugins' => array()
                    ),
                    '1.4.11' => array(
                       'requires_source_patch' => 0,
                    ),
                    '1.4.0' => array(
                       'requires_source_patch' => 1,
                       'required_plugins' => array(
                          'compatibility' => array(
                             'version' => '2.0.12',
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
function spam_buttons_version()
{
   $info = spam_buttons_info();
   return $info['version'];
}



/**
  * Takes care of spam/ham button click from mailbox list
  *
  */
function sb_button_action($args)
{
   include_once(SM_PATH . 'plugins/spam_buttons/report.php');
   return sb_button_action_do($args);
}



/**
  * Adds spam/ham buttons to mailbox listing
  *
  */
function sb_mailbox_list_buttons(&$args)
{
   include_once(SM_PATH . 'plugins/spam_buttons/buttons.php');
   sb_mailbox_list_buttons_do($args);
}



/**
  * Adds spam/ham buttons to message display
  *
  */
function sb_read_message_buttons($args)
{
   include_once(SM_PATH . 'plugins/spam_buttons/buttons.php');
   return sb_read_message_buttons_do($args);
}



/**
  * Adds spam/ham links to message options list on read body page
  *
  */
function sb_read_message_links(&$links)
{
   include_once(SM_PATH . 'plugins/spam_buttons/buttons.php');
   sb_read_message_links_do($links);
}



/**
  * Display user configuration options on display preferences page
  *
  */
function spam_buttons_display_options()
{
   include_once(SM_PATH . 'plugins/spam_buttons/options.php');
   spam_buttons_display_options_do();
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function spam_buttons_check_configuration()
{
   include_once(SM_PATH . 'plugins/spam_buttons/configtest.php');
   return spam_buttons_check_configuration_do();
}



/**
  * Abort message view when message is moved/deleted
  * out from under current message view
  *
  */
function sb_abort_message_view()
{
   include_once(SM_PATH . 'plugins/spam_buttons/report.php');
   sb_abort_message_view_do();
}



