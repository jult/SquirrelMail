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
  * Display user configuration options on display preferences page
  *
  */
function spam_buttons_display_options_do()
{

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');

   global $data_dir, $username, $sb_reselect_messages, $javascript_on,
          $sb_reselect_messages_allow_override, $optpage_data,
          $sb_delete_after_report, $sb_delete_after_report_allow_override,
          $sb_move_after_report_spam, $sb_move_after_report_spam_allow_override,
          $sb_move_after_report_not_spam, 
          $sb_move_after_report_not_spam_allow_override,
          $sb_report_spam_by_move_to_folder, 
          $sb_report_not_spam_by_move_to_folder,
          $sb_copy_after_report_spam_allow_override, $sb_copy_after_report_spam,
          $sb_copy_after_report_not_spam_allow_override, 
          $sb_copy_after_report_not_spam, $sb_suppress_spam_button_folder,
          $sb_suppress_not_spam_button_folder,
          $sb_suppress_spam_button_folder_allow_override,
          $sb_suppress_not_spam_button_folder_allow_override,
          $sb_show_spam_button_folder, $sb_report_not_spam_by_copy_to_folder,
          $sb_show_not_spam_button_folder, $sb_report_spam_by_copy_to_folder,
          $sb_show_spam_button_folder_allow_override,
          $sb_show_not_spam_button_folder_allow_override,
          $sb_move_to_other_message_after_report,
          $sb_move_to_other_message_after_report_allow_override;

   spam_buttons_init();

   sq_change_text_domain('spam_buttons');

   $my_optpage_values = array();

   if ($sb_reselect_messages_allow_override 
    && check_sm_version(1, 4, 11)
    && (empty($sb_report_spam_by_move_to_folder) 
     || empty($sb_report_not_spam_by_move_to_folder)))
   {
      $sb_reselect_messages = getPref($data_dir, $username, 
                                      'sb_reselect_messages', $sb_reselect_messages);
      $my_optpage_values[] = array(
         'name'          => 'sb_reselect_messages',
         'caption'       => _("Reselect Messages After Reporting"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $sb_reselect_messages,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($sb_delete_after_report_allow_override 
    && empty($sb_report_spam_by_move_to_folder))
   {
      $sb_delete_after_report = getPref($data_dir, $username, 
                                        'sb_delete_after_report', $sb_delete_after_report);
      $my_optpage_values[] = array(
         'name'          => 'sb_delete_after_report',
         'caption'       => _("Delete Spam After Reporting"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $sb_delete_after_report,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   $boxes = array();
   if ($sb_move_after_report_spam_allow_override 
    && (empty($sb_report_spam_by_move_to_folder) || $sb_report_spam_by_copy_to_folder))
   {

      // Get folder list
      //
      global $username, $key, $imapServerAddress, $imapPort, $data_dir;
      if (check_sm_version(1, 5, 2))
      { 
         $key = FALSE; 
         include_once(SM_PATH . 'functions/imap_general.php'); 
      }
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
      $boxes = sqimap_mailbox_list($imapConnection);
      array_unshift($boxes, array('unformatted-disp' => _("[Do not move]"),
                                  'formatted' => _("[Do not move]"),
                                  'unformatted-dm' => 0,
                                  'unformatted' => 0,
                                  'raw' => '', 
                                  'id' => 0, 
                                  'flags' => array()));
      sqimap_logout($imapConnection);

      $sb_move_after_report_spam = getPref($data_dir, $username, 
                                           'sb_move_after_report_spam', 
                                           $sb_move_after_report_spam);

      if ($sb_copy_after_report_spam && !$sb_copy_after_report_spam_allow_override)
         $opt_caption = _("Copy Spam After Reporting To");
      else
         $opt_caption = _("Move Spam After Reporting To");

      $my_optpage_values[] = array(
         'name'          => 'sb_move_after_report_spam',
         'caption'       => $opt_caption,
         'type'          => SMOPT_TYPE_FLDRLIST,
         'posvals'       => array('ignore' => $boxes),
         'initial_value' => $sb_move_after_report_spam,
         'refresh'       => SMOPT_REFRESH_NONE,
      );


      // add "copy instead of move" selection if needed
      //
      if ($sb_copy_after_report_spam_allow_override) 
      {
         $sb_copy_after_report_spam = getPref($data_dir, $username, 
                                              'sb_copy_after_report_spam', 
                                              $sb_copy_after_report_spam);
         $my_optpage_values[] = array(
            'name'          => 'sb_copy_after_report_spam',
            'caption'       => _("Copy Instead of Moving"),
            'type'          => SMOPT_TYPE_BOOLEAN,
            'initial_value' => $sb_copy_after_report_spam,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }

   }


   if ($sb_move_after_report_not_spam_allow_override 
    && (empty($sb_report_not_spam_by_move_to_folder) || $sb_report_not_spam_by_copy_to_folder))
   {

      // Get folder list
      //
      if (empty($boxes))
      {
         global $username, $key, $imapServerAddress, $imapPort, $data_dir;
         if (check_sm_version(1, 5, 2)) 
         { 
            $key = FALSE; 
            include_once(SM_PATH . 'functions/imap_general.php'); 
         }
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         array_unshift($boxes, array('unformatted-disp' => _("[Do not move]"),
                                     'formatted' => _("[Do not move]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
         sqimap_logout($imapConnection);
      }

      $sb_move_after_report_not_spam = getPref($data_dir, $username, 
                                               'sb_move_after_report_not_spam', 
                                               $sb_move_after_report_not_spam);

      if ($sb_copy_after_report_not_spam && !$sb_copy_after_report_not_spam_allow_override)
         $opt_caption = _("Copy Non-Spam After Reporting To");
      else
         $opt_caption = _("Move Non-Spam After Reporting To");

      $my_optpage_values[] = array(
         'name'          => 'sb_move_after_report_not_spam',
         'caption'       => $opt_caption,
         'type'          => SMOPT_TYPE_FLDRLIST,
         'posvals'       => array('ignore' => $boxes),
         'initial_value' => $sb_move_after_report_not_spam,
         'refresh'       => SMOPT_REFRESH_NONE,
      );


      // add "copy instead of move" selection if needed
      //
      if ($sb_copy_after_report_not_spam_allow_override) 
      {
         $sb_copy_after_report_not_spam = getPref($data_dir, $username, 
                                                  'sb_copy_after_report_not_spam', 
                                                  $sb_copy_after_report_not_spam);
         $my_optpage_values[] = array(
            'name'          => 'sb_copy_after_report_not_spam',
            'caption'       => _("Copy Instead of Moving"),
            'type'          => SMOPT_TYPE_BOOLEAN,
            'initial_value' => $sb_copy_after_report_not_spam,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }

   }


   // all these tests below are the possible permutations of
   // configuration settings that will result in report-by-move,
   // move-after-report, delete-after-report, or the possiblility
   // that the user may enable one of them 
   // (in which case we want to display the option to configure 
   // moving to another message after reporting)
   //
   if ($sb_move_to_other_message_after_report_allow_override
    && $javascript_on
    && ($sb_report_spam_by_move_to_folder
     || $sb_report_not_spam_by_move_to_folder
     || ($sb_delete_after_report_allow_override || $sb_delete_after_report)
     || ($sb_move_after_report_spam_allow_override && ($sb_copy_after_report_spam_allow_override || !$sb_copy_after_report_spam))
     || (!$sb_move_after_report_spam_allow_override && $sb_move_after_report_spam && !$sb_copy_after_report_spam)
     || ($sb_move_after_report_not_spam_allow_override && ($sb_copy_after_report_not_spam_allow_override || !$sb_copy_after_report_not_spam))
     || (!$sb_move_after_report_not_spam_allow_override && $sb_move_after_report_not_spam && !$sb_copy_after_report_not_spam)))
   {
      $sb_move_to_other_message_after_report = getPref($data_dir, $username, 
                                                       'sb_move_to_other_message_after_report', 
                                                       $sb_move_to_other_message_after_report);
      $my_optpage_values[] = array(
         'name'          => 'sb_move_to_other_message_after_report',
//TODO: note to user that this only takes effect if a report-by-move, move-after-report or delete-after-report functionality is currently enabled??
//TODO: well, we could just always move to next/previous message after reporting if the user likes that.... but that's more screwing up the report code, which is very complicated at this point, so for now, we'll set this option aside
         'caption'       => _("Move To Next Message After Reporting"),
         'type'          => SMOPT_TYPE_STRLIST,
         'initial_value' => $sb_move_to_other_message_after_report,
         'posvals' => array(''         => _("Return to Message List"),
                            'next'     => _("Next"),
                            'previous' => _("Previous")),
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   } 


   if ($sb_show_spam_button_folder_allow_override)
   {

      // Get folder list
      //
      if (empty($boxes))
      {
         global $username, $key, $imapServerAddress, $imapPort, $data_dir;
         if (check_sm_version(1, 5, 2)) 
         { 
            $key = FALSE; 
            include_once(SM_PATH . 'functions/imap_general.php'); 
         }
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
         sqimap_logout($imapConnection);
      }
      else // remove the "Do Not Move" entry
      {
         array_shift($boxes);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
      }

      $sb_show_spam_button_folder = getPref($data_dir, $username,
                                            'sb_show_spam_button_folder',
                                            $sb_show_spam_button_folder);


      // this option used to be a string; these lines convert
      // values that may have already been stored in user prefs
      // as strings into proper array format (also unpacks
      // serialized arrays for SM v1.4.14+)...  perhaps this 
      // code should be removed a year (or more?) after 1.4.x
      // supports multiple select folder lists on the options page
      // (but not the unserialize() call)
      //
      if (empty($sb_show_spam_button_folder))
         $sb_show_spam_button_folder = array();
      else if (check_sm_version(1, 4, 14) && !is_array($sb_show_spam_button_folder))
         $sb_show_spam_button_folder = unserialize($sb_show_spam_button_folder);
      if (!is_array($sb_show_spam_button_folder))
         $sb_show_spam_button_folder = array($sb_show_spam_button_folder);


      if (check_sm_version(1, 4, 14))
      {
         $my_optpage_values[] = array(
            'name'          => 'sb_show_spam_button_folder',
            'caption'       => _("Display Spam Button in Folders"),
            'type'          => SMOPT_TYPE_FLDRLIST_MULTI,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_show_spam_button_folder,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }
      else
      {

         // In 1.4.13 and below, SM options don't allow multiple
         // select folder lists... 
         // until they do, just use the first array element 
         //
         $sb_show_spam_button_folder = array_shift($sb_show_spam_button_folder);

         $my_optpage_values[] = array(
            'name'          => 'sb_show_spam_button_folder',
            'caption'       => _("Display Spam Button in Folder"),
            'type'          => SMOPT_TYPE_FLDRLIST,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_show_spam_button_folder,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }

   }


   if ($sb_show_not_spam_button_folder_allow_override) 
   {

      // Get folder list
      //
      if (empty($boxes))
      {
         global $username, $key, $imapServerAddress, $imapPort, $data_dir;
         if (check_sm_version(1, 5, 2))
         {
            $key = FALSE;
            include_once(SM_PATH . 'functions/imap_general.php');
         }
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0,
                                     'flags' => array()));
         sqimap_logout($imapConnection);
      }

      // remove the "Do Not Move" entry (but only if it's there)
      else if (!$sb_show_spam_button_folder_allow_override)
      {
         array_shift($boxes);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
      }

      $sb_show_not_spam_button_folder = getPref($data_dir, $username, 
                                                'sb_show_not_spam_button_folder', 
                                                $sb_show_not_spam_button_folder);

      // this option used to be a string; these lines convert
      // values that may have already been stored in user prefs
      // as strings into proper array format (also unpacks
      // serialized arrays for SM v1.4.14+)...  perhaps this
      // code should be removed a year (or more?) after 1.4.x
      // supports multiple select folder lists on the options page
      // (but not the unserialize() call)
      //
      if (empty($sb_show_not_spam_button_folder))
         $sb_show_not_spam_button_folder = array();
      else if (check_sm_version(1, 4, 14) && !is_array($sb_show_not_spam_button_folder))
         $sb_show_not_spam_button_folder = unserialize($sb_show_not_spam_button_folder);
      if (!is_array($sb_show_not_spam_button_folder))
         $sb_show_not_spam_button_folder = array($sb_show_not_spam_button_folder);


      if (check_sm_version(1, 4, 14))
      {
         $my_optpage_values[] = array(
            'name'          => 'sb_show_not_spam_button_folder',
            'caption'       => _("Display Non-Spam Button in Folders"),
            'type'          => SMOPT_TYPE_FLDRLIST_MULTI,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_show_not_spam_button_folder,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }
      else
      {

         // In 1.4.13 and below, SM options don't allow multiple
         // select folder lists... 
         // until they do, just use the first array element 
         //
         $sb_show_not_spam_button_folder = array_shift($sb_show_not_spam_button_folder);

         $my_optpage_values[] = array(
            'name'          => 'sb_show_not_spam_button_folder',
            'caption'       => _("Display Non-Spam Button in Folder"),
            'type'          => SMOPT_TYPE_FLDRLIST,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_show_not_spam_button_folder[0],
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }

   }


   if ($sb_suppress_spam_button_folder_allow_override) 
   {

      // Get folder list
      //
      if (empty($boxes))
      {
         global $username, $key, $imapServerAddress, $imapPort, $data_dir;
         if (check_sm_version(1, 5, 2))
         {
            $key = FALSE;
            include_once(SM_PATH . 'functions/imap_general.php');
         }
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
         sqimap_logout($imapConnection);
      }

      // remove the "Do Not Move" entry (but only if it's there)
      else if (!$sb_show_spam_button_folder_allow_override
            && !$sb_show_not_spam_button_folder_allow_override)
      {
         array_shift($boxes);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
      }

      $sb_suppress_spam_button_folder = getPref($data_dir, $username, 
                                                'sb_suppress_spam_button_folder', 
                                                $sb_suppress_spam_button_folder);

      // this option used to be a string; these lines convert
      // values that may have already been stored in user prefs
      // as strings into proper array format (also unpacks
      // serialized arrays for SM v1.4.14+)...  perhaps this
      // code should be removed a year (or more?) after 1.4.x
      // supports multiple select folder lists on the options page
      // (but not the unserialize() call)
      //
      if (empty($sb_suppress_spam_button_folder))
         $sb_suppress_spam_button_folder = array();
      else if (check_sm_version(1, 4, 14) && !is_array($sb_suppress_spam_button_folder))
         $sb_suppress_spam_button_folder = unserialize($sb_suppress_spam_button_folder);
      if (!is_array($sb_suppress_spam_button_folder))
         $sb_suppress_spam_button_folder = array($sb_suppress_spam_button_folder);


      if (check_sm_version(1, 4, 14))
      {
         $my_optpage_values[] = array(
            'name'          => 'sb_suppress_spam_button_folder',
            'caption'       => _("Don't Display Spam Button in Folders"),
            'type'          => SMOPT_TYPE_FLDRLIST_MULTI,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_suppress_spam_button_folder,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }
      else
      {

         // In 1.4.13 and below, SM options don't allow multiple
         // select folder lists... 
         // until they do, just use the first array element 
         //
         $sb_suppress_spam_button_folder = array_shift($sb_suppress_spam_button_folder);

         $my_optpage_values[] = array(
            'name'          => 'sb_suppress_spam_button_folder',
            'caption'       => _("Don't Display Spam Button in Folder"),
            'type'          => SMOPT_TYPE_FLDRLIST,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_suppress_spam_button_folder[0],
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }

   }


   if ($sb_suppress_not_spam_button_folder_allow_override)
   {

      // Get folder list
      //
      if (empty($boxes))
      {
         global $username, $key, $imapServerAddress, $imapPort, $data_dir;
         if (check_sm_version(1, 5, 2))
         {
            $key = FALSE;
            include_once(SM_PATH . 'functions/imap_general.php');
         }
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
         sqimap_logout($imapConnection);
      }

      // remove the "Do Not Move" entry (but only if it's there)
      else if (!$sb_show_spam_button_folder_allow_override
            && !$sb_show_not_spam_button_folder_allow_override
            && !$sb_suppress_spam_button_folder_allow_override)
      {
         array_shift($boxes);
         array_unshift($boxes, array('unformatted-disp' => _("[Always Display]"),
                                     'formatted' => _("[Always Display]"),
                                     'unformatted-dm' => 0,
                                     'unformatted' => 0,
                                     'raw' => '', 
                                     'id' => 0, 
                                     'flags' => array()));
      }

      $sb_suppress_not_spam_button_folder = getPref($data_dir, $username,
                                                'sb_suppress_not_spam_button_folder',
                                                $sb_suppress_not_spam_button_folder);


      // this option used to be a string; these lines convert
      // values that may have already been stored in user prefs
      // as strings into proper array format (also unpacks
      // serialized arrays for SM v1.4.14+)...  perhaps this
      // code should be removed a year (or more?) after 1.4.x
      // supports multiple select folder lists on the options page
      // (but not the unserialize() call)
      //
      if (empty($sb_suppress_not_spam_button_folder))
         $sb_suppress_not_spam_button_folder = array();
      else if (check_sm_version(1, 4, 14) && !is_array($sb_suppress_not_spam_button_folder))
         $sb_suppress_not_spam_button_folder = unserialize($sb_suppress_not_spam_button_folder);
      if (!is_array($sb_suppress_not_spam_button_folder))
         $sb_suppress_not_spam_button_folder = array($sb_suppress_not_spam_button_folder);


      if (check_sm_version(1, 4, 14))
      {
         $my_optpage_values[] = array(
            'name'          => 'sb_suppress_not_spam_button_folder',
            'caption'       => _("Don't Display Non-Spam Button in Folders"),
            'type'          => SMOPT_TYPE_FLDRLIST_MULTI,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_suppress_not_spam_button_folder,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }
      else
      {

         // In 1.4.13 and below, SM options don't allow multiple
         // select folder lists... 
         // until they do, just use the first array element 
         //
         $sb_suppress_not_spam_button_folder = array_shift($sb_suppress_not_spam_button_folder);

         $my_optpage_values[] = array(
            'name'          => 'sb_suppress_not_spam_button_folder',
            'caption'       => _("Don't Display Non-Spam Button in Folder"),
            'type'          => SMOPT_TYPE_FLDRLIST,
            'posvals'       => array('ignore' => $boxes),
            'initial_value' => $sb_suppress_not_spam_button_folder,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
      }

   }


   if (!empty($my_optpage_values))
   {
      $optpage_data['grps']['spam_buttons'] = _("Spam Reporting");
      $optpage_data['vals']['spam_buttons'] = $my_optpage_values;
   }

   sq_change_text_domain('squirrelmail');

}



