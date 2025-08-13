<?php


/**
  * SquirrelMail Sent Confirmation Plugin
  * Copyright (C) 2004 Paul Lesneiwski <pdontthink@angrynerds.com>
  * This program is licensed under GPL. See COPYING for details
  *
  */


   global $sent_confirmation_debug;
   $sent_confirmation_debug = 0;

   // include compatibility plugin
   //
   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/compatibility/functions.php');
   else if (file_exists('../plugins/compatibility/functions.php'))
      include_once('../plugins/compatibility/functions.php');
   else if (file_exists('./plugins/compatibility/functions.php'))
      include_once('./plugins/compatibility/functions.php');



// Very simple place to indicate that the message was sent
//
function sent_conf_message_sent_do() {


   // if saving as a draft, this plugin should be ignored
   //
   global $draft; if ($draft) return;


   // first, check if the restrict_senders plugin 
   // is installed and if so, it needs to run first
   //
   global $plugins;

   if (in_array('restrict_senders', $plugins))
   {
      global $restrict_senders_finished, $sent_confirmation_was_delayed;
      if (!$restrict_senders_finished)
      {
         $sent_confirmation_was_delayed = 1;
         return;
      }
   }



   global $sent_confirmation_debug, $sent_conf_message_sent_status,
          $sent_conf_include_recip_addr, $send_to, $send_to_cc, $send_to_bcc,
          $sent_conf_show_only_first_recip_addr, $sent_conf_enable,
          $sent_conf_allow_user_override, $data_dir, $username,
          $sent_conf_include_cc, $sent_conf_include_bcc,
          $sent_conf_message_style, $sent_conf_show_headers,
          $mailbox, $passed_id, $action, $sort, $sent_conf_orig_reply_msg,
          $username, $imapServerAddress, $key, $imapPort, $sent_conf_orig_subject,
          $sent_conf_orig_sender, $sent_conf_enable_orig_msg_options, 
          $lastTargetMailbox, $sent_conf_mbox_list;

   compatibility_sqextractGlobalVar('lastTargetMailbox');

   if (compatibility_check_sm_version(1, 3))
      include_once (SM_PATH . 'plugins/sent_confirmation/config.php');
   else
      include_once ('../plugins/sent_confirmation/config.php');


   $sent_conf_enable = ($sent_conf_message_style == 'off' ? 0 : 1);


   // get all our config set up
   //
   if ($sent_conf_allow_user_override)
   {
      $sent_conf_enable = getPref($data_dir, $username, 'sent_conf_enable', $sent_conf_enable);
      $sent_conf_message_style = getPref($data_dir, $username, 'sent_conf_style', $sent_conf_message_style);
      $sent_conf_include_recip_addr = getPref($data_dir, $username, 'sent_conf_incl_recip', $sent_conf_include_recip_addr);
      $sent_conf_show_only_first_recip_addr = getPref($data_dir, $username, 'sent_conf_show_only_first_recip_addr', $sent_conf_show_only_first_recip_addr);
      $sent_conf_include_cc = getPref($data_dir, $username, 'sent_conf_include_cc', $sent_conf_include_cc);
      $sent_conf_include_bcc = getPref($data_dir, $username, 'sent_conf_include_bcc', $sent_conf_include_bcc);
      $sent_conf_show_headers = getPref($data_dir, $username, 'sent_conf_show_headers', $sent_conf_show_headers);
      $sent_conf_enable_orig_msg_options = getPref($data_dir, $username, 'sent_conf_enable_orig_msg_options', $sent_conf_enable_orig_msg_options);
   }


   // if not enabled, just quit
   //
   if (!$sent_conf_enable)
      return;


   // build information to be passed to display function below
   //
   $sent_conf_message_sent_status = 'sent';

   if ($sent_conf_include_recip_addr)
   {
      if ($sent_conf_show_only_first_recip_addr)
      {
         preg_match('/\s*([\'"].*?[\'"]){0,1}(.*?)([,;]|$)/', trim($send_to), $matches);
         if (isset($matches[2]))
         {
            if (isset($matches[1]))
               $sent_conf_message_sent_status = $matches[1] . $matches[2] 
                  . ($sent_conf_message_style < 3 ? ', et al' : '');
            else
               $sent_conf_message_sent_status = $matches[2] 
                  . ($sent_conf_message_style < 3 ? ', et al' : '');
         }

         // failsafe
         //
         else
            $sent_conf_message_sent_status = $send_to;

      }
      else
      {
         if ($sent_conf_show_headers)
            //$sent_conf_message_sent_status = 'To: ';
            $sent_conf_message_sent_status = '';
         else
            $sent_conf_message_sent_status = '';
         $sent_conf_message_sent_status .= $send_to;
         $send_to_cc = trim($send_to_cc);
         $send_to_bcc = trim($send_to_bcc);
         if ($sent_conf_include_cc && !empty($send_to_cc))
         {
            if ($sent_conf_show_headers)
               $sent_conf_message_sent_status .= ', Cc: ' . $send_to_cc;
            else
               $sent_conf_message_sent_status .= ', ' . $send_to_cc;
         }
         if ($sent_conf_include_bcc && !empty($send_to_bcc))
         {
            if ($sent_conf_show_headers)
               $sent_conf_message_sent_status .= ', Bcc: ' . $send_to_bcc;
            else
               $sent_conf_message_sent_status .= ', ' . $send_to_bcc;
         }
      }
   }


   // get reply info for deletion, move, back-to options
   //
   $sent_conf_orig_reply_msg = '';
   $sent_conf_orig_subject = '';
   $sent_conf_orig_sender = '';
   $sent_conf_mbox_list = '';

   if ($sent_conf_message_style > 2 && $sent_conf_enable_orig_msg_options 
   && (strpos($action, 'reply') !== FALSE || strpos($action, 'forward') !== FALSE))
   {
      $sent_conf_orig_reply_msg = $mailbox . '|' . $passed_id . '|' . $sort;

      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
      sqimap_mailbox_select($imapConnection, $mailbox);
      $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);
      $header = $message->rfc822_header;

      $sent_conf_orig_subject = decodeHeader($header->subject,false,true);

      $orig_from = (is_array($header->from)) ? $header->from[0] : $header->from;
      if (is_object($orig_from)) {
         $sent_conf_orig_sender = $orig_from->getAddress();
      } else {
         $sent_conf_orig_sender = '';
      }

      $sent_conf_mbox_list = sqimap_mailbox_option_list($imapConnection, 
                                                        array(strtolower($lastTargetMailbox)));

   }


   compatibility_sqsession_register($sent_conf_orig_sender, 
                                    'sent_conf_orig_sender');
   compatibility_sqsession_register($sent_conf_orig_subject, 
                                    'sent_conf_orig_subject');
   compatibility_sqsession_register($sent_conf_orig_reply_msg, 
                                    'sent_conf_orig_reply_msg');
   compatibility_sqsession_register($sent_conf_mbox_list, 
                                    'sent_conf_mbox_list');
   compatibility_sqsession_register($sent_conf_message_sent_status, 
                                    'sent_conf_message_sent_status');

   if ($sent_confirmation_debug == 1)
   {
      echo "<b>just before sending</b><pre>";
      print_r($_SESSION);
      echo "</pre>";
      exit;
   }

}



// Determine if there was a message just sent, if so, display message
//
function sent_conf_check_is_sent_do() 
{


   // get global variable for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4,1)) {
      global $HTTP_SERVER_VARS;
      $_SERVER = $HTTP_SERVER_VARS;
   }


   // strange, but on replies, the session doesn't seem
   // to be getting started before output is being sent
   // so we force it here
   //
//   if (stristr($_SERVER['SCRIPT_NAME'], 'compose.php'))
//      compatibility_sqsession_is_active();
// Nope, doesn't do the trick; seems that even though the
// session is started, it throughs up errors... whatever!


   // this is called in the generic header, so we 
   // only want to do this when we are on the right_main
   // page (unless "compose in new window" is on, in
   // which case we want to do it in the compose page,
   // or we are on the compose page and we have "mail_sent",
   // meaning that a draft was just resumed and sent)
   //
   global $compose_new_win, $mail_sent;
   if (($compose_new_win && !stristr($_SERVER['SCRIPT_NAME'], 'compose.php'))
   || (!$compose_new_win && !stristr($_SERVER['SCRIPT_NAME'], 'right_main.php')
      && !($mail_sent && stristr($_SERVER['SCRIPT_NAME'], 'compose.php'))))
   {

/*
      // make sure sent flag is off
      //
  Note: this clears flag when most any page is loaded; whereas the only
  problem it is meant to solve is when SMTP errors occur upon sending,
  the next time this plugin runs, it displays the sent confirmation, even
  though the message DID NOT get sent due to the SMTP errors.  This is
  an architectural compromise.  It could be solved by adding a new hook
  just after the message was sent in the SM core, but for now, I'll assume
  that most SM installations actually have working SMTP or sendmail servers.
      sent_conf_compose_bottom_do();
*/

      return;

   }
   


   global $sent_conf_message_sent_status, $color, $sent_confirmation_debug, 
          $sent_conf_message_style, $sent_conf_include_recip_addr, 
          $sent_conf_allow_user_override, $data_dir, $username, $sent_conf_enable,
          $sent_conf_show_headers, $sent_conf_orig_reply_msg, $pp_noPageHeader,
          $sent_conf_orig_subject, $sent_conf_orig_sender, $pp_forceTopURL, 
          $sent_conf_enable_orig_msg_options, $compose_new_win,
          $sent_conf_mbox_list, $blockedAddresses, $mailbox, $plugins;

   compatibility_sqextractGlobalVar('sent_conf_message_sent_status');
   compatibility_sqextractGlobalVar('sent_conf_orig_reply_msg');
   compatibility_sqextractGlobalVar('sent_conf_orig_subject');
   compatibility_sqextractGlobalVar('sent_conf_orig_sender');
   compatibility_sqextractGlobalVar('sent_conf_mbox_list');
   compatibility_sqextractGlobalVar('blockedAddresses');

   if (compatibility_check_sm_version(1, 3))
      include_once (SM_PATH . 'plugins/sent_confirmation/config.php');
   else
      include_once ('../plugins/sent_confirmation/config.php');

   $sent_conf_enable = ($sent_conf_message_style == 'off' ? 0 : 1);

   if ($sent_conf_allow_user_override)
   {
      $sent_conf_enable = getPref($data_dir, $username, 'sent_conf_enable', $sent_conf_enable);
      $sent_conf_message_style = getPref($data_dir, $username, 'sent_conf_style', $sent_conf_message_style);
      $sent_conf_include_recip_addr = getPref($data_dir, $username, 'sent_conf_incl_recip', $sent_conf_include_recip_addr);
      $sent_conf_show_headers = getPref($data_dir, $username, 'sent_conf_show_headers', $sent_conf_show_headers);
      $sent_conf_enable_orig_msg_options = getPref($data_dir, $username, 'sent_conf_enable_orig_msg_options', $sent_conf_enable_orig_msg_options);
   }


   if ($sent_confirmation_debug == 2)
   {
      echo "<b>session...</b><pre>";
      print_r($_SESSION);
      echo "</pre>";
   }


   // if not enabled, just quit
   //
   if (!$sent_conf_enable)
      return;



   // if we should be showing a notification, then do it
   //
   if (isset($sent_conf_message_sent_status) 
          && !empty($sent_conf_message_sent_status)
          && $sent_conf_message_sent_status != 'not_sent'
          && $sent_conf_message_style != 'off')
   {


      // if using preview pane plugin and it hasn't run
      // first, go run it now
      //
      if (in_array('preview_pane', $plugins))
      {

         if (!(isset($pp_forceTopURL) && !empty($pp_forceTopURL)))
         {
            if (compatibility_check_sm_version(1, 3))
               include_once (SM_PATH . 'plugins/preview_pane/functions.php');
            else
               include_once ('../plugins/preview_pane/functions.php');
            preview_pane_check_frames_do();
         }
         
         // if preview_pane is redirecting us, don't do anything here
         //
         if ($pp_forceTopURL == "yes") return;

      }
      else 
         $pp_noPageHeader = FALSE;


      // a little trickery to get the page header
      // printed out... the problem is that this
      // function is called in a hook that is part
      // of displayPageHeader(), so we need to avoid
      // an endless recursion loop here
      //
      if ($sent_conf_message_style > 2)
      {
  
         global $color, $sent_conf_count;
         if (!$sent_conf_count)
         {
            $sent_conf_count = 1;
            if (!empty($mailbox))
               ($pp_noPageHeader ? pp_displayPageHeader($color, $mailbox)
                                 : displayPageHeader($color, $mailbox));
            else
               ($pp_noPageHeader ? pp_displayPageHeader($color, 'None')
                                 : displayPageHeader($color, 'None'));
         }
         else
         {
            return;
         }

      }


      echo '<table width="100%" bgcolor="' . $color[0] 
         . '" cellpadding="0" cellspaceing="0"><tr><td>';



  
      bindtextdomain('sent_confirmation', SM_PATH . 'plugins/sent_confirmation/locale');
      textdomain('sent_confirmation');



      //-------------------------------------------------------------
      // STYLE 1
      //-------------------------------------------------------------
      if ($sent_conf_message_style == 1)
      {
         if ($sent_conf_include_recip_addr)
         {
            $addresses = sc_parseEmailAddresses($sent_conf_message_sent_status);
            $output = '';
            $firstTime = TRUE;
            foreach ($addresses as $addressAndNick) 
            {
               $address = trim($addressAndNick[0]);
               if (!empty($address))
               {
                  if (!$firstTime) 
                     $output .= ', ';
                  $firstTime = FALSE;
                  $output .= $address;
               }
            }
            echo '<strong>' . _("Message Sent To: ") 
               . $output;
 

            // if any disallowed addresses from the
            // restrict senders plugin, display them
            //
            if ($blockedAddresses)
            {
               echo '<br><font color="' . $color[1] . '">BLOCKED ADDRESSES: ';
               $first = 1;
               foreach ($blockedAddresses as $badAddr)
               {
                  if (!$first) echo ', ';
                  $first = 0;
                  echo $badAddr;
               }
               echo '</font>';
            }
            echo '</strong>';
         }
         else
            echo '<strong>' . _("Message Sent") . '</strong>';
      }


      //-------------------------------------------------------------
      // STYLE 2
      //-------------------------------------------------------------
      else if ($sent_conf_message_style == 2)
      {
         if ($sent_conf_include_recip_addr)
         {
            $addresses = sc_parseEmailAddresses($sent_conf_message_sent_status);
            $output = '';
            $firstTime = TRUE;
            foreach ($addresses as $addressAndNick)
            {
               $address = trim($addressAndNick[0]);
               if (!empty($address))
               {
                  if (!$firstTime)
                     $output .= ', ';
                  $firstTime = FALSE;
                  $output .= $address;
               }
            }
            echo '<strong><center>' . _("Your message has been sent to ") 
               . $output;

            // if any disallowed addresses from the
            // restrict senders plugin, display them
            //
            if ($blockedAddresses)
            {
               echo '<br><font color="' . $color[1] . '">BLOCKED ADDRESSES: ';
               $first = 1;
               foreach ($blockedAddresses as $badAddr)
               {
                  if (!$first) echo ', ';
                  $first = 0;
                  echo $badAddr;
               }
               echo '</font>';
            }
            echo '</center></strong>';
         }
         else
            echo '<strong><center>' . _("Your message has been sent") . '</center></strong>';
      }


      //-------------------------------------------------------------
      // STYLE 3
      //-------------------------------------------------------------
      else if ($sent_conf_message_style == 3)
      {

         // if the quicksave plugin is installed and active, we 
         // need to tell it here that the message was sent successfully
         // (in case the user doesn't return to the message list, 
         // where quicksave usually does this for itself)
         //
         global $plugins;
         if (in_array('quicksave', $plugins))
         {
            if (compatibility_check_sm_version(1, 3))
               include_once (SM_PATH . 'plugins/quicksave/functions.php');
            else
               include_once ('../plugins/quicksave/functions.php');

            quicksave_turn_off();
         }


         // code stolen from /src/read_body.php as 
         // well as Wolf Bergenheim's Todo plugin
         //
         global $color, $base_uri, $sort, $startMessage,
                $sent_logo, $sent_logo_width, $sent_logo_height;
         $urlMailbox = urlencode($mailbox);


         echo '<TABLE CELLSPACING="0" WIDTH="100%" BORDER="0" ALIGN="CENTER" CELLPADDING="0">' 
            . '<TR><TD BGCOLOR="' . $color[9] . '" WIDTH="100%">'
            . '<TABLE WIDTH="100%" CELLSPACING="0" BORDER="0" CELLPADDING="3">' 
            . '<TR>' 
            . '<TD ALIGN="LEFT" WIDTH="100%">' 
            . '<SMALL>';
         if ($compose_new_win)
         {
            echo '<A href="#" onClick="return self.close()">'
               . _("Close") . '</A></SMALL>';
//            echo '<A HREF="' . $base_uri . 'src/'
//               . "compose.php?newmessage=1\">"
//               . _("Compose") . '</A></SMALL>';
         }
         else
         {
            echo '<A HREF="' . $base_uri . 'src/'
               . "right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox\""
               . ($pp_noPageHeader ? ' target="right">' : '>')
               . _("Message List") . '</A></SMALL>';
         }
         echo '</TD></TR></TABLE>'
            . '</TD></TR><TR><TD>';


         echo '<br><table align=center width="60%" cellpadding=0 bgcolor="'
            . $color[9] . '" cellspacing=3 border=0>'
            . '<tr><td>'
            . '<table width="100%" cellpadding=5 cellspacing=1 border=0 bgcolor="' 
            . $color[4] .'">';
         if (isset($sent_logo) && !empty($sent_logo))
         {
            echo '<tr><td align="center"><img src="' . $sent_logo . '"';
            if (isset($sent_logo_width) && !empty($sent_logo_width))
               echo 'width="' . $sent_logo_width . '"';
            if (isset($sent_logo_height) && !empty($sent_logo_height))
               echo ' height="' . $sent_logo_height . '"';
            echo '></td></tr>'. "\n"; 
         }
         echo '<tr><td align="center"><form name="sent_conf_form" action="../plugins/sent_confirmation/address_book_import.php" method="POST" onSubmit="if (document.sent_conf_form.elements.length == 2) { document.sent_conf_form.elements[0].checked=true; return true;} okToSubmit=false; for (i=0; i<document.sent_conf_form.elements.length; i++){if (document.sent_conf_form.elements[i].type == \'checkbox\') if (document.sent_conf_form.elements[i].checked) okToSubmit=true} if (!okToSubmit) alert(\'' . _("Please select an address to add to your address book.") . '\'); return okToSubmit"><strong>'. "\n" 
            . _("Message Sent To:") . '</strong></td></tr>'."\n"
            . '<tr><td align=center><table border=0 cellspacing=0 cellpadding=0>'."\n";

          
         // grab actual email addresses
         //
         $addresses = sc_parseEmailAddresses($sent_conf_message_sent_status);


         // put addresses in the form
         //
         $x = 0;
         foreach ($addresses as $addressAndNick)
         {
            $address = trim($addressAndNick[0]);
            $nick = $addressAndNick[1];
            if (!empty($address))
            {
               if ($sent_conf_show_headers && strpos($address, 'Cc:') === 0)
               {
                  $address = substr($address, 3);
                  echo '</table></td></tr><tr><td align="center"><strong>'."\n"
                     . _("Cc:") . '</strong></td></tr><tr><td align="center">'
                     . '<table border=0 cellspacing=0 cellpadding=0>'
                     . '<tr><td><input type="checkbox" name="address' . (++$x) 
                     . '" value="' . urlencode($address) . '---' . urlencode($nick) . '">&nbsp;</td><td>'."\n"
                     . $address . '</td></tr>'."\n";
               }
               else if ($sent_conf_show_headers && strpos($address, 'Bcc:') === 0)
               {
                  $address = substr($address, 4);
                  echo '</table></td></tr><tr><td align="center"><strong>'."\n"
                     . _("Bcc:") . '</strong></td></tr><tr><td align="center">'
                     . '<table border=0 cellspacing=0 cellpadding=0>'
                     . '<tr><td><input type="checkbox" name="address' . (++$x) 
                     . '" value="' . urlencode($address) . '---' . urlencode($nick) . '">&nbsp;</td><td>'."\n"
                     . $address . '</td></tr>'."\n";
               }
               else
                  echo '<tr><td><input type="checkbox" name="address' . (++$x) 
                     . '" value="' . urlencode($address) . '---' . urlencode($nick) . '">&nbsp;</td><td>'."\n"
                     . $address . '</td></tr>'."\n";
            }
         }

         echo '</table>'
            . '<br><input type=submit value="' . _("Add To Address Book")
            . '"></form>';

         // if is reply, offer deletion of original message here
         //
         if ($sent_conf_enable_orig_msg_options && isset($sent_conf_orig_reply_msg) && !empty($sent_conf_orig_reply_msg))
         {

            list($mailbox, $passed_id, $sort) = explode('|', $sent_conf_orig_reply_msg);


            //echo '<table><tr><td><small>' . _("Original Message") . ':</small></td><td><small>' 
               //. $sent_conf_orig_subject 
               //. '</small></td></tr><tr><td>&nbsp;</td><td><small><a href="../src/delete_message.php?mailbox=' 
               //. $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=1">'
//Note: if we use this Delete link, have to retrofit w/if statement about $pp_noPageHeader (for preview pane plugin)
               //. _("Delete") . '</a><small></td></tr></table>';


            //echo '<small><a href="../src/delete_message.php?mailbox=' . $mailbox . '&message=' 
               //. $passed_id . '&sort=' . $sort . '&startMessage=1">' 
//Note: if we use this Delete link, have to retrofit w/if statement about $pp_noPageHeader (for preview pane plugin)
               //. _("Delete Original Message") . '</a><br>' . _("Subject") . ': ' 
               //. $sent_conf_orig_subject . '</small>';


// this one looked nice (only delete link)
            //echo '<table><tr><td colspan="2" align="center"><small><a href="../src/delete_message.php?mailbox=' 
               //. $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=1">' 
//Note: if we use this Delete link, have to retrofit w/if statement about $pp_noPageHeader (for preview pane plugin)
               //. _("Delete Original Message") . '</a></small></td></tr><tr><td align="right"><small><strong>' . _("From") 
               //. ':</strong></small></td><td><small>' . $sent_conf_orig_sender . '</small></td></tr><tr><td align="right"><small><strong>'
               //. _("Subject") . ':</strong></small></td><td><small>' 
               //. $sent_conf_orig_subject . '</small></td></tr></table>';


            $safe_name = preg_replace("/[^0-9A-Za-z_]/", '_', $mailbox);
            $form_name = "FormMsgs" . $safe_name;

            echo '<form name="' . $form_name . '" method="post" action="../src/move_messages.php">' . "\n"
               . '<input type="hidden" name="mailbox" value="' . htmlspecialchars($mailbox) . '">'
               . '<input type="hidden" name="sort" value="' . $sort . '">'
               . '<input type="hidden" name="msg" value="">'
               . '<input type="hidden" name="msg[0]" value="' . $passed_id . '">'
               . '<input type="hidden" name="location" value="'
               . "right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox"
               . '">'
               . "\n"
               . '<table><tr><td colspan="2" align="center"><small><strong>' . _("Original Message") 
               . '<strong></small><hr></td></tr><tr><td align="right"><small><strong>' . _("From") 
               . ':</strong></small></td><td><small>' . $sent_conf_orig_sender . '</small></td></tr><tr><td align="right"><small><strong>'
               . _("Subject") . ':</strong></small></td><td><small>' 
               . $sent_conf_orig_subject . '</small></td></tr>'
////               . '<tr><td colspan="2" align="center"><hr></td></tr>'
               . '<tr><td colspan="2" align="center"><small><br>';

            if ($pp_noPageHeader)
               echo '<a href="../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '" onClick="parent.right.document.location=\'../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '\'; document.location=\'' . SM_PATH . 'plugins/preview_pane/empty_frame.php\'; return false;">';
// was:  echo '<a href="' . rand() . '" onClick="parent.right.document.location=\'../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '\'; document.location=\'' . SM_PATH . 'plugins/preview_pane/empty_frame.php\'; return false;">';
            else
               echo '<a href="../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '">';

            echo 'Delete</a> | <a href="../src/read_body.php?mailbox='
               . $mailbox . '&passed_id=' . $passed_id 
               . '&startMessage=' . $startMessage . '">Return To Message</a></small>'
               . '</td></tr><tr><td align="center" colspan="2"><small>' 
               . _("Move to:") . ' <select name="targetMailbox">' . $sent_conf_mbox_list
               . '</SELECT>&nbsp;'
               . getButton('SUBMIT', 'moveButton', _("Move")) . "\n"
. ""
               . '</small></td></tr>'
               . '</table></form>';

// read links as constructed in mailbox_display.php
// but stuff like startMessage and searchstr don't
//////// note - startMessage has been fixed
// appear to be available after the send button is 
// pressed
//
//<a href="read_body.php?mailbox='.$urlMailbox
//  .  '&amp;passed_id='. $msg["ID"]
//  .  '&amp;startMessage='.$start_msg.$searchstr.'"';
//  $td_str .= ' ' .concat_hook_function('subject_link', array($start_msg, $searchstr));
//  if ($subject != $msg['SUBJECT']) {
//  $title = get_html_translation_table(HTML_SPECIALCHARS);
//  $title = array_flip($title);
//  $title = strtr($msg['SUBJECT'], $title);
//  $title = str_replace('"', "''", $title);
//  $td_str .= " title=\"$title\"";
//  }
//  $td_str .= ">

         }


         // if any disallowed addresses from the
         // restrict senders plugin, display them
         //
         if ($blockedAddresses)
         {
            echo '</td></tr><tr><td align="center"><strong><font color="' . $color[1] . '">' . _("BLOCKED ADDRESSES") . ':</font></strong></td></tr>'."\n"
               . '<tr><td align="center"><table border=0 cellspacing=0 cellpadding=0>'."\n";
            foreach ($blockedAddresses as $badAddr)
               echo '<tr><td><font color="' . $color[1] . '">' . $badAddr . '</font></td></tr>';
            echo '</table>';
         }

         echo '</td></tr></table>'
            . '</td></tr></table>';

         echo '<TABLE WIDTH="100%" CELLSPACING="0" BORDER="0" CELLPADDING="3">' 
            . '<TR>' 
            . '<TD ALIGN="RIGHT" WIDTH="100%"><br>' 
            . '<SMALL>';
         if ($compose_new_win)
         {
            echo '<A href="#" onClick="return self.close()">'
               . _("Close") . '</A></SMALL>';
//            echo '<A HREF="' . $base_uri . 'src/'
//               . "compose.php?newmessage=1\">"
//               . _("Compose") . '</A></SMALL>';
         }
         else
         {
            echo '<A HREF="' . $base_uri . 'src/'
               . "right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox\""
               . ($pp_noPageHeader ? ' target="right">' : '>')
               . _("Message List") . '</A></SMALL>';
         }
         echo '</TD></TR></TABLE></TD></TR></TABLE>';

         echo '</td></tr></table>'
            . '</body></html>';

         $sent_conf_message_sent_status = 'not_sent';
         compatibility_sqsession_register($sent_conf_message_sent_status, 
                                          'sent_conf_message_sent_status');
         exit;

      }


      //-------------------------------------------------------------
      // STYLE 4
      //-------------------------------------------------------------
      else if ($sent_conf_message_style == 4)
      {


         // if the quicksave plugin is installed and active, we
         // need to tell it here that the message was sent successfully
         // (in case the user doesn't return to the message list,
         // where quicksave usually does this for itself)
         //
         global $plugins;
         if (in_array('quicksave', $plugins))
         {
            if (compatibility_check_sm_version(1, 3))
               include_once (SM_PATH . 'plugins/quicksave/functions.php');
            else
               include_once ('../plugins/quicksave/functions.php');

            quicksave_turn_off();
         }


         // code stolen from /src/read_body.php as 
         // well as Wolf Bergenheim's Todo plugin
         //
         global $color, $base_uri, $mailbox, $sort, $startMessage,
                $sent_logo, $sent_logo_width, $sent_logo_height;
         $urlMailbox = urlencode($mailbox);

         echo '<TABLE CELLSPACING="0" WIDTH="100%" BORDER="0" ALIGN="CENTER" CELLPADDING="0">' 
            . '<TR><TD BGCOLOR="' . $color[9] . '" WIDTH="100%">' 
            . '<TABLE WIDTH="100%" CELLSPACING="0" BORDER="0" CELLPADDING="3">' 
            . '<TR>' 
            . '<TD ALIGN="LEFT" WIDTH="100%">' 
            . '<SMALL>';
         if ($compose_new_win)
         {
            echo '<A HREF="' . $base_uri . 'src/'
               . "compose.php?newmessage=1\">"
               . _("Compose") . '</A></SMALL>';
         }
         else
         {
            echo '<A HREF="' . $base_uri . 'src/'
               . "right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox\""
               . ($pp_noPageHeader ? ' target="right">' : '>')
               . _("Message List") . '</A></SMALL>';
         }
         echo '</TD></TR></TABLE>'
            . '</TD></TR><TR><TD>';


         echo '<br><table align=center width="60%" cellpadding=0 bgcolor="'
            . $color[9] . '" cellspacing=3 border=0>'
            . '<tr><td>'
            . '<table width="100%" cellpadding=5 cellspacing=1 border=0 bgcolor="' 
            . $color[4] .'">';
         if (isset($sent_logo) && !empty($sent_logo))
         {
            echo '<tr><td align="center"><img src="' . $sent_logo . '"';
            if (isset($sent_logo_width) && !empty($sent_logo_width))
               echo 'width="' . $sent_logo_width . '"';
            if (isset($sent_logo_height) && !empty($sent_logo_height))
               echo ' height="' . $sent_logo_height . '"';
            echo '></td></tr>'; 
         }
         echo '<tr><td align="center"><strong>' 
            . _("Message Sent To:") . '</strong></td></tr>'
            . '<tr><td align=center><table border=0 cellspacing=0 cellpadding=0>';

          
         // grab actual email addresses
         //
         $addresses = sc_parseEmailAddresses($sent_conf_message_sent_status);


         // put addresses in the form
         //
         $x = 0;
         foreach ($addresses as $addressAndNick)
         {
            $address = trim($addressAndNick[0]);
            if (!empty($address))
            {
               if ($sent_conf_show_headers && strpos($address, 'Cc:') === 0)
               {
                  $address = substr($address, 4);
                  echo '</table></td></tr><tr><td align="center"><strong>'."\n"
                     . _("Cc:") . '</strong></td></tr><tr><td align="center">'
                     . '<table border=0 cellspacing=0 cellpadding=0>'
                     . '<tr><td>' . $address . '</td></tr>'."\n";
               }
               else if ($sent_conf_show_headers && strpos($address, 'Bcc:') === 0)
               {
                  $address = substr($address, 5);
                  echo '</table></td></tr><tr><td align="center"><strong>'."\n"
                     . _("Bcc:") . '</strong></td></tr><tr><td align="center">'
                     . '<table border=0 cellspacing=0 cellpadding=0>'
                     . '<tr><td>' . $address . '</td></tr>'."\n";
               }
               else
                  echo '<tr><td>' . $address . '</td></tr>';
            }
         }

         echo '</table>'
            . '<br>';


         // if is reply, offer deletion of original message here
         //
         if ($sent_conf_enable_orig_msg_options && isset($sent_conf_orig_reply_msg) && !empty($sent_conf_orig_reply_msg))
         {

            list($mailbox, $passed_id, $sort) = explode('|', $sent_conf_orig_reply_msg);


            //echo '<table><tr><td><small>' . _("Original Message") . ':</small></td><td><small>' 
               //. $sent_conf_orig_subject 
               //. '</small></td></tr><tr><td>&nbsp;</td><td><small><a href="../src/delete_message.php?mailbox=' 
               //. $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=1">'
//Note: if we use this Delete link, have to retrofit w/if statement about $pp_noPageHeader (for preview pane plugin)
               //. _("Delete") . '</a><small></td></tr></table>';


            //echo '<small><a href="../src/delete_message.php?mailbox=' . $mailbox . '&message=' 
               //. $passed_id . '&sort=' . $sort . '&startMessage=1">' 
//Note: if we use this Delete link, have to retrofit w/if statement about $pp_noPageHeader (for preview pane plugin)
               //. _("Delete Original Message") . '</a><br>' . _("Subject") . ': ' 
               //. $sent_conf_orig_subject . '</small>';


// this one looked nice (only delete link)
            //echo '<table><tr><td colspan="2" align="center"><small><a href="../src/delete_message.php?mailbox=' 
               //. $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=1">' 
//Note: if we use this Delete link, have to retrofit w/if statement about $pp_noPageHeader (for preview pane plugin)
               //. _("Delete Original Message") . '</a></small></td></tr><tr><td align="right"><small><strong>' . _("From") 
               //. ':</strong></small></td><td><small>' . $sent_conf_orig_sender . '</small></td></tr><tr><td align="right"><small><strong>'
               //. _("Subject") . ':</strong></small></td><td><small>' 
               //. $sent_conf_orig_subject . '</small></td></tr></table>';


            $safe_name = preg_replace("/[^0-9A-Za-z_]/", '_', $mailbox);
            $form_name = "FormMsgs" . $safe_name;

            echo '<form name="' . $form_name . '" method="post" action="../src/move_messages.php">' . "\n"
               . '<input type="hidden" name="mailbox" value="' . htmlspecialchars($mailbox) . '">'
               . '<input type="hidden" name="sort" value="' . $sort . '">'
               . '<input type="hidden" name="msg" value="">'
               . '<input type="hidden" name="msg[0]" value="' . $passed_id . '">'
               . '<input type="hidden" name="location" value="'
               . "right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox"
               . '">'
               . "\n"
               . '<table><tr><td colspan="2" align="center"><small><strong>' . _("Original Message") 
               . '<strong></small><hr></td></tr><tr><td align="right"><small><strong>' . _("From") 
               . ':</strong></small></td><td><small>' . $sent_conf_orig_sender . '</small></td></tr><tr><td align="right"><small><strong>'
               . _("Subject") . ':</strong></small></td><td><small>' 
               . $sent_conf_orig_subject . '</small></td></tr>'
////               . '<tr><td colspan="2" align="center"><hr></td></tr>'
               . '<tr><td colspan="2" align="center"><small><br>';

            if ($pp_noPageHeader)
               echo '<a href="../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '" onClick="parent.right.document.location=\'../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '\'; document.location=\'' . SM_PATH . 'plugins/preview_pane/empty_frame.php\'; return false;">';
// was:  echo '<a href="' . rand() . '" onClick="parent.right.document.location=\'../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '\'; document.location=\'' . SM_PATH . 'plugins/preview_pane/empty_frame.php\'; return false;">';
            else
               echo '<a href="../src/delete_message.php?mailbox=' . $mailbox . '&message=' . $passed_id . '&sort=' . $sort . '&startMessage=' . $startMessage . '">';

            echo 'Delete</a> | <a href="../src/read_body.php?mailbox='
               . $mailbox . '&passed_id=' . $passed_id 
               . '&startMessage=' . $startMessage . '">Return To Message</a></small>'
               . '</td></tr><tr><td align="center" colspan="2"><small>' 
               . _("Move to:") . ' <select name="targetMailbox">' . $sent_conf_mbox_list
               . '</SELECT>&nbsp;'
               . getButton('SUBMIT', 'moveButton', _("Move")) . "\n"
. ""
               . '</small></td></tr>'
               . '</table></form>';

// read links as constructed in mailbox_display.php
// but stuff like startMessage and searchstr don't
//////// note - startMessage has been fixed
// appear to be available after the send button is 
// pressed
//
//<a href="read_body.php?mailbox='.$urlMailbox
//  .  '&amp;passed_id='. $msg["ID"]
//  .  '&amp;startMessage='.$start_msg.$searchstr.'"';
//  $td_str .= ' ' .concat_hook_function('subject_link', array($start_msg, $searchstr));
//  if ($subject != $msg['SUBJECT']) {
//  $title = get_html_translation_table(HTML_SPECIALCHARS);
//  $title = array_flip($title);
//  $title = strtr($msg['SUBJECT'], $title);
//  $title = str_replace('"', "''", $title);
//  $td_str .= " title=\"$title\"";
//  }
//  $td_str .= ">

         }


         // if any disallowed addresses from the
         // restrict senders plugin, display them
         //
         if ($blockedAddresses)
         {
            echo '</td></tr><tr><td align="center"><strong><font color="' . $color[1] . '">' . _("BLOCKED ADDRESSES") . ':</font></strong></td></tr>'."\n"
               . '<tr><td align="center"><table border=0 cellspacing=0 cellpadding=0>'."\n";
            foreach ($blockedAddresses as $badAddr)
               echo '<tr><td><font color="' . $color[1] . '">' . $badAddr . '</font></td></tr>';
            echo '</table>';
         }

         echo '</td></tr></table>'
            . '</td></tr></table>';

         echo '<TABLE WIDTH="100%" CELLSPACING="0" BORDER="0" CELLPADDING="3">' 
            . '<TR>' 
            . '<TD ALIGN="RIGHT" WIDTH="100%"><br>' 
            . '<SMALL>';
         if ($compose_new_win)
         {
            echo '<A HREF="' . $base_uri . 'src/'
               . "compose.php?newmessage=1\">"
               . _("Compose") . '</A></SMALL>';
         }
         else
         {
            echo '<A HREF="' . $base_uri . 'src/'
               . "right_main.php?sort=$sort&amp;startMessage=$startMessage&amp;mailbox=$urlMailbox\""
               . ($pp_noPageHeader ? ' target="right">' : '>')
               . _("Message List") . '</A></SMALL>';
         }
         echo '</TD></TR></TABLE></TD></TR></TABLE>';

         echo '</td></tr></table>'
            . '</body></html>';

         $sent_conf_message_sent_status = 'not_sent';
         compatibility_sqsession_register($sent_conf_message_sent_status, 
                                          'sent_conf_message_sent_status');
         exit;

      }

      echo '</td></tr></table>';

   }

   $sent_conf_message_sent_status = 'not_sent';
   compatibility_sqsession_register($sent_conf_message_sent_status, 
                                    'sent_conf_message_sent_status');


   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain('squirrelmail');

}



/**
  * Parse Out Email Addresses
  *
  * Takes a string of email addresses (possibly with
  * full names, Cc or Bcc headers) and parses them
  * into an array of real email addresses and optional
  * nick names.
  *
  * @param string $emailAddressWithJunk The messy string 
  *                                     of email addresses.
  * @param string $header An optional string that will be 
  *                       prepended to the first email address 
  *                       that is parsed out. (optional)
  *
  */
function sc_parseEmailAddresses($emailAddressWithJunk, $header='')
{

   // need to grab headers and do this one section at
   // a time if headers are there (headers being CC: and BCC:)
   //
   if (strpos($emailAddressWithJunk, 'Cc:') !== FALSE
    || strpos($emailAddressWithJunk, 'Bcc:') !== FALSE)
   {

      if (preg_match('/(.*)Cc:(.*)Bcc:(.*)/', $emailAddressWithJunk, $matches))
      {
         $toAddresses = sc_parseEmailAddresses($matches[1]);
         $ccAddresses = sc_parseEmailAddresses($matches[2], 'Cc: ');
         $bccAddresses = sc_parseEmailAddresses($matches[3], 'Bcc: ');
         return array_merge($toAddresses, $ccAddresses, $bccAddresses);
      }
 
      else if (preg_match('/(.*)Cc:(.*)/', $emailAddressWithJunk, $matches))
      {
         $toAddresses = sc_parseEmailAddresses($matches[1]);
         $ccAddresses = sc_parseEmailAddresses($matches[2], 'Cc: ');
         return array_merge($toAddresses, $ccAddresses);
      }

      else if (preg_match('/(.*)Bcc:(.*)/', $emailAddressWithJunk, $matches))
      {
         $toAddresses = sc_parseEmailAddresses($matches[1]);
         $bccAddresses = sc_parseEmailAddresses($matches[2], 'Bcc: ');
         return array_merge($toAddresses, $bccAddresses);
      }
 
   }


   // grab actual email addresses
   //
   preg_match_all('/\s*([\'"].*?[\'"]){0,1}(.*?)([,;]|$)/', $emailAddressWithJunk, $matches, PREG_SET_ORDER);
   $addresses = array();
   $isFirst = TRUE;
   foreach ($matches as $match)
   {
      $nick = preg_replace('/[\'"]/', '', $match[1]);
      if (!empty($match[2]))
      {

         // first try to get email address from within
         // brackets (avoids malformed addresses with
         // full name in front without quotes)
         //
         preg_match('/(.*)<(.*)>/', $match[2], $moreMatches);


         // also try to grab nickname if didn't find it already
         //
         if (empty($nick) && !empty($moreMatches[1]))
         {
            $nick = trim($moreMatches[1]);
         }


         if (!empty($moreMatches[2]))
         {
            if (!empty($header) && $isFirst)
               $moreMatches[2] = $header . $moreMatches[2];
            $isFirst = FALSE;
            $addresses[] = array($moreMatches[2], $nick);
         }


         // otherwise, it's properly formed, but might
         // still have extraneous characters on front
         // or back (spaces, brackets...)
         //
         else 
         {
            $address = preg_replace(array('/^(\W*)/', '/(\W*)$/'), '', $match[2]);
            if (!empty($address))
            {
               if (!empty($header) && $isFirst)
                  $address = $header . $address;
               $isFirst = FALSE;
               $addresses[] = array($address, $nick);
            }
         }

      }
   }

   return $addresses;

}



// clear out sent indicator
//
function sent_conf_compose_bottom_do() 
{

   global $sent_conf_message_sent_status;
   $sent_conf_message_sent_status = 'not_sent';
   compatibility_sqsession_register($sent_conf_message_sent_status, 
                                    'sent_conf_message_sent_status');

}



?>
