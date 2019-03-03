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


include_once(SM_PATH . 'functions/identity.php');


//
// ripped from src/compose.php
//

/* This function is used when not sending or adding attachments */
function newMail ($mailbox='', $passed_id='', $passed_ent_id='', $action='', $session='') {
    global $editor_size, $default_use_priority, $body, $idents,
        $use_signature, $data_dir, $username,
        $key, $imapServerAddress, $imapPort, 
        $composeMessage, $body_quote, $request_mdn, $request_dr,
        $mdn_user_support, $languages, $squirrelmail_language,
        $default_charset;

    /*
     * Set $default_charset to correspond with the user's selection
     * of language interface. $default_charset global is not correct,
     * if message is composed in new window.
     */
    set_my_charset();

    $send_to = $send_to_cc = $send_to_bcc = $subject = $identity = '';
    $mailprio = 3;

    if ($passed_id) {
        $imapConnection = sqimap_login($username, false, $imapServerAddress,
                $imapPort, 0);

        sqimap_mailbox_select($imapConnection, $mailbox);
        $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);

        $body = '';
        if ($passed_ent_id) {
            /* redefine the messsage in case of message/rfc822 */
            $message = $message->getEntity($passed_ent_id);
            /* message is an entity which contains the envelope and type0=message
             * and type1=rfc822. The actual entities are childs from
             * $message->entities[0]. That's where the encoding and is located
             */

            $entities = $message->entities[0]->findDisplayEntity
                (array(), $alt_order = array('text/plain'));
            if (!count($entities)) {
                $entities = $message->entities[0]->findDisplayEntity
                    (array(), $alt_order = array('text/plain','text/html'));
            }
            $orig_header = $message->rfc822_header; /* here is the envelope located */
            /* redefine the message for picking up the attachments */
            $message = $message->entities[0];

        } else {
            $entities = $message->findDisplayEntity (array(), $alt_order = array('text/plain'));
            if (!count($entities)) {
                $entities = $message->findDisplayEntity (array(), $alt_order = array('text/plain','text/html'));
            }
            $orig_header = $message->rfc822_header;
        }

        $type0 = $message->type0;
        $type1 = $message->type1;
        foreach ($entities as $ent) {
            $msg = $message->getEntity($ent);
            $type0 = $msg->type0;
            $type1 = $msg->type1;
            $unencoded_bodypart = mime_fetch_body($imapConnection, $passed_id, $ent);
            $body_part_entity = $message->getEntity($ent);
            $bodypart = decodeBody($unencoded_bodypart,
                    $body_part_entity->header->encoding);
            if ($type1 == 'html') {
                $bodypart = str_replace("\n", ' ', $bodypart);
                $bodypart = preg_replace(array('/<\/?p>/i','/<div><\/div>/i','/<br\s*(\/)*>/i','/<\/?div>/i'), "\n", $bodypart);
                $bodypart = str_replace(array('&nbsp;','&gt;','&lt;'),array(' ','>','<'),$bodypart);
                $bodypart = strip_tags($bodypart);
            }
            if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
                    function_exists($languages[$squirrelmail_language]['XTRA_CODE'] . '_decode')) {
                if (mb_detect_encoding($bodypart) != 'ASCII') {
                    $bodypart = call_user_func($languages[$squirrelmail_language]['XTRA_CODE'] . '_decode', $bodypart);
                }
            }

            // charset encoding in compose form stuff
            if (isset($body_part_entity->header->parameters['charset'])) {
                $actual = $body_part_entity->header->parameters['charset'];
            } else {
                $actual = 'us-ascii';
            }

            if ( $actual && is_conversion_safe($actual) && $actual != $default_charset){
                $bodypart = charset_convert($actual,$bodypart,$default_charset,false);
            }
            // end of charset encoding in compose

            $body .= $bodypart;
        }
        if ($default_use_priority) {
            $mailprio = substr($orig_header->priority,0,1);
            if (!$mailprio) {
                $mailprio = 3;
            }
        } else {
            $mailprio = '';
        }

        $from_o = $orig_header->from;
        if (is_array($from_o)) {
            if (isset($from_o[0])) {
                $from_o = $from_o[0];
            }
        }
        if (is_object($from_o)) {
            $orig_from = $from_o->getAddress();
        } else {
            $orig_from = '';
        }

        $identities = array();
        if (count($idents) > 1) {
            foreach($idents as $nr=>$data) {
                $enc_from_name = '"'.$data['full_name'].'" <'. $data['email_address'].'>';
                if(strtolower($enc_from_name) == strtolower($orig_from)) {
                    $identity = $nr;
                    break;
                }
                $identities[] = $enc_from_name;
            }

            $identity_match = $orig_header->findAddress($identities);
            if ($identity_match) {
                $identity = $identity_match;
            }
        }

        switch ($action) {
            case ('draft'):
                $use_signature = FALSE;
                $composeMessage->rfc822_header = $orig_header;
                $send_to = decodeHeader($orig_header->getAddr_s('to'),false,false,true);
                $send_to_cc = decodeHeader($orig_header->getAddr_s('cc'),false,false,true);
                $send_to_bcc = decodeHeader($orig_header->getAddr_s('bcc'),false,false,true);
                $send_from = $orig_header->getAddr_s('from');
                $send_from_parts = new AddressStructure();
                $send_from_parts = $orig_header->parseAddress($send_from);
                $send_from_add = $send_from_parts->mailbox . '@' . $send_from_parts->host;
                $identity = find_identity(array($send_from_add));
                $subject = decodeHeader($orig_header->subject,false,false,true);

                // Remember the receipt settings
                $request_mdn = $mdn_user_support && !empty($orig_header->dnt) ? '1' : '0';
                $request_dr = $mdn_user_support && !empty($orig_header->drnt) ? '1' : '0';

                /* remember the references and in-reply-to headers in case of an reply */
//FIXME: it would be better to fiddle with headers inside of the message object or possibly when delivering the message to its destination (drafts folder?); is this possible?
                $composeMessage->rfc822_header->more_headers['References'] = $orig_header->references;
                $composeMessage->rfc822_header->more_headers['In-Reply-To'] = $orig_header->in_reply_to;
                // rewrap the body to clean up quotations and line lengths
                sqBodyWrap($body, $editor_size);
                $composeMessage = getAttachments($message, $composeMessage, $passed_id, $entities, $imapConnection);
                break;
            case ('edit_as_new'):
                $send_to = decodeHeader($orig_header->getAddr_s('to'),false,false,true);
                $send_to_cc = decodeHeader($orig_header->getAddr_s('cc'),false,false,true);
                $send_to_bcc = decodeHeader($orig_header->getAddr_s('bcc'),false,false,true);
                $subject = decodeHeader($orig_header->subject,false,false,true);
                $mailprio = $orig_header->priority;
                $orig_from = '';
                $composeMessage = getAttachments($message, $composeMessage, $passed_id, $entities, $imapConnection);
                // rewrap the body to clean up quotations and line lengths
                sqBodyWrap($body, $editor_size);
                break;
            case ('forward'):
                $send_to = '';
                $subject = getforwardSubject(decodeHeader($orig_header->subject,false,false,true));
                $body = getforwardHeader($orig_header) . $body;
                // the logic for calling sqUnWordWrap here would be to allow the browser to wrap the lines
                // forwarded message text should be as undisturbed as possible, so commenting out this call
                // sqUnWordWrap($body);
                $composeMessage = getAttachments($message, $composeMessage, $passed_id, $entities, $imapConnection);

                //add a blank line after the forward headers
                $body = "\n" . $body;
                break;
            case ('forward_as_attachment'):
                $subject = getforwardSubject(decodeHeader($orig_header->subject,false,false,true));
                $composeMessage = getMessage_RFC822_Attachment($message, $composeMessage, $passed_id, $passed_ent_id, $imapConnection);
                $body = '';
                break;
            case ('reply_all'):
                if(isset($orig_header->mail_followup_to) && $orig_header->mail_followup_to) {
                    $send_to = $orig_header->getAddr_s('mail_followup_to');
                } else {
                    $send_to_cc = replyAllString($orig_header);
                    $send_to_cc = decodeHeader($send_to_cc,false,false,true);
                }
            case ('reply'):
                // skip this if send_to was already set right above here
                if(!$send_to) {
                    $send_to = $orig_header->reply_to;
                    if (is_array($send_to) && count($send_to)) {
                        $send_to = $orig_header->getAddr_s('reply_to');
                    } else if (is_object($send_to)) { /* unneccesarry, just for failsafe purpose */
                        $send_to = $orig_header->getAddr_s('reply_to');
                    } else {
                        $send_to = $orig_header->getAddr_s('from');
                    }
                }
                $send_to = decodeHeader($send_to,false,false,true);
                $subject = decodeHeader($orig_header->subject,false,false,true);
                $subject = str_replace('"', "'", $subject);
                $subject = trim($subject);
                if (substr(strtolower($subject), 0, 3) != 're:') {
                    $subject = 'Re: ' . $subject;
                }
                /* this corrects some wrapping/quoting problems on replies */
                $rewrap_body = explode("\n", $body);
                $from = (is_array($orig_header->from) && !empty($orig_header->from)) ? $orig_header->from[0] : $orig_header->from;
                $body = '';
                $strip_sigs = getPref($data_dir, $username, 'strip_sigs');
                foreach ($rewrap_body as $line) {
                    if ($strip_sigs && substr($line,0,3) == '-- ') {
                        break;
                    }
                    if (preg_match("/^(>+)/", $line, $matches)) {
                        $gt = $matches[1];
                        $body .= $body_quote . str_replace("\n", "\n$body_quote$gt ", rtrim($line)) ."\n";
                    } else {
                        $body .= $body_quote . (!empty($body_quote) ? ' ' : '') . str_replace("\n", "\n$body_quote" . (!empty($body_quote) ? ' ' : ''), rtrim($line)) . "\n";
                    }
                }

                //rewrap the body to clean up quotations and line lengths
                $body = sqBodyWrap ($body, $editor_size);

                $body = getReplyCitation($from , $orig_header->date) . $body;
                $composeMessage->reply_rfc822_header = $orig_header;

                break;
            default:
                break;
        }
//FIXME: we used to register $compose_messages in the session here, but not any more - so do we still need the session_write_close() and sqimap_logout() here?  We probably need the IMAP logout, but what about the session closure?
/// CHANGE FOR SPAM BUTTONS PLUGIN -- comment out next 2 lines
///        session_write_close();
///        sqimap_logout($imapConnection);
    }
    $ret = array( 'send_to' => $send_to,
            'send_to_cc' => $send_to_cc,
            'send_to_bcc' => $send_to_bcc,
            'subject' => $subject,
            'mailprio' => $mailprio,
            'body' => $body,
            'identity' => $identity );

    return ($ret);
} /* function newMail() */


function getforwardSubject($subject)
{
    if ((substr(strtolower($subject), 0, 4) != 'fwd:') &&
            (substr(strtolower($subject), 0, 5) != '[fwd:') &&
            (substr(strtolower($subject), 0, 6) != '[ fwd:')) {
        $subject = '[Fwd: ' . $subject . ']';
    }
    return $subject;
}


function getMessage_RFC822_Attachment($message, $composeMessage, $passed_id,
        $passed_ent_id='', $imapConnection) {
    if (!$passed_ent_id) {
        $body_a = sqimap_run_command($imapConnection,
                'FETCH '.$passed_id.' RFC822',
                TRUE, $response, $readmessage,
                TRUE);
    } else {
        $body_a = sqimap_run_command($imapConnection,
                'FETCH '.$passed_id.' BODY['.$passed_ent_id.']',
                TRUE, $response, $readmessage, TRUE);
        $message = $message->parent;
    }
    if ($response == 'OK') {
        $subject = encodeHeader($message->rfc822_header->subject);
        array_shift($body_a);
        array_pop($body_a);
        $body = implode('', $body_a) . "\r\n";

        global $username, $attachment_dir;
        $hashed_attachment_dir = getHashedDir($username, $attachment_dir);
        $localfilename = sq_get_attach_tempfile();
        $fp = fopen($hashed_attachment_dir . '/' . $localfilename, 'wb');
        fwrite ($fp, $body);
        fclose($fp);
        $composeMessage->initAttachment('message/rfc822',$subject.'.eml',
                $localfilename);
    }
    return $composeMessage;
}


/**
 * temporary function to make use of the deliver class.
 * In the future the responsible backend should be automaticly loaded
 * and conf.pl should show a list of available backends.
 * The message also should be constructed by the message class.
 *
 * @param object $composeMessage The message being sent.  Please note
 *                               that it is passed by reference and
 *                               will be returned modified, with additional
 *                               headers, such as Message-ID, Date, In-Reply-To,
 *                               References, and so forth.
 *
 * @return boolean FALSE if delivery failed, or some non-FALSE value
 *                 upon success.
 *
 */
function deliverMessage(&$composeMessage, $draft=false) {
    global $send_to, $send_to_cc, $send_to_bcc, $mailprio, $subject, $body,
        $username, $identity, $idents, $data_dir,
        $request_mdn, $request_dr, $default_charset, $useSendmail,
        $domain, $action, $default_move_to_sent, $move_to_sent,
        $imapServerAddress, $imapPort, $sent_folder, $key;
/// CHANGE FOR SPAM_BUTTONS PLUGIN
/* --- do we need to do overrides again here?  should not need to, but
//     someone reported problems with the default not being overriden
//     when using this reporting method ---
   global $spam_report_email_method, $spam_report_smtpServerAddress,
          $spam_report_smtpPort, $spam_report_useSendmail,
          $spam_report_smtp_auth_mech, $spam_report_use_smtp_tls,
          $smtpServerAddress, $smtpPort, $useSendmail, $smtp_auth_mech,
          $use_smtp_tls, $sb_debug;

   spam_buttons_init();


   // take care of overrides for SMTP server
   //
   if (!empty($spam_report_smtpServerAddress))
      $smtpServerAddress = $spam_report_smtpServerAddress;
   if (!empty($spam_report_smtpPort))
      $smtpPort = $spam_report_smtpPort;
   if ($spam_report_useSendmail !== '')
      $useSendmail = $spam_report_useSendmail;
   if (!empty($spam_report_smtp_auth_mech))
      $smtp_auth_mech = $spam_report_smtp_auth_mech;
   if (!empty($spam_report_use_smtp_tls))
      $use_smtp_tls = $spam_report_use_smtp_tls;
--- */

    $rfc822_header = $composeMessage->rfc822_header;

    $abook = addressbook_init(false, true);
    $rfc822_header->to = $rfc822_header->parseAddress($send_to,true, array(), '', $domain, array(&$abook,'lookup'));
    $rfc822_header->cc = $rfc822_header->parseAddress($send_to_cc,true,array(), '',$domain, array(&$abook,'lookup'));
    $rfc822_header->bcc = $rfc822_header->parseAddress($send_to_bcc,true, array(), '',$domain, array(&$abook,'lookup'));
    $rfc822_header->priority = $mailprio;
    $rfc822_header->subject = $subject;

    $special_encoding='';
    if (strtolower($default_charset) == 'iso-2022-jp') {
        if (mb_detect_encoding($body) == 'ASCII') {
            $special_encoding = '8bit';
        } else {
            $body = mb_convert_encoding($body, 'JIS');
            $special_encoding = '7bit';
        }
    }
    $composeMessage->setBody($body);

    $reply_to = '';
    $reply_to  = $idents[$identity]['reply_to'];

    $from_addr = build_from_header($identity);
    $rfc822_header->from = $rfc822_header->parseAddress($from_addr,true);
    if ($reply_to) {
        $rfc822_header->reply_to = $rfc822_header->parseAddress($reply_to,true);
    }
    /* Receipt: On Read */
    if (isset($request_mdn) && $request_mdn) {
        $rfc822_header->dnt = $rfc822_header->parseAddress($from_addr,true);
    } elseif (isset($rfc822_header->dnt)) {
        unset($rfc822_header->dnt);
    }

    /* Receipt: On Delivery */
    if (!empty($request_dr)) {
//FIXME: it would be better to fiddle with headers inside of the message object or possibly when delivering the message to its destination; is this possible?
        $rfc822_header->more_headers['Return-Receipt-To'] = $from->mailbox.'@'.$from->domain;
    } elseif (isset($rfc822_header->more_headers['Return-Receipt-To'])) {
        unset($rfc822_header->more_headers['Return-Receipt-To']);
    }

    /* multipart messages */
    if (count($composeMessage->entities)) {
        $message_body = new Message();
        $message_body->body_part = $composeMessage->body_part;
        $composeMessage->body_part = '';
        $mime_header = new MessageHeader;
        $mime_header->type0 = 'text';
        $mime_header->type1 = 'plain';
        if ($special_encoding) {
            $mime_header->encoding = $special_encoding;
        } else {
            $mime_header->encoding = '8bit';
        }
        if ($default_charset) {
            $mime_header->parameters['charset'] = $default_charset;
        }
        $message_body->mime_header = $mime_header;
        array_unshift($composeMessage->entities, $message_body);
        $content_type = new ContentType('multipart/mixed');
    } else {
        $content_type = new ContentType('text/plain');
        if ($special_encoding) {
            $rfc822_header->encoding = $special_encoding;
        } else {
            $rfc822_header->encoding = '8bit';
        }
        if ($default_charset) {
            $content_type->properties['charset']=$default_charset;
        }
    }

    $rfc822_header->content_type = $content_type;
    $composeMessage->rfc822_header = $rfc822_header;
    if ($action == 'reply' || $action == 'reply_all') {
        global $passed_id, $passed_ent_id;
        $reply_id = $passed_id;
        $reply_ent_id = $passed_ent_id;
    } else {
        $reply_id = '';
        $reply_ent_id = '';
    }

    /* Here you can modify the message structure just before we hand
       it over to deliver; plugin authors note that $composeMessage
       is sent and modified by reference since 1.5.2 */
/// CHANGE FOR SPAM BUTTONS PLUGIN -- comment out next line
///    do_hook('compose_send', $composeMessage);

    if (!$useSendmail && !$draft) {
        require_once(SM_PATH . 'class/deliver/Deliver_SMTP.class.php');
        $deliver = new Deliver_SMTP();
        global $smtpServerAddress, $smtpPort, $pop_before_smtp;

        $authPop = (isset($pop_before_smtp) && $pop_before_smtp) ? true : false;
        get_smtp_user($user, $pass);
        $stream = $deliver->initStream($composeMessage,$domain,0,
                $smtpServerAddress, $smtpPort, $user, $pass, $authPop);
    } elseif (!$draft) {
        require_once(SM_PATH . 'class/deliver/Deliver_SendMail.class.php');
        global $sendmail_path, $sendmail_args;
        // Check for outdated configuration
        if (!isset($sendmail_args)) {
            if ($sendmail_path=='/var/qmail/bin/qmail-inject') {
                $sendmail_args = '';
            } else {
                $sendmail_args = '-i -t';
            }
        }
        $deliver = new Deliver_SendMail(array('sendmail_args'=>$sendmail_args));
        $stream = $deliver->initStream($composeMessage,$sendmail_path);
    } elseif ($draft) {
        global $draft_folder;
        $imap_stream = sqimap_login($username, false, $imapServerAddress,
                $imapPort, 0);
        if (sqimap_mailbox_exists ($imap_stream, $draft_folder)) {
            require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
            $imap_deliver = new Deliver_IMAP();
            $success = $imap_deliver->mail($composeMessage, $imap_stream, $reply_id, $reply_ent_id, $imap_stream, $draft_folder);
            sqimap_logout($imap_stream);
            unset ($imap_deliver);
            $composeMessage->purgeAttachments();
            return $success;
        } else {
//FIXME: htmlspecialchars is applied when msg is assigned to template; is it safe to remove it from the following line?
//SPAM BUTTONS NOTE: htmlspecialchars is not applied to $draft_folder below in anticipation of the core using sq_htmlspecialchars when strings are assinged to the template
            $msg  = "\n" 
                  . sprintf(_("Error: Draft folder %s does not exist."), $draft_folder);
            plain_error_message($msg);
            return false;
        }
    }
    $success = false;
    if ($stream) {
        $deliver->mail($composeMessage, $stream, $reply_id, $reply_ent_id);
        $success = $deliver->finalizeStream($stream);
    }
    if (!$success) {
        // $deliver->dlv_server_msg is not always server's reply
        $msg = _("Message not sent.") . "\n" .
            $deliver->dlv_msg;
        if (!empty($deliver->dlv_server_msg)) {
            // add 'server replied' part only when it is not empty.
            // Delivery error can be generated by delivery class itself
            $msg .= "\n" 
                 . _("Server replied:") . ' ' . $deliver->dlv_ret_nr . ' ' .
//SPAM BUTTONS NOTE: htmlspecialchars is not applied to $deliver->dlv_server_msg below in anticipation of the core using sq_htmlspecialchars when strings are assinged to the template
                nl2br($deliver->dlv_server_msg);
        }
        plain_error_message($msg);
    } else {
        unset ($deliver);
        $imap_stream = sqimap_login($username, false, $imapServerAddress, $imapPort, 0);


        // mark as replied or forwarded if applicable
        //
        global $what, $iAccount, $startMessage, $passed_id, $mailbox;

        if ($action=='reply' || $action=='reply_all' || $action=='forward' || $action=='forward_as_attachment') {
            require(SM_PATH . 'functions/mailbox_display.php');
            $aMailbox = sqm_api_mailbox_select($imap_stream, $iAccount, $mailbox,array('setindex' => $what, 'offset' => $startMessage),array());
            switch($action) {
            case 'reply':
            case 'reply_all':
                // check if we are allowed to set the \\Answered flag
                if (in_array('\\answered',$aMailbox['PERMANENTFLAGS'], true)) {
                    $aUpdatedMsgs = sqimap_toggle_flag($imap_stream, array($passed_id), '\\Answered', true, false);
                    if (isset($aUpdatedMsgs[$passed_id]['FLAGS'])) {
                        /**
                        * Only update the cached headers if the header is
                        * cached.
                        */
                        if (isset($aMailbox['MSG_HEADERS'][$passed_id])) {
                            $aMailbox['MSG_HEADERS'][$passed_id]['FLAGS'] = $aMsg['FLAGS'];
                        }
                    }
                }
                break;
            case 'forward':
            case 'forward_as_attachment':
                // check if we are allowed to set the $Forwarded flag (RFC 4550 paragraph 2.8)
                if (in_array('$forwarded',$aMailbox['PERMANENTFLAGS'], true) ||
                    in_array('\\*',$aMailbox['PERMANENTFLAGS'])) {

                    $aUpdatedMsgs = sqimap_toggle_flag($imap_stream, array($passed_id), '$Forwarded', true, false);
                    if (isset($aUpdatedMsgs[$passed_id]['FLAGS'])) {
                        if (isset($aMailbox['MSG_HEADERS'][$passed_id])) {
                            $aMailbox['MSG_HEADERS'][$passed_id]['FLAGS'] = $aMsg['FLAGS'];
                        }
                    }
                }
                break;
            }

            /**
             * Write mailbox with updated seen flag information back to cache.
             */
            if(isset($aUpdatedMsgs[$passed_id])) {
                $mailbox_cache[$iAccount.'_'.$aMailbox['NAME']] = $aMailbox;
                sqsession_register($mailbox_cache,'mailbox_cache');
            }

        }


        // move to sent folder
        //
/// CHANGE FOR SPAM_BUTTONS PLUGIN (added next 2 lines)
global $sb_keep_copy_in_sent;
if ($sb_keep_copy_in_sent) {
        $move_to_sent = getPref($data_dir,$username,'move_to_sent');
        if (isset($default_move_to_sent) && ($default_move_to_sent != 0)) {
            $svr_allow_sent = true;
        } else {
            $svr_allow_sent = false;
        }

        if (isset($sent_folder) && (($sent_folder != '') || ($sent_folder != 'none'))
                && sqimap_mailbox_exists( $imap_stream, $sent_folder)) {
            $fld_sent = true;
        } else {
            $fld_sent = false;
        }

        if ((isset($move_to_sent) && ($move_to_sent != 0)) || (!isset($move_to_sent))) {
            $lcl_allow_sent = true;
        } else {
            $lcl_allow_sent = false;
        }

        if (($fld_sent && $svr_allow_sent && !$lcl_allow_sent) || ($fld_sent && $lcl_allow_sent)) {
            if ($action == 'reply' || $action == 'reply_all') {
                $save_reply_with_orig=getPref($data_dir,$username,'save_reply_with_orig');
                if ($save_reply_with_orig) {
                    $sent_folder = $mailbox;
                }
            }
            require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
            $imap_deliver = new Deliver_IMAP();
            $imap_deliver->mail($composeMessage, $imap_stream, $reply_id, $reply_ent_id, $imap_stream, $sent_folder);
            unset ($imap_deliver);
        }
/// CHANGE FOR SPAM_BUTTONS PLUGIN (added next line)
}


        // final cleanup
        //
        $composeMessage->purgeAttachments();
        sqimap_logout($imap_stream);

    }
    return $success;
}
