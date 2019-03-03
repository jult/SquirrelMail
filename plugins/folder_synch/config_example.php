<?php

/**
  * SquirrelMail Folder Synchronization Plugin
  *
  * Copyright (c) 2011-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003 Nick Bartos <>
  * Copyright (c) 2002 Jimmy Conner <jimmy@advcs.org>
  * Copyright (c) 2002 Jay Guerette <JayGuerette@pobox.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage folder_synch
  *
  */

global $default_folder_list_resynch, $allow_override_folder_list_resynch,
       $default_message_list_resynch, $allow_override_message_list_resynch;


// By default, should updates in the folder list (manual or automatic
// refresh - "check mail") force the message list to update itself?
//
//    1 = yes, force message list to stay synchronized
//    0 = no, don't update the message list based on folder list updates
//
$default_message_list_resynch = 1;



// Should the setting for $default_message_list_resynch above be fixed,
// or should users have the ability to change it to suit their desires?
//
//    1 = allow users to determine if the message list should be resynchronized
//    0 = don't allow users to override $default_message_list_resynch
//
$allow_override_message_list_resynch = 1;



// By default, should updates in the message list (e.g., message move,
// delete, un-read actions) or viewing of messages force the folder list
// to update itself?
//
//    1 = yes, force folder list to stay synchronized
//    0 = no, don't update the folder list based on message list/view actions
//
$default_folder_list_resynch = 1;



// Should the setting for $default_folder_list_resynch above be fixed,
// or should users have the ability to change it to suit their desires?
//
//    1 = allow users to determine if the folder list should be resynchronized
//    0 = don't allow users to override $default_folder_list_resynch
//
$allow_override_folder_list_resynch = 1;



