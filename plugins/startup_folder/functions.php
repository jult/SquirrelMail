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
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function startup_folder_init()
{

   if (!@include_once(SM_PATH . 'plugins/startup_folder/config.php'))
      if (!@include_once(SM_PATH . 'plugins/startup_folder/config.sample.php'))
         return FALSE;

   return TRUE;

}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function startup_folder_check_configuration()
{

   // make sure base config is available
   //
   if (!startup_folder_init())
   {
      do_err('Startup Folder plugin is missing a configuration file', FALSE);
      return TRUE;
   }


   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {

      // if using SM version before 1.4.10 or between
      // 1.5.0 and 1.5.1, try to find Compatibility, and 
      // then that it is v2.0.7+
      //
      if (!check_sm_version(1, 4, 10) 
       || (check_sm_version(1, 5, 0) && !check_sm_version(1, 5, 2)))
      {
         if (function_exists('check_plugin_version')
          && check_plugin_version('compatibility', 2, 0, 7, TRUE))
            return FALSE;


         // something went wrong
         //
         do_err('Startup Folder plugin requires the Compatibility plugin version 2.0.7+', FALSE);
         return TRUE;

      }

   }

}



/**
  * Override Startup Location
  *
  * Called when setting up main frameset, this function 
  * detects if it should be redirecting the startup 
  * location, and if so, adjusts the target location of
  * the right frame
  *
  */
function startup_folder_override()
{

   global $data_dir, $username, $startup_location, $startup_folder,
          $startup_plugin, $startup_allow_user_config, $right_frame,
          $right_frame_url;

   startup_folder_init();


   // can user change startup settings?
   //
   if ($startup_allow_user_config)
   {
      $startup_location = getPref($data_dir, $username, 'startup_location', $startup_location);
      $startup_folder = getPref($data_dir, $username, 'startup_folder_folder', $startup_folder);
      $startup_plugin = getPref($data_dir, $username, 'startup_plugin', $startup_plugin);
   }


   $target_location = '';


   // are we starting with a plugin?
   //
   if ($startup_location == 2 && !empty($startup_plugin))
   {

      // plugin startup, forget mailboxes, just go to that plugin's location
      //
      $target_location = '../plugins/' . $startup_plugin;

   }


   // starting with a folder
   //
   else if ($startup_location == 1 && !empty($startup_folder))
   {

      // get sort value, same as load_prefs.php
      //
      $sort = getPref($data_dir, $username, 'sort', 6 );


      // tell it what mailbox to go to
      //
      $target_location = "right_main.php?sort=$sort&startMessage=1&mailbox=$startup_folder";

   }


   // redirect to target, but only if right_frame is not already populated
   //
   // NOTE: we could only do this when "just_logged_in", but for now, won't use this
   // sqGetGlobalVar('just_logged_in', $just_logged_in, SQ_SESSION);
   //
   // NOTE: we could do this, but why?
   // if (!empty($target_location) && strpos($right_frame, $target_location) === FALSE)
   //
   if (!empty($target_location) && empty($right_frame))
   {

      $right_frame = $target_location;
      $right_frame_url = $right_frame;

   }

}



/**
  * Display user configuration options on folder preferences page
  *
  */
function startup_folder_options() 
{

   global $data_dir, $username, $startup_location, $startup_folder, 
          $startup_plugin, $startup_allow_user_config, $startup_plugin_list;

   startup_folder_init();


   // can user change startup settings?
   //
   if (!$startup_allow_user_config)
      return;


   $startup_location = getPref($data_dir, $username, 'startup_location', $startup_location);
   $startup_folder = getPref($data_dir, $username, 'startup_folder_folder', $startup_folder);
   $startup_plugin = getPref($data_dir, $username, 'startup_plugin', $startup_plugin);


   // get folder list...
   //
   global $username, $key, $imapServerAddress, $imapPort, 
          $data_dir, $imapConnection;
   if (check_sm_version(1, 5, 2))
      $key = FALSE;
   $log_out_of_IMAP = FALSE;
   if (!is_resource($imapConnection))
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   $boxes = sqimap_mailbox_list($imapConnection);


   sq_change_text_domain('startup_folder');


   global $optpage_data;
   $optpage_data['grps']['startup_folder'] = 'Startup Location';
   $optionValues = array();

   $optionValues[] = array(
      'name'          => 'startup_location',
      'caption'       => _("Startup Location"),
      'type'          => SMOPT_TYPE_STRLIST,
      'value'         => $startup_location,
      'refresh'       => SMOPT_REFRESH_NONE,
      'posvals'       => 
         (empty($startup_plugin_list) 
            ? array(0 => _("Off"), 1 => _("Folder"))
            : array(0 => _("Off"), 1 => _("Folder"), 2 => _("Other")))
   );

   $optionValues[] = array(
      'name'          => 'startup_folder_folder',
      'caption'       => _("Startup Folder"),
      'type'          => SMOPT_TYPE_FLDRLIST,
      'posvals'       =>  array('whatever' => $boxes),
      'initial_value' => $startup_folder,
      'refresh'       => SMOPT_REFRESH_NONE
   );

   if (!empty($startup_plugin_list))
   {
      $optionValues[] = array(
         'name'          => 'startup_plugin',
         'caption'       => _("Other Startup Locations"),
         'type'          => SMOPT_TYPE_STRLIST,
         'value'         => $startup_plugin,
         'refresh'       => SMOPT_REFRESH_NONE,
         'posvals'       => $startup_plugin_list
      );
   }


   $optpage_data['vals']['startup_folder'] = $optionValues;


   sq_change_text_domain('squirrelmail');

}



