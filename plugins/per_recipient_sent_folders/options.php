<?php

/**
  * SquirrelMail Per Recipient Sent Folders Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage per_recipient_sent_folders
  *
  */



/**
  * Display user configuration options on folder
  * preferences page
  *
  */
function prsf_options()
{
          
   global $data_dir, $username, $optpage_data, $prsf_detect_upon_send_default,
          $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient_default;
         
   include_once(SM_PATH . 'plugins/per_recipient_sent_folders/functions.php');
   prsf_init();

   
   // get folder list...
   // 
   global $key, $imapServerAddress, $imapPort, $imap_stream_options, $imapConnection;
   if (check_sm_version(1, 5, 2))
   {
      $key = FALSE;
      include_once(SM_PATH . 'functions/imap_general.php');
   }
   if (!is_resource($imapConnection))
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0, $imap_stream_options);
   $boxes = sqimap_mailbox_list($imapConnection);


   sq_change_text_domain('per_recipient_sent_folders');

   $my_optpage_values = array();


   $prsf_detect_upon_send = getPref($data_dir, $username,
                                    'prsf_detect_upon_send',
                                    $prsf_detect_upon_send_default);
   $my_optpage_values[] = array(
      'name'          => 'prsf_detect_upon_send',
      'caption'       => _("Track Recipient To Sent Folder Associations When Sending"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'initial_value' => $prsf_detect_upon_send,
      'refresh'       => SMOPT_REFRESH_NONE,
   );


   $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient
      = getPref($data_dir, $username,
                'prsf_use_previous_multi_recipient_sent_folder_for_single_recipient',
                $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient_default);
   $my_optpage_values[] = array(
      'name'          => 'prsf_use_previous_multi_recipient_sent_folder_for_single_recipient',
      'caption'       => _("Use Sent Folder For Single Recipients That Were Part Of A Previous Multiple-Recipient Message"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'initial_value' => $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient,
      'refresh'       => SMOPT_REFRESH_NONE,
   );


   $per_recipient_sent_folders = getPref($data_dir, $username, 'per_recipient_sent_folders', '');
   if (!empty($per_recipient_sent_folders) && is_string($per_recipient_sent_folders))
      $per_recipient_sent_folders = unserialize($per_recipient_sent_folders);
   $my_optpage_values[] = array(
      'name'               => 'per_recipient_sent_folders',
      'caption'            => _("Recipient Sent Folders"),
      'type'               => SMOPT_TYPE_EDIT_LIST_ASSOCIATIVE,
      'layout_type'        => SMOPT_EDIT_LIST_LAYOUT_SELECT,
      'poss_value_folders' => array('ignore' => $boxes),
      'size'               => SMOPT_SIZE_MEDIUM,
      'posvals'            => $per_recipient_sent_folders,
      'refresh'            => SMOPT_REFRESH_NONE,
      'save'               => 'prsf_save_recipient_sent_folders',
   );


   // add to the variable sent folders plugin subsection
   // of the folder options (be careful to merge with options
   // from that plugin if it has already executed)
   //
   $optpage_data['grps']['variable_sent_folder'] = _("Custom Sent Folder");
   if (empty($optpage_data['vals']['variable_sent_folder']))
      $optpage_data['vals']['variable_sent_folder'] = array();
   $optpage_data['vals']['variable_sent_folder'] = array_merge($optpage_data['vals']['variable_sent_folder'], $my_optpage_values);


   sq_change_text_domain('squirrelmail');

}



/**
  * Save per-recipient sent folders submitted from the
  * SquirrelMail options page (called from within the
  * options save routine). This function is intended
  * to do ancillary processing and use the internal
  * SquirrelMail facilities to actually save the groups
  * data.
  *
  * Specifically, recipient lists need to be valid lists
  * of email addresses.
  *
  * @param object $option The option class object representing
  *                       the address book group data being
  *                       saved.
  *
  */
function prsf_save_recipient_sent_folders($option)
{

   // only stay here if there is a valid submission
   // for the "add widget" for this option
   //
   if (!isset($option->use_add_widget)
    || !$option->use_add_widget
    || !sqGetGlobalVar('add_' . $option->name . '_key', $new_recipients, SQ_POST))
   {
      // saving here allows the delete function
      // to work despite there being nothing
      // to add (the SquirrelMail code knows
      // how to deal with that)
      save_option($option);
      return;
   }

   $new_recipients = trim($new_recipients);
   if (empty($new_recipients))
   {
      // saving here allows the delete function
      // to work despite there being nothing
      // to add (the SquirrelMail code knows
      // how to deal with that)
      save_option($option);
      return;
   }


   global $Host_RegExp_Match;
   include_once(SM_PATH . 'functions/url_parser.php');
   if (check_sm_version(1, 5, 2))
   {  
      global $Email_RegExp_Match;
   }
   else
   {  
      $atext = '([a-z0-9!#$&%*+\/=?^_`{|}~-]|&amp;)';
      $dot_atom = $atext . '+(\.' . $atext . '+)*';
      $Email_RegExp_Match = $dot_atom . '(%' . $Host_RegExp_Match . ')?@' .
                            $Host_RegExp_Match;
   }


   // check each recipient one at a time
   //
   $recipients = explode(',', $new_recipients);
   foreach ($recipients as $recipient)
   {
      $recipient = trim($recipient);
      if (!preg_match('/^(' . $dot_atom . '|' . $Email_RegExp_Match . ')$/i', $recipient))
      {
         global $optpage_save_error;
         sq_change_text_domain('per_recipient_sent_folders');
         $optpage_save_error = array(_("Invalid recipient email address"));
         sq_change_text_domain('squirrelmail');
         return;
      }
   }

   // if we get this far, everything checked
   // out, so we can go ahead and save the
   // new value as normal
   //
   save_option($option);

}



