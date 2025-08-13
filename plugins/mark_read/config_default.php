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


global $read_button_text, $unread_button_text,
       $read_button_confirm_text, $unread_button_confirm_text,
       $read_link_text, $unread_link_text,
       $read_link_title_text, $read_button_title_text,
       $unread_link_title_text, $unread_button_title_text,
       $read_link_confirm_text, $unread_link_confirm_text,
       $show_read_button_allow_override, $confirm_read_button,
       $folders_to_show_read_button, $folders_to_not_show_read_button,
       $show_unread_button_allow_override, $confirm_unread_button,
       $folders_to_show_unread_button, $folders_to_not_show_unread_button,
       $show_read_link_allow_override, $confirm_read_link,
       $folders_to_show_read_link, $folders_to_not_show_read_link,
       $show_unread_link_allow_override, $confirm_unread_link,
       $folders_to_show_unread_link, $folders_to_not_show_unread_link,
       $mark_read_button_onclick, $mark_read_link_onclick;



// -------------------------------------------------------------------
//
// FOLDER LIST READ/UNREAD LINK OPTIONS
//


// This is a list of folders that by default will have a
// "read" link next to them in the folder list.  You may
// leave this empty if none should.
//
// The read link will mark all messages in the folder
// as read.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_show_read_link = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_show_read_link = array(
);



// This is a list of folders that by default will NOT have
// a "read" link next to them in the folder list.  All
// other folders WILL have a "read" link next to them if
// this setting is anything except an empty list.
//
// The read link will mark all messages in the folder
// as read.
//
// If any of the folders identified here overlap with
// $folders_to_show_read_link, a read link WILL be
// shown for that folder.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_not_show_read_link = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_not_show_read_link = array(
);



// When the user clicks the read link, should they
// be presented with a confirmation message before
// the action is performed?
//
// 0 = no, 1 = yes
//
$confirm_read_link = 1;



// Allow users to determine what folders the read
// link is to be shown next to?
//
$show_read_link_allow_override = 1;



// This is a list of folders that by default will have an
// "unread" link next to them in the folder list.  You may
// leave this empty if none should.
//
// The unread link will mark all messages in the folder
// as unread.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_show_unread_link = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_show_unread_link = array(
);



// This is a list of folders that by default will NOT have
// an "unread" link next to them in the folder list.  All
// other folders WILL have an "unread" link next to them if
// this setting is anything except an empty list.
//
// The unread link will mark all messages in the folder
// as unread.
//
// If any of the folders identified here overlap with
// $folders_to_show_unread_link, an unread link WILL be
// shown for that folder.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_not_show_unread_link = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_not_show_unread_link = array(
);



// When the user clicks the unread link, should they
// be presented with a confirmation message before
// the action is performed?
//
// 0 = no, 1 = yes
//
$confirm_unread_link = 1;



// Allow users to determine what folders the unread
// link is to be shown next to?
//
$show_unread_link_allow_override = 1;



// When a confirmation message is used upon link click, this
// is the JavaScript that is used to produce it.  You should
// never need to change this unless you know what you are
// doing.
//
// If you do, note that it will be encapsulated in double
// quotes and "###TEXT###" will be replaced with the
// appropriate confirmation message (configured elsewhere).
//
$mark_read_link_onclick = 'if (!confirm(\'###TEXT###\')) return false;';



// -------------------------------------------------------------------
//
// MESSAGE LIST READ/UNREAD BUTTON OPTIONS
//


// This is a list of folders that by default will have a
// "read" button on the mailbox list page.  You may leave
// this empty if none should.
//
// The read button will mark all messages in the folder
// as read.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_show_read_button = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_show_read_button = array(
);



// This is a list of folders that by default will NOT have
// a "read" button on the mailbox list page.  All other
// folders WILL have a "read" button in them if this
// setting is anything except an empty list.
//
// The read button will mark all messages in the folder
// as read.
//
// If any of the folders identified here overlap with
// $folders_to_show_read_button, a read button WILL
// be shown for that folder.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_not_show_read_button = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_not_show_read_button = array(
);



// When the user clicks the read button, should they
// be presented with a confirmation message before
// the action is performed?
//
// 0 = no, 1 = yes
//
$confirm_read_button = 1;



// Allow users to determine what folders the read
// button is to be shown in?
//
$show_read_button_allow_override = 1;



// This is a list of folders that by default will have an
// "unread" button on the mailbox list page.  You may leave
// this empty if none should.
//
// The unread button will mark all messages in the folder
// as unread.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_show_unread_button = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_show_unread_button = array(
);



// This is a list of folders that by default will NOT have
// an "unread" button on the mailbox list page.  All other
// folders WILL have an "unread" button in them if this
// setting is anything except an empty list.
//
// The unread button will mark all messages in the folder
// as unread.
//
// If any of the folders identified here overlap with
// $folders_to_show_unread_button, an unread button WILL
// be shown for that folder.
//
// If set, this value must contain the exact name(s) of
// the specified folder(s) (the format of which may depend
// on your IMAP server).
//
// $folders_to_not_show_unread_button = array(
//    'INBOX',
//    'INBOX.Sent',
//    'INBOX.Mailing Lists',
// );
//
$folders_to_not_show_unread_button = array(
);



// When the user clicks the unread button, should they
// be presented with a confirmation message before
// the action is performed?
//
// 0 = no, 1 = yes
//
$confirm_unread_button = 1;



// Allow users to determine what folders the unread
// button is to be shown in?
//
$show_unread_button_allow_override = 1;



// When a confirmation message is used upon button click,
// this is the JavaScript that is used to produce it.  You
// should never need to change this unless you know what
// you are doing.
//
// If you do, note that it will be encapsulated in double
// quotes and "###TEXT###" will be replaced with the
// appropriate confirmation message (configured elsewhere).
//
$mark_read_button_onclick = 'if (!confirm(\'###TEXT###\')) return false;';



// -------------------------------------------------------------------
//
// LANGUAGE OPTIONS
//


// You may change the text of the links and buttons that
// this plugin generates.  The following strings are
// included in the translation file for this plugin:
//
//    "read"
//    "Read"
//    "read all"
//    "Read All"
//    "read all messages"
//    "Read All Messages"
//    "read all (%d)"
//    "Read All (%d)"
//    "read all (%d) messages"
//    "Read All (%d) Messages"
//    "unread"
//    "Unread"
//    "unread all"
//    "Unread All"
//    "unread all messages"
//    "Unread All Messages"
//    "unread all (%d)"
//    "Unread All (%d)"
//    "unread all (%d) messages"
//    "Unread All (%d) Messages"
//
// Note that if "%d" is found in the string, it will be replaced
// with the number of messages in the current folder.
//
// You can use any other string you like, but if you use something
// other than those strings above, the buttons/links will not be
// correctly translated into other languages unless you add the
// string(s) to your locale files manually.
// 
$read_button_text = "Read All";
$unread_button_text = "Unread All";
$read_link_text = "Read";
$unread_link_text ="Unread";



// You may also change the text of the confirmation (warning)
// messages for the links and buttons that this plugin generates.
// The following strings are included in the translation file
// for this plugin:
//
//    "This will mark ALL messages in this folder as having been read.\\n\\nAre you sure you want to continue?"
//    "This will mark ALL messages in this folder as NOT having been read.\\n\\nAre you sure you want to continue?"
//    "This will mark ALL %d messages in this folder as having been read.\\n\\nAre you sure you want to continue?"
//    "This will mark ALL %d messages in this folder as NOT having been read.\\n\\nAre you sure you want to continue?"
//    "Mark ALL messages in this folder as read - are you sure?"
//    "Mark ALL messages in this folder as unread - are you sure?"
//    "Mark ALL %d messages in this folder as read - are you sure?"
//    "Mark ALL %d messages in this folder as unread - are you sure?"
//
// Note that if "%d" is found in the string, it will be replaced
// with the number of messages in the current folder.
//
// You can use any other string you like, but if you use something
// other than those strings above, the confirmation/warning message
// will not be correctly translated into other languages unless you
// add the string(s) to your locale files manually.
// 
$read_button_confirm_text = "This will mark ALL messages in this folder as having been read.\\n\\nAre you sure you want to continue?";
$unread_button_confirm_text = "This will mark ALL messages in this folder as NOT having been read.\\n\\nAre you sure you want to continue?";
$read_link_confirm_text = "This will mark ALL messages in this folder as having been read.\\n\\nAre you sure you want to continue?";
$unread_link_confirm_text = "This will mark ALL messages in this folder as NOT having been read.\\n\\nAre you sure you want to continue?";



// You may change the text of the title attribute for the links
// and buttons that this plugin generates.  The following strings
// are included in the translation file for this plugin:
//
//    "Mark all messages in this folder as read"
//    "Mark all messages in this folder as having been read"
//    "Mark all %d messages in this folder as read"
//    "Mark all %d messages in this folder as having been read"
//    "Mark all messages in this folder as unread"
//    "Mark all messages in this folder as having been unread"
//    "Mark all %d messages in this folder as unread"
//    "Mark all %d messages in this folder as having been unread"
//
// Note that if "%d" is found in the string, it will be replaced
// with the number of messages in the current folder.
//
// You can use any other string you like, but if you use something
// other than those strings above, the confirmation/warning message
// will not be correctly translated into other languages unless you
// add the string(s) to your locale files manually.
// 
$read_link_title_text = "Mark all messages in this folder as having been read";
$read_button_title_text = "Mark all messages in this folder as having been read";
$unread_link_title_text = "Mark all messages in this folder as having been unread";
$unread_button_title_text = "Mark all messages in this folder as having been unread";



