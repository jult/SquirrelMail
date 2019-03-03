<?php

/**
  * SquirrelMail Mark Read Plugin
  * Copyright (c) 2004-2005 Dave Kliczbor <maligree@gmx.de>
  * Copyright (c) 2003-2009 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2004 Ferdie Ferdsen <ferdie.ferdsen@despam.de>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage mark_read
  *
  */



/**
  * Register this plugin with SquirrelMail
  *
  */
function squirrelmail_plugin_init_mark_read() 
{

   global $squirrelmail_plugin_hooks;


   // SM 1.5.x - provide an RPC interface to this plugin
   //
   $squirrelmail_plugin_hooks['squirrelmail_rpc']['mark_read']
      = 'mark_read_rpc';


   // SM 1.4.x - places text and widgets on the folder page
   //
   $squirrelmail_plugin_hooks['folders_bottom']['mark_read'] 
      = 'mark_read_show_options';


   // SM 1.5.x - places text and widgets on the folder page
   //
   $squirrelmail_plugin_hooks['template_construct_folder_manip.tpl']['mark_read'] 
      = 'mark_read_show_options';


   // display links on target folders
   //
   $squirrelmail_plugin_hooks['left_main_after_each_folder']['mark_read']
      = 'mark_read_show_link';


   // SM 1.4.x - display button(s) in target mailbox listings
   //
   $squirrelmail_plugin_hooks['mailbox_display_buttons']['mark_read']
      = 'mark_read_show_button';


   // SM 1.5.x - display button(s) in target mailbox listings
   //
   $squirrelmail_plugin_hooks['message_list_controls']['mark_read']
      = 'mark_read_show_button';


   // SM 1.4.x - perform button actions
   //
   $squirrelmail_plugin_hooks['move_before_move']['mark_read']
       = 'mark_read_handle_button_click';


   // SM 1.5.x - perform button actions
   //
   $squirrelmail_plugin_hooks['mailbox_display_button_action']['mark_read']
       = 'mark_read_handle_button_click';


   // configuration check
   //
   $squirrelmail_plugin_hooks['configtest']['mark_read']
      = 'mark_read_check_configuration';

}



/**
  * Returns info about this plugin
  *
  */
function mark_read_info()
{

   return array(
                 'english_name' => 'Mark Read',
                 'authors' => array(
                    'Paul Lesniewski' => array(
                       'email' => 'paul@squirrelmail.org',
                       'sm_site_username' => 'pdontthink',
                    ),
                    'Dave Kliczbor' => array(
                       'email' => 'maligree@gmx.de',
                    ),
                    'Ferdie Ferdsen' => array(
                       'email' => 'ferdie.ferdsen@despam.de',
                    ),
                 ),
                 'version' => '2.0.1',
                 'required_sm_version' => '1.2',
                 'requires_configuration' => 0,
                 'required_plugins' => array(
                    'compatibility' => array(
                       'version' => '2.0.12',
                       'activate' => FALSE,
                    )
                 ),
                 'summary' => 'Places a "read" and/or "unread" link next to any folder in the folder list or "Read All" and/or "Unread All" buttons on the mailbox list page that serve to mark all messages in the associated folder as (un)read.',
                 'details' => 'This plugin places "read" and/or "unread" links next to any of the folders in the folder list, or "Read All" and/or "Unread All" buttons on the mailbox listing page.  These buttons or links will mark all messages in the associated folder as having been read or unread.',
                 'per_version_requirements' => array(
                    '1.5.1' => array(
                       'requires_source_patch' => 0,
                    ),
                    '1.5.0' => array(
                       'requires_source_patch' => 1,
                    ),
                    '1.4.1' => array(
                       'requires_source_patch' => 0,
                    ),
                    '1.2' => array(
                       'requires_source_patch' => 1,
                    ),
                 ),
                 'rpc' => array(
                    'commands' => array(
                       'mark_read_read_all' => 'Marks all messages in a folder as read',
                       'mark_read_unread_all' => 'Marks all messages in a folder as unread',
                    ),
                    'errors' => array(
                       503 => 'mark_read_read_all failed',
                       504 => 'mark_read_unread_all failed',
                    ),
                 ),
               );

}



/**
  * Returns version info about this plugin
  *
  */
function mark_read_version()
{
   $info = mark_read_info();
   return $info['version'];
}



/**
  * Places text and widgets on the folder page
  *
  */
function mark_read_show_options() 
{
   include_once(SM_PATH . 'plugins/mark_read/options.php');
   return mark_read_show_options_do();
}



/**
  * Display links on target folders
  *
  * @param array $parms Parameters passed to this hook by SquirrelMail -
  *                     First element is number of messages, second is
  *                     mailbox name, third is a usable IMAP server connection.
  *
  */
function mark_read_show_link(&$parms) 
{
   include_once(SM_PATH . 'plugins/mark_read/functions.php');
   return mark_read_show_link_do($parms);
}



/**
  * Display button(s) in target mailbox listings
  *
  */
function mark_read_show_button(&$args)
{
   include_once(SM_PATH . 'plugins/mark_read/functions.php');
   mark_read_show_button_do($args);
}



/**
  * Perform button actions
  *
  */
function mark_read_handle_button_click(&$args)
{
   include_once(SM_PATH . 'plugins/mark_read/functions.php');
   return mark_read_handle_button_click_do($args);
}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function mark_read_check_configuration()
{
   include_once(SM_PATH . 'plugins/mark_read/functions.php');
   return mark_read_check_configuration_do();
}



/**
  * Provide an RPC interface to this plugin
  *
  */
function mark_read_rpc($args)
{
   include_once(SM_PATH . 'plugins/mark_read/functions.php');
   return mark_read_rpc_do($args);
}



/**
  * Force the getpot script to pick up these translations
  * (which are in the config file in non-translated form)
  *
  * @ignore
  *
  */
function mr_no_op()
{
   $ignore = _("read");
   $ignore = _("Read");
   $ignore = _("read all");
   $ignore = _("Read All");
   $ignore = _("read all messages");
   $ignore = _("Read All Messages");
   $ignore = _("read all (%d)");
   $ignore = _("Read All (%d)");
   $ignore = _("read all (%d) messages");
   $ignore = _("Read All (%d) Messages");
   $ignore = _("unread");
   $ignore = _("Unread");
   $ignore = _("unread all");
   $ignore = _("Unread All");
   $ignore = _("unread all messages");
   $ignore = _("Unread All Messages");
   $ignore = _("unread all (%d)");
   $ignore = _("Unread All (%d)");
   $ignore = _("unread all (%d) messages");
   $ignore = _("Unread All (%d) Messages");
   $ignore = _("This will mark ALL messages in this folder as having been read.\\n\\nAre you sure you want to continue?");
   $ignore = _("This will mark ALL messages in this folder as NOT having been read.\\n\\nAre you sure you want to continue?");
   $ignore = _("This will mark ALL %d messages in this folder as having been read.\\n\\nAre you sure you want to continue?");
   $ignore = _("This will mark ALL %d messages in this folder as NOT having been read.\\n\\nAre you sure you want to continue?");
   $ignore = _("Mark ALL messages in this folder as read - are you sure?");
   $ignore = _("Mark ALL messages in this folder as unread - are you sure?");
   $ignore = _("Mark ALL %d messages in this folder as read - are you sure?");
   $ignore = _("Mark ALL %d messages in this folder as unread - are you sure?");
   $ignore = _("Mark all messages in this folder as read");
   $ignore = _("Mark all messages in this folder as having been read");
   $ignore = _("Mark all %d messages in this folder as read");
   $ignore = _("Mark all %d messages in this folder as having been read");
   $ignore = _("Mark all messages in this folder as unread");
   $ignore = _("Mark all messages in this folder as having been unread");
   $ignore = _("Mark all %d messages in this folder as unread");
   $ignore = _("Mark all %d messages in this folder as having been unread");
}



