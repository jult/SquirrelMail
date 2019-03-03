<?php

/**
 * folders_subscribe.php
 *
 * Subscribe and unsubcribe from folders. 
 * Called from folders.php
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: folders_subscribe.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package squirrelmail
 */

/** This is the folders_subscribe page */
define('PAGE_NAME', 'folders_subscribe');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/display_messages.php');

/* globals */
sqgetGlobalVar('key',       $key,           SQ_COOKIE);
sqgetGlobalVar('username',  $username,      SQ_SESSION);
sqgetGlobalVar('onetimepad',$onetimepad,    SQ_SESSION);
sqgetGlobalVar('method',    $method,        SQ_GET);
sqgetGlobalVar('mailbox',   $mailbox,       SQ_POST);
if (!sqgetGlobalVar('smtoken',$submitted_token, SQ_POST)) {
    $submitted_token = '';
}
/* end globals */

// first, validate security token
sm_validate_security_token($submitted_token, -1, TRUE);

$location = get_location();

if (!isset($mailbox) || !isset($mailbox[0]) || $mailbox[0] == '') {
    header("Location: $location/folders.php");

    exit(0);
}

global $imap_stream_options; // in case not defined in config
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0, $imap_stream_options);

if ($method == 'sub') {
    if($no_list_for_subscribe && $imap_server_type == 'cyrus') {
       /* Cyrus, atleast, does not typically allow subscription to
        * nonexistent folders (this is an optional part of IMAP),
        * lets catch it here and report back cleanly. */
       if(!sqimap_mailbox_exists($imapConnection, $mailbox[0])) {
          header("Location: $location/folders.php?success=subscribe-doesnotexist");
          sqimap_logout($imapConnection);
          exit(0);
       }
    }
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_subscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'subscribe';
} else {
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_unsubscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'unsubscribe';
}

sqimap_logout($imapConnection);
header("Location: $location/folders.php?success=$success");

