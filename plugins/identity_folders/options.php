<?php

/**
  * SquirrelMail Identity Folders Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage identity_folders
  *
  */



/**
  * Add folder selector on advanced identities page
  *
  */
function if_add_identity_inputs($args)
{

   global $username, $data_dir, $plugins;
   static $boxes = array();


   $identity_folder = getPref($data_dir, $username, 'identity_folder_' . $args[2], SMPREF_NONE);


   // get folder list only if we didn't get it before
   //
   if (!isset($boxes[$args[2]]))
   {

      $imap_connection = NULL;
      $close_imap_connection = FALSE;
      $is_secondary_imap_connection = FALSE;


// NB/TODO While this code adds compatibility with the multiple_accounts plugin, the SquirrelMail architecture doesn't suit using the two plugins together in some common scenarios.  The folder list available in the custom sent folder dropdown will always be from the IMAP server of the account currently being used, and therefore may not match that for the IMAP server of the identity for which the reply is being sent.
      // if the multiple_accounts plugin is active, check
      // if this identity uses a different IMAP server
      //
      if (in_array('multiple_accounts', $plugins))
      {

         $use_custom_imap_server = getPref($data_dir, $username, 'multiple_accounts_use_custom_imap_server_' . $args[2], 'no');
         $imap_server = getPref($data_dir, $username, 'multiple_accounts_imap_server_' . $args[2], '');
         $imap_login = getPref($data_dir, $username, 'multiple_accounts_imap_login_' . $args[2], '');


         // if this identity uses a different IMAP server,
         // make sure we get folder list from the right place
         //
         if ($use_custom_imap_server == 'yes' && !empty($imap_server) && !empty($imap_login))
         {
            $imap_password = getPref($data_dir, $username, 'multiple_accounts_imap_password_' . $args[2], '');

            $imap_port = getPref($data_dir, $username, 'multiple_accounts_imap_port_' . $args[2], 143);
            $imap_tls = getPref($data_dir, $username, 'multiple_accounts_imap_tls_' . $args[2], 'no');
            $imap_auth = getPref($data_dir, $username, 'multiple_accounts_imap_auth_' . $args[2], 'login');
            $imap_type = getPref($data_dir, $username, 'multiple_accounts_imap_type_' . $args[2], 'other');


            $imap_connection = multiple_accounts_imap_login($imap_login, $imap_password, $imap_server, $imap_port, ($imap_tls === 'yes' ? TRUE : FALSE), $imap_auth, TRUE);
            if (is_resource($imap_connection))
            {
               $close_imap_connection = TRUE;
               $is_secondary_imap_connection = TRUE;
            }
            else
            {
               // TODO: we could somehow log the error we got when trying to log in, which is in string form in $imap_connection right here
               // we could just set the connection to NULL to use
               // the folder list from the default IMAP server, but
               // that doesn't make sense - instead give an empty list
               //$imap_connection = NULL;
               $boxes[$args[2]] = array();
            }
         }

      }


      // if we didn't get an imap connection above, get a
      // connection to the default IMAP server now
      //
      if (is_null($imap_connection))
      {
         global $key, $imapServerAddress, $imapPort, $imapConnection, $imap_stream_options;
         if (check_sm_version(1, 5, 2))
         {
            $key = FALSE;
            include_once(SM_PATH . 'functions/imap_general.php');
         }
         else
            // for some reason in 1.4.x $key isn't yet populated on advanced idents page
            sqgetGlobalVar('key', $key, SQ_COOKIE);
         if (!is_resource($imapConnection))
         {
            $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0, $imap_stream_options);
            $close_imap_connection = TRUE;
         }
         $imap_connection = $imapConnection;
      }


      // now we can get the folder list (if it wasn't populated above
      //
      if (!isset($boxes[$args[2]]))
      {
//TODO: technically, for secondary IMAP connections, we should use a different version of the sqimap_mailbox_list() function which should be provided by the multiple_accounts plugin
         global $delimiter;
         sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
         $re_store_original_boxes = FALSE;
         if ($is_secondary_imap_connection
          && sqgetGlobalVar('boxesnew', $original_boxes, SQ_SESSION))
            $re_store_original_boxes = TRUE;
         $boxes[$args[2]] = sqimap_mailbox_list($imap_connection, $is_secondary_imap_connection);
         if ($re_store_original_boxes)
            sqsession_register($original_boxes, 'boxesnew');
      }


      // close the connection if needed
      //
      if ($close_imap_connection)
         sqimap_logout($imap_connection);

   }


   $mailboxes = $boxes[$args[2]];

   $none = _("None"); // do this in the squirrelmail domain
   sq_change_text_domain('identity_folders');


   // TODO: 1.5.2 will need to change when HTML is completely extracted from the core
   if (check_sm_version(1, 5, 2))
   {
      $output = '<tr ' . $args[0] . '>'
              . '<td class="fieldName">Mail Folder</td>'
              . '<td class="fieldValue">'
              . '<select name="identity_folder_' . $args[2] . '">'
              . '<option value="'. SMPREF_NONE . '"'
              . ($identity_folder == SMPREF_NONE ? ' selected="selected"' : '')
              . '>&lt;' . $none . '&gt;</option>';
      for ($boxnum = 0; $boxnum < count($mailboxes); $boxnum++)
      {
         $output .= '<option value="' . $mailboxes[$boxnum]['unformatted'] . '"'
                  . ($identity_folder == $mailboxes[$boxnum]['unformatted'] ? ' selected' : '')
                  . '>' . $mailboxes[$boxnum]['formatted'] . "</option>\n";
      }
      $output .= '</select> ' . _("(default folder for sent messages)")
               . '<br /></td></tr>';
   }
   else
   {
      $output = '<tr ' . $args[0] . '>'
              . '<td style="white-space: nowrap; text-align:right;">Mail Folder</td>'
              . '<td>&nbsp;'
              . '<select name="identity_folder_' . $args[2] . '">'
              . '<option value="'. SMPREF_NONE . '"'
              . ($identity_folder == SMPREF_NONE ? ' selected="selected"' : '')
              . '>&lt;' . $none . '&gt;</option>';
      for ($boxnum = 0; $boxnum < count($mailboxes); $boxnum++)
      {
         $output .= '<option value="' . $mailboxes[$boxnum]['unformatted'] . '"'
                  . ($identity_folder == $mailboxes[$boxnum]['unformatted'] ? ' selected' : '')
                  . '>' . $mailboxes[$boxnum]['formatted'] . "</option>\n";
      }
      $output .= '</select> ' . _("(default folder for sent messages)")
               . '<br /></td></tr>';
             // what was this for?
             //. '<tr ' . $args[0] . '><td colspan="1"></td><td></td></tr>';
   }


   sq_change_text_domain('squirrelmail');

   return $output;

}



/**
  * Process server configuration inputs on advanced identities page
  *
  */
function if_process_identity_inputs($args)
{

   // get needed arguments
   //
   if (check_sm_version(1, 5, 2))
   {
      $action = $args[0];
      $identity_number = $args[1];
   }
   else
   {
      $action = $args[1];
      $identity_number = $args[2];
   }


   // (only process on known actions)
   //
   // save action
   //
   if ($action == 'save')
   {

      global $data_dir, $username;


      // get user input
      //
      sqgetGlobalVar('identity_folder_' . $identity_number, $identity_folder, SQ_POST);


      // save (if setting to "none", just delete)
      //
      if ($identity_folder == SMPREF_NONE)
         setPref($data_dir, $username, 'identity_folder_' . $identity_number, '');
      else
         setPref($data_dir, $username, 'identity_folder_' . $identity_number, $identity_folder);

   }


   // delete action
   //
   if ($action == 'delete')
   {

      global $data_dir, $username;

      // now this is really dumb... the core doesn't call the renumbering hook
      // for subsequent identities if the deleted identity is not the last one
      // so we have to figure that out here and renumber our prefs ourselves
      //
      $number_of_identities = getPref($data_dir, $username, 'identities', 0);
      for ($i = 0; $i < $number_of_identities; $i++)
      {
         if ($i >= $identity_number)
         {
            if (check_sm_version(1, 5, 2))
               $arg_array = array($i + 1, $i);
            else
               $arg_array = array('ignore', $i + 1, $i);
            if_renumber_identites($arg_array, FALSE);
         }
      }

   }

}



/**
  * Synchronize plugin preference settings with identities reorder
  *
  * NOTE that SquirrelMail core's "renumbering" hook for identities
  *      is actually a swap, thus $swap defaults to TRUE
  *
  * @param array $args Hook argument array: 0 = hook name, 1 = old identity
  *                    number, 2 = new identity number
  * @param boolean $swap Preserve any preferences at the new identity
  *                      number, moving them to the old identity number,
  *                      such that a swap occurs (OPTIONAL; defalut = TRUE)
  *
  */
function if_renumber_identites($args, $swap=TRUE)
{

   // get needed arguments
   //
   if (check_sm_version(1, 5, 2))
   {  
      $from = $args[0];
      $to = $args[1];
   }
   else
   {  
      $from = $args[1];
      $to = $args[2];
   }


   global $data_dir, $username;
   if ($from == 'default') $from = 0;


   // ergh.  SquirrelMail core is really borked from what I can tell...
   // while normal "move up" actions require a swap of identity positions,
   // the "make default" action moves all identities downward up to the
   // identity being made default, which is moved to the top.  OK, BUT,
   // the hook is called the same in both cases (that's a bug)
   //
   if ($to == 'default')
   {
      $to = 0;

      if ($swap)
      {
         // bubble the target identity to the top
         //
         for ($i = $from; $i > 0; $i--)
         {
            if (check_sm_version(1, 5, 2))
               $arg_array = array($i - 1, $i);
            else
               $arg_array = array('ignore', $i - 1, $i);
            if_renumber_identites($arg_array);
         }

         return;
      }
   }


   // get preference being moved
   //
   $identity_folder = getPref($data_dir, $username, 'identity_folder_' . $from, SMPREF_NONE);


   // if a swap is occurring, get preference that would otherwise be overwritten
   //
   if ($swap)
   {
      $old_identity_folder = getPref($data_dir, $username, 'identity_folder_' . $to, SMPREF_NONE);
   }


   // save in new location
   //
   if ($identity_folder == SMPREF_NONE)
      setPref($data_dir, $username, 'identity_folder_' . $to, '');
   else
      setPref($data_dir, $username, 'identity_folder_' . $to, $identity_folder);


   // if swap, save old pref here
   //
   if ($swap)
   {
      if ($old_identity_folder == SMPREF_NONE)
         setPref($data_dir, $username, 'identity_folder_' . $from, '');
      else
         setPref($data_dir, $username, 'identity_folder_' . $from, $old_identity_folder);
   }


   // if no swap, delete old location
   //
   else
   {
      setPref($data_dir, $username, 'identity_folder_' . $from, '');
   }

}



