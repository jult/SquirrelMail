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
  * Constructs spam button text
  *
  * @param array $buttons The list of buttons being added to under 1.5.2+
  *                       NOTE that this parameter is passed by reference
  *                       and is potentially modified herein.  Because it
  *                       is passed by reference, PHP 4 doesn't like a default
  *                       value being specified, so callers should pass an
  *                       empty string when it is not needed (usually only
  *                       needed under 1.5.2 during the relevant hooks, such 
  *                       as "message_list_controls").
  * @param boolean $dont_show_if_report_by_move_to_folder When true, and the
  *                                                       report-by-move-to-
  *                                                       folder method is
  *                                                       configured, don't
  *                                                       show the corresponding
  *                                                       button if the message
  *                                                       in question is an
  *                                                       attachment to another
  *                                                       message, otherwise all
  *                                                       buttons are shown no
  *                                                       matter what (OPTIONAL;
  *                                                       default = FALSE).
  *
  * @return string HTML for the buttons to be displayed (for use in 1.4.x)
  *
  */
function get_spam_buttons(&$buttons, 
                          $dont_show_if_report_by_move_to_folder=FALSE)
{

   global $show_not_spam_button, $spam_button_text, $not_spam_button_text,
          $show_is_spam_button, $username, $data_dir,
          $sb_report_spam_by_move_to_folder, 
          $sb_report_not_spam_by_move_to_folder,
          $sb_suppress_spam_button_folder,
          $sb_suppress_spam_button_folder_allow_override,
          $sb_suppress_not_spam_button_folder,
          $sb_suppress_not_spam_button_folder_allow_override,
          $sb_show_spam_button_folder,
          $sb_show_spam_button_folder_allow_override,
          $sb_show_not_spam_button_folder,
          $sb_show_not_spam_button_folder_allow_override,
          $extra_buttons;
 
   $passed_ent_id = 0;
   sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
   sqgetGlobalVar('mailbox',       $mailbox,       SQ_FORM);
   if (empty($mailbox)) $mailbox = 'INBOX';

   spam_buttons_init();

   if ($sb_suppress_spam_button_folder_allow_override)
   {
      $sb_suppress_spam_button_folder = getPref($data_dir, $username, 
                                                'sb_suppress_spam_button_folder', 
                                                $sb_suppress_spam_button_folder);
   }
   if ($sb_suppress_not_spam_button_folder_allow_override)
   {
      $sb_suppress_not_spam_button_folder = getPref($data_dir, $username, 
                                                    'sb_suppress_not_spam_button_folder', 
                                                    $sb_suppress_not_spam_button_folder);
   }
   if ($sb_show_spam_button_folder_allow_override)
   {
      $sb_show_spam_button_folder = getPref($data_dir, $username, 
                                            'sb_show_spam_button_folder', 
                                            $sb_show_spam_button_folder);
   }
   if ($sb_show_not_spam_button_folder_allow_override)
   {
      $sb_show_not_spam_button_folder = getPref($data_dir, $username, 
                                                'sb_show_not_spam_button_folder', 
                                                $sb_show_not_spam_button_folder);
   }


   // these options used to be a strings; these lines convert
   // values that may have already been stored in user prefs
   // as strings into proper array format (also unpacks
   // serialized arrays for SM v1.4.14+)...  perhaps this
   // code should be removed a year (or more?) after 1.4.x
   // supports multiple select folder lists on the options page
   // (but not the unserialize() calls)
   //
   if (empty($sb_suppress_spam_button_folder))
      $sb_suppress_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_suppress_spam_button_folder))
      $sb_suppress_spam_button_folder = @unserialize($sb_suppress_spam_button_folder);
   if (!is_array($sb_suppress_spam_button_folder))
      $sb_suppress_spam_button_folder = array($sb_suppress_spam_button_folder);

   if (empty($sb_suppress_not_spam_button_folder))
      $sb_suppress_not_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_suppress_not_spam_button_folder))
      $sb_suppress_not_spam_button_folder = @unserialize($sb_suppress_not_spam_button_folder);
   if (!is_array($sb_suppress_not_spam_button_folder))
      $sb_suppress_not_spam_button_folder = array($sb_suppress_not_spam_button_folder);

   if (empty($sb_show_spam_button_folder))
      $sb_show_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_show_spam_button_folder))
      $sb_show_spam_button_folder = @unserialize($sb_show_spam_button_folder);
   if (!is_array($sb_show_spam_button_folder))
      $sb_show_spam_button_folder = array($sb_show_spam_button_folder);

   if (empty($sb_show_not_spam_button_folder))
      $sb_show_not_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_show_not_spam_button_folder))
      $sb_show_not_spam_button_folder = @unserialize($sb_show_not_spam_button_folder);
   if (!is_array($sb_show_not_spam_button_folder))
      $sb_show_not_spam_button_folder = array($sb_show_not_spam_button_folder);


//TODO: create tooltip with more verbose explanation 
   $ret = '';

   if ($show_is_spam_button || $show_not_spam_button)
      $ret .= ' ';
   
   sq_change_text_domain('spam_buttons');


   // 1) is button turned on in the first place?
   // 2) either A or B below:
   //     A) if current message is tagged as spam, don't show spam button
   //     B) if not using message tag settings or not in message view, then:
   //        a) if in (one of) the ham folder(s) ($sb_show_spam_button_folder
   //           defines a list of ham folders to show the spam button in),
   //           show spam button
   //        b) otherwise, if not using the ham folder (list), then
   //           if in (one of) the spam folder(s), don't show spam button
   // 3) can't show button if caller doesn't want it shown
   //    when report method is move-to-folder and we're 
   //    looking at a message that is an attachment to another
   // 
   if ($show_is_spam_button                                              // 1
    && (current_message_is_tagged(TRUE) === FALSE                        // 2A
     || (current_message_is_tagged(TRUE) === 0                           // 2B
      && (in_array($mailbox, $sb_show_spam_button_folder)                // 2Ba
       || (empty($sb_show_spam_button_folder)                            // 2Bb
        && !in_array($mailbox, $sb_suppress_spam_button_folder)))))
    && (!$dont_show_if_report_by_move_to_folder                          // 3
     || !($sb_report_spam_by_move_to_folder && !empty($passed_ent_id))))
   {
      if (check_sm_version(1, 5, 2))
         $buttons['isSpam'] = array('value' => _($spam_button_text), 'type' => 'submit');
      else if (check_sm_version(1, 5, 1))
         $buttons[1]['isSpam'] = array(0 => _($spam_button_text), 1 => 'submit');
      else
         $ret .= '<input type="submit" name="isSpam" value="' . _($spam_button_text) . "\" />\n";
   }


   // 1) is button turned on in the first place?
   // 2) either A or B below:
   //     A) if current message is tagged as ham, don't show ham button
   //     B) if not using message tag settings or not in message view, then:
   //        a) if in (one of) the spam folder(s) ($sb_show_not_spam_button_folder
   //           defines a list of spam folders to show the ham button in),
   //           show ham button
   //        b) otherwise, if not using the spam folder (list), then
   //           if in (one of) the ham folder(s), don't show ham button
   // 3) can't show button if caller doesn't want it shown
   //    when report method is move-to-folder and we're 
   //    looking at a message that is an attachment to another
   // 
   if ($show_not_spam_button                                                 // 1
    && (current_message_is_tagged(FALSE) === FALSE                           // 2A
     || (current_message_is_tagged(FALSE) === 0                              // 2B
      && (in_array($mailbox, $sb_show_not_spam_button_folder)                // 2Ba
       || (empty($sb_show_not_spam_button_folder)                            // 2Bb
        && !in_array($mailbox, $sb_suppress_not_spam_button_folder)))))
    && (!$dont_show_if_report_by_move_to_folder                              // 3
     || !($sb_report_not_spam_by_move_to_folder && !empty($passed_ent_id))))
   {
      if (check_sm_version(1, 5, 2))
         $buttons['notSpam'] = array('value' => _($not_spam_button_text), 'type' => 'submit');
      else if (check_sm_version(1, 5, 1))
         $buttons[1]['notSpam'] = array(0 => _($not_spam_button_text), 1 => 'submit');
      else
         $ret .= '<input type="submit" name="notSpam" value="' . _($not_spam_button_text) . "\" />\n";
   }


   // add any extra buttons
   //
   foreach ($extra_buttons as $button => $button_info)
   {

      // build button
      //
      if (sb_show_custom_button_or_link($button, $button_info))
      {
         $button_name = preg_replace('/[^a-zA-Z0-9]/', '_', $button);
         if (check_sm_version(1, 5, 2))
            $buttons[$button_name] = array('value' => _($button), 'type' => 'submit');
         else if (check_sm_version(1, 5, 1))
            $buttons[1][$button_name] = array(0 => _($button), 1 => 'submit');
         else
            $ret .= '<input type="submit" name="' . $button_name . '" value="' . _($button) . "\" />\n";
      }

   }


   sq_change_text_domain('squirrelmail');

   return $ret;

}



/**
  * Adds spam/ham links to message options list on read body page
  *
  */
function sb_read_message_links_do(&$links)
{

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');

   global $show_spam_link_on_read_body, $show_not_spam_button, 
          $spam_button_text, $not_spam_button_text, $show_is_spam_button,
          $sb_report_spam_by_move_to_folder, $username,
          $sb_report_not_spam_by_move_to_folder,
          $sb_suppress_spam_button_folder, $data_dir,
          $sb_suppress_spam_button_folder_allow_override,
          $sb_suppress_not_spam_button_folder,
          $sb_suppress_not_spam_button_folder_allow_override,
          $sb_show_spam_button_folder,
          $sb_show_spam_button_folder_allow_override,
          $sb_show_not_spam_button_folder, $extra_buttons,
          $sb_show_not_spam_button_folder_allow_override;

   spam_buttons_init();

   if (!$show_spam_link_on_read_body)
      return;

   if ($sb_suppress_spam_button_folder_allow_override)
   {
      $sb_suppress_spam_button_folder = getPref($data_dir, $username, 
                                                'sb_suppress_spam_button_folder', 
                                                $sb_suppress_spam_button_folder);
   }
   if ($sb_suppress_not_spam_button_folder_allow_override)
   {
      $sb_suppress_not_spam_button_folder = getPref($data_dir, $username, 
                                                    'sb_suppress_not_spam_button_folder', 
                                                    $sb_suppress_not_spam_button_folder);
   }
   if ($sb_show_spam_button_folder_allow_override)
   {
      $sb_show_spam_button_folder = getPref($data_dir, $username, 
                                            'sb_show_spam_button_folder', 
                                            $sb_show_spam_button_folder);
   }
   if ($sb_show_not_spam_button_folder_allow_override)
   {
      $sb_show_not_spam_button_folder = getPref($data_dir, $username, 
                                                'sb_show_not_spam_button_folder', 
                                                $sb_show_not_spam_button_folder);
   }


   // these options used to be a strings; these lines convert
   // values that may have already been stored in user prefs
   // as strings into proper array format (also unpacks
   // serialized arrays for SM v1.4.14+)...  perhaps this
   // code should be removed a year (or more?) after 1.4.x
   // supports multiple select folder lists on the options page
   // (but not the unserialize() calls)
   //
   if (empty($sb_suppress_spam_button_folder))
      $sb_suppress_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_suppress_spam_button_folder))
      $sb_suppress_spam_button_folder = @unserialize($sb_suppress_spam_button_folder);
   if (!is_array($sb_suppress_spam_button_folder))
      $sb_suppress_spam_button_folder = array($sb_suppress_spam_button_folder);

   if (empty($sb_suppress_not_spam_button_folder))
      $sb_suppress_not_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_suppress_not_spam_button_folder))
      $sb_suppress_not_spam_button_folder = @unserialize($sb_suppress_not_spam_button_folder);
   if (!is_array($sb_suppress_not_spam_button_folder))
      $sb_suppress_not_spam_button_folder = array($sb_suppress_not_spam_button_folder);

   if (empty($sb_show_spam_button_folder))
      $sb_show_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_show_spam_button_folder))
      $sb_show_spam_button_folder = @unserialize($sb_show_spam_button_folder);
   if (!is_array($sb_show_spam_button_folder))
      $sb_show_spam_button_folder = array($sb_show_spam_button_folder);

   if (empty($sb_show_not_spam_button_folder))
      $sb_show_not_spam_button_folder = array();
   else if (check_sm_version(1, 4, 14) && !is_array($sb_show_not_spam_button_folder))
      $sb_show_not_spam_button_folder = @unserialize($sb_show_not_spam_button_folder);
   if (!is_array($sb_show_not_spam_button_folder))
      $sb_show_not_spam_button_folder = array($sb_show_not_spam_button_folder);


   $spam_links = array();
   $passed_ent_id = 0;
   sqgetGlobalVar('mailbox',       $mailbox,       SQ_FORM);
   sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
   sqgetGlobalVar('startMessage',  $startMessage,  SQ_FORM);
   sqgetGlobalVar('view_as_html',  $view_as_html,  SQ_FORM);
   sqgetGlobalVar('account',       $account,       SQ_FORM);
   if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
      // fix for Dovecot UIDs can be bigger than normal integers
      $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');


   sq_change_text_domain('spam_buttons');

//TODO: create tooltip with more verbose explanation 
   // 1) is button turned on in the first place?
   // 2) either A or B below: 
   //     A) if current message is tagged as spam, don't show spam button
   //     B) if not using message tag settings, then:
   //        a) if in (one of) the ham folder(s) ($sb_show_spam_button_folder
   //           defines a list of ham folders to show the spam button in),
   //           show spam button
   //        b) otherwise, if not using the ham folder (list), then
   //           if in (one of) the spam folder(s), don't show spam button
   // 3) can't show button if report method is move-to-folder and we're
   //    looking at a message that is an attachment to another
   //
   if ($show_is_spam_button                                              // 1
    && (current_message_is_tagged(TRUE) === FALSE                        // 2A
     || (current_message_is_tagged(TRUE) === 0                           // 2B
      && (in_array($mailbox, $sb_show_spam_button_folder)                // 2Ba
       || (empty($sb_show_spam_button_folder)                            // 2Bb
        && !in_array($mailbox, $sb_suppress_spam_button_folder)))))
    && !($sb_report_spam_by_move_to_folder && !empty($passed_ent_id)))   // 3
      $spam_links[] = array('URL' => sqm_baseuri() 
         . 'src/read_body.php?isSpam=yslnk&mailbox=' 
         . urlencode($mailbox) . '&passed_id=' . $passed_id . '&view_as_html='
         . $view_as_html . '&startMessage=' . $startMessage . '&passed_ent_id=' 
         . $passed_ent_id . '&account=' . $account, 
         'Text' => _($spam_button_text));

   // 1) is button turned on in the first place?
   // 2) either A or B below: 
   //     A) if current message is tagged as ham, don't show ham button
   //     B) if not using message tag settings, then:
   //        a) if in (one of) the spam folder(s) ($sb_show_not_spam_button_folder
   //           defines a list of spam folders to show the ham button in),
   //           show ham button
   //        b) otherwise, if not using the spam folder (list), then
   //           if in (one of) the ham folder(s), don't show ham button
   // 3) can't show button if report method is move-to-folder and we're
   //    looking at a message that is an attachment to another
   //
   if ($show_not_spam_button                                                 // 1
    && (current_message_is_tagged(FALSE) === FALSE                           // 2A
     || (current_message_is_tagged(FALSE) === 0                              // 2B
      && (in_array($mailbox, $sb_show_not_spam_button_folder)                // 2Ba
       || (empty($sb_show_not_spam_button_folder)                            // 2Bb
        && !in_array($mailbox, $sb_suppress_not_spam_button_folder)))))
    && !($sb_report_not_spam_by_move_to_folder && !empty($passed_ent_id)))   // 3
      $spam_links[] = array('URL' => sqm_baseuri() 
         . 'src/read_body.php?notSpam=yslnk&mailbox='
         . urlencode($mailbox) . '&passed_id=' . $passed_id . '&view_as_html='
         . $view_as_html . '&startMessage=' . $startMessage . '&passed_ent_id=' 
         . $passed_ent_id . '&account=' . $account, 
         'Text' => _($not_spam_button_text));

   if (check_sm_version(1, 5, 2))
      $links = array_merge($links, $spam_links);

   else
   {
      foreach ($spam_links as $link)
         echo ' | <a href="' . $link['URL'] . '">' . $link['Text'] . '</a>';
   }


   // add any extra links
   //
   foreach ($extra_buttons as $button => $button_info)
   {

      // build button
      //
      if (sb_show_custom_button_or_link($button, $button_info))
      {
         $button_name = preg_replace('/[^a-zA-Z0-9]/', '_', $button);
         $button_link = sqm_baseuri() . 'src/read_body.php?' . $button_name
                      . '=yslnk&mailbox=' . urlencode($mailbox) . '&passed_id='
                      . $passed_id . '&view_as_html=' . $view_as_html
                      . '&startMessage=' . $startMessage . '&passed_ent_id='
                      . $passed_ent_id . '&account=' . $account;
         if (check_sm_version(1, 5, 2))
            $links[] = array('URL'  => $button_link,
                             'Text' => $button);

         else
            echo ' | <a href="' . $button_link . '">' . $button . '</a>';
      }

   }


   sq_change_text_domain('squirrelmail');

}



/**
  * Adds spam/ham buttons to mailbox listing
  *
  */
function sb_mailbox_list_buttons_do(&$buttons)
{

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');

   global $show_spam_buttons_on_message_list;

   spam_buttons_init();

   if (!$show_spam_buttons_on_message_list)
      return;

   if (check_sm_version(1, 5, 1))
   {
      get_spam_buttons($buttons);
   }
   else
   {
      $temp = '';
      echo get_spam_buttons($temp);
   }

}



/**
  * Adds spam/ham buttons to message display 
  * (currently only useful in 1.5.x)
  *
  */
function sb_read_message_buttons_do($args)
{

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');

   global $show_spam_buttons_on_read_body;

   spam_buttons_init();

   if (!$show_spam_buttons_on_read_body)
      return;


   if (check_sm_version(1, 5, 2))
   {
//TODO: 1.5.2 should change the API for the buttons on the read message screen, then this code will have to change to suit
      global $oTemplate;

      sqgetGlobalVar('mailbox',       $mailbox,       SQ_FORM);
      sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
      sqgetGlobalVar('startMessage',  $startMessage,  SQ_FORM);
      sqgetGlobalVar('view_as_html',  $view_as_html,  SQ_FORM);
      sqgetGlobalVar('account',       $account,       SQ_FORM);
      if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
         // fix for Dovecot UIDs can be bigger than normal integers
         $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');

      $buttons = array();
      get_spam_buttons($buttons, TRUE); 

      $oTemplate->assign('buttons', $buttons);
      $oTemplate->assign('mailbox', $mailbox);
      $oTemplate->assign('passed_id', $passed_id);
      $oTemplate->assign('passed_ent_id', $passed_ent_id);
      $oTemplate->assign('startMessage', $startMessage);
      $oTemplate->assign('view_as_html', $view_as_html);
      $oTemplate->assign('account', $account);

      $output = $oTemplate->fetch('plugins/spam_buttons/read_menubar_buttons.tpl');

      return array('read_body_menu_buttons_bottom' => $output,
                   'read_body_menu_buttons_top' => $output);
   }


   // as of SM version 1.5.0, this works a little differently
   //
   else if (check_sm_version(1, 5, 0))
   {

      sqgetGlobalVar('mailbox',       $mailbox,       SQ_FORM);
      sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
      sqgetGlobalVar('startMessage',  $startMessage,  SQ_FORM);
      sqgetGlobalVar('view_as_html',  $view_as_html,  SQ_FORM);
      if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
         // fix for Dovecot UIDs can be bigger than normal integers
         $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');

      // add spam buttons after delete stuff
      //
      $temp = '';
      $args[1] = preg_replace('/<\/td>/', 
                 '<form method="post" style="display: inline">' 
               . get_spam_buttons($temp, TRUE) 
               . '<input type="hidden" name="mailbox" value="' . $mailbox . '" />'
               . '<input type="hidden" name="msg[0]" value="' . $passed_id . '" />'
               . '<input type="hidden" name="passed_ent_id" value="' . $passed_ent_id . '" />'
               . '<input type="hidden" name="view_as_html" value="' . $view_as_html . '" />'
               . '<input type="hidden" name="startMessage" value="' . $startMessage . '" />'
               . '</form></td>', $args[1], 1);
   
      return $args;

   }


   // all older versions...  way too much trouble, especially if 
   // preview pane is in use; read_body_header_right will be 
   // sufficient
   //
   else
   {

      return;

   }


}



/**
  * Determine if the current message being viewed is spam or ham
  *
  * If the configuration items related to this functionality are
  * not set, or we are not on a message view screen, this always 
  * returns 0 (see WARNING below).
  *
  * @param boolean $spam When TRUE, check if the message is spam,
  *                      when FALSE, check if the message is ham.
  *
  * @return mixed TRUE if the message has been tagged as inquired
  *               FALSE if the message has not been tagged as such
  *               0 if the needed configuration settings are not
  *               set up in the configuration file or the current
  *               page request is not the message view page.  
  *               WARNING: callers need to carefully check for 
  *               either 0 or FALSE (using === or !==).
  *
  */
function current_message_is_tagged($spam)
{

   if (defined('PAGE_NAME'))
   {
      if (PAGE_NAME != 'read_body') return 0;
   }
   else
   {
      global $PHP_SELF;
      if (strpos($PHP_SELF, '/src/read_body') === FALSE) 
         return 0;
   }

   global $sb_spam_header_name, $sb_not_spam_header_name,
          $sb_spam_header_value, $sb_not_spam_header_value;

   spam_buttons_init();

   sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
   if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
      // fix for Dovecot UIDs can be bigger than normal integers
      $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');

   if ($spam)
   {

      if (empty($sb_spam_header_name) || empty($sb_spam_header_value))
         return 0;

      $header = sb_get_message_header($passed_id, $passed_ent_id, $sb_spam_header_name);
      if (preg_match($sb_spam_header_value, trim($header[1])))
         return TRUE;

/* ......old code, less efficient; left for posterity............................
      $headers = sb_get_message_headers($passed_id, $passed_ent_id);
      foreach ($headers as $header)
         if (strtolower($header[0]) == strtolower($sb_spam_header_name . ':')
          && preg_match($sb_spam_header_value, trim($header[1])))
            return TRUE;
............................................................................. */

      return FALSE;

   }
   else 
   {

      if (empty($sb_not_spam_header_name) || empty($sb_not_spam_header_value))
         return 0;

      $header = sb_get_message_header($passed_id, $passed_ent_id, $sb_not_spam_header_name);
      if (preg_match($sb_not_spam_header_value, trim($header[1])))
         return TRUE;

/* ......old code, less efficient; left for posterity............................
      $headers = sb_get_message_headers($passed_id, $passed_ent_id);
      foreach ($headers as $header)
         if (strtolower($header[0]) == strtolower($sb_not_spam_header_name . ':')
          && preg_match($sb_not_spam_header_value, trim($header[1])))
            return TRUE;
............................................................................. */

      return FALSE;

   }

}



/**
  * Determines if a custom/extra button or link should be shown.
  *
  * @param string $button_name The name of the button or link
  * @param array  $button_info The array of button callbacks and
  *                            associated information from the
  *                            configuration file
  *
  * @return boolean TRUE if the button should be shown, or FALSE
  *                 if it should not.
  *
  */
function sb_show_custom_button_or_link($button_name, $button_info)
{

   global $username;
   $callback = '';
   $action = '';
   $passed_id = '';
   $passed_ent_id = '';
   $from = '';
   $error = '';
   $show_it = FALSE;


   // this function is found in the Compatibility plugin v2.0.5+
   //
   $hook_name = get_current_hook_name();


   sq_change_text_domain('spam_buttons');


   // in this case, SquirrelMail is building the buttons at the
   // top of the message list screen
   //
   if ($hook_name == 'mailbox_display_buttons'  // SM 1.4.x
    || $hook_name == 'message_list_controls')  // SM 1.5.x
   {
      $action = 'MESSAGE_LIST_BUTTON';
      if (!isset($button_info[0]))
         $error = sprintf(_("Spam Buttons plugin is not configured correctly!  Check custom button configuration array for \"%s\""), $button_name);
      else
         $callback = $button_info[0];
   }


   // in this case, SquirrelMail is building the "Options" links
   // on the message view screen
   //
   else if ($hook_name == 'read_body_header_right')
   {

      $action = 'MESSAGE_VIEW_LINK';
      if (!isset($button_info[1]))
         $error = sprintf(_("Spam Buttons plugin is not configured correctly!  Check custom button configuration array for \"%s\""), $button_name);
      else
         $callback = $button_info[1];


      // identify the message being viewed in case they are needed
      //
      sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
      if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
         // fix for Dovecot UIDs can be bigger than normal integers
         $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');


      // this retrieves the message's From header in the format
      // array(0 => 'From:', 1 => '"Jose" <jose@example.org>')
      //
      $from = sb_get_message_header($passed_id, $passed_ent_id, 'From');


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

   }


   // in this case, SquirrelMail is building the buttons in the
   // message view screen's button row (SquirrelMail 1.5.2+ only)
   //
   else if ($hook_name == 'template_construct_read_menubar_buttons.tpl')
   {
// NOTE: at some point, 1.5.x is likely to change the API for the buttons on the read message screen, then this code will have to change to suit

      $action = 'MESSAGE_VIEW_BUTTON';
      if (!isset($button_info[2]))
         $error = sprintf(_("Spam Buttons plugin is not configured correctly!  Check custom button configuration array for \"%s\""), $button_name);
      else
         $callback = $button_info[2];


      // these values identify the message being viewed
      // in case you need them
      //
      sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
      if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
         // fix for Dovecot UIDs can be bigger than normal integers
         $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');


      // this retrieves the message's From header in the format
      // array(0 => 'From:', 1 => '"Jose" <jose@example.org>')
      //
      $from = sb_get_message_header($passed_id, $passed_ent_id, 'From');


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

   }


   // is the button always to be shown, or never to be shown?
   //
   if (empty($error))
   {
      if ($callback === 1 || $callback === '1')
      {
         sq_change_text_domain('squirrelmail');
         return TRUE;
      }
      if ($callback === 0 || $callback === '0')
      {
         sq_change_text_domain('squirrelmail');
         return FALSE;
      }
   }


   // otherwise, execute callback to figure it out...
   //
   if (empty($callback))
      $error = sprintf(_("Spam Buttons plugin is not configured correctly!  Check custom button configuration array for \"%s\""), $button_name);
   else if (!function_exists($callback))
      $error = sprintf(_("Function \"%s\" not found in Spam Buttons plugin"), $callback);
   else
      $show_it = $callback($action, $username, $from, $passed_id, $passed_ent_id);


   // error?
   //
   if (!empty($error))
   {
      global $color;
      sq_change_text_domain('squirrelmail');
      $ret = plain_error_message($error, $color);
      if (check_sm_version (1, 5, 2))
      {
         echo $ret;
         global $oTemplate;
         $oTemplate->display('footer.tpl');
      }
      exit;
   }


   // return value from the callback
   //
   sq_change_text_domain('squirrelmail');
   return $show_it;

}



