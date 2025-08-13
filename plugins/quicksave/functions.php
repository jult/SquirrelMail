<?php


/**
  * SquirrelMail Quick Save Plugin
  * Copyright (c) 2001-2002 Ray Black <allah@accessnode.net>
  * Copyright (c) 2003-2010 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage quicksave
  *
  */



/**
  * Present user quicksave preferences on display/compose 
  * options page for SquirrelMail 1.5.x
  *
  */
function quicksave_options_15_do() 
{

   // SquirrelMail 1.4?  bail.
   //
   if (!check_sm_version(1, 5, 2))
      return;

   quicksave_options();

}



/**
  * Present user quicksave preferences on display/compose 
  * options page for SquirrelMail 1.4.x
  *
  */
function quicksave_options_14_do() 
{

   // SquirrelMail 1.5?  bail.
   //
   if (check_sm_version(1, 5, 2))
      return;

   quicksave_options();

}



/**
  * Present user quicksave preferences on display/compose 
  * options page
  *
  */
function quicksave_options() 
{

   global $username, $data_dir, $default_save_frequency, 
          $default_cookie_encryption, $default_save_frequency_units, 
          $user_can_override_save_frequency, 
          $user_can_override_save_frequency_units,
          $user_can_override_encryption;

   include_once(SM_PATH . 'plugins/quicksave/common_functions.php');
   quicksave_init();

   $frequency = $default_save_frequency; 
   $units = $default_save_frequency_units; 
   $encryption = $default_cookie_encryption;

   if ($user_can_override_save_frequency)
   {
      $frequency = getPref($data_dir, $username, 'quicksave_frequency', $frequency);
      if ($user_can_override_save_frequency_units)
         $units = getPref($data_dir, $username, 'quicksave_units', $units);
   }
   if ($user_can_override_encryption)
      $encryption = getPref($data_dir, $username, 'quicksave_encryption', $encryption);

   sq_change_text_domain('quicksave');

   global $optpage_data;

   if ($user_can_override_save_frequency)
   {
      $optpage_data['vals']['quicksave'][] = array(
         'name'          => 'quicksave_frequency',
         'caption'       => _("Message Save Frequency"),
         'trailing_text' => _("(set to zero to turn off)"),
         'type'          => SMOPT_TYPE_INTEGER,
         'initial_value' => $frequency,
         'refresh'       => SMOPT_REFRESH_NONE,
         'size'          => SMOPT_SIZE_TINY,
      );
      if ($user_can_override_save_frequency_units)
         $optpage_data['vals']['quicksave'][] = array(
            'name'          => 'quicksave_units',
            'caption'       => _("Message Save Units"),
            'posvals'       => array(
                                       'seconds' => _("Seconds"),
                                       'miliseconds' => _("Miliseconds"),
                                    ),
            'type'          => SMOPT_TYPE_STRLIST,
            'initial_value' => $units,
            'refresh'       => SMOPT_REFRESH_NONE,
         );
   }

   if ($user_can_override_encryption)
      $optpage_data['vals']['quicksave'][] = array(
         'name'          => 'quicksave_encryption',
         'caption'       => _("Cookie Encryption Level"),
         'posvals'       => array(
                                    'none' => _("None"),
                                    'low' => _("Low"),
                                    'medium' => _("Medium"),
                                    'moderate' => _("Moderate"),
                                 ),
         'type'          => SMOPT_TYPE_STRLIST,
         'initial_value' => $encryption,
         'refresh'       => SMOPT_REFRESH_NONE,
      );

   if (!empty($optpage_data['vals']['quicksave']))
      $optpage_data['grps']['quicksave'] = _("Auto Message Save and Recovery");

   sq_change_text_domain('squirrelmail');

}



/**
  * Set flag indicating message was sent
  *
  */
function quicksave_message_sent_do($args) 
{

   global $javascript_on;
   if (!$javascript_on) return;


   $current_hook_name = get_current_hook_name($args);


   // only use compose_send_after hook when in SM 1.4.6+
   // and only use compose_send hook when in SM 1.4.5-
   //
   if (check_sm_version(1, 4, 6))
   {

      // even 1.4.6+ needs to work with drafts on the
      // compose_send hook
      //
      if ($current_hook_name == 'compose_send') 
      {
         global $draft;
         if ($draft)
            sqsession_register('sent', 'quicksave_message_sent_status');
         return;
      }

      // on compose_send_after hook, we have access to information
      // about whether or not the message was actually sent,
      // which is very helpful!
      //
      if (check_sm_version(1, 5, 2))
         $result = $args[0];
      else
         $result = $args[1];

      if (!$result) return;

   }
   else
   {
      if ($current_hook_name == 'compose_send_after') return;
   }


   // set "message sent" status flag
   //
   sqsession_register('sent', 'quicksave_message_sent_status');

}



/**
  * Turn off quicksave if "message sent" flag is set
  *
  */
function quicksave_clear_do() 
{

   global $javascript_on;
   if (!$javascript_on) return;



   // on compose screen, make sure html_mail has all its
   // compose_bottom code added before quicksave
   //
   global $PHP_SELF;
   if (stristr($PHP_SELF, 'src/compose.php'))
      reposition_plugin_on_hook('quicksave', 'compose_bottom', FALSE, 'html_mail');



//TODO: if SM core has $compose_messages purged, won't need qs_cancelled (see below)
   global $quicksave_message_sent_status, $qs_cancelled, $username;
   sqGetGlobalVar('quicksave_message_sent_status', $quicksave_message_sent_status, SQ_SESSION);
//TODO: if SM core has $compose_messages purged, won't need qs_cancelled (see below)
   sqGetGlobalVar('qs_cancelled', $qs_cancelled, SQ_FORM);
   $qs_username = preg_replace('/[\.@_]/', '', $username);



   // now, deal with the message sent flag
   //
   if (!empty($quicksave_message_sent_status) && $quicksave_message_sent_status == 'sent')
   {

      $quicksave_message_sent_status = 'not_sent';
      sqsession_register($quicksave_message_sent_status, 'quicksave_message_sent_status');


      // SM 1.5.2+ no output has gone to browser yet, so 
      // just use PHP to remove quicksave message cache
      //
      if (check_sm_version(1, 5, 2))
      {
         sqsetcookie('QS' . $qs_username . 'is_active', 0, 0, '', '', false, false);
//TODO: when AJAXified, here is were we can simply wipe message cache from our server-side store
      }


      // SM 1.4.x, manage the cookie in JavaScript 
      //
      else
      {

?>

<script type="text/javascript" language="javascript">
<!--

     // QUICKSAVE: Previous Message Was Sent; Deactivate QuickSave Cookie
     //
     document.cookie = escape("QS<?php echo $qs_username ?>") + "is_active=0; expires=Thu, 01-Jan-70 00:00:01 GMT";

//-->
</script>

<?php

      }

      // we also hack the right value into $_COOKIE which is bad, 
      // but if we do it through (sq)setcookie(), apparently it
      // doesn't always get replaced on the server side and just
      // gets sent in the browser headers
      //
      global $_COOKIE;
      if (!check_php_version(4,1))
      {
         global $HTTP_COOKIE_VARS;
         $_COOKIE = $HTTP_COOKIE_VARS;
      }
      $_COOKIE['QS' . $qs_username . 'is_active'] = 0;

   }


   // and if Cancel button was clicked, clean up compose messages
   //
//TODO: if SM core has $compose_messages purged, won't need qs_cancelled and any of this here (confirmed that SM core has in fact been fixed (1.4.11+/1.5.2+) as such, although for backward compatibility I am leaving this alone - the empty() test makes sure in newer SM versions this code won't do much of anything)
   else if (!empty($qs_cancelled) && $qs_cancelled == '1')
   {
      global $composesession, $compose_messages;
      sqGetGlobalVar('composesession', $composesession, SQ_SESSION);
      sqGetGlobalVar('compose_messages', $compose_messages, SQ_SESSION);
      if (!empty($compose_messages[$composesession])) 
      {
         unset($compose_messages[$composesession]);
         sqsession_register($compose_messages,'compose_messages');
      }
   }

}



/**
  * Add "cancel" button to the compose page
  *
  */
function quicksave_cancel_button_do()
{

   global $javascript_on;
   if (!$javascript_on) return;


   // we need this button to send us back to the right mailbox 
   // or message, thus we need all this...
   //
   global $mailbox, $sort, $action, $startMessage, $passed_id, 
          $compose_new_win, $passed_ent_id, $reply_id, $forward_id;

   $urlMailbox = urlencode($mailbox);


   // if they're composing in a new window...
   //
//FIXME: when just closing popup, there is no way to clean up SM composesession
   if ($compose_new_win == '1') 
      $quick_save_return_url = '::CLOSE::';


   // if they're forwarding an email...
   //
   elseif (strpos($action, 'forward') !== FALSE || $forward_id > 0)
   {
      $quick_save_return_url = 'read_body.php'
                             . '?passed_id=' . (($forward_id > 0) ? $forward_id : $passed_id)
                             . '&startMessage=' . $startMessage
                             . '&mailbox=' . $urlMailbox
                             . (isset($passed_ent_id) ? '&passed_ent_id=' . $passed_ent_id : '')
//TODO: if SM core has $compose_messages purged, won't need qs_cancelled
                             . '&qs_cancelled=1';
   }


   // or if they're replyinging to an email...
   //
   elseif ( strpos($action, 'reply') !== FALSE || $reply_id > 0 )
   {
      $quick_save_return_url = 'read_body.php'
                             . '?passed_id=' . (($reply_id > 0) ? $reply_id : $passed_id)
                             . '&startMessage=' . $startMessage
                             . '&mailbox=' . $urlMailbox
                             . (isset($passed_ent_id) ? '&passed_ent_id=' . $passed_ent_id : '')
//TODO: if SM core has $compose_messages purged, won't need qs_cancelled
                             . '&qs_cancelled=1';
   }


   // or if they're just composing from anywhere else, we return to the list
   //
   else
   {
      $quick_save_return_url = 'right_main.php'
                             . '?startMessage=' . $startMessage
                             . '&mailbox=' . $urlMailbox
//TODO: if SM core has $compose_messages purged, won't need qs_cancelled
                             . '&qs_cancelled=1';
   }


   sq_change_text_domain('quicksave');


   // SM 1.5.2+, just add a button
   //
   if (check_sm_version(1, 5, 2))
   {

      global $oTemplate;
      $nbsp = $oTemplate->fetch('non_breaking_space.tpl');
      $output = addButton(_("Cancel"), 'qscancel',
                          array('onclick' => 'quicksave_cancel_button(\'' 
                                           . $quick_save_return_url . '\'); '
                               )
                         ) 
              . $nbsp;

   sq_change_text_domain('squirrelmail');

      return array('compose_button_row' => $output);

   }


   // SquirrelMail 1.4.x
   //
   else
   {

      echo '<input type="button" name="qscancel" value="' . _("Cancel") 
         . '" onclick="quicksave_cancel_button(\'' . $quick_save_return_url . '\');" />';

   }

   sq_change_text_domain('squirrelmail');

}



/**
  * Add the code that does all the work onto the compose page
  *
  */
function quicksave_compose_functions_do()
{

   global $javascript_on;
   if (!$javascript_on) return;



   global $sigappend, $from_htmladdr_search, $restrict_senders_error_no_to_recipients,
          $restrict_senders_error_too_many_recipients, $compose_new_win, $mail_sent,
          $restrict_senders_error_too_many_emails_today, $banned_possible_spammer,
          $quicksave_message_sent_status, $censored, $session_expired;
   sqGetGlobalVar('sigappend', $sigappend, SQ_FORM);
   sqGetGlobalVar('from_htmladdr_search', $from_htmladdr_search, SQ_FORM);
   sqGetGlobalVar('restrict_senders_error_too_many_recipients', $restrict_senders_error_too_many_recipients, SQ_FORM);
   sqGetGlobalVar('restrict_senders_error_no_to_recipients', $restrict_senders_error_no_to_recipients, SQ_FORM);
   sqgetGlobalVar('restrict_senders_error_too_many_emails_today', $restrict_senders_error_too_many_emails_today, SQ_FORM);
   sqgetGlobalVar('banned_possible_spammer', $banned_possible_spammer, SQ_FORM);
   sqgetGlobalVar('censored', $censored, SQ_FORM);
   sqGetGlobalVar('mail_sent', $mail_sent, SQ_FORM);
   sqGetGlobalVar('quicksave_message_sent_status', $quicksave_message_sent_status, SQ_SESSION);


   sq_change_text_domain('quicksave');


   // don't offer any recovery if user just clicked to add 
   // a signature or upload a file or add addresses, etc
   // or if SquirrelMail's own compose session restore is 
   // doing the job or if our "message has been sent" flag 
   // is on (usually happens when in compose_in_new)
   //
   if ($sigappend != 'Signature'
    && $from_htmladdr_search != 'true'
    && !$session_expired
    && $restrict_senders_error_no_to_recipients != 1
    && $restrict_senders_error_too_many_recipients != 1
    && $restrict_senders_error_too_many_emails_today != 1
    && $banned_possible_spammer != 1
    && $censored != 1
    && empty($_FILES['attachfile'])
// NOTE: prior version of quicksave only did the following check if $compose_in_new is turned on or if $mail_sent is 'yes', but technically, if quicksave is working correctly, those are not useful... if this becomes a problem, they could be added back...
    && (empty($quicksave_message_sent_status) || $quicksave_message_sent_status != 'sent'))
   {
      $offer_recovery_OK = TRUE;
   }

   else
   {
      $offer_recovery_OK = FALSE;
      $quicksave_message_sent_status = 'not_sent';
// the below causes PHP error in 1.5.2 until 1.5.2 is fixed so compose.php 
// uses templates for all output; not sure if this happens in 1.4.x too...
// plugin might work OK without this...?
      if (!headers_sent())
         sqsession_register($quicksave_message_sent_status, 'quicksave_message_sent_status');
   }



   // figure out where (for use in JavaScript code) the message 
   // body is located - when using HTML_Mail plugin, it isn't 
   // in regular text area
   //
   $message_body_location_test = 'if (document.compose.body)';
   $message_body_location_restore_test = 'if (document.compose.body)';
   $message_body_html = 'document.compose.body.value';
   if (is_plugin_enabled('html_mail'))
   {

      include_once(SM_PATH . 'plugins/html_mail/functions.php');
      if (html_area_is_on_and_is_supported_by_users_browser())
      {

         global $editor_style, $allow_change_html_editor_style;
         hm_get_config();
         if ($allow_change_html_editor_style)
            $editor_style = getPref($data_dir, $username, 'html_editor_style', $editor_style);

         list($browser, $browserVersion) = getBrowserType();


         // FCKeditor
         //
         if ($editor_style == 1)
         {

            // IE
            //
            if ($browser == 'Explorer' && $browserVersion >= 5.5)
            {
               $message_body_location_test = 'if (document.frames[0].document.frames[0].document.body)';
               $message_body_location_restore_test = 'if (document.frames[0].document.frames[0].document.body)';
               $message_body_html = 'document.frames[0].document.frames[0].document.body.innerHTML';
            }

            // Gecko
            //
            else if ($browser == 'Gecko' && $browserVersion >= 20030624)
            {
               $message_body_location_test = 'if (document.getElementsByTagName("iframe").item(0).contentDocument.getElementsByTagName("iframe").item(0).contentDocument.body)';
               $message_body_location_restore_test = 'if (0)';
               $message_body_html = 'document.getElementsByTagName("iframe").item(0).contentDocument.getElementsByTagName("iframe").item(0).contentDocument.body.innerHTML';
// supposedly these work for updated fckeditor versions
//               $message_body_location_test = 'if(document.getElementById("body___Frame").contentDocument.getElementById("xEditingArea").getElementsByTagName("iframe")[0].contentDocument.body)';
//               $message_body_html = 'document.getElementById("body___Frame").contentDocument.getElementById("xEditingArea").getElementsByTagName("iframe")[0].contentDocument.body.innerHTML';
            }

         }


         // HTMLArea editor
         //
         else if ($editor_style == 2)
         {

            // IE
            //
            if ($browser == 'Explorer' && $browserVersion >= 5.5)
            {
               $message_body_location_test = 'if (document.frames[0].document.body)';
               $message_body_location_restore_test = 'if (0)';
               $message_body_html = 'document.frames[0].document.body.innerHTML';
            }

            // Gecko
            //
            else if ($browser == 'Gecko' && $browserVersion >= 20030624)
            {
               $message_body_location_test = 'if (document.getElementsByTagName("iframe").item(0).contentDocument.body)';
//FIXME: which is best?  both the next two lines seem to work *sometimes*... shrug
               //$message_body_location_restore_test = 'if (document.getElementsByTagName("iframe").item(0).contentDocument.body)';
               $message_body_location_restore_test = 'if (0)';
               $message_body_html = 'document.getElementsByTagName("iframe").item(0).contentDocument.body.innerHTML';
            }

         }

      }

   }



   global $quicksave_cookie_days, $quicksave_cookie_hours, $quicksave_cookie_minutes,
          $maxCookieLength, $maxCookies, $useMultipleCookies, $maxSingleCookieLength,
          $username, $data_dir, $plugins, $default_cookie_encryption, 
          $user_can_override_encryption;

   $qs_username = preg_replace('/[\.@_]/', '', $username);

   include_once(SM_PATH . 'plugins/quicksave/common_functions.php');
   quicksave_init();

   $encryption = $default_cookie_encryption;
   if ($user_can_override_encryption)
      $encryption = getPref($data_dir, $username, 'quicksave_encryption', $encryption);

   $cookie_time_miliseconds = ($quicksave_cookie_days * 24 * 60 * 60 * 1000)
                            + ($quicksave_cookie_hours * 60 * 60 * 1000)
                            + ($quicksave_cookie_minutes * 60 * 1000);


   // begin building JavaScript output
   //
   $output = "<!-- start QuickSave plugin -->\n\n"
           . "<script type=\"text/javascript\" language=\"javascript\">\n"
           . "<!--\n\n"
           . "var qs_stop = 0;\n"
           . 'var maxSingleCookieLength = ' . $maxSingleCookieLength . ";\n"
           . 'var maxCookieLength = ' . $maxCookieLength . ";\n"
           . 'var maxCookies = ' . $maxCookies . ";\n";




   // continue building output... general use quicksave JavaScript functions
   //
   $output .= <<<EOS

// this should usually be called from the onclick handler 
// for the cancel button; it will either close the window
// (such as when compose_in_new is turned on) or redirect
// the current page to the given uri
//
function quicksave_cancel_button(return_uri)
{

   if (return_uri)
   {
      qs_stop = 1;
      quicksave_clear_storage();
      if (return_uri == "::CLOSE::")
      {
         return window.close();
      }
      else
      {
         document.location = return_uri;
      }
   }

} 


// this function makes sure all quicksave message stores are cleared
//
function quicksave_clear_storage()
{
   quicksave_cookie_shove("is_active=0; expires=Thu, 01-Jan-70 00:00:01 GMT");
   quicksave_cookie_shove("send_to=; expires=Thu, 01-Jan-70 00:00:01 GMT");
   quicksave_cookie_shove("send_to_cc=; expires=Thu, 01-Jan-70 00:00:01 GMT");
   quicksave_cookie_shove("send_to_bcc=; expires=Thu, 01-Jan-70 00:00:01 GMT");
   quicksave_cookie_shove("subject=; expires=Thu, 01-Jan-70 00:00:01 GMT");
   quicksave_cookie_shove("body=; expires=Thu, 01-Jan-70 00:00:01 GMT");
//TODO: When AJAXified, make AJAX call here to ask server to delete message cache store
}


// this function trims all whitespace from 
// the front and end of a string
//
function trim(stringToTrim) 
{

   if (stringToTrim == null) return null;


   // we could probably use the following, but the code below works 
   // works with even older browsers (pre-version-4 generation)
   //
   //return stringToTrim.replace(/^\s+|\s+$/g,"");

 
   while (stringToTrim.charAt(0) == ' ' 
       || stringToTrim.charAt(0) == '\\n' 
       || stringToTrim.charAt(0) == '\\t' 
       || stringToTrim.charAt(0) == '\\f' 
       || stringToTrim.charAt(0) == '\\r')
      stringToTrim = stringToTrim.substring(1, stringToTrim.length);

   while (stringToTrim.charAt(stringToTrim.length - 1) == ' ' 
       || stringToTrim.charAt(stringToTrim.length - 1) == '\\n' 
       || stringToTrim.charAt(stringToTrim.length - 1) == '\\t' 
       || stringToTrim.charAt(stringToTrim.length - 1) == '\\f' 
       || stringToTrim.charAt(stringToTrim.length - 1) == '\\r')
      stringToTrim = stringToTrim.substring(0, stringToTrim.length - 1);

   return stringToTrim;

}


// this function saves the current to/cc/bcc/subject and mesasge body
// in the quicksave message cache
//
function quicksave_save()
{

   // gotta make sure we haven't posted already
   //
   if (qs_stop == 0)
   {

      var expiration = new Date();
      expiration.setTime(expiration.getTime() + ($cookie_time_miliseconds));
      qs_send_to = "";
      qs_send_to_cc = "";
      qs_send_to_bcc = "";
      qs_subject = "";
      qs_body = "";

      // gather form data to be stored
      //
      if (document.compose.send_to)
         qs_send_to = document.compose.send_to.value;
      if (document.compose.send_to_cc)
         qs_send_to_cc = document.compose.send_to_cc.value;
      if (document.compose.send_to_bcc)
         qs_send_to_bcc = document.compose.send_to_bcc.value;
      if (document.compose.subject)
         qs_subject = document.compose.subject.value;
      $message_body_location_test
         qs_body = $message_body_html;
EOS;


   // encrypted or unencrypted cookie storage...
   //
   if ($encryption == 'none')
   {
      $encrypt_function_pre = 'escape';
      $encrypt_function_post = '';
   }
   else
   {
      $encrypt_function_pre = 'quicksave_encrypt(escape';
      $encrypt_function_post = ')';
   }


   // return to JavaScript...  finish the quicksave_save() function 
   //
   $output .= <<<EOS
	 	

      // now actually save message cache data
      //
//TODO: When AJAXified, also add AJAX call here to put data into server side msg cache
      quicksave_cookie_shove("send_to=" + $encrypt_function_pre(qs_send_to)$encrypt_function_post, expiration);
      quicksave_cookie_shove("send_to_cc=" + $encrypt_function_pre(qs_send_to_cc)$encrypt_function_post, expiration);
      quicksave_cookie_shove("send_to_bcc=" + $encrypt_function_pre(qs_send_to_bcc)$encrypt_function_post, expiration);
      quicksave_cookie_shove("subject=" + $encrypt_function_pre(qs_subject)$encrypt_function_post, expiration);
      quicksave_cookie_shove("body=" + $encrypt_function_pre(qs_body)$encrypt_function_post, expiration);
      quicksave_cookie_shove("is_active=1", expiration);

   }

}

EOS;


   // continue building JavaScript functions... cookie restoration function...
   //
   if ($encryption == 'none') 
   {
      $decrypt_function_pre = 'quicksave_cookie_pull';
      $decrypt_function_post = '';
   }
   else
   {
      $decrypt_function_pre = 'unescape(quicksave_decrypt(quicksave_cookie_pull';
      $decrypt_function_post = '))';
   }

   $output .= <<<EOS

   // this function gets a value out of a cookie
   //
   function quicksave_get_from_cookie(name)
   {
//TODO: When AJAXified, need one of these for AJAX storage mechanism
      return $decrypt_function_pre(name)$decrypt_function_post;
   }


   // this function gets a value out of a cookie, always unencrytped
   //
   function quicksave_get_from_cookie_unencrypted(name)
   {
//TODO: When AJAXified, need one of these for AJAX storage mechanism
      return quicksave_cookie_pull(name);
   }


   // this function recovers message cache data from cookie back 
   // into the compose screen
   //
//TODO: When AJAXified, need one of these for AJAX storage mechanism
   function quicksave_cookie_restore()
   {
      var qs_send_to = quicksave_get_from_cookie("send_to");
      var qs_send_to_cc = quicksave_get_from_cookie("send_to_cc");
      var qs_send_to_bcc = quicksave_get_from_cookie("send_to_bcc");
      var qs_subject = quicksave_get_from_cookie("subject");
      var qs_body = quicksave_get_from_cookie("body");

      if ( qs_send_to && qs_send_to.length >= 1 )
         document.compose.send_to.value = qs_send_to;
      if ( qs_send_to_cc && qs_send_to_cc.length >= 1 )
         document.compose.send_to_cc.value = qs_send_to_cc;
      if ( qs_send_to_bcc && qs_send_to_bcc.length >= 1 )
         document.compose.send_to_bcc.value = qs_send_to_bcc;
      if ( qs_subject && qs_subject.length >= 1 )
         document.compose.subject.value = qs_subject;
      if ( qs_body && qs_body.length >= 1 )
      {
         $message_body_location_restore_test
            $message_body_html = qs_body;
         else
            document.compose.body.value = qs_body;
      }

      return true;

   }

EOS;


   // depending on whether or not we are using
   // multiple cookie storage, use different cookie_push 
   // and cookie_pull JavaScript functions
   //
   if (!$useMultipleCookies)
   {

      // single cookie shove and pull functions
      //
      $output .= <<<EOS

   // send a cookie to the browser
   //
   function quicksave_cookie_shove(cookie_value, expiration)
   {

      // put an expiration on if not specified
      //
      if (cookie_value.indexOf("expires") == -1)
      {
         cookie_value = cookie_value + "; expires=" + expiration.toGMTString();
      }

      cookieData = cookie_value.substring(cookie_value.indexOf("=") + 1, cookie_value.indexOf(";"));

// is this truncating the cookie to fit within allowable size?
      //
      if (cookieData.length > maxSingleCookieLength)
      {
         cookieName = cookie_value.substring(0, cookie_value.indexOf("="));
         cookieInfo = cookie_value.substring(cookie_value.indexOf(";") + 1);
         cookie_value = cookieName + "=" + cookieData.substring(0, maxSingleCookieLength) 
                      + ";" + cookieInfo;
      }

      document.cookie = escape("QS$qs_username") + cookie_value;

   }


   // retrieve a cookie from the browser (much of this code apparently
   // comes from javascript.com)
   //
   function quicksave_cookie_pull(var_name)
   {
      var cookie_str = document.cookie;

      var prefix = escape("QS$qs_username") + var_name + "=";
      var begin = cookie_str.indexOf("; " + prefix);
      if (begin == -1)
      {
         begin = cookie_str.indexOf(prefix);
         if (begin != 0)
            return null;
      }
      else
      {
         begin += 2;
      }

      var end = document.cookie.indexOf(";", begin);
      if (end == -1)
         end = cookie_str.length;

      return unescape(cookie_str.substring(begin + prefix.length, end));
   } 

EOS;

   }


   // multiple cookie shove and pull functions
   //
   else
   {

      $output .= <<<EOS

   // send a cookie to the browser
   //
   function quicksave_cookie_shove(cookie_value, expiration)
   {

      // put an expiration on if not specified
      //
      if (cookie_value.indexOf("expires") == -1)
      {
         cookie_value = cookie_value + "; expires=" + expiration.toGMTString();
      }


      // anything that isn't "body" gets written as usual
      //
      cookieName = cookie_value.substring(0, cookie_value.indexOf("="));
      cookieData = cookie_value.substring(cookie_value.indexOf("=") + 1, cookie_value.indexOf(";"));
      if (cookieName != "body")
      {
         if (cookieData.length > maxSingleCookieLength)
         {
            cookieInfo = cookie_value.substring(cookie_value.indexOf(";") + 1);
            cookie_value = cookieName + "=" + cookieData.substring(0, maxSingleCookieLength) 
                         + ";" + cookieInfo;
         }
         document.cookie = escape("QS$qs_username") + cookie_value;
         return;
      }


      // cookies small enough just get written
      //
      if (cookieData.length <= maxCookieLength)
      {
         document.cookie = escape("QS$qs_username") + cookie_value;
         document.cookie = escape("QS$qs_username") 
                         + cookieName + "1=; expires=Thu, 01-Jan-70 00:00:01 GMT";
// too time consuming... locks up browser
//         for (i = 1; i <= maxCookies; i++)
//         {
//            document.cookie = escape("QS$qs_username") + 
//            cookieName + i + "=; expires=Thu, 01-Jan-70 00:00:01 GMT";
//         }
         return;
      }


      // limit size of each cookie
      //
      cookieInfo = cookie_value.substring(cookie_value.indexOf(";") + 1);
      cookieCount = 1;

      // clear nonsuffixed cookie
      //
      document.cookie = escape("QS$qs_username") + cookieName
                      + "=; expires=Thu, 01-Jan-70 00:00:01 GMT";

      while (cookieData.length > maxCookieLength && cookieCount < maxCookies)
      {
         document.cookie = escape("QS$qs_username") + cookieName 
                         + (cookieCount++) + "=" 
                         + cookieData.substring(0, maxCookieLength) 
                         + "; " + cookieInfo;
         cookieData = cookieData.substring(maxCookieLength);
      }

      document.cookie = escape("QS$qs_username") + cookieName 
                      + cookieCount + "=" 
                      + cookieData.substring(0, maxCookieLength) 
                      + "; " + cookieInfo;
      document.cookie = escape("QS$qs_username") 
                      + cookieName + (cookieCount + 1) 
                      + "=; expires=Thu, 01-Jan-70 00:00:01 GMT";
// too time consuming... locks up browser
//      for (i = cookieCount + 1; i <= maxCookies; i++)
//      {
//         document.cookie = escape("QS$qs_username") + 
//         cookieName + i + "=; expires=Thu, 01-Jan-70 00:00:01 GMT";
//      }

   }


   // retrieve a cookie from the browser (much of this code apparently
   // comes from javascript.com)
   //
   function quicksave_multi_cookie_pull(var_name)
   {

      var cookie_str = document.cookie;

      var prefix = escape("QS$qs_username") + var_name + "=";
      var begin = cookie_str.indexOf("; " + prefix);

      if (begin == -1)
      {
         begin = cookie_str.indexOf(prefix);
         if (begin != 0)
            return null;
      }
      else
      {
         begin += 2;
      }

      var end = document.cookie.indexOf(";", begin);
      if (end == -1)
         end = cookie_str.length;

      return unescape(cookie_str.substring(begin + prefix.length, end));

   }


   // retrieve a cookie from the browser by possibly piecing it 
   // together from multiple cookie values
   //
   function quicksave_cookie_pull(var_name)
   {

      var cookie_value = quicksave_multi_cookie_pull(var_name);

      if (cookie_value == null || cookie_value == '')
      {

         var cookieCount = 1;
         cookie_value = "";

         var cookie_crumb = quicksave_multi_cookie_pull(var_name + cookieCount);

         while (cookie_crumb != null && cookie_crumb != '')
         {
            cookie_value += cookie_crumb;
            cookie_crumb = quicksave_multi_cookie_pull(var_name + (++cookieCount));
         }

      }

      if (cookie_value == "")
      return null;

      return cookie_value;

   }

EOS;

   }


   // include JavaScript encryption functions only if needed
   // (different ones depending on encryption level)
   //
   if ($encryption == 'moderate')
   {

      $error_no_encrypt_pwd = _("QuickSave Error - No encryption password given.  Please contact your system administrator.");
      $error_bad_encrypt_pwd = _("QuickSave Error - Algorithm cannot find a suitable hash; bad password.\\nPlease contact your system administrator.");
      $error_encrypt_salt_not_found = _("QuickSave Error - A salt value could not be extracted from the encrypted message\\nbecause its length is too short.  The message cannot be decrypted.\\nPlease contact your system administrator.");
      $error_no_decrypt_pwd = _("QuickSave Error - No decryption password given.  Please contact your system administrator.");

      $output .= <<<EOS

   // pretty good XOR encryption (but in NO way uncrackable) script
   // taken from javascript.com:  
   // http://javascript.internet.com/passwords/xor-encryption4.html
   // Copyright 2001 by Terry Yuen.
   // Email: kaiser40@yahoo.com
   // Last update: July 15, 2001
   //
   // Encrypts a given string
   //
   function quicksave_encrypt(str)
   {
      var pwd = '$username';
      if (pwd == null || pwd.length <= 0) {
         alert('$error_no_encrypt_pwd');
         return null;
      }
      var prand = "";
      for(var i=0; i<pwd.length; i++) {
         prand += pwd.charCodeAt(i).toString();
      }

      var i = 0;
      var sPos = 0;
      var char = '';
      var mult = '';
      var found_non_zero = false;

      var divisor = 5;
 
      while (divisor > 1)
      {
         mult = '';
         sPos = Math.floor(prand.length / divisor);
         found_non_zero = false;
         for (i = 1; i <= divisor; i++)
         {
            char = prand.charAt(sPos > 0 ? sPos * i - 1 : 0);
            if (char != '0' && char != '')
            {
               found_non_zero = true;
            }
            mult += char;
         }

         if (found_non_zero) break;

         divisor = divisor - 1;
      }
      mult = parseInt(mult, 10);

      var incr = Math.ceil(pwd.length / 2);
      var modu = Math.pow(2, 31) - 1;
      if(mult < 2) {
         alert('$error_bad_encrypt_pwd');
         return null;
      }
      var salt = Math.round(Math.random() * 1000000000) % 100000000;
      prand += salt;
      while(prand.length > 10) {
         prand = (parseInt(prand.substring(0, 10)) + parseInt(prand.substring(10, prand.length))).toString();
      }
      prand = (mult * prand + incr) % modu;
      var enc_chr = "";
      var enc_str = "";
      for(var i=0; i<str.length; i++) {
         enc_chr = parseInt(str.charCodeAt(i) ^ Math.floor((prand / modu) * 255));
         if(enc_chr < 16) {
            enc_str += "0" + enc_chr.toString(16);
         } else enc_str += enc_chr.toString(16);
         prand = (mult * prand + incr) % modu;
      }
      salt = salt.toString(16);
      while(salt.length < 8)salt = "0" + salt;
      enc_str += salt;
      return enc_str;
   }



   // pretty good XOR encryption (but in NO way uncrackable) script
   // taken from javascript.com:  
   // http://javascript.internet.com/passwords/xor-encryption4.html
   // Copyright 2001 by Terry Yuen.
   // Email: kaiser40@yahoo.com
   // Last update: July 15, 2001
   //
   // Decrypts a given string
   //
   function quicksave_decrypt(str)
   {
      var pwd = '$username';
      if(str == null || str.length < 5) {
         alert('$error_encrypt_salt_not_found');
         return;
      }
      if(pwd == null || pwd.length <= 0) {
         alert('$error_no_decrypt_pwd');
         return;
      }
      var prand = "";
      for(var i=0; i<pwd.length; i++) {
         prand += pwd.charCodeAt(i).toString();
      }

      var i = 0;
      var sPos = 0;
      var char = '';
      var mult = '';
      var found_non_zero = false;

      var divisor = 5;
 
      while (divisor > 1)
      {
         mult = '';
         sPos = Math.floor(prand.length / divisor);
         found_non_zero = false;
         for (i = 1; i <= divisor; i++)
         {
            char = prand.charAt(sPos > 0 ? sPos * i - 1 : 0);
            if (char != '0' && char != '')
            {
               found_non_zero = true;
            }
            mult += char;
         }

         if (found_non_zero) break;

         divisor = divisor - 1;
      }
      mult = parseInt(mult, 10);

      var incr = Math.round(pwd.length / 2);
      var modu = Math.pow(2, 31) - 1;
      var salt = parseInt(str.substring(str.length - 8, str.length), 16);
      str = str.substring(0, str.length - 8);
      prand += salt;
      while(prand.length > 10) {
         prand = (parseInt(prand.substring(0, 10)) + parseInt(prand.substring(10, prand.length))).toString();
      }
      prand = (mult * prand + incr) % modu;
      var enc_chr = "";
      var enc_str = "";
      for(var i=0; i<str.length; i+=2) {
         enc_chr = parseInt(parseInt(str.substring(i, i+2), 16) ^ Math.floor((prand / modu) * 255));
         enc_str += String.fromCharCode(enc_chr);
         prand = (mult * prand + incr) % modu;
      }
      return enc_str;
   }

EOS;


   }
   else if ($encryption == 'medium')
   { 
  
      $output .= <<<EOS
    
   // basic/simple ascii encryption
   // script taken and slightly modified 
   // from javascript.com:  
   // http://javascript.internet.com/passwords/ascii-encryption.html
   // Original:  David Salsinha (david.salsinha@popsi.pt)
   //  
   // Encrypts a given string
   //    
   function quicksave_encrypt(str)
   {   

      if (str == "" || str == null) return "";

      output = new String;
      Temp = new Array();
      Temp2 = new Array();
      TextSize = str.length;
      for (i = 0; i < TextSize; i++) {
         rnd = Math.round(Math.random() * 122) + 68;
         Temp[i] = str.charCodeAt(i) + rnd;
         Temp2[i] = rnd;
      }
      for (i = 0; i < TextSize; i++) {
         output += String.fromCharCode(Temp[i], Temp2[i]);
      }
      return escape(output);

   }


   // basic/simple ascii encryption
   // script taken and slightly modified
   // from javascript.com:  
   // http://javascript.internet.com/passwords/ascii-encryption.html
   // Original:  David Salsinha (david.salsinha@popsi.pt)
   //
   // Decrypts a given string
   //
   function quicksave_decrypt(str)
   {

      if (str == "" || str == null) return "";

      str = unescape(str);

      output = new String;
      Temp = new Array();
      Temp2 = new Array();
      TextSize = str.length;
      for (i = 0; i < TextSize; i++) {
         Temp[i] = str.charCodeAt(i);
         Temp2[i] = str.charCodeAt(i + 1);
      }
      for (i = 0; i < TextSize; i = i+2) {
         output += String.fromCharCode(Temp[i] - Temp2[i]);
      }
      return output;

   }

EOS;


    }
    else if ($encryption == 'low')
    {

      $output .= <<<EOS

   // basic/simple ascii encryption
   // script taken and slightly modified 
   // from javascript.com:  
   // http://javascript.internet.com/passwords/character-encoder.html
   // Original:  Mike McGrath (mike_mcgrath@lineone.net) 
   // Web Site:  http://website.lineone.net/~mike_mcgrath/ 
   //
   // Encrypts a given string
   //
   function quicksave_encrypt(str)
   {

      if (str == "" || str == null) return "";

      enc_str = "";

      for(i = 0; i < str.length; i++) {
         enc_str += str.charCodeAt(i) - 23;
      }
      return enc_str;

   }


   // basic/simple ascii encryption
   // script taken and slightly modified 
   // from javascript.com:  
   // http://javascript.internet.com/passwords/character-encoder.html
   // Original:  Mike McGrath (mike_mcgrath@lineone.net) 
   // Web Site:  http://website.lineone.net/~mike_mcgrath/ 
   //
   // Decrypts a given string
   //
   function quicksave_decrypt(str)
   {

      if (str == "" || str == null) return "";

      enc_str = "";

      for(i = 0; i < str.length; i += 2) {
         num_in = parseInt(str.substr(i,[2])) + 23;
         num_in = unescape('%' + num_in.toString(16));
         enc_str += num_in;
      }
      return enc_str;

   }

EOS;

   }


   // if recovery is possibly needed, add necessary code to 
   // check and start recovery
   //
   // NOTE that unlike previous versions of this plugin that
   // compared the current body contents with what is in the
   // cookie, we now always offer recovery as long as the user
   // is coming to the compose screen fresh and there is something
   // in one of the cookies
   //
   if ($offer_recovery_OK)
   {

      $none_string = _("<none>");
      $confirm_recovery_string_pre = _("WARNING: The following email was interrupted and was never sent!");
      $to_string = _("To:");
      $subject_string = _("Subject:");
      $confirm_recovery_string_post = _("Do you wish to resume it?  (Press cancel to discard message)");
      $restored_string = _("Email restored!");
      $reminder_string = _("Please remember to press Send when finished typing your message.");

      $output .= <<<EOS

//TODO: When AJAXified, have to get from AJAX/server side here (instead? - yeah, probably should just make it an admin config switch, although it would have to be overridden to be cookie based if sm version < 1.5.2 or um, er, if the current skin does not load the right SM AJAX backend (?))
// is the cookie storage active?
//
if (quicksave_get_from_cookie_unencrypted("is_active") == "1")
{

   // start restoration process
   //
   var sendToCheck = trim(quicksave_get_from_cookie("send_to"));
   var sendToCcCheck = trim(quicksave_get_from_cookie("send_to_cc"));
   var sendToBccCheck = trim(quicksave_get_from_cookie("send_to_bcc"));
   var subjectCheck = trim(quicksave_get_from_cookie("subject"));
   var bodyCheck = trim(quicksave_get_from_cookie("body"));


   // format strings to be shown in alert popup
   //
   var showTo = new String(''+sendToCheck+'');
   var showSub = new String(''+subjectCheck+'');

   if (showTo.length > 40)
      showTo = showTo.substr(0,35) + '...';
   else if (showTo.toString() == 'null' || showTo.toString() == null || showTo.toString() == '')
      showTo = new String('$none_string');

   if (showSub.length > 50)
      showSub = showSub.substr(0,45) + '...';
   else if (showSub.toString() == 'null' || showSub.toString() == null || showSub.toString() == '')
      showSub = new String('$none_string');


   // only offer to restore if there was any data there
   //
   if (!(showTo.toString() == '$none_string' && showSub.toString() == '$none_string'
    && (!bodyCheck || bodyCheck.length <= 0)
    && (!sendToCcCheck || sendToCcCheck.length <= 0)
    && (!sendToBccCheck || sendToBccCheck.length <= 0)))
   {

EOS;

      // at this point, we can short-cut directly to the 
      // recovery if the right flag is given
      //
      if (sqGetGlobalVar('qs_recover', $qs_recover, SQ_FORM) && $qs_recover == 1)
         $output .= "      // recover immediately, because qs_recover was specified in page request\n      //\n      quicksave_cookie_restore();\n      //alert('$restored_string\\n\\n$reminder_string');\n";

      else
      {
         global $show_message_body_on_recover_notice;
         if ($show_message_body_on_recover_notice)
         {
            $output .= <<<EOS
      var showBody = new String(''+bodyCheck+'');
      if (showBody.length > 80)
         showBody = showBody.substr(0,75) + '...';
      if (showBody.length < 1)
         showBody = '';
      else
         showBody = '\\n\\n' + showBody;
EOS;
         }
         else
            $output .= "      var showBody = '';\n";

      //if (confirm('$confirm_recovery_string_pre\\n\\n  $to_string ' + showTo.toString() + '\\n  $subject_string ' + showSub.toString() + '\\n\\n$confirm_recovery_string_post'))

         $output .= <<<EOS
      if (confirm('$confirm_recovery_string_pre\\n\\n  $to_string ' + showTo.toString() + '\\n  $subject_string ' + showSub.toString() + showBody + '\\n\\n$confirm_recovery_string_post'))
      {
         quicksave_cookie_restore();
         //alert('$restored_string\\n\\n$reminder_string');
      }
      else 
      {
         quicksave_clear_storage();
      }
EOS;
      }
      $output .= <<<EOS
   }
   else 
   {
      quicksave_clear_storage();
   }

}

EOS;

   }


   // if recovery is not needed, delete quicksave message cache too
   //
   else
   {
      $output .= "\nquicksave_clear_storage();\n";
   }


   // finally, start up the auto-save system if enabled (when frequency
   // is zero, it is supposed to be disabled)
   //
   global $default_save_frequency, $default_save_frequency_units, 
          $user_can_override_save_frequency, 
          $user_can_override_save_frequency_units;
   $frequency = $default_save_frequency; 
   $units = $default_save_frequency_units; 
   if ($user_can_override_save_frequency)
   {
      $frequency = getPref($data_dir, $username, 'quicksave_frequency', $frequency);
      if ($user_can_override_save_frequency_units)
         $units = getPref($data_dir, $username, 'quicksave_units', $units);
   }

   if ($frequency > 0)
   {
      if ($units == 'seconds') 
         $frequency *= 1000;
      $output .= "\n\n// this should set us on the path to glory...\n//\n"
              . 'setInterval(\'quicksave_save()\', ' . $frequency . ");\n";
   }
   else 
      $output .= "\n\n// QuickSave is turned off... reenable by setting frequency to something more than zero in your user preferences\n";

   $output .= "\n\n"
           . "//-->\n"
           . "</script>\n"
	   . "<!-- end QuickSave plugin -->\n\n";


   // send output to browser
   //
   if (check_sm_version(1, 5, 2))
   {
      // for now, there is no template needed because this
      // is all just javascript without formatting...  also,
      // the compose_bottom hook in 1.5.2+ is such that it
      // currently is just like 1.4.x where output goes out
      // to browser right here (it's not an in-template hook
      // for example)
      //
      echo $output;
   }
   else
      echo $output;


   sq_change_text_domain('squirrelmail');

}



