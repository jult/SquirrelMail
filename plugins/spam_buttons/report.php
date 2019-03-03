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
  * Takes care of spam/ham button click 
  *
  */
function sb_button_action_do($args)
{

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');

   global $is_spam_shell_command, $is_spam_resend_destination,
          $is_not_spam_shell_command, $is_not_spam_resend_destination,
          $is_spam_subject_prefix, $is_not_spam_subject_prefix,
          $sb_reselect_messages, $sb_delete_after_report,
          $sb_reselect_messages_allow_override, $username, $data_dir,
          $sb_delete_after_report_allow_override,
          $sb_move_after_report_spam, $sb_move_after_report_spam_allow_override,
          $sb_move_after_report_not_spam, $note,
          $sb_move_after_report_not_spam_allow_override,
          $sb_report_spam_by_move_to_folder, 
          $sb_report_not_spam_by_move_to_folder,
          $sb_copy_after_report_spam_allow_override, $sb_copy_after_report_spam,
          $sb_copy_after_report_not_spam_allow_override, 
          $sb_copy_after_report_not_spam, $sb_report_spam_by_copy_to_folder, 
          $sb_report_not_spam_by_copy_to_folder,
          $reported_spam_text, $reported_not_spam_text,
          $sb_move_to_other_message_after_report, $location,
          $sb_move_to_other_message_after_report_allow_override,
          $abort_message_view, $extra_buttons, $is_spam_keep_copy_in_sent,
          $sb_report_spam_by_custom_function, $is_not_spam_keep_copy_in_sent,
          $sb_report_not_spam_by_custom_function;

   spam_buttons_init();


   if ($sb_reselect_messages_allow_override)
   {
      $sb_reselect_messages = getPref($data_dir, $username, 
                                      'sb_reselect_messages', $sb_reselect_messages);
   }
   if ($sb_delete_after_report_allow_override)
   {
      $sb_delete_after_report = getPref($data_dir, $username, 
                                        'sb_delete_after_report', $sb_delete_after_report);
   }
   if ($sb_move_after_report_spam_allow_override)
   {
      $sb_move_after_report_spam = getPref($data_dir, $username, 
                                           'sb_move_after_report_spam', 
                                           $sb_move_after_report_spam);

   }
   if ($sb_move_after_report_not_spam_allow_override)
   {
      $sb_move_after_report_not_spam = getPref($data_dir, $username, 
                                               'sb_move_after_report_not_spam', 
                                               $sb_move_after_report_not_spam);
   }
   if ($sb_copy_after_report_spam_allow_override)
   {
      $sb_copy_after_report_spam = getPref($data_dir, $username, 
                                           'sb_copy_after_report_spam', 
                                           $sb_copy_after_report_spam);
   }
   if ($sb_copy_after_report_not_spam_allow_override)
   {
      $sb_copy_after_report_not_spam = getPref($data_dir, $username, 
                                               'sb_copy_after_report_not_spam', 
                                               $sb_copy_after_report_not_spam);
   }
   if ($sb_move_to_other_message_after_report_allow_override)
   {
      $sb_move_to_other_message_after_report = getPref($data_dir, $username, 
                                               'sb_move_to_other_message_after_report', 
                                               $sb_move_to_other_message_after_report);
   }


//sm_print_r($_GET, $_POST, $_SERVER);
//sm_print_r($_SESSION);
//exit;


   $passed_ent_id = 0;
   sqGetGlobalVar('passed_ent_id',   $passed_ent_id,  SQ_FORM);
   sqGetGlobalVar('REQUEST_METHOD',  $method,         SQ_SERVER);

   if (sqGetGlobalVar('location', $location, SQ_POST))
      { /* $location = htmlspecialchars($location); */ }
   else
      $location = php_self();

   if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
      // fix for Dovecot UIDs can be bigger than normal integers
      $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');

   if (sqGetGlobalVar('msg', $msg, SQ_FORM))
      // fix for Dovecot UIDs can be bigger than normal integers
      if (is_array($msg)) foreach ($msg as $i => $messageID)
         $msg[$i] = (preg_match('/^[0-9]+$/', $messageID) ? $messageID : '0');
      else
         $msg = (preg_match('/^[0-9]+$/', $msg) ? $msg : '0');


   // determine if the report was done from the message view screen
   //
   // the use of get_current_hook_name() means the Compatibility plugin is required
   //
   $hook_name = get_current_hook_name($args);
   $move_to_message_after_report = -1;
   if ($hook_name == 'read_body_header' || $hook_name == 'template_construct_read_headers.tpl')
   {
      $reporting_from_message_view = TRUE;

      // get previous/next message UIDs before we possibly move/delete the current one
      //
      if (strtolower($sb_move_to_other_message_after_report) == 'next')
         $move_to_message_after_report = spam_buttons_findNextMessage($passed_id);
      else if (strtolower($sb_move_to_other_message_after_report) == 'previous')
         $move_to_message_after_report = spam_buttons_findPreviousMessage($passed_id);
   }
   else
   {
      $reporting_from_message_view = FALSE;
   }


   // if in 1.4.x we need to print message 
   // to user after report, do that here
   //
   if (!check_sm_version(1, 5, 0) && sqGetGlobalVar('sb_note', $sb_note, SQ_SESSION))
   {
      echo html_tag('div', '<b>' . $sb_note .'</b>', 'center') . "<br />\n";
      sqsession_unregister('sb_note');
   }


   // pull button/link flags differently since during 
   // POST submissions, the $_GET array sticks around 
   //
   $isSpam = NULL;
   $notSpam = NULL;
   $extraButton = NULL;
   $callback = NULL;
   $custom_button_success_singular = '';
   $custom_button_success_plural = '';
   $button_name = NULL;
   if (strtoupper($method) == 'POST')
   {
      sqGetGlobalVar('isSpam',  $isSpam,  SQ_POST);
      sqGetGlobalVar('notSpam', $notSpam, SQ_POST);
   }
   else
   {
      sqGetGlobalVar('isSpam',  $isSpam,  SQ_GET);
      sqGetGlobalVar('notSpam', $notSpam, SQ_GET);
   }


   // detect if extra button was clicked
   //
   if (is_null($isSpam) && is_null($notSpam) && !empty($extra_buttons))
   {
      foreach ($extra_buttons as $button => $button_info)
      {
         $button_name = preg_replace('/[^a-zA-Z0-9]/', '_', $button);
         if ((strtoupper($method) == 'POST' 
           && sqGetGlobalVar($button_name, $extraButton, SQ_POST))
          || (strtoupper($method) == 'GET' 
           && sqGetGlobalVar($button_name, $extraButton, SQ_GET)))
         {
            if (!empty($button_info[3]))
               $callback = $button_info[3];
            if (!empty($button_info[4]))
               $custom_button_success_singular = $button_info[4];
            if (!empty($button_info[5]))
               $custom_button_success_plural = $button_info[5];
            break;
         }
      }
   }


   // build message ID array if user came from one of the 
   // links on the message view page
   //
   if ($isSpam == 'yslnk' || $notSpam == 'yslnk' || $extraButton == 'yslnk')
      $msg = array($passed_id);


   // build list of checkboxes to be pre-selected when
   // returning to message list
   //
   $prechecked = array();
   if ($sb_reselect_messages)
// could add the following, but not absolutely necessary
//    && (empty($sb_report_spam_by_move_to_folder)
//     || empty($sb_report_not_spam_by_move_to_folder)))
   {
      if (is_array($msg)) foreach ($msg as $messageID)
         $prechecked[$messageID] = TRUE;
   }



   // if no messages were selected or spam buttons were not clicked on
   // this request, just return and let SM handle the error, if any
   //
   if (empty($msg) || (empty($isSpam) && empty($notSpam) && empty($extraButton)))
      return;


   sq_change_text_domain('spam_buttons');



   $note = '';
   $success = FALSE;
   $abort_message_view = FALSE;



//TODO: if we implement "dont_wait" functionality, we can fork a child process here; then the child uses the reporting code below and exits, but the parent needs to skip to the section a few hundred lines down where it redirects back to the message list or read-message page ("DONE, WHERE DO WE RETURN TO?").  See the TODO section of the README file for some issues regarding this kind of functioality



   // -----------------------------------------------------------------
   //
   // HANDLE EXTRA BUTTON CLICK
   //


   if (!empty($extraButton))
   {
      list($result, $note) = sb_custom_button_action($button_name, $callback, $msg, $passed_ent_id);


      // what note do we use?  if we have success and there
      // is a note configured in the config file, use it
      //
      if ($result)
      {
         if (!empty($custom_button_success_singular)
          && !empty($custom_button_success_plural))
            $note = ngettext($custom_button_success_singular, $custom_button_success_plural, count($msg));
         else if (count($msg) < 2 && !empty($custom_button_success_singular))
            $note = _($custom_button_success_singular);
         else if (count($msg) > 1 && !empty($custom_button_success_plural))
            $note = _($custom_button_success_plural);
         else
            $note = _($note);
      }


      // this should never actually be needed, but to be safe, let's
      // make sure that none of the other action handlers below get
      // kicked off...
      //
      $isSpam = NULL;
      $notSpam = NULL;

   }



   // -----------------------------------------------------------------
   //
   // SPAM
   //


   // mark as spam!
   // 
   if (!empty($isSpam))
   {
   
      // move-to-folder (only when target mailbox is not the same as source mailbox)
      //
      // note that we don't have to to check for $passed_ent_id because the report
      // links/buttons are not shown when $passed_ent_id is non-zero, so we can never
      // get here
      //
      global $mailbox;
      if (!empty($sb_report_spam_by_move_to_folder)
       && $mailbox != $sb_report_spam_by_move_to_folder
       && is_array($msg))
      {

         global $auto_expunge, $imapConnection, $username, $key,
                $imapServerAddress, $imapPort;
         if (check_sm_version(1, 5, 2)) $key = FALSE; 
         if (!is_resource($imapConnection))
            $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

         if (check_sm_version(1, 5, 2))
         {
            global $aMailbox;
            sqGetGlobalVar('lastTargetMailbox', $lastTargetMailbox, SQ_SESSION);
            spam_buttons_auto_create_folder($imapConnection, $sb_report_spam_by_move_to_folder);
            if ($sb_report_spam_by_copy_to_folder)
               $note = handleMessageListForm($imapConnection, $aMailbox, 'copy', $msg, $sb_report_spam_by_move_to_folder);
            else
               $note = handleMessageListForm($imapConnection, $aMailbox, 'move', $msg, $sb_report_spam_by_move_to_folder);
            sqsession_register($lastTargetMailbox,'lastTargetMailbox');
         }
         else
         {
//TODO -- how to populate $note if an error occurs?  sqimap_msgs_list_copy() doesn't have a return value...
            if ($sb_report_spam_by_copy_to_folder)
               spam_buttons_sqimap_msgs_list_copy($imapConnection, $msg, $sb_report_spam_by_move_to_folder);
            else
               spam_buttons_sqimap_msgs_list_move($imapConnection, $msg, $sb_report_spam_by_move_to_folder);
            if ($auto_expunge) 
               $cnt = sqimap_mailbox_expunge($imapConnection, $mailbox, true);
            else 
               $cnt = 0;
         }


         // done reporting-by-move, now redirect user as needed 
         // right away if javascript is available and reporting
         // from message view, or just prepare redirect location 
         // otherwise
         //
         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?


            global $javascript_on, $username, $mbx_response, $mailbox,
                   $startMessage, $show_num, $sort, $imapConnection,
                   $key, $imapServerAddress, $imapPort, $account;
            $uri_args = 'mailbox=' . urlencode($mailbox)
                      . (!empty($sort) ? "&sort=$sort" : '')
                      . (!empty($account) ? "&account=$account" : '')
                      . (!empty($startMessage) ? "&startMessage=$startMessage" : '');
            if (check_sm_version(1, 5, 2)) $key = FALSE;
            if (empty($mbx_response))
            {
               if (!is_resource($imapConnection))
                  $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
               $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
            }


            // when reading message itself, redirect back to
            // message list after having moved it
            //
            if ($reporting_from_message_view && $javascript_on)
            {

//TODO: this may cause problems in 1.4.x, where output is probably already started
               sqsession_register($note, 'sb_note');


               // do we want to move to next message or return to message list?
               //
               global $redirect_location;
               if ($move_to_message_after_report < 0)
                  $redirect_location = sqm_baseuri() 
                                     . 'src/right_main.php?' . $uri_args;
               else
                  $redirect_location = sqm_baseuri() 
                                     . 'src/read_body.php?passed_id=' 
                                     . $move_to_message_after_report . '&' . $uri_args;


               // when viewing a message in the preview pane,
               // need to clear pane (or go to next message)
               // after delete as well as refresh message list
               //
               global $data_dir, $PHP_SELF;
               if (is_plugin_enabled('preview_pane')
                && getPref($data_dir, $username, 'use_previewPane', 0) == 1)
               {

                  global $request_refresh_message_list;
                  $request_refresh_message_list = 1;

                  // if not going to next message, go to empty preview pane
                  //
                  if ($move_to_message_after_report < 0)
                     $redirect_location = sqm_baseuri() . 'plugins/preview_pane/empty_frame.php';


                  // refresh message list & close
                  //
                  if (check_sm_version(1, 5, 2))
                  {
                     global $oTemplate;
                     $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                     $oTemplate->assign('request_refresh_message_list', $request_refresh_message_list);
                     $output = $oTemplate->fetch('plugins/spam_buttons/redirect_preview_pane.tpl');
                     return array('read_body_header' => $output);
                  }
                  else
                  {
                     global $t;
                     $t = array(); // no need to put config vars herein, they are already globalized
                     include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_preview_pane.tpl');
                  }

               }


               // otherwise, just redirect with javascript
               //
               else
               {
                  if (check_sm_version(1, 5, 2))
                  {
                     global $oTemplate;
                     $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                     $output = $oTemplate->fetch('plugins/spam_buttons/redirect_standard.tpl');
                     return array('read_body_header' => $output);
                  }
                  else
                  {
                     global $t;
                     $t = array(); // no need to put config vars herein, they are already globalized
                     include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_standard.tpl');
                  }

               }

            }


            // otherwise (reporting from message list or no javascript support), 
            // make sure we didn't move ourselves off the last page or anything 
            // like that (code copied from 1.4.11 src/move_messages.php)
            //
            else if (!$reporting_from_message_view)
            {
               if (($startMessage + $cnt - 1) >= $mbx_response['EXISTS'])
               {
                  if ($startMessage > $show_num)
                     $location = set_url_var($location,'startMessage',$startMessage-$show_num, false);
                  else
                     $location = set_url_var($location,'startMessage',1, false);
               }
            }


            // finally, if we don't have JavaScript and we moved the message
            // out from under the message view, so we need to indicate that 
            // the current message view needs to be aborted
            //
            else if ($reporting_from_message_view && !$javascript_on)
            {
               $abort_message_view = TRUE;
            }

         }

      }


      // shell command
      //
      else if (!empty($is_spam_shell_command))
      {

         $note = report_by_shell_command($is_spam_shell_command, $msg, $passed_ent_id);

         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?
         }

      }


      // re-send elsewhere via email
      //
      else if (!empty($is_spam_resend_destination))
      {

         $note = report_by_email($is_spam_resend_destination, $msg, $passed_ent_id, $is_spam_keep_copy_in_sent, $is_spam_subject_prefix);

         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?
         }

      }


      // custom function callout
      //
      else if (!empty($sb_report_spam_by_custom_function))
      {

         $note = $sb_report_spam_by_custom_function($msg, $passed_ent_id);

         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?
         }

      }


      else
//TODO: we could put a warning here for the sysadmin... (but note that it is possible to get here when report-by-move-to-folder is correctly configured but no messages were selected by the user before pressing the report button)
         $note = '';

   }



   // -----------------------------------------------------------------
   //
   // HAM
   //


   // mark as ham!
   // 
   else if (!empty($notSpam))
   {

      // move-to-folder (only when target mailbox is not the same as source mailbox)
      //
      // note that we don't have to to check for $passed_ent_id because the report
      // links/buttons are not shown when $passed_ent_id is non-zero, so we can never
      // get here
      //
      global $mailbox;
      if (!empty($sb_report_not_spam_by_move_to_folder)
       && $mailbox != $sb_report_not_spam_by_move_to_folder
       && is_array($msg))
      {

         global $auto_expunge, $imapConnection, $username, $key,
                $imapServerAddress, $imapPort;
         if (check_sm_version(1, 5, 2)) $key = FALSE;
         if (!is_resource($imapConnection))
            $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

         if (check_sm_version(1, 5, 2))
         {
            global $aMailbox;
            sqGetGlobalVar('lastTargetMailbox', $lastTargetMailbox, SQ_SESSION);
            spam_buttons_auto_create_folder($imapConnection, $sb_report_not_spam_by_move_to_folder);
            if ($sb_report_not_spam_by_copy_to_folder)
               $note = handleMessageListForm($imapConnection, $aMailbox, 'copy', $msg, $sb_report_not_spam_by_move_to_folder);
            else
               $note = handleMessageListForm($imapConnection, $aMailbox, 'move', $msg, $sb_report_not_spam_by_move_to_folder);
            sqsession_register($lastTargetMailbox,'lastTargetMailbox');
         }
         else
         {
//TODO -- how to populate $note if an error occurs?  sqimap_msgs_list_copy() doesn't have a return value...
            if ($sb_report_not_spam_by_copy_to_folder)
               spam_buttons_sqimap_msgs_list_copy($imapConnection, $msg, $sb_report_not_spam_by_move_to_folder);
            else
               spam_buttons_sqimap_msgs_list_move($imapConnection, $msg, $sb_report_not_spam_by_move_to_folder);
            if ($auto_expunge) 
               $cnt = sqimap_mailbox_expunge($imapConnection, $mailbox, true);
            else 
               $cnt = 0;
         }


         // done reporting-by-move, now redirect user as needed 
         // right away if javascript is available and reporting
         // from message view, or just prepare redirect location 
         // otherwise
         //
         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_not_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?


            global $javascript_on, $username, $mbx_response, $mailbox,
                   $startMessage, $show_num, $sort, $imapConnection,
                   $key, $imapServerAddress, $imapPort, $account;
            $uri_args = 'mailbox=' . urlencode($mailbox)
                      . (!empty($sort) ? "&sort=$sort" : '')
                      . (!empty($account) ? "&account=$account" : '')
                      . (!empty($startMessage) ? "&startMessage=$startMessage" : '');
            if (check_sm_version(1, 5, 2)) $key = FALSE;
            if (empty($mbx_response))
            {
               if (!is_resource($imapConnection))
                  $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
               $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
            }


            // when reading message itself, redirect back to
            // message list after having moved it
            //
            if ($reporting_from_message_view && $javascript_on)
            {

//TODO: this may cause problems in 1.4.x, where output is probably already started
               sqsession_register($note, 'sb_note');


               // do we want to move to next message or return to message list?
               //
               global $redirect_location;
               if ($move_to_message_after_report < 0)
                  $redirect_location = sqm_baseuri()
                                     . 'src/right_main.php?' . $uri_args;
               else
                  $redirect_location = sqm_baseuri()
                                     . 'src/read_body.php?passed_id='
                                     . $move_to_message_after_report . '&' . $uri_args;


               // when viewing a message in the preview pane,
               // need to clear pane (or go to next message)
               // after delete as well as refresh message list
               //
               global $data_dir, $PHP_SELF;
               if (is_plugin_enabled('preview_pane')
                && getPref($data_dir, $username, 'use_previewPane', 0) == 1)
               {

                  global $request_refresh_message_list;
                  $request_refresh_message_list = 1;

                  // if not going to next message, go to empty preview pane
                  //
                  if ($move_to_message_after_report < 0)
                     $redirect_location = sqm_baseuri() . 'plugins/preview_pane/empty_frame.php';


                  // refresh message list & close
                  //
                  if (check_sm_version(1, 5, 2))
                  {
                     global $oTemplate;
                     $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                     $oTemplate->assign('request_refresh_message_list', $request_refresh_message_list);
                     $output = $oTemplate->fetch('plugins/spam_buttons/redirect_preview_pane.tpl');
                     return array('read_body_header' => $output);
                  }
                  else
                  {
                     global $t;
                     $t = array(); // no need to put config vars herein, they are already globalized
                     include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_preview_pane.tpl');
                  }

               }


               // otherwise, just redirect with javascript
               //
               else
               {
                  if (check_sm_version(1, 5, 2))
                  {
                     global $oTemplate;
                     $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                     $output = $oTemplate->fetch('plugins/spam_buttons/redirect_standard.tpl');
                     return array('read_body_header' => $output);
                  }
                  else
                  {
                     global $t;
                     $t = array(); // no need to put config vars herein, they are already globalized
                     include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_standard.tpl');
                  }

               }

            }


            // otherwise (reporting from message list or no javascript support), 
            // make sure we didn't move ourselves off the last page or anything 
            // like that (code copied from 1.4.11 src/move_messages.php)
            //
            else if (!$reporting_from_message_view)
            {
               if (($startMessage + $cnt - 1) >= $mbx_response['EXISTS'])
               {
                  if ($startMessage > $show_num)
                     $location = set_url_var($location,'startMessage',$startMessage-$show_num, false);
                  else
                     $location = set_url_var($location,'startMessage',1, false);
               }
            }


            // finally, if we don't have JavaScript and we moved the message
            // out from under the message view, so we need to indicate that 
            // the current message view needs to be aborted
            //
            else if ($reporting_from_message_view && !$javascript_on)
            {
               $abort_message_view = TRUE;
            }

         }

      }


      // shell command
      //
      else if (!empty($is_not_spam_shell_command))
      {

         $note = report_by_shell_command($is_not_spam_shell_command, $msg, $passed_ent_id);


         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_not_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?
         }

      }


      // re-send elsewhere via email
      //
      else if (!empty($is_not_spam_resend_destination))
      {

         $note = report_by_email($is_not_spam_resend_destination, $msg, $passed_ent_id, $is_not_spam_keep_copy_in_sent, $is_not_spam_subject_prefix);

         if (empty($note)) 
         {
            $success = TRUE;
            $note = _($reported_not_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?
         }

      }


      // custom function callout
      //
      else if (!empty($sb_report_not_spam_by_custom_function))
      {

         $note = $sb_report_not_spam_by_custom_function($msg, $passed_ent_id);

         if (empty($note))
         {
            $success = TRUE;
            $note = _($reported_not_spam_text);
//TODO? include something that identifies the message(s) that were reported(have to add it up in the loop above)?  might take too much screen real estate....?
         }

      }


      else
//TODO: we could put a warning here for the sysadmin... (but note that it is possible to get here when report-by-move-to-folder is correctly configured but no messages were selected by the user before pressing the report button)
         $note = '';

   }


   sq_change_text_domain('squirrelmail');



   // -----------------------------------------------------------------
   //
   // REPORTED... NOW MOVE?
   //


   // move spam
   //
   if ($success && !empty($isSpam) && $sb_move_after_report_spam
    && (empty($sb_report_spam_by_move_to_folder)   // not if already moved!
     || $sb_report_spam_by_copy_to_folder) 
    && empty($passed_ent_id)  // not if reporting an attachment!
    && is_array($msg))
   {

      global $auto_expunge, $imapConnection, $username, $key, $show_num,
             $mbx_response, $imapServerAddress, $imapPort, $mailbox, 
             $startMessage, $sort, $account, $javascript_on;
      $uri_args = 'mailbox=' . urlencode($mailbox)
                . (!empty($sort) ? "&sort=$sort" : '')
                . (!empty($account) ? "&account=$account" : '')
                . (!empty($startMessage) ? "&startMessage=$startMessage" : '');
      if (check_sm_version(1, 5, 2)) $key = FALSE;
      if (!is_resource($imapConnection))
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
      if (empty($mbx_response))
         $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);

      // move messages only when target mailbox is not the same as source mailbox
      //
      if ($mailbox != $sb_move_after_report_spam) 
      {

         if (check_sm_version(1, 5, 2))
         {
            global $aMailbox;
            sqGetGlobalVar('lastTargetMailbox', $lastTargetMailbox, SQ_SESSION);
            spam_buttons_auto_create_folder($imapConnection, $sb_move_after_report_spam);
            if ($sb_copy_after_report_spam)
               $move_result = handleMessageListForm($imapConnection, $aMailbox, 'copy', $msg, $sb_move_after_report_spam);
            else
               $move_result = handleMessageListForm($imapConnection, $aMailbox, 'move', $msg, $sb_move_after_report_spam);
            sqsession_register($lastTargetMailbox,'lastTargetMailbox');
         }
         else
         {
            if ($sb_copy_after_report_spam)
               spam_buttons_sqimap_msgs_list_copy($imapConnection, $msg, $sb_move_after_report_spam);
            else
               spam_buttons_sqimap_msgs_list_move($imapConnection, $msg, $sb_move_after_report_spam);
            if ($auto_expunge) 
               $cnt = sqimap_mailbox_expunge($imapConnection, $mailbox, true);
            else 
               $cnt = 0;
         }


         // when reading message itself, redirect back to 
         // message list after having moved it
         //
         if ($reporting_from_message_view && $javascript_on)
         {

//TODO: this may cause problems in 1.4.x, where output is probably already started
            sqsession_register($note, 'sb_note');


            // do we want to move to next message or return to message list?
            //
            global $redirect_location;
            if ($move_to_message_after_report < 0)
               $redirect_location = sqm_baseuri()
                                  . 'src/right_main.php?' . $uri_args;
            else
               $redirect_location = sqm_baseuri()
                                  . 'src/read_body.php?passed_id='
                                  . $move_to_message_after_report . '&' . $uri_args;


            // when viewing a message in the preview pane,
            // need to clear pane (or go to next message)
            // after delete as well as refresh message list
            //
            global $data_dir, $PHP_SELF;
            if (is_plugin_enabled('preview_pane')
             && getPref($data_dir, $username, 'use_previewPane', 0) == 1)
            {

               global $request_refresh_message_list;
               $request_refresh_message_list = 1;

               // if not going to next message, go to empty preview pane
               //
               if ($move_to_message_after_report < 0)
                  $redirect_location = sqm_baseuri() . 'plugins/preview_pane/empty_frame.php';


               // refresh message list & close
               //
               if (check_sm_version(1, 5, 2))
               {
                  global $oTemplate;
                  $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                  $oTemplate->assign('request_refresh_message_list', $request_refresh_message_list);
                  $output = $oTemplate->fetch('plugins/spam_buttons/redirect_preview_pane.tpl');
                  return array('read_body_header' => $output);
               }
               else
               {
                  global $t;
                  $t = array(); // no need to put config vars herein, they are already globalized
                  include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_preview_pane.tpl');
               }

            }


            // otherwise, just redirect with javascript
            //
            else
            {
               if (check_sm_version(1, 5, 2))
               {
                  global $oTemplate;
                  $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                  $output = $oTemplate->fetch('plugins/spam_buttons/redirect_standard.tpl');
                  return array('read_body_header' => $output);
               }
               else
               {
                  global $t;
                  $t = array(); // no need to put config vars herein, they are already globalized
                  include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_standard.tpl');
               }

            }

         }


         // otherwise (reporting from message list or no javascript support), 
         // make sure we didn't move ourselves off the last page or anything 
         // like that (code copied from 1.4.11 src/move_messages.php)
         //
         else if (!$reporting_from_message_view)
         {
            if (($startMessage + $cnt - 1) >= $mbx_response['EXISTS']) 
            {
               if ($startMessage > $show_num) 
                  $location = set_url_var($location,'startMessage',$startMessage-$show_num, false);
               else
                  $location = set_url_var($location,'startMessage',1, false);
            }
         }


         // finally, if we don't have JavaScript and we moved the message
         // out from under the message view, so we need to indicate that 
         // the current message view needs to be aborted
         //
         else if ($reporting_from_message_view && !$javascript_on)
         {
            $abort_message_view = TRUE;
         }

      }

   }


   // move ham
   //
   if ($success && !empty($notSpam) && $sb_move_after_report_not_spam
    && (empty($sb_report_not_spam_by_move_to_folder)   // not if already moved!
     || $sb_report_not_spam_by_copy_to_folder) 
    && empty($passed_ent_id)  // not if reporting an attachment!
    && is_array($msg))
   {

      global $auto_expunge, $imapConnection, $username, $key, $show_num,
             $mbx_response, $imapServerAddress, $imapPort, $mailbox, 
             $startMessage, $sort, $account, $javascript_on;
      $uri_args = 'mailbox=' . urlencode($mailbox)
                . (!empty($sort) ? "&sort=$sort" : '')
                . (!empty($account) ? "&account=$account" : '')
                . (!empty($startMessage) ? "&startMessage=$startMessage" : '');
      if (check_sm_version(1, 5, 2)) $key = FALSE;
      if (!is_resource($imapConnection))
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
      if (empty($mbx_response))
         $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);

      // move messages only when target mailbox is not the same as source mailbox
      //
      if ($mailbox != $sb_move_after_report_not_spam) 
      {

         if (check_sm_version(1, 5, 2))
         {
            global $aMailbox;
            sqGetGlobalVar('lastTargetMailbox', $lastTargetMailbox, SQ_SESSION);
            spam_buttons_auto_create_folder($imapConnection, $sb_move_after_report_not_spam);
            if ($sb_copy_after_report_not_spam)
               $move_result = handleMessageListForm($imapConnection, $aMailbox, 'copy', $msg, $sb_move_after_report_not_spam);
            else
               $move_result = handleMessageListForm($imapConnection, $aMailbox, 'move', $msg, $sb_move_after_report_not_spam);
            sqsession_register($lastTargetMailbox,'lastTargetMailbox');
         }
         else
         {
            if ($sb_copy_after_report_not_spam)
               spam_buttons_sqimap_msgs_list_copy($imapConnection, $msg, $sb_move_after_report_not_spam);
            else
               spam_buttons_sqimap_msgs_list_move($imapConnection, $msg, $sb_move_after_report_not_spam);
            if ($auto_expunge) 
               $cnt = sqimap_mailbox_expunge($imapConnection, $mailbox, true);
            else 
               $cnt = 0;
         }


         // when reading message itself, redirect back to 
         // message list after having moved it
         //
         if ($reporting_from_message_view && $javascript_on)
         {

//TODO: this may cause problems in 1.4.x, where output is probably already started
            sqsession_register($note, 'sb_note');


            // do we want to move to next message or return to message list?
            //
            global $redirect_location;
            if ($move_to_message_after_report < 0)
               $redirect_location = sqm_baseuri()
                                  . 'src/right_main.php?' . $uri_args;
            else
               $redirect_location = sqm_baseuri()
                                  . 'src/read_body.php?passed_id='
                                  . $move_to_message_after_report . '&' . $uri_args;


            // when viewing a message in the preview pane,
            // need to clear pane (or go to next message)
            // after delete as well as refresh message list
            //
            global $data_dir, $PHP_SELF;
            if (is_plugin_enabled('preview_pane')
             && getPref($data_dir, $username, 'use_previewPane', 0) == 1)
            {

               global $request_refresh_message_list;
               $request_refresh_message_list = 1;

               // if not going to next message, go to empty preview pane
               //
               if ($move_to_message_after_report < 0)
                  $redirect_location = sqm_baseuri() . 'plugins/preview_pane/empty_frame.php';


               // refresh message list & close
               //
               if (check_sm_version(1, 5, 2))
               {
                  global $oTemplate;
                  $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                  $oTemplate->assign('request_refresh_message_list', $request_refresh_message_list);
                  $output = $oTemplate->fetch('plugins/spam_buttons/redirect_preview_pane.tpl');
                  return array('read_body_header' => $output);
               }
               else
               {
                  global $t;
                  $t = array(); // no need to put config vars herein, they are already globalized
                  include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_preview_pane.tpl');
               }

            }


            // otherwise, just redirect with javascript
            //
            else
            {
               if (check_sm_version(1, 5, 2))
               {
                  global $oTemplate;
                  $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                  $output = $oTemplate->fetch('plugins/spam_buttons/redirect_standard.tpl');
                  return array('read_body_header' => $output);
               }
               else
               {
                  global $t;
                  $t = array(); // no need to put config vars herein, they are already globalized
                  include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_standard.tpl');
               }

            }

         }


         // otherwise (reporting from message list or no javascript support), 
         // make sure we didn't move ourselves off the last page or anything 
         // like that (code copied from 1.4.11 src/move_messages.php)
         //
         else if (!$reporting_from_message_view)
         {
            if (($startMessage + $cnt - 1) >= $mbx_response['EXISTS']) 
            {
               if ($startMessage > $show_num) 
                  $location = set_url_var($location,'startMessage',$startMessage-$show_num, false);
               else
                  $location = set_url_var($location,'startMessage',1, false);
            }
         }


         // finally, if we don't have JavaScript and we moved the message
         // out from under the message view, so we need to indicate that 
         // the current message view needs to be aborted
         //
         else if ($reporting_from_message_view && !$javascript_on)
         {
            $abort_message_view = TRUE;
         }

      }

   }



   // -----------------------------------------------------------------
   //
   // OR DELETE?
   //


   // delete spam if needed (but not if already moved)
   //
   if ($success && $sb_delete_after_report && !empty($isSpam) 
    && !$sb_move_after_report_spam  // not if already moved!
    && empty($passed_ent_id)  // not if reporting an attachment!
    && empty($sb_report_spam_by_move_to_folder))  // not if already moved!
   {

      if (is_array($msg))
      {
         global $auto_expunge, $imapConnection, $username, $key, $show_num,
                $mbx_response, $imapServerAddress, $imapPort, $mailbox, 
                $startMessage, $sort, $account, $javascript_on;
         $uri_args = 'mailbox=' . urlencode($mailbox)
                   . (!empty($sort) ? "&sort=$sort" : '')
                   . (!empty($account) ? "&account=$account" : '')
                   . (!empty($startMessage) ? "&startMessage=$startMessage" : '');
         if (check_sm_version(1, 5, 2)) $key = FALSE;
//LEFT OFF HERE -- debugging UW 
//sm_print_r('$imapConnection contains:', $imapConnection);
//sqimap_logout($imapConnection);
         if (!is_resource($imapConnection))
            $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
//echo '$mbx_response now contains:<br />';
//sm_print_r($mbx_response);
         if (empty($mbx_response))
//{
            $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
//echo 'And now $mbx_response contains:<br />';
//sm_print_r($mbx_response);
//}

         if (check_sm_version(1, 5, 2))
         {
            global $aMailbox;
            handleMessageListForm($imapConnection, $aMailbox, 'setDeleted', $msg);
         }
         else
         {
            sqimap_msgs_list_delete($imapConnection, $mailbox, $msg);
//echo "Finished deleting; now expunge if necessary...<br />";
            if ($auto_expunge) 
            {
//echo "Expunging...<br />";
               $cnt = sqimap_mailbox_expunge($imapConnection, $mailbox, true);
            }
//echo "Finished<br />";
//exit;
         }

         
         // when reading message itself, redirect back to 
         // message list after deletion
         //
         if ($reporting_from_message_view && $javascript_on)
         {

//TODO: this may cause problems in 1.4.x, where output is probably already started
            sqsession_register($note, 'sb_note');


            // do we want to move to next message or return to message list?
            //
            global $redirect_location;
            if ($move_to_message_after_report < 0)
               $redirect_location = sqm_baseuri()
                                  . 'src/right_main.php?' . $uri_args;
            else
               $redirect_location = sqm_baseuri()
                                  . 'src/read_body.php?passed_id='
                                  . $move_to_message_after_report . '&' . $uri_args;


            // when viewing a message in the preview pane,
            // need to clear pane (or go to next message)
            // after delete as well as refresh message list
            //
            global $data_dir, $PHP_SELF;
            if (is_plugin_enabled('preview_pane')
             && getPref($data_dir, $username, 'use_previewPane', 0) == 1)
            {

               global $request_refresh_message_list;
               $request_refresh_message_list = 1;

               // if not going to next message, go to empty preview pane
               //
               if ($move_to_message_after_report < 0)
                  $redirect_location = sqm_baseuri() . 'plugins/preview_pane/empty_frame.php';


               // refresh message list & close
               //
               if (check_sm_version(1, 5, 2))
               {
                  global $oTemplate;
                  $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                  $oTemplate->assign('request_refresh_message_list', $request_refresh_message_list);
                  $output = $oTemplate->fetch('plugins/spam_buttons/redirect_preview_pane.tpl');
                  return array('read_body_header' => $output);
               }
               else
               {
                  global $t;
                  $t = array(); // no need to put config vars herein, they are already globalized
                  include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_preview_pane.tpl');
               }

            }


            // otherwise, just redirect with javascript
            //
            else
            {
               if (check_sm_version(1, 5, 2))
               {
                  global $oTemplate;
                  $oTemplate->assign('redirect_location', $redirect_location, FALSE);
                  $output = $oTemplate->fetch('plugins/spam_buttons/redirect_standard.tpl');
                  return array('read_body_header' => $output);
               }
               else
               {
                  global $t;
                  $t = array(); // no need to put config vars herein, they are already globalized
                  include(SM_PATH . 'plugins/spam_buttons/templates/default/redirect_standard.tpl');
               }

            }

         }


         // otherwise (reporting from message list or no javascript support), 
         // make sure we didn't move ourselves off the last page or anything 
         // like that (code copied from 1.4.11 src/move_messages.php)
         //
         else if (!$reporting_from_message_view)
         {
            if (($startMessage + $cnt - 1) >= $mbx_response['EXISTS']) 
            {
               if ($startMessage > $show_num) 
                  $location = set_url_var($location,'startMessage',$startMessage-$show_num, false);
               else
                  $location = set_url_var($location,'startMessage',1, false);
            }
         }


         // finally, if we don't have JavaScript and we moved the message
         // out from under the message view, so we need to indicate that 
         // the current message view needs to be aborted
         //
         else if ($reporting_from_message_view && !$javascript_on)
         {
            $abort_message_view = TRUE;
         }

      }

   }



   // -----------------------------------------------------------------
   //
   // DONE, WHERE DO WE RETURN TO?
   //


   // if no note, then we shouldn't do anything at all
   //
   if (empty($note)) 
   {
      if (check_sm_version(1, 5, 0))
         return;
      else
      {
         header('Location: ' . $location);
         exit;
      }
   }


   // when reading message itself, print message 
   // in header and continue (unless message has been deleted)
   //
   if ($reporting_from_message_view)
   {
      if (check_sm_version(1, 5, 2))
         global $br;
      else
         $br = '<br />';

      if ($abort_message_view)
         $note .= $br . _("The reported message is no longer available in this folder");

      if (check_sm_version(1, 5, 2))
      {
         global $oTemplate;
         $oTemplate->assign('note', $note, FALSE); // FALSE because of use of $br
         $output = $oTemplate->fetch('plugins/spam_buttons/confirmation_note.tpl');
         return array('read_body_header' => $output);
         // note that message view will be aborted in next executed
         // template hook: template_construct_read_message_body.tpl
         // using the global $abort_message_view variable
      }
      else
      {
         echo html_tag('tr', html_tag('td', "<strong>$note</strong>", 'center', '', ' colspan="2"'));

         // if we need to abort, do that now
         //
         if ($abort_message_view)
         {
            echo '</table></td></tr></table></body></html>';
            exit;
         }

         return;
      }
   }



   //
   // behavior is different when button on message list was clicked
   //


   // SM 1.5.2+: $note will be displayed automatically, so 
   // all we need to do is return
   //
   if (check_sm_version(1, 5, 2))
   {
      global $preselected;
      $preselected = array_keys($prechecked);
      return;
   }


   // SM 1.5: just display note and let go
   //
   else if (check_sm_version(1, 5, 0))
   {
      global $preselected;
      $preselected = array_keys($prechecked);
      echo html_tag('div', '<b>' . $note .'</b>', 'center') . "<br />\n";
      return;
   }


   // For SM 1.4.x, redirect to message list so SM 
   // doesn't try to do something else funny (1.4.x 
   // assumes it has to delete messages... yikes)
   //
   else
   {
      // put note in session so we can display it when back in 
      // message list, without putting $note in the location, since
      // it is hard to remove from there, even on subsequent requests
      //
      sqsession_register($note, 'sb_note');

      // compress prechecked array and pass it along
      //
      $query = '';
      foreach ($prechecked as $msgid => $ignore)
         $query .= '&preselected[' . $msgid . ']=1';
      if (strpos($location, '?') === FALSE)
         $query{0} = '?';

      //header('Location: ' . $location . $query . '&note=' . urlencode($note));
      header('Location: ' . $location . $query);
      exit;
   }
       

}



/**
  * Reports one or more spam/non-spam using email redirection.
  *
  * @param string  $destination       The email address to which the
  *                                   message should be redirected.
  * @param array   $msg               An array of message IDs to be reported.
  * @param string  $passed_ent_id     The message entity being reported
  *                                   (zero if the message itself is being
  *                                   reported (only applicable when there
  *                                   is just one element in the $msg array))
  * @param boolean $keep_copy_in_sent When sending as an attachment,
  *                                   should a copy of the spam report
  *                                   being sent out get stored in the
  *                                   user's sent folder?
  * @param string  $subjectPrefix     Any extra subject info for 
  *                                   redirected mail's subject.
  *                                   (optional; default is empty 
  *                                   string, nothing is done to subject)
  *
  * @return string An error message if an error occurred,
  *                empty string otherwise
  *
  */
function report_by_email($destination, $msg, $passed_ent_id,
                         $keep_copy_in_sent, $subjectPrefix='')
{

   global $spam_report_email_method, $spam_report_smtpServerAddress, 
          $spam_report_smtpPort, $spam_report_useSendmail, 
          $spam_report_smtp_auth_mech, $spam_report_use_smtp_tls,
          $smtpServerAddress, $smtpPort, $useSendmail, $smtp_auth_mech, 
          $use_smtp_tls, $sb_debug, $data_dir, $username, $domain;
   $at_sign = '@';

   spam_buttons_init();


   // do replacements on destination
   //
   if (strpos($username, $at_sign) !== FALSE)
      list($user, $dom) = explode($at_sign, $username);
   else
   {
      $user = $username;
      $dom = $domain;
   }
   $email_address = getPref($data_dir, $username, 'email_address');
   $destination = str_replace(array('###EMAIL_PREF###', '###EMAIL_ADDRESS###', '###USERNAME###', '###DOMAIN###'), 
                              array($email_address, $user . $at_sign . $dom, $user, $dom), 
                              $destination);


   // take care of overrides for SMTP server
   //
   if (!empty($spam_report_smtpServerAddress))
      $smtpServerAddress = $spam_report_smtpServerAddress;
   if (!empty($spam_report_smtpPort))
      $smtpPort = $spam_report_smtpPort;
   if ($spam_report_useSendmail !== '')
      if (strtolower($spam_report_useSendmail) === 'false')
         $useSendmail = FALSE;
      else
         $useSendmail = $spam_report_useSendmail;
   if (!empty($spam_report_smtp_auth_mech))
      $smtp_auth_mech = $spam_report_smtp_auth_mech;
   if (!empty($spam_report_use_smtp_tls))
      $use_smtp_tls = $spam_report_use_smtp_tls;


   $note = '';


   // redirect by bouncing message and preserving headers
   //
   if ($spam_report_email_method == 'bounce')
   {

      global $imapServerAddress, $imapPort, $useSendmail;


      // we will be manually manipulating $_GET, so...
      //
      global $_GET;
      if (!check_php_version(4,1)) 
      {
         global $HTTP_GET_VARS;
         $_GET = $HTTP_GET_VARS;
      }


      if (is_array($msg)) foreach ($msg as $messageID)
      {
         $_GET['bounce_send_to'] = $destination;
         $_GET['passed_id'] = $messageID;
         $_GET['passed_ent_id'] = $passed_ent_id;
         require(SM_PATH . 'plugins/spam_buttons/bounce_send.php');
      }

   }


   // redirect by including message as an attachment
   //
   else
   {

      // some versions of SM need this when using some compose functions
      //
      if (!function_exists('addressbook_init'))
         include_once(SM_PATH . 'functions/addressbook.php');

      sqGetGlobalVar('mailbox', $mailbox);
      if (check_sm_version(1, 5, 2))
         include_once(SM_PATH . 'plugins/spam_buttons/compose_functions-1.5.2.php');
      else if (check_sm_version(1, 4, 14))
         include_once(SM_PATH . 'plugins/spam_buttons/compose_functions-1.4.14.php');
      else if (check_sm_version(1, 4, 11))
         include_once(SM_PATH . 'plugins/spam_buttons/compose_functions-1.4.11.php');
      else
         include_once(SM_PATH . 'plugins/spam_buttons/compose_functions-1.4.10.php');


      if (is_array($msg)) foreach ($msg as $messageID)
      {

         global $composeMessage, $send_to, $subject, $sb_keep_copy_in_sent;

         $sb_keep_copy_in_sent = $keep_copy_in_sent;

         $composeMessage = new Message();
         $rfc822_header = new Rfc822Header();
         $composeMessage->rfc822_header = $rfc822_header;
         $composeMessage->reply_rfc822_header = '';

         $message = newMail($mailbox, $messageID, $passed_ent_id, 'forward_as_attachment', '');
         $subject = $message['subject'];
         if (!empty($subjectPrefix))
            $subject = preg_replace('/fwd/i', $subjectPrefix, $subject);

         $send_to = $destination;

         $Result = deliverMessage($composeMessage);
         if (! $Result) 
         {
            sq_change_text_domain('spam_buttons');
            $note = _("ERROR: Report could not be delivered");
            sq_change_text_domain('squirrelmail');
            break; 
         }

         // dump stuff out if debugging
         //
         if ($sb_debug)
         {
            echo '<hr /><strong>EMAIL ADDRESS USED TO REPORT:</strong> ' . $destination . '<br /><br />';
            echo '<hr /><strong>MESSAGE BODY AS REPORTED:</strong> (note that this is a parsed representation thereof)';
            sm_print_r($composeMessage);
            echo '<br /><br />';
            exit;
         }

      }

   }


   return $note;

}



/**
  * Reports one or more spam/non-spam using the shell
  * command provided.
  *
  * @param string $command       The shell command to be used.
  * @param array  $msg           An array of message IDs to be reported.
  * @param string $passed_ent_id The message entity being reported
  *                              (zero if the message itself is being
  *                              reported (only applicable when there
  *                              is just one element in the $msg array))
  *
  * @return string An error message if an error occurred, 
  *                empty string otherwise
  *
  */
function report_by_shell_command($command, $msg, $passed_ent_id)
{

   global $attachment_dir, $data_dir, $username, $domain, $sb_debug;
   $at_sign = '@';

   spam_buttons_init();


   $passed_ent_id = 0;
   sqGetGlobalVar('passed_ent_id',   $passed_ent_id,  SQ_FORM);
   sqGetGlobalVar('mailbox', $mailbox);


   // do replacements on command
   //
   if (strpos($username, $at_sign) !== FALSE)
      list($user, $dom) = explode($at_sign, $username);
   else
   {
      $user = $username;
      $dom = $domain;
   }
   $email_address = getPref($data_dir, $username, 'email_address');
   $command = str_replace(array('###EMAIL_PREF###', '###EMAIL_ADDRESS###', '###USERNAME###', '###DOMAIN###'), 
                          array($email_address, $user . $at_sign . $dom, $user, $dom), 
                          $command);


   $note = '';
   $timestamp = time();


   if (is_array($msg)) foreach ($msg as $messageID)
   {
      
      // get message body, correctly formatted
      //
      global $uid_support, $imapConnection, $username, $key, 
             $mbx_response, $imapServerAddress, $imapPort, $mailbox; 
      if (check_sm_version(1, 5, 2)) { $key = FALSE; $uid_support = TRUE; }

      if (!is_resource($imapConnection))
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
      if (empty($mbx_response))
         $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
      $response = '';
      $message = '';

      if (empty($passed_ent_id))
         $raw_message = sqimap_run_command($imapConnection, "FETCH $messageID BODY.PEEK[]", true, $response, $message, $uid_support);
      else
         $raw_message = sqimap_run_command($imapConnection, "FETCH $messageID BODY.PEEK[$passed_ent_id]", true, $response, $message, $uid_support);

      if ($response != 'OK')
      {
         global $color;
         sq_change_text_domain('spam_buttons');
         $msg = sprintf(_("Could not find requested message: %s"), $message);
         sq_change_text_domain('squirrelmail');
         $ret = plain_error_message($msg, $color);
         if (check_sm_version (1, 5, 2)) 
         {
            echo $ret;
            global $oTemplate;
            $oTemplate->display('footer.tpl');
         }
         exit;
      }

      // rebuild the message exactly as it comes from the IMAP server
      // (except first and last array entries are command wrappers, so skip them)
      //
      array_shift($raw_message);
      array_pop($raw_message);
      $raw_message = implode('', $raw_message);


      // store message in attachments directory in temp file
      //
      $tempFile = $attachment_dir . '/sb_tmp_' . $messageID . '_' . $timestamp;
      $tempFileOK = FALSE;
      if ($FILE = @fopen($tempFile, 'w'))
      {

         fwrite($FILE, $raw_message);
         fclose($FILE);
         $tempFileOK = TRUE;


         // run command
         // 
         $cmd = $command . ' < ' . $tempFile;
//sm_print_r($cmd, $raw_message);exit;
         $lastLineOfOutput = exec($cmd, $allOutput, $retValue);
//sm_print_r($allOutput);exit;


         // remove temp file
         //
         unlink($tempFile);


         // dump stuff out if debugging
         //
         if ($sb_debug)
         {
            echo '<hr /><strong>COMMAND USED TO REPORT:</strong> ' . $cmd . '<br /><br />';
            echo '<hr /><strong>MESSAGE BODY AS REPORTED:</strong> ';
            sm_print_r($raw_message);
            echo '<br /><br />';
            echo '<hr /><strong>RESULTS FROM REPORT:</strong> (' . $retValue . ')';
            sm_print_r($allOutput);
            echo '<br /><br />';
            exit;
         }

      }


      // couldn't open temp file
      //
      if (!$tempFileOK)
      {
         $note = _("ERROR: Could not open temp file; check attachments directory permissions");
         break; 
      }
      
      
      // oops, command failed 
      //
      else if ($retValue !== 0)
      {
         if (empty($passed_ent_id))
            $note = str_replace(array('%1', '%2', '%3'),
                                array($retValue, $messageID, $lastLineOfOutput),
                                _("ERROR %1: Problem reporting message ID %2: %3"));
         else
            $note = str_replace(array('%1', '%2', '%3', '%4'),
                                array($retValue, $messageID, $lastLineOfOutput, $passed_ent_id),
                                _("ERROR %1: Problem reporting message ID %2 (entity %4): %3"));
         break;
      }

   }

   return $note;

}



/**
  * Abort message view when message is moved/deleted
  * out from under current message view (SM 1.5.2+ only)
  *
  */
function sb_abort_message_view_do()
{

   global $abort_message_view;

   if ($abort_message_view)
   {
      global $oTemplate;
      $oTemplate->display('footer.tpl');
      exit;
   }

}



//
// ripped from functions/auth.php (merged both STABLE and DEVEL;
// the only difference being how the hook is handled)
//

/**
 * Fillin user and password based on SMTP auth settings.
 *
 * @param string $user Reference to SMTP username
 * @param string $pass Reference to SMTP password (unencrypted)
 */
if (!function_exists('get_smtp_user'))
{
function get_smtp_user(&$user, &$pass) {
    global $username, $smtp_auth_mech,
           $smtp_sitewide_user, $smtp_sitewide_pass;

    if ($smtp_auth_mech == 'none') {
        $user = '';
        $pass = '';
    } elseif ( isset($smtp_sitewide_user) && isset($smtp_sitewide_pass) &&
               !empty($smtp_sitewide_user)) {
        $user = $smtp_sitewide_user;
        $pass = $smtp_sitewide_pass;
    } else {
        $user = $username;
        $pass = sqauth_read_password();
    }

    if (check_sm_version(1, 5, 2)) {
        $temp = array(&$user, &$pass);
        do_hook('smtp_auth', $temp);
    } else {
        $ret = do_hook_function('smtp_auth', array($user, $pass));
        if (!empty($ret[0]))
            $user = $ret[0];
        if (!empty($ret[1]))
            $pass = $ret[1];
    }
}
}



// findPreviousMessage()'s prototype changed as of 1.5.1
//
function spam_buttons_findPreviousMessage($passed_id)
{
   if (check_sm_version(1, 5, 1))
   {
      if (!sqGetGlobalVar('what', $what, SQ_GET)) $what = 0;
      global $aMailbox;
      return findPreviousMessage($aMailbox['UIDSET'][$what], $passed_id);
   }
   else
   {
      global $mbx_response;
      if (empty($mbx_response))
      {
         global $imapConnection, $mailbox, $key, $username, 
                $imapServerAddress, $imapPort;
         if (!is_resource($imapConnection))
            $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
      }
      return findPreviousMessage($mbx_response['EXISTS'], $passed_id);
   }
}



// findNextMessage()'s prototype changed as of 1.5.1
//
function spam_buttons_findNextMessage($passed_id)
{
   if (check_sm_version(1, 5, 1))
   {
      if (!sqGetGlobalVar('what', $what, SQ_GET)) $what = 0;
      global $aMailbox;
      return findNextMessage($aMailbox['UIDSET'][$what], $passed_id);
   }
   else
      return findNextMessage($passed_id);
}



// The move and copy function names change as of 1.4.18 to be what 
// they say they are, so use those if we are at the right version.
//
// For versions less than 1.4.18, there is no copy function, so we
// use a modified version of the move function from 1.4.11 (which,
// ironically is mis-labelled "copy")
//
// Also, if configured to do so, the target folder will be created
// first if it does not exist.
//
function spam_buttons_sqimap_msgs_list_copy($imap_stream, $id, $mailbox) {

   spam_buttons_auto_create_folder($imap_stream, $mailbox);


   if (check_sm_version(1, 4, 18))
      return sqimap_msgs_list_copy($imap_stream, $id, $mailbox);


   // here is our own modified version of 1.4.11's move ("copy") function...
   //
   global $uid_support;
   $msgs_id = sqimap_message_list_squisher($id);
   $read = sqimap_run_command ($imap_stream, "COPY $msgs_id \"$mailbox\"", true, $response, $message, $uid_support);
   if ($response == 'OK')
      return true;
   else
      return false;

}



// the move and copy function names change as of 1.4.18 to be what they say they are
//
// Also, if configured to do so, the target folder will be created
// first if it does not exist.
//
function spam_buttons_sqimap_msgs_list_move($imap_stream, $id, $mailbox) {

   spam_buttons_auto_create_folder($imap_stream, $mailbox);


   if (check_sm_version(1, 4, 18))
      return sqimap_msgs_list_move($imap_stream, $id, $mailbox);


   // mis-labelled function name in versions less than 1.4.18
   //
   return sqimap_msgs_list_copy($imap_stream, $id, $mailbox);

}



// create a folder if it does not exist if necessary
//
function spam_buttons_auto_create_folder($imap_stream, $mailbox)
{

   // auto-create non-existing folder?
   //
   global $sb_auto_create_destination_folder; // assume config file already globally included
   if ($sb_auto_create_destination_folder 
    && !sqimap_mailbox_exists($imap_stream, $mailbox))
      sqimap_mailbox_create($imap_stream, $mailbox, '');

}



/**
  * Execute action for custom button click.
  *
  * @param string $button_name    The name of the button or link
  *                               (with non-alphanumerics having
  *                               been replaced with underscores).
  * @param string $callback       The name of the function that
  *                               will handle the button action.
  * @param array  $messages       A list of message IDs.
  * @param string $passed_ent_id  Entity ID when message is an
  *                               attachment (might be empty).
  *
  * @return array A two-element array, the first element being
  *               a boolean value that is TRUE if all message(s)
  *               were processed normally and FALSE if some error
  *               occured.  The second element is a string containing
  *               a note (untranslated) that will be displayed to
  *               the user upon completion (may be blank, in which
  *               case, any message from the user configuration will
  *               be used if available).
  *
  */
function sb_custom_button_action($button_name, $callback, $messages, $passed_ent_id)
{

   global $username;
   $result = array(FALSE, 'ERROR: Unknown error');


   // first, make sure the callback is correctly configured
   //
   if (empty($callback) || !function_exists($callback))
   {
      global $color;
      
      // if users complain of seeing a message "Function  not found in 
      // Spam Buttons plugin", it means they don't have a action callback
      // defined at all
      //
      sq_change_text_domain('spam_buttons');
      $msg = sprintf(_("Function %s not found in Spam Buttons plugin"), $callback);
      sq_change_text_domain('squirrelmail');
      $ret = plain_error_message($msg, $color);
      if (check_sm_version (1, 5, 2))
      {
         echo $ret;
         global $oTemplate;
         $oTemplate->display('footer.tpl');
      }
      exit;
   }


   // loop through each message (even if there is just one)
   //
   foreach ($messages as $message_id)
   {

      // this retrieves the message's From header in the format
      // array(0 => 'From:', 1 => '"Jose" <jose@example.org>')
      //
      $from = sb_get_message_header($message_id, $passed_ent_id, 'From');


      // this parses out just the email address portion of the From header
      //
      if (function_exists('parseRFC822Address'))
      {
         $from = parseRFC822Address($from[1], 1);
         $from = $from[0][2] . '@' . $from[0][3];
      }
      else
      {
         $from = parseAddress($from[1], 1);
         $from = $from[0][0];
      }


      // execute the callback
      //
      $result = $callback($button_name, $username, $from, $message_id, $passed_ent_id);

      if (!is_array($result)) $result = array($result, '');

      if (!$result[0])
         return array(FALSE, 'ERROR: ' . $result[1]);

   }


   // finished, just return success (which will be the last known
   // value of $result... any note therein will also be used)
   //
   return $result;

}



