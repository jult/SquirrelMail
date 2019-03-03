<?php

/**
  * SquirrelMail Spam Buttons Plugin
  * Copyright (c) 2005-2009 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage spam_buttons
  *
  * This file is originally based on the Bounce plugin by Seth E.
  * Randall, massively re-worked for Spam Buttons 2.0 by Paul
  * Lesniewski.
  *
  */



/* 

Notes:

RFC 2822 - redirect specifications are in section 3.6.6
http://www.ietf.org/rfc/rfc2822.txt

RFC 822 - some notes about message ID are in section 4.6.1
http://www.ietf.org/rfc/rfc0822.txt

RFC 2076 - lists common Resent-X headers in section 3.14
http://www.ietf.org/rfc/rfc2076.txt


Summary:

All headers on the original message must be preserved as-is.
Add at least the first four of these fields, prepending them 
as a group to the beginning of all the preserved/original headers:
  Resent-From: {Bouncer}
  Resent-To: {Recipient of the "bounce"}
  Resent-Date: {Time the message was redirected/bounced}
  Resent-Message-ID: {New message ID for the bounce message}
  Resent-User-Agent: {The user agent used to send the bounce message}
  Resent-Subject: {New subject if you changed it during the bounce}

Note that the resent headers are supposed to preceed all other headers
and that if resent again, another new set of them (keeping them grouped) 
should be added to the top of the headers again

*/



global $imapConnection, $key, $imapServerAddress, $username, $imapPort,
       $mailbox, $passed_id, $passed_ent_id, $useSendmail, $uid_support,
       $mbx_response, $domain, $encode_header_key, $version;
if (check_sm_version(1, 5, 2)) 
{ 
   $key = FALSE; 
   $uid_support = TRUE; 
   $version = SM_VERSION; 
}


$rn = "\r\n";


sqGetGlobalVar('passed_ent_id', $passed_ent_id, SQ_FORM);
if (sqGetGlobalVar('passed_id', $passed_id, SQ_FORM))
   // fix for Dovecot UIDs can be bigger than normal integers
   $passed_id = (preg_match('/^[0-9]+$/', $passed_id) ? $passed_id : '0');


// identities 
//
include_once(SM_PATH . 'plugins/spam_buttons/bounce_identity.php');
$idents = get_identities();


if (!sqgetGlobalVar('bounce_send_to', $bounce_send_to, SQ_FORM))
{
   global $color;
   sq_change_text_domain('spam_buttons');
   $msg = _("No resend destination specified");
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



// initiate IMAP server connection if none already exists
//
if (!is_resource($imapConnection))
{
   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   if (!$imapConnection) 
      die('ERROR: Could not establish IMAP connection');
}
if (empty($mbx_response))
{
   $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);
}



// get original message in parsed form, just so 
// we can initiate SMTP/Sendmail stream below
//
$fetch = '';
$composeMessage = sqimap_get_message($imapConnection, $passed_id, $mailbox);
if (!empty($passed_ent_id))
{
   $fetch = $passed_ent_id . '.';
   $composeMessage = $composeMessage->getEntity($passed_ent_id);
}


// try to guess what identity to send from, 
// based on original message sender/recipients
//
$orig_header = $composeMessage->rfc822_header;

// ripped from src/compose.php from 1.4.11SVN on 2007/09/11
// (also matches 1.5.2 99%)
//
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
$identity = 0;
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



// however, only want to have the recipient for the bounce, remove all the
// other recipients while the SMTP/Sendmail stream is being created (they
// will be added back when actually sending the message headers/data)
//
$composeMessage->rfc822_header->to = array();
$composeMessage->rfc822_header->cc = array();
$composeMessage->rfc822_header->bcc = $composeMessage->rfc822_header->parseAddress($bounce_send_to, true);



// ripped from src/compose.php (function deliverMessage() from 1.4.11SVN 2007/09/11
// (only change was the if and else conditions)
// (also matches 1.5.2 90%)
//
if (!$useSendmail) {
    include_once(SM_PATH . 'class/deliver/Deliver_SMTP.class.php');
    $deliver = new Deliver_SMTP();
    global $smtpServerAddress, $smtpPort, $pop_before_smtp, $smtp_auth_mech;

    $authPop = (isset($pop_before_smtp) && $pop_before_smtp) ? true : false;

    $user = '';
    $pass = '';

    get_smtp_user($user, $pass);

    $stream = $deliver->initStream($composeMessage,$domain,0,
            $smtpServerAddress, $smtpPort, $user, $pass, $authPop);
} else {
    include_once(SM_PATH . 'class/deliver/Deliver_SendMail.class.php');
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
}


if (!$stream)
{
   global $color;
   sq_change_text_domain('spam_buttons');
   $msg = _("Could not establish connection to outgoing mail server");
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



// first, build Resent-X headers and the needed BCC: to the bounce recipient
//
$new_headers = '';
$new_headers .= 'Resent-To: ' . $composeMessage->rfc822_header->getAddr_s('bcc', ",$rn ", true) . $rn;


// build the new from header
//
// ripped from src/compose.php from 1.4.11SVN on 2007/09/11
//
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
$composeMessage->rfc822_header->from = $composeMessage->rfc822_header->parseAddress($from_mail,true);
if ($full_name) {
    $from = $composeMessage->rfc822_header->from[0];
    if (!$from->host) $from->host = $domain;
    $full_name_encoded = encodeHeader($full_name);
    if ($full_name_encoded != $full_name) {
        $from_addr = $full_name_encoded .' <'.$from->mailbox.'@'.$from->host.'>';
    } else {
        $from_addr = '"'.$full_name .'" <'.$from->mailbox.'@'.$from->host.'>';
    }
    $composeMessage->rfc822_header->from = $composeMessage->rfc822_header->parseAddress($from_addr,true);
}
$new_headers .= 'Resent-From: ' . $composeMessage->rfc822_header->getAddr_s('from', ",$rn ", true) . $rn;


// create a RFC 822 date
//
$date = date('D, j M Y H:i:s ', time()) . Deliver::timezone();
$new_headers .= 'Resent-Date: ' . $date . $rn;


// create new message-id
//
// if server var SERVER_NAME is not available, use $domain
//
if (!sqGetGlobalVar('SERVER_NAME', $SERVER_NAME, SQ_SERVER)) 
   $SERVER_NAME = $domain;
if (!sqGetGlobalVar('REMOTE_PORT', $REMOTE_PORT, SQ_SERVER))
   $REMOTE_PORT = 'unk';
if (!sqGetGlobalVar('REMOTE_ADDR', $REMOTE_ADDR, SQ_SERVER))
   $REMOTE_ADDR = 'unk';
$message_id = '<' . $REMOTE_PORT . '.';
if (isset($encode_header_key) && trim($encode_header_key) != '')
    // use encrypted form of remote address
    $message_id .= OneTimePadEncrypt(Deliver::ip2hex($REMOTE_ADDR), base64_encode($encode_header_key));
else
    $message_id .= $REMOTE_ADDR;
$message_id .= '.' . time() . '.squirrel.redirect@' . $SERVER_NAME .'>';
$new_headers .= 'Resent-Message-ID: ' . $message_id . $rn;


// identify SquirrelMail as the user agent
//
$new_headers .= 'Resent-User-Agent: SquirrelMail/' . $version . $rn;


// bcc to new recipient
//
$new_headers .= 'Bcc: ' . $composeMessage->rfc822_header->getAddr_s('bcc', ",$rn ", true) . $rn;



// retrieve original message in raw format
//
$response = '';
$message = '';
if (empty($passed_ent_id))
   $raw_message = sqimap_run_command($imapConnection, "FETCH $passed_id BODY.PEEK[]", true, $response, $message, $uid_support);
else
   $raw_message = sqimap_run_command($imapConnection, "FETCH $passed_id BODY.PEEK[$passed_ent_id]", true, $response, $message, $uid_support);
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



// rebuild the message exactly as it comes from the IMAP server, with
// Resent-X headers pre-pended (except first and last array entries 
// are command wrappers, so skip them)
//
array_shift($raw_message);
array_pop($raw_message);
$raw_message = $new_headers . implode('', $raw_message);
$deliver->preWriteToStream($raw_message);
$deliver->writeToStream($stream, $raw_message);
$success = $deliver->finalizeStream($stream);
if (!$success) 
{
   global $color;
   if (empty($deliver->dlv_msg))
      $deliver->dlv_msg = '';
   if (empty($deliver->dlv_server_msg))
      $deliver->dlv_server_msg = '';
   if (empty($deliver->dlv_ret_nr))
      $deliver->dlv_ret_nr = '';
   sq_change_text_domain('spam_buttons');
   if (check_sm_version(1, 5, 2))
      $msg = _("Could not send report: \n%1\n%2 %3");
   else
      $msg = _("Could not send report: <blockquote>%1<br />%2 %3</blockquote>");
   $msg = str_replace(array('%1', '%2', '%3'), array($deliver->dlv_msg, $deliver->dlv_ret_nr, $deliver->dlv_server_msg), $msg);
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
else 
{
   unset($deliver);
}



