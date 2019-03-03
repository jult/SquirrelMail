<?php

/**
  * SquirrelMail Variable Sent Folder Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002-2003 Robin Rainton <robin@rainton.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage variable_sent_folder
  *
  */



/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function variable_sent_folder_init()
{  

   if (!@include_once (SM_PATH . 'config/config_variable_sent_folder.php'))
      if (!@include_once (SM_PATH . 'plugins/variable_sent_folder/config.php'))
         if (!@include_once (SM_PATH . 'plugins/variable_sent_folder/config_default.php'))
            return FALSE;

   return TRUE;

/* ----------  This is how to do the same thing using the Compatibility plugin
   return load_config('variable_sent_folder',
                      array('../../config/config_variable_sent_folder.php',
                            'config.php',
                            'config_default.php'),
                      TRUE, TRUE);
----------- */

}



/**                          
  * Change the folder sent messages will be saved to
  *                       
  */
function variable_sent_folder_override_sent_folder()
{
   global $sent_folder, $original_sent_folder;

   if (sqGetGlobalVar('variable_sent_folder', $variable_sent_folder, SQ_POST))
   {
      $original_sent_folder = $sent_folder;
      $sent_folder = $variable_sent_folder;
   }
}



/**
  * In the case of replies, move the original message to the
  * sent folder if needed
  *
  */
function variable_sent_folder_move_orig_to_sent_folder()
{

   global $action, $Result, $passed_id, $sent_folder, $mailbox,
          $startMessage, $original_sent_folder, $username,
          $data_dir, $move_orig_only_when_not_sent_folder;

   $move_orig_only_when_not_sent_folder = getPref($data_dir, $username, 'move_orig_only_when_not_sent_folder', 1);

   if ((($move_orig_only_when_not_sent_folder && $original_sent_folder != $sent_folder)
     || !$move_orig_only_when_not_sent_folder)
    && ($action == 'reply' || $action == 'reply_all')
    && $Result
    && $passed_id
    && sqGetGlobalVar('move_orig_to_sent_folder', $move_orig_to_sent_folder, SQ_POST)
    && sqGetGlobalVar('variable_sent_folder', $variable_sent_folder, SQ_POST)
    && $variable_sent_folder != $mailbox)
   {

      global $key, $imapPort, $imapServerAddress, $imapConnection,
             $imap_stream_options, $mbx_response, $auto_expunge;

      if (check_sm_version(1, 5, 2))
         $key = FALSE;
      if (!is_resource($imapConnection))
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0, $imap_stream_options);
      if (empty($mbx_response))
         $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);

      sqimap_msgs_list_move($imapConnection, $passed_id, $variable_sent_folder);
      if ($auto_expunge)
         $cnt = sqimap_mailbox_expunge($imapConnection, $mailbox, true);
      else
         $cnt = 0;

      if (($startMessage + $cnt - 1) >= $mbx_response['EXISTS'])
      {
         if ($startMessage > $show_num)
            $startMessage = $startMessage - $show_num;
         else
            $startMessage = 1;
      }

   }

}



/**
  * Show the custom folder selector on the compose screen
  *                       
  */
function variable_sent_folder_show_custom_sent_selector()
{
   global $sent_folder, $action, $mailbox, $data_dir, $username,
          $variable_sent_folder_default_status, $variable_sent_folder_default_folder;

   variable_sent_folder_init();


   $variable_sent_folder_on = (string)getPref($data_dir, $username, 'variable_sent_folder_on', $variable_sent_folder_default_status);
   if (empty($variable_sent_folder_on)) $variable_sent_folder_on = '0';
   $variable_sent_folder_default = (string)getPref($data_dir, $username, 'variable_sent_folder_default', $variable_sent_folder_default_folder);


   // exit if we don't need to show the selector
   //
   switch ($variable_sent_folder_on)
   {
      // 0 = Never
      //
      case '0':
         return;

      // 3 = Always
      //
      case '3':
         break;

      // 1 = Only when replying
      //
      case '1':
         if ($action == 'reply' || $action == 'reply_all')
            break;
         return;

      // 2 = Only when composing new
      //
      case '2':
         if ($action == 'draft'
          || $action == 'edit_as_new'
          || $action == 'forward'
          || $action == 'forward_as_attachment'
          || $action == 'reply_all'
          || $action == 'reply')
            return;
         break;
   }


   // set default as needed
   //
   if (sqGetGlobalVar('variable_sent_folder', $variable_sent_folder, SQ_POST))
      $sent_folder = $variable_sent_folder;
   else
   {
      // other plugins can use this hook to override the
      // default custom sent folder location - plugin
      // using this hook must specify a properly formatted
      // IMAP folder name in the global variable named
      // $variable_sent_folder_custom_sent_folder_default
      // (the hook return value is not used because in
      // SquirrelMail 1.4.x, it is not shared between
      // plugins on the same hook)
      //
      // plugin authors should be aware of other plugins
      // on the same hook trying to override this value
      // and play nicely
      //
      global $variable_sent_folder_custom_sent_folder_default;
      $variable_sent_folder_custom_sent_folder_default = NULL;
      if (check_sm_version(1, 5, 2))
         $hook_return = do_hook('custom_sent_folder');
      else
         $hook_return = do_hook_function('custom_sent_folder');
      //if (!empty($hook_return))
      //   $sent_folder = $hook_return;
      if (!empty($variable_sent_folder_custom_sent_folder_default))
         $sent_folder = $variable_sent_folder_custom_sent_folder_default;
      else if ($variable_sent_folder_default == '2')
         $sent_folder = $mailbox;
   }


   // get folder list...
   //
   global $key, $imapServerAddress, $imapPort, $imap_stream_options, $imapConnection;
   if (check_sm_version(1, 5, 2))
      $key = FALSE;
   if (!is_resource($imapConnection))
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0, $imap_stream_options);
   $boxes = sqimap_mailbox_list($imapConnection);


   if (count($boxes) < 1) return;


   // build the selector
   //
   sq_change_text_domain('variable_sent_folder');
   $output = '<br />' . _("Save Sent Message In:")
      . ' <select name="variable_sent_folder">'
      . '<option value="' . SMPREF_NONE . '"'
      . ($sent_folder == SMPREF_NONE ? ' selected' : '')
      . '>&lt;' . _("Do not Save") . "&gt;</option>\n";
   for ($boxnum = 0; $boxnum < count($boxes); $boxnum++)
   {
      $output .= '<option value="' . $boxes[$boxnum]['unformatted'] . '"'
         . ($sent_folder == $boxes[$boxnum]['unformatted'] ? ' selected' : '')
         . '>' . $boxes[$boxnum]['formatted'] . "</option>\n";
   }
   $output .= "</select>\n";


   // add checkbox for moving the original message as well
   //
   if ($action == 'reply' || $action == 'reply_all')
   {
      $default_move_orig = getPref($data_dir, $username, 'default_move_orig', 0);
      $output .= '<input type="checkbox" ' . ($default_move_orig ? 'checked="checked"' : '') . ' name="move_orig_to_sent_folder" id="move_orig_to_sent_folder" /><label for="move_orig_to_sent_folder"> ' . _("Also move original") . '</label>';
   }
   sq_change_text_domain('squirrelmail');


   // output the selector
   //
   if (check_sm_version(1, 5, 2))
   {
      return array('compose_button_row' => $output);
   }
   else
   {
      echo $output;
   }

}



/**
  * Display user configuration options on folder preferences page
  *
  */
function variable_sent_folder_options()
{  

   global $data_dir, $username, $variable_sent_folder_default_status,
          $variable_sent_folder_default_folder;

   variable_sent_folder_init();


   $variable_sent_folder_on = (string)getPref($data_dir, $username, 'variable_sent_folder_on', $variable_sent_folder_default_status);
   if (empty($variable_sent_folder_on)) $variable_sent_folder_on = '0';
   $variable_sent_folder_default = (string)getPref($data_dir, $username, 'variable_sent_folder_default', $variable_sent_folder_default_folder);
   $move_orig_only_when_not_sent_folder = getPref($data_dir, $username, 'move_orig_only_when_not_sent_folder', 1);
   $default_move_orig = getPref($data_dir, $username, 'default_move_orig', 0);


   // get folder list...
   //
   global $key, $imapServerAddress, $imapPort, $imap_stream_options, $imapConnection;
   if (check_sm_version(1, 5, 2))
      $key = FALSE;
   if (!is_resource($imapConnection))
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0, $imap_stream_options);
   $boxes = sqimap_mailbox_list($imapConnection);


   sq_change_text_domain('variable_sent_folder');


   global $optpage_data;
   $optpage_data['grps']['variable_sent_folder'] = _("Custom Sent Folder");
   $optionValues = array();

   $optionValues[] = array(
      'name'          => 'variable_sent_folder_on',
      'caption'       => _("Enable Custom Sent Folder Selector"),
      'type'          => SMOPT_TYPE_STRLIST,
      'initial_value' => $variable_sent_folder_on,
      'refresh'       => SMOPT_REFRESH_NONE,
      'posvals'       => array(
         '0' => _("Off"),
         '1' => _("Only when replying"),
         '2' => _("Only when composing new"),
         '3' => _("Always on"),
      ),
   );

   $sent_folder_text = _("Sent Folder");
   $optionValues[] = array(
      'name'          => 'variable_sent_folder_default',
      'caption'       => _("Default Custom Sent Folder"),
      'type'          => SMOPT_TYPE_STRLIST,
      'initial_value' => $variable_sent_folder_default,
      'refresh'       => SMOPT_REFRESH_NONE,
      'posvals'       => array(
         '1' => sprintf(_("\"%s\" as defined above"), $sent_folder_text),
         '2' => _("Current folder being viewed"),
      ),
   );

   $optionValues[] = array(
      'name'          => 'default_move_orig',
      'initial_value' => $default_move_orig,
      'caption'       => _("For Replies, Default To Move Original Message To Same Folder As Sent Message"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'refresh'       => SMOPT_REFRESH_NONE,
   );

   $optionValues[] = array(
      'name'          => 'move_orig_only_when_not_sent_folder',
      'initial_value' => $move_orig_only_when_not_sent_folder,
      'caption'       => sprintf(_("Only Move Original When Not Saving To \"%s\" Defined Above"), $sent_folder_text),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'refresh'       => SMOPT_REFRESH_NONE,
   );



   // add to our own subsection of the folder options
   // (be careful to merge with options of other plugins)
   //
   if (empty($optpage_data['vals']['variable_sent_folder']))
      $optpage_data['vals']['variable_sent_folder'] = array();
   $optpage_data['vals']['variable_sent_folder'] = array_merge($optpage_data['vals']['variable_sent_folder'], $optionValues);


   sq_change_text_domain('squirrelmail');

}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function variable_sent_folder_check_configuration()
{

   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {  

      // need SM version 1.4.6 or better
      //
      if (!check_sm_version(1, 4, 6))
      {
         do_err('The Variable Sent Folder plugin requires SquirrelMail version 1.4.6 or above', FALSE);
         return TRUE;
      }


      // try to find Compatibility, and then that it is v2.0.5+
      //
      if (function_exists('check_plugin_version')
       && check_plugin_version('compatibility', 2, 0, 5, TRUE))
      { /* no-op */ }


      // something went wrong
      //
      else
      {  
         do_err('The Variable Sent Folder plugin requires the Compatibility plugin version 2.0.5 or above', FALSE);
         return TRUE;
      }

   }


   // make sure plugin is correctly configured
   //
   if (!variable_sent_folder_init())
   {  
      do_err('The Variable Sent Folder plugin is not configured correctly', FALSE);
      return TRUE;
   }

}



