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



//
// ripped from src/compose.php
//

/* This function is used when not sending or adding attachments */
function newMail ($mailbox='', $passed_id='', $passed_ent_id='', $action='', $session='') {
    global $editor_size, $default_use_priority, $body, $idents,
        $use_signature, $composesession, $data_dir, $username,
        $username, $key, $imapServerAddress, $imapPort, $compose_messages,
        $composeMessage, $body_quote;
    global $languages, $squirrelmail_language, $default_charset;

    /*
     * Set $default_charset to correspond with the user's selection
     * of language interface. $default_charset global is not correct,
     * if message is composed in new window.
     */
    set_my_charset();

    $send_to = $send_to_cc = $send_to_bcc = $subject = $identity = '';
    $mailprio = 3;

    if ($passed_id) {
        $imapConnection = sqimap_login($username, $key, $imapServerAddress,
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

        $encoding = $message->header->encoding;
        $type0 = $message->type0;
        $type1 = $message->type1;
        foreach ($entities as $ent) {
            $unencoded_bodypart = mime_fetch_body($imapConnection, $passed_id, $ent);
            $body_part_entity = $message->getEntity($ent);
            $bodypart = decodeBody($unencoded_bodypart,
                    $body_part_entity->header->encoding);
            if ($type1 == 'html') {
                $bodypart = str_replace("\n", ' ', $bodypart);
                $bodypart = preg_replace(array('/<p>/i','/<br\s*(\/)*>/i'), "\n", $bodypart);
                $bodypart = str_replace(array('&nbsp;','&gt;','&lt;'),array(' ','>','<'),$bodypart);
                $bodypart = strip_tags($bodypart);
            }
            if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
                    function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
                if (mb_detect_encoding($bodypart) != 'ASCII') {
                    $bodypart = $languages[$squirrelmail_language]['XTRA_CODE']('decode', $bodypart);
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

        $identity = '';
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
                // FIXME: ident support?
                $subject = decodeHeader($orig_header->subject,false,false,true);
                /* remember the references and in-reply-to headers in case of an reply */
                $composeMessage->rfc822_header->more_headers['References'] = $orig_header->references;
                $composeMessage->rfc822_header->more_headers['In-Reply-To'] = $orig_header->in_reply_to;
                $body_ary = explode("\n", $body);
                $cnt = count($body_ary) ;
                $body = '';
                for ($i=0; $i < $cnt; $i++) {
                    if (!ereg("^[>\\s]*$", $body_ary[$i])  || !$body_ary[$i]) {
                        sqWordWrap($body_ary[$i], $editor_size, $default_charset );
                        $body .= $body_ary[$i] . "\n";
                    }
                    unset($body_ary[$i]);
                }
                sqUnWordWrap($body);
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
                sqUnWordWrap($body);
                break;
            case ('forward'):
                $send_to = '';
                $subject = decodeHeader($orig_header->subject,false,false,true);
                if ((substr(strtolower($subject), 0, 4) != 'fwd:') &&
                    (substr(strtolower($subject), 0, 5) != '[fwd:') &&
                    (substr(strtolower($subject), 0, 6) != '[ fwd:')) {
                    $subject = '[Fwd: ' . $subject . ']';
                }
                $body = getforwardHeader($orig_header) . $body;
                $composeMessage = getAttachments($message, $composeMessage, $passed_id, $entities, $imapConnection);
                $body = "\n" . $body;
                break;
            case ('forward_as_attachment'):
                $composeMessage = getMessage_RFC822_Attachment($message, $composeMessage, $passed_id, $passed_ent_id, $imapConnection);
                $body = '';
/// CHANGE FOR SPAM_BUTTONS PLUGIN (added next 6 lines)
                $subject = decodeHeader($orig_header->subject,false,false,true);
                if ((substr(strtolower($subject), 0, 4) != 'fwd:') &&
                    (substr(strtolower($subject), 0, 5) != '[fwd:') &&
                    (substr(strtolower($subject), 0, 6) != '[ fwd:')) {
                    $subject = '[Fwd: ' . $subject . ']';
                }
                break;
            case ('reply_all'):
                if(isset($orig_header->mail_followup_to) && $orig_header->mail_followup_to) {
                    $send_to = $orig_header->getAddr_s('mail_followup_to');
                } else {
                    $send_to_cc = replyAllString($orig_header);
                    $send_to_cc = decodeHeader($send_to_cc,false,false,true);
                }
            case ('reply'):
                if (!$send_to) {
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
                $subject = trim($subject);
                if (substr(strtolower($subject), 0, 3) != 're:') {
                    $subject = 'Re: ' . $subject;
                }
                /* this corrects some wrapping/quoting problems on replies */
                $rewrap_body = explode("\n", $body);
                $from = (is_array($orig_header->from) && !empty($orig_header->from)) ? $orig_header->from[0] : $orig_header->from;
                sqUnWordWrap($body);
                $body = '';
                $cnt = count($rewrap_body);
                for ($i=0;$i<$cnt;$i++) {
                    sqWordWrap($rewrap_body[$i], $editor_size, $default_charset);
                    if (preg_match("/^(>+)/", $rewrap_body[$i], $matches)) {
                        $gt = $matches[1];
                        $body .= $body_quote . str_replace("\n", "\n" . $body_quote
                              . "$gt ", rtrim($rewrap_body[$i])) ."\n";
                    } else {
                        $body .= $body_quote . (!empty($body_quote) ? ' ' : '') . str_replace("\n", "\n" . $body_quote . (!empty($body_quote) ? ' ' : ''), rtrim($rewrap_body[$i])) . "\n";
                    }
                    unset($rewrap_body[$i]);
                }
                $body = getReplyCitation($from , $orig_header->date) . $body;
                $composeMessage->reply_rfc822_header = $orig_header;

                break;
            default:
                break;
        }
/// CHANGE FOR SPAM_BUTTONS PLUGIN
///        $compose_messages[$session] = $composeMessage;
///        // Not used any more, but left for posterity
///        //sqsession_register($compose_messages, 'compose_messages');
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

function getforwardHeader($orig_header) {
    global $editor_size;

    $display = array( _("Subject") => strlen(_("Subject")),
            _("From")    => strlen(_("From")),
            _("Date")    => strlen(_("Date")),
            _("To")      => strlen(_("To")),
            _("Cc")      => strlen(_("Cc")) );
    $maxsize = max($display);
    $indent = str_pad('',$maxsize+2);
    foreach($display as $key => $val) {
        $display[$key] = $key .': '. str_pad('', $maxsize - $val);
    }
    $from = decodeHeader($orig_header->getAddr_s('from',"\n$indent"),false,false,true);
    $from = str_replace('&nbsp;',' ',$from);
    $to = decodeHeader($orig_header->getAddr_s('to',"\n$indent"),false,false,true);
    $to = str_replace('&nbsp;',' ',$to);
    $subject = decodeHeader($orig_header->subject,false,false,true);
    $subject = str_replace('&nbsp;',' ',$subject);
    $bodyTop =  str_pad(' '._("Original Message").' ',$editor_size -2,'-',STR_PAD_BOTH) .
        "\n". $display[_("Subject")] . $subject . "\n" .
        $display[_("From")] . $from . "\n" .
        $display[_("Date")] . getLongDateString( $orig_header->date, $orig_header->date_unparsed ). "\n" .
        $display[_("To")] . $to . "\n";
    if ($orig_header->cc != array() && $orig_header->cc !='') {
        $cc = decodeHeader($orig_header->getAddr_s('cc',"\n$indent"),false,false,true);
        $cc = str_replace('&nbsp;',' ',$cc);
        $bodyTop .= $display[_("Cc")] .$cc . "\n";
    }
    $bodyTop .= str_pad('', $editor_size -2 , '-') .
        "\n\n";
    return $bodyTop;
}

function getMessage_RFC822_Attachment($message, $composeMessage, $passed_id,
        $passed_ent_id='', $imapConnection) {
    global $attachment_dir, $username, $data_dir, $uid_support;
    $hashed_attachment_dir = getHashedDir($username, $attachment_dir);
    if (!$passed_ent_id) {
        $body_a = sqimap_run_command($imapConnection,
                'FETCH '.$passed_id.' BODY.PEEK[]',
                TRUE, $response, $readmessage,
                $uid_support);
    } else {
        $body_a = sqimap_run_command($imapConnection,
                'FETCH '.$passed_id.' BODY.PEEK['.$passed_ent_id.']',
                TRUE, $response, $readmessage, $uid_support);
        $message = $message->parent;
    }
    if ($response == 'OK') {
        $subject = encodeHeader($message->rfc822_header->subject);
        array_shift($body_a);
        array_pop($body_a);
        $body = implode('', $body_a) . "\r\n";

        $localfilename = GenerateRandomString(32, 'FILE', 7);
        $full_localfilename = "$hashed_attachment_dir/$localfilename";

        $fp = fopen($full_localfilename, 'w');
        fwrite ($fp, $body);
        fclose($fp);
        $composeMessage->initAttachment('message/rfc822',$subject.'.msg',
                $localfilename);
    }
    return $composeMessage;
}

/**
 * temporary function to make use of the deliver class.
 * In the future the responsible backend should be automaticly loaded
 * and conf.pl should show a list of available backends.
 * The message also should be constructed by the message class.
 */
function deliverMessage($composeMessage, $draft=false) {
    global $send_to, $send_to_cc, $send_to_bcc, $mailprio, $subject, $body,
        $username, $popuser, $usernamedata, $identity, $idents, $data_dir,
        $request_mdn, $request_dr, $default_charset, $color, $useSendmail,
        $domain, $action, $default_move_to_sent, $move_to_sent;
    global $imapServerAddress, $imapPort, $sent_folder, $key;
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

    if (ereg("^([^@%/]+)[@%/](.+)$", $username, $usernamedata)) {
        $popuser = $usernamedata[1];
        $domain  = $usernamedata[2];
        unset($usernamedata);
    } else {
        $popuser = $username;
    }
    $reply_to = '';
    $from_mail = $idents[$identity]['email_address'];
    $full_name = $idents[$identity]['full_name'];
    $reply_to  = $idents[$identity]['reply_to'];
    if (!$from_mail) {
        $from_mail = "$popuser@$domain";
    }
    $rfc822_header->from = $rfc822_header->parseAddress($from_mail,true);
    if ($full_name) {
        $from = $rfc822_header->from[0];
        if (!$from->host) $from->host = $domain;
        $full_name_encoded = encodeHeader($full_name);
        if ($full_name_encoded != $full_name) {
            $from_addr = $full_name_encoded .' <'.$from->mailbox.'@'.$from->host.'>';
        } else {
            $from_addr = '"'.$full_name .'" <'.$from->mailbox.'@'.$from->host.'>';
        }
        $rfc822_header->from = $rfc822_header->parseAddress($from_addr,true);
    }
    if ($reply_to) {
        $rfc822_header->reply_to = $rfc822_header->parseAddress($reply_to,true);
    }
    /* Receipt: On Read */
    if (isset($request_mdn) && $request_mdn) {
        $rfc822_header->dnt = $rfc822_header->parseAddress($from_mail,true);
    }

    /* Receipt: On Delivery */
    if (isset($request_dr) && $request_dr) {
        $rfc822_header->more_headers['Return-Receipt-To'] = $from_mail;
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

    /* Here you can modify the message structure just before we hand
       it over to deliver */
/// CHANGE FOR SPAM_BUTTONS PLUGIN
///    $hookReturn = do_hook('compose_send', $composeMessage);
///    /* Get any changes made by plugins to $composeMessage. */
///    if ( is_object($hookReturn[1]) ) {
///        $composeMessage = $hookReturn[1];
///    }

    if (!$useSendmail && !$draft) {
        require_once(SM_PATH . 'class/deliver/Deliver_SMTP.class.php');
        $deliver = new Deliver_SMTP();
        global $smtpServerAddress, $smtpPort, $pop_before_smtp, $smtp_auth_mech;

        $authPop = (isset($pop_before_smtp) && $pop_before_smtp) ? true : false;

        $user = '';
        $pass = '';

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
        require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
        $imap_stream = sqimap_login($username, $key, $imapServerAddress,
                $imapPort, 0);
        if (sqimap_mailbox_exists ($imap_stream, $draft_folder)) {
            require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
            $imap_deliver = new Deliver_IMAP();
            $length = $imap_deliver->mail($composeMessage);
            sqimap_append ($imap_stream, $draft_folder, $length);
            $imap_deliver->mail($composeMessage, $imap_stream);
            sqimap_append_done ($imap_stream, $draft_folder);
            sqimap_logout($imap_stream);
            unset ($imap_deliver);
            $composeMessage->purgeAttachments();
            return $length;
        } else {
            $msg  = '<br />'.sprintf(_("Error: Draft folder %s does not exist."),
                htmlspecialchars($draft_folder));
            plain_error_message($msg, $color);
            return false;
        }
    }
    $succes = false;
    if ($stream) {
        $length = $deliver->mail($composeMessage, $stream);
        $succes = $deliver->finalizeStream($stream);
    }
    if (!$succes) {
        $msg  = _("Message not sent.") .' '.  _("Server replied:") .
            "\n<blockquote>\n" . $deliver->dlv_msg . '<br />' .
            $deliver->dlv_ret_nr . ' ' .
            $deliver->dlv_server_msg . "</blockquote>\n\n";
        plain_error_message($msg, $color);
    } else {
        unset ($deliver);
        $move_to_sent = getPref($data_dir,$username,'move_to_sent');
        $imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

        /* Move to sent code */
/// CHANGE FOR SPAM_BUTTONS PLUGIN (added next 2 lines)
global $sb_keep_copy_in_sent;
if ($sb_keep_copy_in_sent) {
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
            sqimap_append ($imap_stream, $sent_folder, $length);
            require_once(SM_PATH . 'class/deliver/Deliver_IMAP.class.php');
            $imap_deliver = new Deliver_IMAP();
            $imap_deliver->mail($composeMessage, $imap_stream);
            sqimap_append_done ($imap_stream, $sent_folder);
            unset ($imap_deliver);
        }
/// CHANGE FOR SPAM_BUTTONS PLUGIN (added next line)
}
        global $passed_id, $mailbox, $action;
        $composeMessage->purgeAttachments();
        if ($action == 'reply' || $action == 'reply_all') {
            sqimap_mailbox_select ($imap_stream, $mailbox);
            sqimap_messages_flag ($imap_stream, $passed_id, $passed_id, 'Answered', false);
        }
        sqimap_logout($imap_stream);
    }
    return $succes;
}



if (!function_exists('is_conversion_safe'))
{
/**
 * Function informs if it is safe to convert given charset to the one that is used by user.
 *
 * It is safe to use conversion only if user uses utf-8 encoding and when
 * converted charset is similar to the one that is used by user.
 *
 * @param string $input_charset Charset of text that needs to be converted
 * @return bool is it possible to convert to user's charset
 */
function is_conversion_safe($input_charset) {
    global $languages, $sm_notAlias, $default_charset, $lossy_encoding;

    if (isset($lossy_encoding) && $lossy_encoding )
        return true;

    // convert to lower case
    $input_charset = strtolower($input_charset);

    // Is user's locale Unicode based ?
    if ( $default_charset == "utf-8" ) {
        return true;
    }

    // Charsets that are similar
    switch ($default_charset) {
    case "windows-1251":
        if ( $input_charset == "iso-8859-5" ||
           $input_charset == "koi8-r" ||
           $input_charset == "koi8-u" ) {
        return true;
        } else {
            return false;
        }
    case "windows-1257":
        if ( $input_charset == "iso-8859-13" ||
             $input_charset == "iso-8859-4" ) {
            return true;
        } else {
            return false;
        }
    case "iso-8859-4":
        if ( $input_charset == "iso-8859-13" ||
             $input_charset == "windows-1257" ) {
            return true;
        } else {
            return false;
        }
    case "iso-8859-5":
        if ( $input_charset == "windows-1251" ||
             $input_charset == "koi8-r" ||
             $input_charset == "koi8-u" ) {
            return true;
        } else {
            return false;
        }
    case "iso-8859-13":
        if ( $input_charset == "iso-8859-4" ||
             $input_charset == "windows-1257" ) {
            return true;
        } else {
            return false;
        }
    case "koi8-r":
        if ( $input_charset == "windows-1251" ||
             $input_charset == "iso-8859-5" ||
             $input_charset == "koi8-u" ) {
            return true;
        } else {
            return false;
        }
    case "koi8-u":
        if ( $input_charset == "windows-1251" ||
             $input_charset == "iso-8859-5" ||
             $input_charset == "koi8-r" ) {
            return true;
        } else {
            return false;
        }
    default:
        return false;
    }
}
}



