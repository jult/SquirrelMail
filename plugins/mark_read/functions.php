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
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function mark_read_check_configuration_do()
{

   // make sure compatibility plugin is there at all (have to do this
   // because the mark_read_init() function uses load_config()
   //
   if (!function_exists('check_file_contents'))
   {
      do_err('Mark Read plugin requires the Compatibility plugin version 2.0.12+', FALSE);
      return TRUE;
   }



   // make sure base config is available
   //
   if (!mark_read_init())
   {
      do_err('Mark Read plugin is missing its main configuration file', FALSE);
      return TRUE;
   }



   // check that the needed patch is in place for SM versions that need it
   //
   if ((!check_sm_version(1, 3)
     && !check_file_contents(SM_PATH . 'src/left_main.php', 'do_hook_function\(\'left_main_after_each_folder\''))
    || (check_sm_version(1, 4, 0) && !check_sm_version(1, 4, 1)
     && !check_file_contents(SM_PATH . 'src/left_main.php', 'concat_hook_function\(\'left_main_after_each_folder\'')))
   {
      do_err('Mark Read plugin requires a patch (found in the Empty Folders plugin) with your version of SquirrelMail, but it has not been applied', FALSE);
      return TRUE;
   }
   if (check_sm_version(1, 5, 0) && !check_sm_version(1, 5, 1)
    && !check_file_contents(SM_PATH . 'src/left_main.php', 'let plugins fiddle with end of line'))
   {
      do_err('Mark Read plugin requires a source file replacement (found in the Empty Folders plugin) for src/left_main.php with your version of SquirrelMail, but it has not been done', FALSE);
      return TRUE;
   }

}



/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function mark_read_init()
{

   return load_config('mark_read',
                      array('../../config/config_mark_read.php',
                            'config.php',
                            'config_default.php'),
                      TRUE, TRUE);

}



/**
  * Display links on target folders
  *
  * @param array $box Parameters passed to this hook by SquirrelMail - 
  *                   in 1.5.2+, this is an array of mailbox info,
  *                   in 1.4.x, the first element is number of messages,
  *                   the second is mailbox name, and the third is a
  *                   usable IMAP server connection.
  *                     
  */
function mark_read_show_link_do(&$box) 
{

   global $data_dir, $username, $show_read_link_allow_override,
          $show_unread_link_allow_override, $confirm_read_link,
          $confirm_unread_link, $folders_to_show_read_link,
          $folders_to_not_show_read_link, $folders_to_show_unread_link,
          $folders_to_not_show_unread_link, $read_link_text,
          $unread_link_text, $read_link_confirm_text,
          $unread_link_confirm_text, $mark_read_link_onclick,
          $read_link_title_text, $unread_link_title_text,
          $javascript_on;


   if (check_sm_version(1, 5, 2))
      $folder_name = $box['MailboxFullName'];
   else
      $folder_name = $box[1];


   mark_read_init();


   // grab user preference for confirm read (just do it once)
   //
   static $confirm_read_link_setting = NULL;
   if (is_null($confirm_read_link_setting))
   {
      if ($show_read_link_allow_override)
         $confirm_read_link_setting = getPref($data_dir, $username,
                                              'confirm_read_link',
                                              $confirm_read_link);
      else
         $confirm_read_link_setting = $confirm_read_link;
   }



   // grab user preference for confirm unread (just do it once)
   //
   static $confirm_unread_link_setting = NULL;
   if (is_null($confirm_unread_link_setting))
   {
      if ($show_unread_link_allow_override)
         $confirm_unread_link_setting = getPref($data_dir, $username,
                                                'confirm_unread_link',
                                                $confirm_unread_link);
      else
         $confirm_unread_link_setting = $confirm_unread_link;
   }



   // grab user preference for read link folders (just do it once)
   //
   static $mark_read_show_read_link = 0;
   if ($mark_read_show_read_link === 0)
   {
      if ($show_read_link_allow_override)
         $allFolders = getPref($data_dir, $username,
                               'mark_read_show_read_link',
                               NULL);
      else
         $allFolders = NULL;


      // reformat folder list if not NULL (which means defaults apply below)
      //
      if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
      if (!is_null($allFolders)) $mark_read_show_read_link = explode('###', $allFolders);
      else $mark_read_show_read_link = NULL;
   }



   // grab user preference for unread link folders (just do it once)
   //
   static $mark_read_show_unread_link = 0;
   if ($mark_read_show_unread_link === 0)
   {
      if ($show_unread_link_allow_override)
         $allFolders = getPref($data_dir, $username,
                               'mark_read_show_unread_link',
                               NULL);
      else
         $allFolders = NULL;


      // reformat folder list if not NULL (which means defaults apply below)
      //
      if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
      if (!is_null($allFolders)) $mark_read_show_unread_link = explode('###', $allFolders);
      else $mark_read_show_unread_link = NULL;
   }



   // now figure out if this folder gets a read link
   //
   // if the user specified a list, just use it to
   // test for what folders have the link turned on
   //
   $show_read_link = FALSE;
   if (is_array($mark_read_show_read_link))
   {
      if (in_array($folder_name, $mark_read_show_read_link))
         $show_read_link = TRUE;
   }


   // or apply the defaults from the configuration file
   //
   // (link enabled if in default list, or if NOT in the
   // default "don't show" list (and that list is non-empty))
   //
   else
   {
      if (in_array($folder_name, $folders_to_show_read_link)
       || (!empty($folders_to_not_show_read_link) && !in_array($folder_name, $folders_to_not_show_read_link)))
         $show_read_link = TRUE;
   }



   // now figure out if this folder gets a unread link
   //
   // if the user specified a list, just use it to
   // test for what folders have the link turned on
   //
   $show_unread_link = FALSE;
   if (is_array($mark_read_show_unread_link))
   {
      if (in_array($folder_name, $mark_read_show_unread_link))
         $show_unread_link = TRUE;
   }


   // or apply the defaults from the configuration file
   //
   // (link enabled if in default list, or if NOT in the
   // default "don't show" list (and that list is non-empty))
   //
   else
   {
      if (in_array($folder_name, $folders_to_show_unread_link)
       || (!empty($folders_to_not_show_unread_link) && !in_array($folder_name, $folders_to_not_show_unread_link)))
         $show_unread_link = TRUE;
   }



   // in 1.5.2+, we get a nice array being prepared for
   // template use - we just add to it to the "ExtraOutput"
   // element
   //
   if (check_sm_version(1, 5, 2))
   {

      global $oTemplate, $trash_folder;

/* ----- nah, we now allow the trash folder - why not?
      // don't bother with the trash folder
      //
      if ($box['MailboxFullName'] == $trash_folder)
         return;
----- */


      // get message count if missing
      //
      if (($show_read_link || $show_unread_link)
       && empty($box['MessageCount']))
      {
         global $imapConnection;
         $aStatus = sqimap_status_messages($imapConnection,
                                           $box['MailboxFullName'],
                                           array('MESSAGES'));
         $box['MessageCount'] = $aStatus['MESSAGES'];
      }


      $read_onclick = '';
      $mark_read_uri = '';
      $read_text = '';
      $read_title_text = '';
      $unread_onclick = '';
      $mark_unread_uri = '';
      $unread_text = '';
      $unread_title_text = '';


      sq_change_text_domain('mark_read');


      // prepare read link (1.5.x)
      //
      if ($show_read_link
       && $box['MessageCount'] > 0)
      {

         // build the onclick handler if necessary
         //
         if ($javascript_on && $confirm_read_link)
            $read_onclick = str_replace('###TEXT###', sprintf(_($read_link_confirm_text), $box['MessageCount']), $mark_read_link_onclick);

         // build the link URI
         //
         $urlMailbox = urlencode($box['MailboxFullName']);
         $mark_read_uri = sqm_baseuri()
                        . 'plugins/mark_read/mark_read.php?mr_act=mr_read&mailbox='
                        . $urlMailbox;

         $read_text = sprintf(_($read_link_text), $box['MessageCount']);
         $read_title_text = sprintf(_($read_link_title_text), $box['MessageCount']);

      }


      // prepare unread link (1.5.x)
      //
      if ($show_unread_link
       && $box['MessageCount'] > 0)
      {

         // build the onclick handler if necessary
         //
         if ($javascript_on && $confirm_unread_link)
            $unread_onclick = str_replace('###TEXT###', sprintf(_($unread_link_confirm_text), $box['MessageCount']), $mark_read_link_onclick);

         // build the link URI
         //
         $urlMailbox = urlencode($box['MailboxFullName']);
         $mark_unread_uri = sqm_baseuri()
                          . 'plugins/mark_read/mark_read.php?mr_act=mr_unread&mailbox='
                          . $urlMailbox;

         $unread_text = sprintf(_($unread_link_text), $box['MessageCount']);
         $unread_title_text = sprintf(_($unread_link_title_text), $box['MessageCount']);

      }


      // generate actual output (1.5.x)
      //
      if (!empty($mark_read_uri) || !empty($mark_unread_uri))
      {  

         $oTemplate->assign('mark_read_uri', $mark_read_uri);
         $oTemplate->assign('mark_unread_uri', $mark_unread_uri);
         $oTemplate->assign('read_text', $read_text);
         $oTemplate->assign('read_title_text', $read_title_text);
         $oTemplate->assign('unread_text', $unread_text);
         $oTemplate->assign('unread_title_text', $unread_title_text);
         $oTemplate->assign('read_onclick', $read_onclick);
         $oTemplate->assign('unread_onclick', $unread_onclick);
         $oTemplate->assign('square_brackets', TRUE);
         $output = $oTemplate->fetch('plugins/mark_read/mark_read_links.tpl');

         if (empty($box['ExtraOutput']))
            $box['ExtraOutput'] = '';
         $box['ExtraOutput'] .= $output;

      }  

      sq_change_text_domain('squirrelmail');

   }



   // in 1.4.x, we have three parameters
   //
   else
   {

      $output = '';

      // don't need to skip trash - in 1.4.x, this hook isn't run for it

      $numMessages = $box[0];
      $real_box = $box[1];
      $imapConnection = $box[2];

      // get message count if missing
      //
      if (($show_read_link || $show_unread_link)
       && empty($numMessages))
         $numMessages = sqimap_get_num_messages($imapConnection, $real_box);


      global $read_onclick, $mark_read_uri, $read_text,
             $unread_onclick, $mark_unread_uri, $unread_text;
      $read_onclick = '';
      $mark_read_uri = '';
      $read_text = '';
      $read_title_text = '';
      $unread_onclick = '';
      $mark_unread_uri = '';
      $unread_text = '';
      $unread_title_text = '';


      sq_change_text_domain('mark_read');


      // prepare read link (1.4.x)
      //
      if ($show_read_link
       && $numMessages > 0)
      {
         // build the link URI
         //
         $urlMailbox = urlencode($real_box);
         $mark_read_uri = sqm_baseuri()
                        . 'plugins/mark_read/mark_read.php?mr_act=mr_read&mailbox='
                        . $urlMailbox;

         // build the onclick handler if necessary
         //
         if ($javascript_on && $confirm_read_link)
            $read_onclick = str_replace('###TEXT###', sprintf(_($read_link_confirm_text), $numMessages), $mark_read_link_onclick);

         $read_text = sprintf(_($read_link_text), $numMessages);
         $read_title_text = sprintf(_($read_link_title_text), $numMessages);
      }


      // prepare empty link (1.4.x)
      //
      if ($show_unread_link
       && $numMessages > 0)
      {
         // build the link URI
         //
         $urlMailbox = urlencode($real_box);
         $mark_unread_uri = sqm_baseuri()
                          . 'plugins/mark_read/mark_read.php?mr_act=mr_unread&mailbox='
                          . $urlMailbox;

         // build the onclick handler if necessary
         //
         if ($javascript_on && $confirm_unread_link)
            $unread_onclick = str_replace('###TEXT###', sprintf(_($unread_link_confirm_text), $numMessages), $mark_read_link_onclick);

         $unread_text = sprintf(_($unread_link_text), $numMessages);
         $unread_title_text = sprintf(_($unread_link_title_text), $numMessages);
      }


      // generate actual output (1.4.x)
      //
      if (!empty($mark_read_uri) || !empty($mark_unread_uri))
      {
         global $t, $square_brackets;

         $square_brackets = FALSE;
         $t = array(); // no need to put config vars herein, they are already globalized

         ob_start();
         include(SM_PATH . 'plugins/mark_read/templates/default/mark_read_links.tpl');
         $output .= ob_get_contents();
         ob_end_clean();
      }

      sq_change_text_domain('squirrelmail');
      return $output;

   }

}



/**
  * Display button(s) in target mailbox listings
  *
  * @param array $buttons The list of buttons being added to when
  *                       using 1.5.2+
  *
  */
function mark_read_show_button_do(&$buttons)
{

   global $numMessages, $aMailbox, $trash_folder, $username, $data_dir,
          $read_button_confirm_text, $unread_button_confirm_text,
          $read_button_text, $unread_button_text,
          $read_button_title_text, $unread_button_title_text,
          $show_read_button_allow_override, $confirm_read_button,
          $folders_to_show_read_button, $folders_to_not_show_read_button,
          $show_unread_button_allow_override, $confirm_unread_button,
          $folders_to_show_unread_button, $folders_to_not_show_unread_button,
          $javascript_on, $mark_read_button_onclick;

   mark_read_init();

   sqgetGlobalVar('mailbox', $mailbox, SQ_FORM);
   if (empty($mailbox)) $mailbox = 'INBOX';


   if (check_sm_version(1, 5, 1))
      $message_count = $aMailbox['EXISTS'];
   else
      $message_count = $numMessages;



/* ----- nah, we now allow the trash folder - why not?
   // skip the trash folder
   //
   if ($mailbox == $trash_folder)
      return;
----- */



   // grab user preference for read button folders
   //
   if ($show_read_button_allow_override)
      $allFolders = getPref($data_dir, $username,
                            'mark_read_show_read_button',
                            NULL);
   else
      $allFolders = NULL;



   // reformat folder list if not NULL (which means defaults apply below)
   //
   if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
   if (!is_null($allFolders)) $mark_read_show_read_button = explode('###', $allFolders);
   else $mark_read_show_read_button = NULL;



   // now figure out if this folder gets a read button
   //       
   // if the user specified a list, just use it to
   // test for what folders have the button turned on
   //
   $show_read_button = FALSE;
   if (is_array($mark_read_show_read_button))
   {
      if (in_array($mailbox, $mark_read_show_read_button))
         $show_read_button = TRUE;
   }


   // or apply the defaults from the configuration file
   //
   // (button enabled if in default list, or if NOT in the
   // default "don't show" list (and that list is non-empty))
   //
   else
   {
      if (in_array($mailbox, $folders_to_show_read_button)
       || (!empty($folders_to_not_show_read_button) && !in_array($mailbox, $folders_to_not_show_read_button)))
         $show_read_button = TRUE;
   }



   // grab user preference for unread button folders
   //
   if ($show_unread_button_allow_override)
      $allFolders = getPref($data_dir, $username,
                            'mark_read_show_unread_button',
                            NULL);
   else
      $allFolders = NULL;



   // reformat folder list if not NULL (which means defaults apply below)
   //
   if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
   if (!is_null($allFolders)) $mark_read_show_unread_button = explode('###', $allFolders);
   else $mark_read_show_unread_button = NULL;



   // now figure out if this folder gets a unread button
   //       
   // if the user specified a list, just use it to
   // test for what folders have the button turned on
   //
   $show_unread_button = FALSE;
   if (is_array($mark_read_show_unread_button))
   {
      if (in_array($mailbox, $mark_read_show_unread_button))
         $show_unread_button = TRUE;
   }


   // or apply the defaults from the configuration file
   //
   // (button enabled if in default list, or if NOT in the
   // default "don't show" list (and that list is non-empty))
   //
   else
   {
      if (in_array($mailbox, $folders_to_show_unread_button)
       || (!empty($folders_to_not_show_unread_button) && !in_array($mailbox, $folders_to_not_show_unread_button)))
         $show_unread_button = TRUE;
   }



   sq_change_text_domain('mark_read');



   // read button?
   //
   if ($show_read_button)
   {

      // grab user preference for confirm read
      //
      if ($show_read_button_allow_override)
         $confirm_read_button = getPref($data_dir, $username,
                                        'confirm_read_button',
                                        $confirm_read_button);

      if ($confirm_read_button && $javascript_on)
         $onclick = str_replace('###TEXT###', sprintf(_($read_button_confirm_text), $message_count), $mark_read_button_onclick);
      else
         $onclick = '';

      if (check_sm_version(1, 5, 2))
         $buttons['mrReadAll'] = array('value' => sprintf(_($read_button_text),
                                                          $message_count),
                                       'type' => 'submit',
                                       'extra_attrs' => array('onclick' => $onclick,
                                                              'title' => sprintf(_($read_button_title_text), $message_count)));
      else if (check_sm_version(1, 5, 1))
         $buttons[1]['mrReadAll'] = array(0 => sprintf(_($read_button_text),
                                                       $message_count),
                                           1 => 'submit');
      else
         echo '<input type="submit" name="mrReadAll" title="' . sprintf(_($read_button_title_text), $message_count) . '" onclick="' . $onclick . '" value="' . sprintf(_($read_button_text), $message_count) . "\" />\n";

   }



   // unread button?
   //
   if ($show_unread_button)
   {

      // grab user preference for confirm unread
      //
      if ($show_unread_button_allow_override)
         $confirm_unread_button = getPref($data_dir, $username,
                                          'confirm_unread_button',
                                          $confirm_unread_button);

      if ($confirm_unread_button && $javascript_on)
         $onclick = str_replace('###TEXT###', sprintf(_($unread_button_confirm_text), $message_count), $mark_read_button_onclick);
      else
         $onclick = '';

      if (check_sm_version(1, 5, 2))
         $buttons['mrUnreadAll'] = array('value' => sprintf(_($unread_button_text),
                                                            $message_count),
                                       'type' => 'submit',
                                       'extra_attrs' => array('onclick' => $onclick,
                                                              'title' => sprintf(_($unread_button_title_text), $message_count)));
      else if (check_sm_version(1, 5, 1))
         $buttons[1]['mrUnreadAll'] = array(0 => sprintf(_($unread_button_text),
                                                       $message_count),
                                           1 => 'submit');
      else
         echo '<input type="submit" name="mrUnreadAll" title="' . sprintf(_($unread_button_title_text), $message_count) . '" onclick="' . $onclick . '" value="' . sprintf(_($unread_button_text), $message_count) . "\" />\n";

   }


   sq_change_text_domain('squirrelmail');

}



/**
  * Perform button actions
  *
  * @param array (only used in 1.5.2) An array consisting of: button name (provided
  *                                   by an external caller - ignore for now), the
  *                                   mailbox cache, the account number, the mailbox
  *                                   name (ignore for now), and a UID list (should
  *                                   be null when we care about handling any actions).
  *
  * @return boolean (only in 1.5.2) TRUE when a button action
  *                                 was handled, FALSE otherwise
  *
  */
function mark_read_handle_button_click_do(&$args)
{

   global $trash_folder;
   if (!sqGetGlobalVar('mailbox', $mailbox, SQ_FORM) || empty($mailbox))
      $mailbox = 'INBOX';


   if (check_sm_version(1, 5, 2))
   {
      $mbox_cache = &$args[1];
      $account_number = $args[2];
      //$mailbox = $args[3];
   }
   else
   {
      $mbox_cache = NULL;
      $account_number = 0;
      //if (!sqGetGlobalVar('mailbox', $mailbox, SQ_FORM) || empty($mailbox))
      //   $mailbox = 'INBOX';
   }



/* ----- nah, we now allow the trash folder - why not?
   // skip trash folder
   //
   if ($mailbox == $trash_folder)
      return;
----- */



   // mark read some other folder?
   //
   if (sqGetGlobalVar('mrReadAll', $mrReadAll, SQ_FORM) && !empty($mrReadAll))
   {

      // mark all messages as read
      //
      flag_all_messages($mailbox, 'Seen', TRUE, $mbox_cache, $account_number);


      // in 1.5.2, we need to return true to indicate we handled this action
      //
      if (check_sm_version(1, 5, 2))
         return TRUE;


      // in 1.4.x, redirect back to message list
      //
      if (!sqGetGlobalVar('location', $location, SQ_FORM)) $location = php_self();
      $location = set_url_var($location, 'startMessage', 1, false);
      header('Location: ' . $location);
      exit;

   }



   // mark unread some other folder?
   //
   if (sqGetGlobalVar('mrUnreadAll', $mrUnreadAll, SQ_FORM) && !empty($mrUnreadAll))
   {

      // mark all messages as unread
      //
      flag_all_messages($mailbox, 'Seen', FALSE, $mbox_cache, $account_number);


      // in 1.5.2, we need to return true to indicate we handled this action
      //
      if (check_sm_version(1, 5, 2))
         return TRUE;


      // in 1.4.x, redirect back to message list
      //
      if (!sqGetGlobalVar('location', $location, SQ_FORM)) $location = php_self();
      $location = set_url_var($location, 'startMessage', 1, false);
      header('Location: ' . $location);
      exit;

   }



   return FALSE;

}



/**
  * Flag all messages in a given folder with the given flag
  *
  * @param string  $mailbox     The name of the folder
  * @param string  $flag        The flag to apply (usually "Seen",
  *                             "Deleted", "Flagged", etc.)
  * @param boolean $enable      When TRUE, the flag is turned on, when FALSE,
  *                             the indicated flag is turned off for the messages.
  * @param mixed   $mbox_cache  An array of mailbox cache information
  *                             (used in SM 1.5.x), which is optional,
  *                             but will be updated if given.  If given
  *                             as boolean TRUE, this function will attempt
  *                             to first retrieve it from the PHP session.
  *                             NOTE that this parameter is passed by reference
  *                             and is potentially modified herein.  Because it
  *                             is passed by reference, PHP 4 doesn't like a default
  *                             value being specified, so callers should pass NULL
  *                             when it is not available or applicable.
  * @param int     $account_num The account number in use (only applicable under
  *                             SquirrelMail 1.5.2+) (OPTIONAL; default = 0)
  * @param boolean $quiet       When TRUE, errors are not shown
  *                             on screen (OPTIONAL; default FALSE)
  *
  * @return boolean TRUE if the operation succeeded, FALSE otherwise
  *                 (in versions prior to 1.5.2, instead of FALSE,
  *                 an error will usually be shown on screen and
  *                 execution will halt)
  *
  */
function flag_all_messages($mailbox, $flag, $enable,
                           &$mbox_cache, $account_num=0, $quiet=FALSE)
{

   global $imapConnection, $uid_support;
   if (!check_sm_version(1, 5, 2)) $uid_support = TRUE;


   // has all the imap function file includes that we need
   //
   include_once (SM_PATH . 'functions/imap.php');


   // needed in case errors occur
   //
   include_once(SM_PATH . 'functions/display_messages.php');


   // create IMAP connection if needed
   //
   if (!is_resource($imapConnection))
   {
      global $username, $imapServerAddress, $imapPort;
      $key = FALSE;
      if (!check_sm_version(1, 5, 2))
         sqgetGlobalVar('key', $key, SQ_COOKIE);
//TODO: in future, for 1.5.2, we should be able to use the last argument to tell this function (sqimap_login) to return any errors.... in which case we have to inspect the return value and handle errors ourselves if there are any
      $imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
      $close_imap_stream = TRUE;
   }
   else
   {
      $imap_stream = $imapConnection;
      $close_imap_stream = FALSE;
   }



   // if needed, get the mailbox cache ourselves
   //
   if (check_sm_version(1, 5, 2) && $mbox_cache === TRUE)
   {
      include_once(SM_PATH . 'functions/mailbox_display.php');
//TODO -- if mailbox name is unknown, the following call generates an error and pushes it to the browser, which is not desirable when running a RPC call.  This is one of those places that will benefit from the SM core API being fixed so it doesn't handle errors in a more mature manner
      $mbox_cache = sqm_api_mailbox_select($imap_stream, $account_num, $mailbox,
                                           array(), array());
   }



   // grab message count
   //
   if (!empty($mbox_cache['EXISTS']))
      $message_count = $mbox_cache['EXISTS'];
   else
   {
      $mbx_response = sqimap_mailbox_select($imap_stream, $mailbox);
      $message_count = $mbx_response['EXISTS'];
   }



   // check of there are any messages to flag at all
   //
   if ($message_count > 0)
   {

      // flag all messages
      //
      $read = sqimap_run_command($imap_stream, 'STORE 1:* '
                                             . ($enable ? '+' : '-')
                                             . 'FLAGS (\\' . $flag . ')',
                                 !$quiet, $response, $message, $uid_support);
      if ($quiet && ($read === FALSE || $response != 'OK'))
         return FALSE;


      // synchronize mailbox cache (only in 1.5.2+)
      //
      if ($mbox_cache && check_sm_version(1, 5, 2))
      {

         if (!empty($mbox_cache['NAME']) && $mbox_cache['NAME'] == $mailbox
          && !empty($mbox_cache['MSG_HEADERS']) && is_array($mbox_cache['MSG_HEADERS']))
         {
            foreach ($mbox_cache['MSG_HEADERS'] as $uid => $headers)
               if ($enable)
                  $mbox_cache['MSG_HEADERS'][$uid]['FLAGS']['\\' . strtolower($flag)] = TRUE;
               else
                  unset($mbox_cache['MSG_HEADERS'][$uid]['FLAGS']['\\' . strtolower($flag)]);
//TODO: when strtolower($flag) == 'seen' maybe we should adjust the value of $mbox_cache['SEEN'] to zero or the same as $mbox_cache['EXISTS'] (depending on $enable), but SM does not appear to use this value(??)
         }

      }

   }



   if ($close_imap_stream) sqimap_logout($imap_stream);

   return TRUE;

}



/**
  * Provide an RPC interface to this plugin.
  *
  * Actions that are handled are:
  *
  *    mark_read_read_all   - Marks all messages in the given folder
  *                           (specified by the "mailbox" argument in
  *                           the RPC request) as read
  *    mark_read_unread_all - Marks all messages in the given folder
  *                           (specified by the "mailbox" argument in
  *                           the RPC request) as unread
  *
  * Error codes that are returned:
  *
  *    503 - mark_read_read_all failed
  *    504 - mark_read_unread_all failed
  *
  * @param array The first element is the requested RPC action
  *
  * @return boolean FALSE if the action was not one that
  *                 this plugin handles - otherwise this
  *                 function will never exit (ala
  *                 sm_rpc_return_success())
  *
  */
function mark_read_rpc_do($args)
{

   switch ($args[0])
   {

      // mark all messages as read
      //
      case 'mark_read_read_all':

         // get mailbox cache if no one has done it yet
         //
         global $mailbox_cache;
         if (empty($mailbox_cache))
            sqgetGlobalVar('mailbox_cache', $mailbox_cache, SQ_SESSION);

         $mbox_cache = TRUE;
         if (!sqGetGlobalVar('mailbox', $mailbox, SQ_FORM) || empty($mailbox))
            $mailbox = 'INBOX';
         if (sqgetGlobalVar('account', $account_number, SQ_FORM) === false)
            $account_number = 0;
         sq_change_text_domain('mark_read');
         $success_message = _("All messages in folder have been marked as read");
         $error_message = _("Messages could not be marked as read");
         sq_change_text_domain('squirrelmail');
         if (flag_all_messages($mailbox, 'Seen', TRUE, $mbox_cache, $account_number, TRUE))
         {
            // save mailbox cache back to session
            //
            $mailbox_cache[$account_number . '_' . $mbox_cache['NAME']] = $mbox_cache;
            sqsession_register($mailbox_cache, 'mailbox_cache');

            sm_rpc_return_success($args[0], 0, $success_message);
         }
         else
            sm_rpc_return_error($args[0], 503, $error_message, 'server', 500, 'Server Error');
         break;


      // mark all messages as unread
      //
      case 'mark_read_unread_all':

         // get mailbox cache if no one has done it yet
         //
         global $mailbox_cache;
         if (empty($mailbox_cache))
            sqgetGlobalVar('mailbox_cache', $mailbox_cache, SQ_SESSION);

         $mbox_cache = TRUE;
         if (!sqGetGlobalVar('mailbox', $mailbox, SQ_FORM) || empty($mailbox))
            $mailbox = 'INBOX';
         if (sqgetGlobalVar('account', $account_number, SQ_FORM) === false)
            $account_number = 0;
         sq_change_text_domain('mark_read');
         $success_message = _("All messages in folder have been marked as unread");
         $error_message = _("Messages could not be marked as unread");
         sq_change_text_domain('squirrelmail');
         if (flag_all_messages($mailbox, 'Seen', FALSE, $mbox_cache, $account_number, TRUE))
         {
            // save mailbox cache back to session
            //
            $mailbox_cache[$account_number . '_' . $mbox_cache['NAME']] = $mbox_cache;
            sqsession_register($mailbox_cache, 'mailbox_cache');

            sm_rpc_return_success($args[0], 0, $success_message);
         }
         else
            sm_rpc_return_error($args[0], 504, $error_message, 'server', 500, 'Server Error');
         break;

   }


   // the RPC action was not one of our own
   //
   return FALSE;

}



