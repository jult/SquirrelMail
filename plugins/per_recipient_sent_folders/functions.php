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
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function prsf_init()
{

   if (!@include_once (SM_PATH . 'config/config_per_recipient_sent_folders.php'))
      if (!@include_once (SM_PATH . 'plugins/per_recipient_sent_folders/config.php'))
         if (!@include_once (SM_PATH . 'plugins/per_recipient_sent_folders/config_default.php'))
            return FALSE;

   return TRUE;

/* ----------  This is how to do the same thing using the Compatibility plugin
   return load_config('per_recipient_sent_folders',
                      array('../../config/config_per_recipient_sent_folders.php',
                            'config.php',
                            'config_default.php'),
                      TRUE, TRUE);
----------- */

}



/**   
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function prsf_check_configuration()
{

   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {

      // need SM version 1.4.23 or better
      //
      if (!check_sm_version(1, 4, 23))
      {
         do_err('The Per Recipient Sent Folders plugin requires SquirrelMail version 1.4.23 or above', FALSE);
         return TRUE;
      }


      // try to find the Variable Sent Folder plugin, and then that it is v1.0+
      // (its check-configuration function was added in version 1.0)
      //
      global $plugins;
      if (!in_array('variable_sent_folder', $plugins)
       || !function_exists('variable_sent_folder_check_configuration'))
      {
         do_err('The Per Recipient Sent Folders plugin requires the Variable Sent Folder plugin version 1.0 or above', FALSE);
         return TRUE;
      }

   }


   // make sure plugin is correctly configured
   //
   if (!prsf_init())
   {
      do_err('The Per Recipient Sent Folders plugin is not configured correctly', FALSE);
      return TRUE;
   }

}



/**
  * Override custom sent folder with the folder that
  * corresponds to the recipient(s)
  * (requires Variable Sent Folder plugin)
  *
  * If a match is found, the corresponding IMAP folder
  * name is placed in the global variable
  * $variable_sent_folder_custom_sent_folder_default
  *
  */
function prsf_custom_sent_folder()
{

   // parse email addresses out of headers
   //
   global $send_to, $send_to_cc, $send_to_bcc, $username, $data_dir,
          $variable_sent_folder_custom_sent_folder_default;


   // get saved address/folder table
   //
   $per_recipient_sent_folders = getPref($data_dir, $username, 'per_recipient_sent_folders', '');
   if (!empty($per_recipient_sent_folders) && is_string($per_recipient_sent_folders))
      $per_recipient_sent_folders = unserialize($per_recipient_sent_folders);
   else
      return;


   $recipient_folder = NULL;


   // look for full match of all recipients
   //
   $all_addresses = parse_recipients($send_to, $send_to_cc, $send_to_bcc);
   $all_addresses_string = implode(',', $all_addresses);
   foreach ($per_recipient_sent_folders as $recipients => $sent_folder)
   {
      if ($all_addresses_string == $recipients)
      {
         $recipient_folder = $sent_folder;
         break;
      }
   }


   // next, try looking for match of all recipients ONLY in the TO field
   //
   if (is_null($recipient_folder))
   {
      $all_to_addresses_string = implode(',', parse_recipients($send_to, '', ''));
      foreach ($per_recipient_sent_folders as $recipients => $sent_folder)
      {
         if ($all_to_addresses_string == $recipients)
         {
            $recipient_folder = $sent_folder;
            break;
         }
      }
   }


   // now, short of better ideas, we'll just iterate through the
   // recipient list and see if any one of them has been sent to
   // (alone) in the past
   //
   // this isn't ideal, because first match wins, and note that
   // the recipient list is sorted (alphabetic)
   //
   if (is_null($recipient_folder))
   {
      foreach ($all_addresses as $address)
      {
         foreach ($per_recipient_sent_folders as $recipients => $sent_folder)
         {
            if ($address == $recipients)
            {
               $recipient_folder = $sent_folder;
               break 2;
            }
         }
      }
   }


   // last, we do the same as above, but look for instances of
   // recipients having been sent to even as part of a group
   // that includes other recipients
   //
   global $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient_default;
   prsf_init();
   $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient
      = getPref($data_dir, $username,
                'prsf_use_previous_multi_recipient_sent_folder_for_single_recipient',
                $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient_default);
   if ($prsf_use_previous_multi_recipient_sent_folder_for_single_recipient)
   {
      if (is_null($recipient_folder))
      {
         foreach ($all_addresses as $address)
         {
            foreach ($per_recipient_sent_folders as $recipients => $sent_folder)
            {
               $recipient_list = explode(',', $recipients);
               if (in_array($address, $recipient_list))
               {
                  $recipient_folder = $sent_folder;
                  break 2;
               }
            }
         }
      }
   }


   if (!is_null($recipient_folder))
      $variable_sent_folder_custom_sent_folder_default = $recipient_folder;

}



/**
  * Detect the sent folder used when sending
  *
  */
function prsf_store_used_sent_folder()
{

   global $username, $data_dir, $Result, $folder_prefix, $default_move_to_sent,
          $send_to, $send_to_cc, $send_to_bcc, $prsf_detect_upon_send_default;


   prsf_init();


   $prsf_detect_upon_send = getPref($data_dir, $username,
                                    'prsf_detect_upon_send',
                                    $prsf_detect_upon_send_default);


   // only process if option is enabled and send was successful...
   //
   if (!$prsf_detect_upon_send || !$Result)
      return;


   // get original sent folder
   // copied from include/load_prefs.php
   //
   $move_to_sent =
      getPref($data_dir, $username, 'move_to_sent', $default_move_to_sent);
   $load_sent_folder = getPref($data_dir, $username, 'sent_folder');
   if (($load_sent_folder == '') && ($move_to_sent)) {
// $sent_folder is overridden at this point; we need the original value
require(SM_PATH . 'config/config.php');
      $sent_folder = $folder_prefix . $sent_folder;
   } else {
      $sent_folder = $load_sent_folder;
   }


   // also, if no custom sent folder was given, there's nothing to do
   //
   $variable_sent_folder = NULL;
   if (!sqGetGlobalVar('variable_sent_folder', $variable_sent_folder, SQ_POST)
    || $variable_sent_folder == $sent_folder)
      return;


   // parse email addresses out of headers into comma-delimited string
   //
   $all_addresses = implode(',', parse_recipients($send_to, $send_to_cc, $send_to_bcc));


   // get saved address/folder table
   //
   $per_recipient_sent_folders = getPref($data_dir, $username, 'per_recipient_sent_folders', '');
   if (!empty($per_recipient_sent_folders) && is_string($per_recipient_sent_folders))
      $per_recipient_sent_folders = unserialize($per_recipient_sent_folders);


   // insert/replace the sent folder selection for this recipient(s)
   //
   $per_recipient_sent_folders[$all_addresses] = $variable_sent_folder;
   setPref($data_dir, $username, 'per_recipient_sent_folders', serialize($per_recipient_sent_folders));

}



/**
  * Given all recipients, extract, sort and
  * return a list of just the email addresses
  *
  * @param string $to The raw TO field recipients
  * @param string $cc The raw CC field recipients
  * @param string $bcc The raw BCC field recipients
  *
  * @return array Sorted list of email addresses
  *
  */
function parse_recipients($to, $cc, $bcc)
{

   $all_addresses = array();

   $to_addresses = parseAddress($to);
   foreach ($to_addresses as $address_info)
      if (!empty($address_info[0]))
         $all_addresses[] = $address_info[0];
   $cc_addresses = parseAddress($cc);
   foreach ($cc_addresses as $address_info)
      if (!empty($address_info[0]))
         $all_addresses[] = $address_info[0];
   $bcc_addresses = parseAddress($bcc);
   foreach ($bcc_addresses as $address_info)
      if (!empty($address_info[0]))
         $all_addresses[] = $address_info[0];
   $all_addresses = array_unique($all_addresses);


   // IMPORTANT so no duplicate lists in prefs
   //
   sort($all_addresses);


   return $all_addresses;

}



