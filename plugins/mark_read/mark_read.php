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



// set up SquirrelMail environment
//
if (file_exists('../../include/init.php'))
   include_once('../../include/init.php');
else if (file_exists('../../include/validate.php'))
{
   define('SM_PATH', '../../');
   include_once(SM_PATH . 'include/validate.php');
}
else
{
   chdir('..');
   define('SM_PATH', '../');
   include_once(SM_PATH . 'src/validate.php');
}


// make sure plugin is activated!
//
global $plugins;
if (!in_array('mark_read', $plugins))
   exit;



include_once (SM_PATH . 'plugins/mark_read/functions.php');
mark_read_init();



global $username, $data_dir;
$location = get_location();



if (sqgetGlobalVar('account', $account_number, SQ_FORM) === false)
   $account_number = 0;



// if no mailbox given, nothing to do
//
sqgetGlobalVar('mailbox', $mailbox, SQ_FORM);
if (!empty($mailbox))
   $mailbox = urldecode($mailbox);
else
{
   session_write_close();
   header('Location: ' . $location . '/../../src/left_main.php');
   exit;
}
   


// should we turn the Seen flag on or off?
//
$enable = NULL;
if (sqGetGlobalVar('mr_act', $mr_action, SQ_FORM) && !empty($mr_action))
{
   if ($mr_action == 'mr_read')
      $enable = TRUE;
   else if ($mr_action == 'mr_unread')
      $enable = FALSE;
}
if (is_null($enable))
{
   session_write_close();
   header('Location: ' . $location . '/../../src/left_main.php');
   exit;
}



// get mailbox cache when using SM 1.5.2+
//
if (check_sm_version(1, 5, 2))
{
   global $mailbox_cache;
   sqgetGlobalVar('mailbox_cache', $mailbox_cache, SQ_SESSION);
   $mbox_cache = TRUE;
}
else
   $mbox_cache = NULL;



$ret = flag_all_messages($mailbox, 'Seen', $enable, $mbox_cache, $account_number);



// save mailbox cache back to session
//
if (check_sm_version(1, 5, 2))
{
   $mailbox_cache[$account_number . '_' . $mbox_cache['NAME']] = $mbox_cache;
   sqsession_register($mailbox_cache, 'mailbox_cache');
}



session_write_close();
header('Location: ' . $location . '/../../src/left_main.php');
exit;



