<?php

/**
  * SquirrelMail Spam Buttons Plugin
  * Copyright (c) 2005-2009 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage spam_buttons
  *
  * See below, starting with "GENERAL OPTIONS" for the beginning 
  * of the configuration section.
  *
  */

global $show_spam_buttons_on_read_body, $show_spam_buttons_on_message_list,
       $show_spam_link_on_read_body, $is_spam_shell_command, $is_spam_resend_destination,
       $is_not_spam_shell_command, $is_not_spam_resend_destination,
       $show_not_spam_button, $spam_button_text, $not_spam_button_text,
       $spam_report_email_method, $is_spam_subject_prefix, $is_not_spam_subject_prefix,
       $show_is_spam_button, $spam_report_smtpServerAddress, $spam_report_smtpPort,
       $spam_report_useSendmail, $spam_report_smtp_auth_mech, $spam_report_use_smtp_tls,
       $sb_debug, $sb_reselect_messages, $sb_reselect_messages_allow_override,
       $sb_delete_after_report, $sb_delete_after_report_allow_override,
       $sb_move_after_report_spam, $sb_move_after_report_spam_allow_override,
       $sb_move_after_report_not_spam, $sb_move_after_report_not_spam_allow_override,
       $sb_report_spam_by_move_to_folder, $sb_report_not_spam_by_move_to_folder,
       $sb_copy_after_report_spam_allow_override, $sb_copy_after_report_spam,
       $sb_copy_after_report_not_spam_allow_override, $sb_copy_after_report_not_spam,
       $sb_report_spam_by_copy_to_folder, $sb_report_not_spam_by_copy_to_folder,
       $sb_suppress_spam_button_folder, $sb_suppress_not_spam_button_folder,
       $sb_suppress_spam_button_folder_allow_override, $is_spam_keep_copy_in_sent,
       $sb_suppress_not_spam_button_folder_allow_override,
       $sb_spam_header_name, $sb_not_spam_header_name, $extra_buttons,
       $sb_spam_header_value, $sb_not_spam_header_value, $is_not_spam_keep_copy_in_sent,
       $sb_show_spam_button_folder, $sb_show_spam_button_folder_allow_override,
       $sb_show_not_spam_button_folder, $sb_show_not_spam_button_folder_allow_override,
       $reported_spam_text, $reported_not_spam_text,
       $sb_move_to_other_message_after_report, $sb_auto_create_destination_folder,
       $sb_move_to_other_message_after_report_allow_override,
       $sb_report_spam_by_custom_function, $sb_report_not_spam_by_custom_function;



// -------------------------------------------------------------------
//
// GENERAL OPTIONS
//


// You may change the button text, but if you do, there is a good
// chance that those buttons will ONLY be displayed in one language
// (unless you add your own translations to your locale files)
// (however, the text "Report Spam" will be translated if you use
// that)
//
// $spam_button_text = 'Report Spam';
$spam_button_text = 'Spam';
$not_spam_button_text = 'Not Spam';



// You may change the report confirmation text, but if you do, there 
// is a good chance that those buttons will ONLY be displayed in one 
// language (unless you add your own translations to your locale files)
//
$reported_spam_text = 'Successfully reported as spam';
$reported_not_spam_text = 'Successfully reported as non-spam';



// You can turn either of the buttons on/off as needed
//
// 0 = don't display button, 1 = display button
//
$show_not_spam_button = 1;
$show_is_spam_button = 1;



// Show spam buttons on message list page?
//
// 0 = no, 1 = yes
//
$show_spam_buttons_on_message_list = 1;



// Show spam link when reading a message?
//
// 0 = no, 1 = yes
//
$show_spam_link_on_read_body = 1;



// Show spam buttons when reading a message?
//
// NOTE: This is only functional as of 
//       SquirrelMail 1.5.0
//
// 0 = no, 1 = yes
//
$show_spam_buttons_on_read_body = 1;



// When viewing an individual message, this plugin can determine if
// it was tagged as spam or ham by your anti-spam scanner if a certain
// header ($sb_spam_header_name) is added to the message.  By comparing 
// the value of that header to what is configured here 
// ($sb_spam_header_value), this plugin can display only the HAM (not 
// spam) button on messages marked as spam, or the SPAM button on messages 
// marked as ham (not spam), or both buttons otherwise.
//
// When in use, this functionality will override 
// $sb_suppress_spam_button_folder, $sb_suppress_not_spam_button_folder
// $sb_show_spam_button_folder and $sb_show_not_spam_button_folder
// (buttons/links displayed in the read-message screen will be based on
// the message headers and NOT what folder the message is in).
//
// Note that $sb_spam_header_name and $sb_not_spam_header_name are usually 
// the same thing in most spam scanner systems.
//
// The values for $sb_spam_header_value and $sb_not_spam_header_value
// are regular expressions that will be used to compare the actual 
// message header values.
//
// $sb_spam_header_name = 'X-Spam-Status';
// $sb_spam_header_value = '/^Yes/i';
// $sb_not_spam_header_name = 'X-Spam-Status';
// $sb_not_spam_header_value = '/^No/i';
//
$sb_spam_header_name = '';
$sb_spam_header_value = '';
$sb_not_spam_header_name = '';
$sb_not_spam_header_value = '';



// When any one of the report-by-move/copy or move/copy-after-report
// options is set to a folder that does not exist for any one user,
// should it be created automatically?  If not, an error will occur
// when the user reports spam/ham.
//
// 0 = don't create folders automatically
// 1 = create folders if they do not exist upon spam/ham report
//
$sb_auto_create_destination_folder = 0;



// Debugging: Turning this on will dump out the command text, the
//            message body and the results of the spam report if
//            done using a shell command.
//            If reported via email (attachment method only), you 
//            currently only get the destination address and a 
//            parsed version of the message body being reported.
//
// Note that you will get more verbose output from a sa-learn
// command if you add the -D flag to the commands below (make sure
// to remove it when you are done debugging!).
//
// 0 = off, 1 = on
//
$sb_debug = 0;



// -------------------------------------------------------------------
//
// BUTTON/LINK SUPPRESSION OPTIONS
//


// When the user is in this folder (or folders), suppress
// all SPAM button/links
//
// Set to an empty list to show the spam button in all folders, 
// or the exact name of the specified spam folder(s) (the format
// of which may depend on your IMAP server).
//
// In the case of collision with $sb_show_spam_button_folder 
// (see below), this setting will lose.
//
// $sb_suppress_spam_button_folder = array('INBOX.Spam');
// $sb_suppress_spam_button_folder = array('INBOX.Spam', 'INBOX.Junk');
//
$sb_suppress_spam_button_folder = array();



// Should users be able to choose what the value
// of $sb_suppress_spam_button_folder is?  Set to 1
// if so, or set to 0 (zero) if the administrator's
// setting above goes for all users.
//
$sb_suppress_spam_button_folder_allow_override = 1;



// When the user is in this folder (or folders), suppress 
// all HAM (not spam) button/links
//
// Set to an empty list to show the ham button in all folders,
// or the exact name of the specified ham folder(s) (the format
// of which may depend on your IMAP server).
//
// In the case of collision with $sb_show_not_spam_button_folder 
// (see below), this setting will lose.
//
// $sb_suppress_not_spam_button_folder = array('INBOX.Ham');
// $sb_suppress_not_spam_button_folder = array('INBOX.Ham', 'INBOX.Personal');
//
$sb_suppress_not_spam_button_folder = array();



// Should users be able to choose what the value
// of $sb_suppress_not_spam_button_folder is?  Set to 1 
// if so, or set to 0 (zero) if the administrator's 
// setting above goes for all users.
// 
$sb_suppress_not_spam_button_folder_allow_override = 0;



// -------------------------------------------------------------------
//
// BUTTON/LINK INCLUSION OPTIONS
//


// Only when the user is in this folder (or folders), 
// show SPAM button/links
//
// When this option is set to anything other than an
// empty list, spam buttons/links won't be shown in
// any folder other than the one(s) specified here.
//
// In the case of collision with 
// $sb_suppress_spam_button_folder, this setting will win.
//
// If set, this value must contain the exact name(s) 
// of the specified folder(s) (the format of which 
// may depend on your IMAP server).
//
// $sb_show_spam_button_folder = array('INBOX.Ham');
// $sb_show_spam_button_folder = array('INBOX.Ham', 'INBOX.Personal');
//
$sb_show_spam_button_folder = array();



// Should users be able to choose what the value
// of $sb_show_spam_button_folder is?  Set to 1
// if so, or set to 0 (zero) if the administrator's
// setting above goes for all users.
//
$sb_show_spam_button_folder_allow_override = 0;



// Only when the user is in this folder (or folders), 
// show HAM (not spam) button/links
//
// When this option is set to anything other than an
// empty list, ham buttons/links won't be shown in
// any folder other than the one(s) specified here.
//
// In the case of collision with 
// $sb_suppress_not_spam_button_folder, this setting will win.
//
// If set, this value must contain the exact name(s) 
// of the specified folder(s) (the format of which 
// may depend on your IMAP server).
//
// $sb_show_not_spam_button_folder = array('INBOX.Spam');
// $sb_show_not_spam_button_folder = array('INBOX.Spam', 'INBOX.Junk');
//
$sb_show_not_spam_button_folder = array();



// Should users be able to choose what the value
// of $sb_show_not_spam_button_folder is?  Set to 1
// if so, or set to 0 (zero) if the administrator's
// setting above goes for all users.
//
$sb_show_not_spam_button_folder_allow_override = 1;



// -------------------------------------------------------------------
//
// POST-REPORT OPTIONS
//


// When reporting spam (NOT ham), should messages
// be deleted afterward?
//
// NOTE that this will be ignored if $sb_move_after_report_spam
// (or the user-overridden value for such) is turned on.
//
// NOTE that when using the move-to-folder type 
// reporting method, this feature will be disabled.
//
// 0 = no, 1 = yes
//
$sb_delete_after_report = 0;



// Should users be able to choose what the value
// of $sb_delete_after_report is?  Set to 1 if so,
// or set to 0 (zero) if the administrator's 
// setting above goes for all users.
// 
$sb_delete_after_report_allow_override = 1;



// When reporting spam, should messages be moved
// to another folder afterward?
//
// Set to an empty string to disable, or the exact
// name of the target folder (the format of which
// may depend on your IMAP server).
//
// NOTE that this will take precedence over 
// $sb_delete_after_report.
//
// NOTE that when using the move-to-folder type 
// reporting method, this feature will be disabled.
//
// $sb_move_after_report_spam = 'INBOX.Spam';
//
$sb_move_after_report_spam = '';



// Should users be able to choose what the value
// of $sb_move_after_report_spam is?  Set to 1 if so,
// or set to 0 (zero) if the administrator's 
// setting above goes for all users.
// 
$sb_move_after_report_spam_allow_override = 1;



// Should $sb_move_after_report_spam COPY reported
// messages instead of moving them?
//
// 0 = no, just move messages, 1 = yes, copy messages
//
// This setting has no effect when 
// $sb_move_after_report_spam is not enabled.
//
$sb_copy_after_report_spam = 0;



// Should users be able to choose what the value
// of $sb_copy_after_report_spam is?  Set to 1 if so,
// or set to 0 (zero) if the administrator's 
// setting above goes for all users.
// 
$sb_copy_after_report_spam_allow_override = 1;



// When reporting non-spam, should messages be moved
// to another folder afterward?
//
// Set to an empty string to disable, or the exact
// name of the target folder (the format of which
// may depend on your IMAP server).
//
// NOTE that when using the move-to-folder type 
// reporting method, this feature will be disabled.
//
// $sb_move_after_report_not_spam = 'INBOX.Ham';
//
$sb_move_after_report_not_spam = '';



// Should users be able to choose what the value
// of $sb_move_after_report_not_spam is?  Set to 1 if so,
// or set to 0 (zero) if the administrator's
// setting above goes for all users.
//
$sb_move_after_report_not_spam_allow_override = 1;



// Should $sb_move_after_report_not_spam COPY reported
// messages instead of moving them?
//
// 0 = no, just move messages, 1 = yes, copy messages
//
// This setting has no effect when 
// $sb_move_after_report_not_spam is not enabled.
//
$sb_copy_after_report_not_spam = 0;



// Should users be able to choose what the value
// of $sb_copy_after_report_not_spam is?  Set to 1 if so,
// or set to 0 (zero) if the administrator's 
// setting above goes for all users.
// 
$sb_copy_after_report_not_spam_allow_override = 1;



// When selecting messages to report from the
// message list page, the messages will be re-
// selected after they are reported (as of 
// SquirrelMail 1.4.11 and 1.5.2).  You can turn
// this functionality off by setting this to zero.
//
$sb_reselect_messages = 1;



// Should users be able to choose what the value
// of $sb_reselect_messages is?  Set to 1 if so,
// or set to 0 (zero) if the administrator's 
// setting above goes for all users.
// 
$sb_reselect_messages_allow_override = 1;



// When either report-by-move or delete-after-report is
// enabled, should the user be directed to the next or
// previous message after the report, or just back to
// the message list?
//
// Acceptable values are "next", "previous" and "" (empty),
// which indicates that the user will be returned to the
// message list.
//
// $sb_move_to_other_message_after_report = 'next';
// $sb_move_to_other_message_after_report = 'previous';
// $sb_move_to_other_message_after_report = '';
//
$sb_move_to_other_message_after_report = '';



// Should users be able to choose what the value
// of $sb_move_to_other_message_after_report is?  
// Set to 1 if so, or set to 0 (zero) if the 
// administrator's setting above goes for all users.
//
$sb_move_to_other_message_after_report_allow_override = 1;



// -------------------------------------------------------------------
//
// REPORT BY MOVE-TO-FOLDER OPTIONS
//
// Be sure if you use this that you have NOT turned on
// reporting by email, by shell command or by custom function.
//


// This reporting method does not acutually make any reports itself.
// Instead, the message is moved to the specified folder and some
// other process is assumed to examine the messages in that folder
// on a regular basis.  
//
// If you enable this reporting method, the report-and-delete and
// report-and-move functions above will be automatically disabled.
//
// You will need to set these values to the exact name of the target
// folders where messages will be moved.  The format of the folder
// names may depend on your IMAP server.
//
// $sb_report_spam_by_move_to_folder = 'INBOX.Spam';
// $sb_report_not_spam_by_move_to_folder = 'INBOX.Ham';
//
$sb_report_spam_by_move_to_folder = '';
$sb_report_not_spam_by_move_to_folder = '';



// If messages should be copied instead of moved, set either of these
// settings to 1.  Leave as 0 (zero) if messages should be moved.
//
// Note that these settings have no effect if their counterparts
// above are not enabled. 
//
$sb_report_spam_by_copy_to_folder = 0;
$sb_report_not_spam_by_copy_to_folder = 0;



// -------------------------------------------------------------------
//
// REPORT-BY-SHELL COMMAND OPTIONS
//
// Be sure if you use this that you have NOT turned on 
// reporting by email, by move-to-folder or by custom function.
//


// If you use a command-line utility for reporting spam/non-spam, 
// this is where you set it up.  This plugin will append " < message"
// to the end of such a command (without the quotes), where "message" 
// will be a file containing the full message as it was originally 
// received.
//
// If you need to include any information about the user for which
// the report is being made, you can use these constants in your command:
//
//    ###EMAIL_PREF###               Will be replaced with the user's 
//                                   main email address preference
//                                   setting (under Options -> Personal
//                                   Information) (email addresses under
//                                   Multiple Identiies not supported)
//
//    ###EMAIL_ADDRESS###            Will be replaced with the user's 
//                                   full email address (based on the
//                                   login username and user's domain)
//
//    ###USERNAME###                 Will be replaced with the username
//                                   portion of the user's email address
//
//    ###DOMAIN###                   Will be replaced with the domain
//                                   portion of the user's email address
//
//
//
// Note that you are not necessarily limited to just calling a spam
// reporting application; you can also do clever things such as dump
// the message to a file that can be used to do cron-based reporting
// at a later time.  For example:
//
// $is_spam_shell_command     = 'cat >> /path/to/spam-###EMAIL_ADDRESS###';
// $is_not_spam_shell_command = 'cat >> /path/to/ham-###EMAIL_ADDRESS###';
//
//
//
// Sample for Bogofilter:
//
// $is_spam_shell_command = 'HOME=/home/###USERNAME### sudo -u ###USERNAME### /usr/bin/bogofilter -s';
// $is_not_spam_shell_command = 'HOME=/home/###USERNAME### sudo -u ###USERNAME### /usr/bin/bogofilter -n';
//
//
//
// Sample for CRM114:
//
// $is_spam_shell_command = '/usr/bin/crm -u /home/crm114 mailfilter.crm --learnspam';
// $is_not_spam_shell_command = '/usr/bin/crm -u /home/crm114 mailfilter.crm --learnnonspam';
//
//
//
// Sample for DSPAM:
//
// $is_spam_shell_command = '/usr/local/bin/dspam --user ###EMAIL_ADDRESS### --mode=teft --class=spam --source=error --client';
// $is_not_spam_shell_command = '/usr/local/bin/dspam --user ###EMAIL_ADDRESS### --mode=teft --class=innocent --source=error --client';
//
//
//
// Sample for SpamAssassin:
//
// $is_spam_shell_command = '/usr/bin/sa-learn --spam --configpath=/etc/spamassassin -p /root/.spamassassin/user_prefs';
// $is_not_spam_shell_command = '/usr/bin/sa-learn --ham --configpath=/etc/spamassassin -p /root/.spamassassin/user_prefs';
//
//
//
// Sample for SpamAssassin per-user configuration:
//
// $is_spam_shell_command = '/usr/bin/sa-learn --spam --username=###EMAIL_ADDRESS###';
// $is_not_spam_shell_command = '/usr/bin/sa-learn --ham --username=###EMAIL_ADDRESS###';
//
//
//
// Sample for SpamAssassin per-user configuration using spamc/spamd:
//
// $is_spam_shell_command = '/usr/bin/spamc -L spam -d localhost -u ###EMAIL_ADDRESS###';
// $is_not_spam_shell_command = '/usr/bin/spamc -L ham -d localhost -u ###EMAIL_ADDRESS###';
//
//
//
// Advanced sample for SpamAssassin:
//
// In order to make sure you are running sa-learn (or equivalent) as the correct user 
// (else it will be run as the user that your web server runs as), you may need to run 
// the command with sudo.  In /etc/sudoers, you will want to set this:
//
// web_server_user   ALL=(sa_user) NOPASSWD: /usr/bin/sa-learn, /usr/bin/spamassassin, /usr/bin/spamc
//
// Where you need to change "web_server_user" to the actual userID of the user running
// your web server, and "sa_user" to the user that should run sa-learn (or spamassasin
// or spamc).
//
// After that, one of the following command pairs should work for you (remember to 
// replace "sa_user" with the correct username):
//
// $is_spam_shell_command = 'sudo -u sa_user /usr/bin/sa-learn --spam';
// $is_not_spam_shell_command = 'sudo -u sa_user /usr/bin/sa-learn --ham';
//
// $is_spam_shell_command = 'sudo -u sa_user /usr/bin/spamassassin -r --configpath=/etc/spamassassin -p /root/.spamassassin/user_prefs';
// $is_not_spam_shell_command = 'sudo -u sa_user /usr/bin/spamassassin -k --configpath=/etc/spamassassin -p /root/.spamassassin/user_prefs';
//
// $is_spam_shell_command = 'sudo -u sa_user /usr/bin/spamc -L spam';
// $is_not_spam_shell_command = 'sudo -u sa_user /usr/bin/spamc -L ham';
//
//
//
$is_spam_shell_command = '';
$is_not_spam_shell_command = '';



// -------------------------------------------------------------------
//
// REPORT-BY-EMAIL OPTIONS
//
// Be sure if you use this that you have NOT turned on reporting
// by shell command, by move-to-folder or by custom function.
//


// If you resend the message in order to report it as spam/non-spam,
// specify the destination address here.
//
// If you need to include any information about the user for which
// the report is being made, you can use these constants in the 
// destination address:
//
//    ###EMAIL_PREF###               Will be replaced with the user's 
//                                   main email address preference
//                                   setting (under Options -> Personal
//                                   Information) (email addresses under
//                                   Multiple Identiies not supported)
//
//    ###EMAIL_ADDRESS###            Will be replaced with the user's 
//                                   full email address (based on the
//                                   login username and user's domain)
//
//    ###USERNAME###                 Will be replaced with the username
//                                   portion of the user's email address
//
//    ###DOMAIN###                   Will be replaced with the domain
//                                   portion of the user's email address
//
//
// $is_spam_resend_destination = 'spam_report';  // let SM append user's domain automatically
// $is_not_spam_resend_destination = 'not_spam_report';
//
// $is_spam_resend_destination = 'spam-###USERNAME###@###DOMAIN###'; 
// $is_not_spam_resend_destination = 'ham-###USERNAME###@###DOMAIN###';
//
$is_spam_resend_destination = '';
$is_not_spam_resend_destination = '';



// When reporting via email, should the message be resent (leaving
// all message headers intact), or should it be sent as an attachment?
//
// resend = 'bounce'
// attach = 'attachment'
//
// $spam_report_email_method = 'bounce';
//
$spam_report_email_method = 'attachment';



// When reporting via email by sending as an attachment,
// should a copy of the spam notification message (with
// the offending message attachment) be placed in the
// user's sent folder?
//
// 1 = yes, 0 (zero) = no
//
$is_spam_keep_copy_in_sent = 0;
$is_not_spam_keep_copy_in_sent = 0;



// When reporting via email (at least when sending as attachment), you
// may indicate any extra subject information here (default empty)
//
// When set to "SPAM", the subject might end up looking like "[SPAM: Buy our product!]"
//
$is_spam_subject_prefix = 'SPAM';
$is_not_spam_subject_prefix = 'HAM';



// You may also specify overrides for the SMTP server for the 
// email only (see SquirrelMail's main configuration file or
// use config/conf.pl to understand what these settings normally
// are).
//
// Set to empty strings to use system defaults.
//
$spam_report_smtpServerAddress = '';
$spam_report_smtpPort = '';
$spam_report_useSendmail = '';
$spam_report_smtp_auth_mech = '';
$spam_report_use_smtp_tls = '';



// -------------------------------------------------------------------
//
// REPORT-BY-CUSTOM-FUNCTION OPTIONS
//
// Be sure if you use this that you have NOT turned on
// reporting by email, by shell command or by move-to-folder.
//


// You must provide any custom PHP function defined here.
// Each of these functions will be called with two parameters,
// the first being an array of message ID strings for each
// of the message(s) being reported (even when there is only
// one message being reported).  The second parameter will
// usually be zero, but when the message being reported is an
// attachment to another message, it will be its entity ID
// within its parent message.
//
// The functions must return an empty string when all messages
// were reported successfully, or an error message string
// if reporting failed or any other problem occurred.
//
// Leave these empty when not using custom PHP callbacks
// for spam/ham reporting.
//
// Sample custom functions that report the spam/ham by adding
// the sender to a black/white-list are included near the
// bottom of this file.
//
// $sb_report_spam_by_custom_function = 'report_spam_by_blacklist';
// $sb_report_not_spam_by_custom_function = 'report_ham_by_whitelist';
//
$sb_report_spam_by_custom_function = '';
$sb_report_not_spam_by_custom_function = '';



// -------------------------------------------------------------------
//
// EXTRA BUTTONS AND LINKS
//
// Any number of additional (custom) buttons or links may
// be added to the SquirrelMail interface by declaring
// them and their handlers here.
//


// Define any extra buttons or links here.
//
// Each one is keyed by its displayable name - what
// the user will see in the interface.  This text
// will be passed through SquirrelMail's translation
// engine, so if you use one of the sample names included
// in the Spam Buttons translation file, it will be
// translated into other languages (assuming translation
// availability).  You can also add your own translations
// as needed for different text values.  Currently, these
// values are included as suggestions:
//
//    Whitelist
//    Whitelist Sender
//    Blacklist
//    Blacklist Sender
//    
// For each of these button/link names, a six-element array
// is needed:
//
//    The first element determines if the button will show up
//    on the message list screen.  It can be set to 1 to
//    indicate that it should always be shown there, 0 (zero)
//    to indicate that it should never be shown there, or the
//    name of a custom callback function that can be used to
//    perform more complex operations to determine if the
//    button should be shown or not.  If you define a custom
//    callback function, it will be called with two
//    arguments: first, the string "MESSAGE_LIST_BUTTON" (which
//    indicates that the plugin is asking if the button can be
//    shown on the message list page), and second, the username
//    of the current user who is logged in.  The function must
//    then return either TRUE to indicate that the button is
//    to be shown, or FALSE when it should not.
//
//    The second element determines if the link will show up
//    on the message view screen.  It can be set to 1 to
//    indicate that it should always be shown there, 0 (zero)
//    to indicate that it should never be shown there, or the
//    name of a custom callback function that can be used to
//    perform more complex operations to determine if the
//    link should be shown or not.  If you define a custom
//    callback function, it will be called with five
//    arguments: first, the string "MESSAGE_VIEW_LINK" (which
//    indicates that the plugin is asking if the link can be
//    shown on the message view page), second, the username
//    of the current user who is logged in, third, the email
//    address of the sender of the message currently being
//    viewed (it is possible to get more information about
//    the message if needed - see the example function below
//    to learn how), fourth, the message ID of the message
//    currently being viewed, and fifth, the message entity ID
//    if the message being viewed is an attachment to another
//    message (if not, it will be empty).  The function must
//    then return either TRUE to indicate that the link is to
//    be shown, or FALSE when it should not.
//
//    The third element determines if the button (as opposed to
//    the link explained above) will show up on the message view
//    screen (only applicable for SquirrelMail 1.5.2+).  It can
//    be set to 1 to indicate that it should always be shown
//    there, 0 (zero) to indicate that it should never be shown
//    there, or the name of a custom callback function that can
//    be used to perform more complex operations to determine if
//    the button should be shown or not.  If you define a custom
//    callback function, it will be called with five arguments:
//    first, the string "MESSAGE_VIEW_BUTTON" (which indicates
//    that the plugin is asking if the button can be shown on the
//    message view page), second, the username of the current
//    user who is logged in, third, the email address of the
//    sender of the message currently being viewed (it is possible
//    to get more information about the message if needed - see
//    the example function below to learn how) fourth, the message
//    ID of the message currently being viewed, and fifth, the
//    message entity ID if the message being viewed is an
//    attachment to another message (if not, it will be empty).
//    The function must then return either TRUE to indicate that
//    the button is to be shown, or FALSE when it should not.
//
//    The fourth element is the name of the callback function
//    that will handle the requested action when this button
//    or link is clicked by the user.  You must define this
//    function yourself (see below for an example).  When called,
//    it is given five arguments, the first of which is the name
//    of the button that was clicked, although if your button names
//    have any non-alphanumeric characters in them (such as dashes,
//    spaces, etc.), those will be converted to underscores.  The
//    second argument is the username of the user who is currently
//    logged in.  The third argument is the email address of the
//    sender of the message that is being processed (it is possible
//    to get more information about the message if needed - see the
//    example function below to learn how).  The fourth argument is
//    the message ID for the message that needs to be processed.
//    The fifth parameter is the message entity ID if the message
//    being processed is an attachment to another message (if not,
//    it will be empty).  The function must then return either a
//    boolean (TRUE/FALSE) success indicator *OR* an array with two
//    elements.  When using an array return value, the first element
//    is a boolean value that is TRUE if the message was processed
//    normally and FALSE if some error occured.  The second element
//    is a string containing a note that will be displayed to the
//    user upon completion.  Note that this message will be ignored
//    pending the following:
//
//    The fifth and sixth elements are text strings that contain
//    the messages that will be displayed to the user upon
//    successful (but not failed) execution of a button or link
//    click.  The fifth element is the message used when only one
//    message has been processed, and the sixth is the message used
//    when more than one message has been processed (this presumes
//    Germanic plurality rules, but these strings will be used with
//    ngettext(), so translators can correctly use their language's
//    native plurality rules)  You may leave these blank and the
//    message returned by the callback function defined just above
//    will be used instead.  This text will be passed through
//    SquirrelMail's translation engine, so if you use sample messages
//    included in the Spam Buttons translation file, it will be
//    translated into other languages (assuming translation
//    availability).  You can also add your own translations as
//    needed for different message values.  Currently, these messages
//    are included as suggestions:
//
//       Sender has been blacklisted
//       Senders have been blacklisted
//       Sender has been whitelisted
//       Senders have been whitelisted
//
// $extra_buttons = array('Blacklist' => array(1,
//                                             'is_not_blacklisted',  // see below
//                                             0,
//                                             'blacklist',  // see below
//                                             'Sender has been blacklisted',
//                                             'Senders have been blacklisted'),
//                        'Whitelist' => array(1,
//                                             'is_not_whitelisted',  // see below
//                                             0,
//                                             'whitelist',  // see below
//                                             'Sender has been whitelisted',
//                                             'Senders have been whitelisted'));
//
$extra_buttons = array();



/**
  * Example custom button test function
  *
  * Determines if the current user has not yet whitelisted the given sender.
  *
  * Implementation is left to your imagination.
  *
  * @param string $action     A string indicating what button or link
  *                           is being created ("MESSAGE_LIST_BUTTON",
  *                           "MESSAGE_VIEW_LINK", or
  *                           "MESSAGE_VIEW_BUTTON").
  * @param string $user       The user whose whitelist we should check.
  * @param string $sender     The sender to check for (will be empty
  *                           when $action is "MESSAGE_LIST_BUTTON").
  * @param string $message_id The ID of the message currently being
  *                           viewed, if any (will be empty when
  *                           $action is "MESSAGE_LIST_BUTTON").
  * @param string $entity_id  The entity ID of the message currently
  *                           being viewed when it is an attachment
  *                           to another message, if any (will be
  *                           empty when it is not an attachment, or
  *                           when $action is "MESSAGE_LIST_BUTTON").
  *
  * @return boolean TRUE if the sender has NOT been whitelisted,
  *                 FALSE otherwise.
  *
  */
function is_not_whitelisted($action, $user, $sender, $message_id, $entity_id)
{

   // you need to supply the functionality herein
   //
   return TRUE;


   // here is how you can use the Server Settings plugin
   // to ask it about a value on the server (assumes
   // you have a setting called "whitelist" that is
   // correctly configured therein)
   //
   include_once(SM_PATH . 'plugins/server_settings/functions.php');
   return !server_settings_test_value('whitelist', $sender);


   // here is how you can retrieve additional headers from
   // the current message, in case you need them...
   //
   // this retrieves the message's Subject header in the format
   // array(0 => 'Subject:', 1 => 'This is my message subject')
   //
   $subject = '';
   if (!empty($message_id))
      $subject = sb_get_message_header($message_id, $entity_id, 'Subject');

}



/**
  * Example custom button test function
  *
  * Determines if the current user has not yet blacklisted the given sender.
  *
  * Implementation is left to your imagination.
  *
  * @param string $action     A string indicating what button or link
  *                           is being created ("MESSAGE_LIST_BUTTON",
  *                           "MESSAGE_VIEW_LINK", or
  *                           "MESSAGE_VIEW_BUTTON").
  * @param string $user       The user whose whitelist we should check.
  * @param string $sender     The sender to check for (will be empty
  *                           when $action is "MESSAGE_LIST_BUTTON").
  * @param string $message_id The ID of the message currently being
  *                           viewed, if any (will be empty when
  *                           $action is "MESSAGE_LIST_BUTTON").
  * @param string $entity_id  The entity ID of the message currently
  *                           being viewed when it is an attachment
  *                           to another message, if any (will be
  *                           empty when it is not an attachment, or
  *                           when $action is "MESSAGE_LIST_BUTTON").
  *
  * @return boolean TRUE if the sender has NOT been blacklisted,
  *                 FALSE otherwise.
  *
  */
function is_not_blacklisted($action, $user, $sender, $message_id, $entity_id)
{

   // you need to supply the functionality herein
   //
   return TRUE;


   // here is how you can use the Server Settings plugin
   // to ask it about a value on the server (assumes
   // you have a setting called "blacklist" that is
   // correctly configured therein)
   //
   include_once(SM_PATH . 'plugins/server_settings/functions.php');
   return !server_settings_test_value('blacklist', $sender);


   // here is how you can retrieve additional headers from
   // the current message, in case you need them...
   //
   // this retrieves the message's Subject header in the format
   // array(0 => 'Subject:', 1 => 'This is my message subject')
   //
   $subject = '';
   if (!empty($message_id))
      $subject = sb_get_message_header($message_id, $entity_id, 'Subject');

}



/**
  * Example custom button handler function
  *
  * Whitelist the given sender.
  *
  * Implementation is left to your imagination.
  *
  * @param string $button_name The name of the button or link
  *                            (with non-alphanumerics having
  *                            been replaced with underscores).
  * @param string $user        The user whose whitelist we are adding to.
  * @param string $sender      The sender to whitelist.
  * @param string $message_id  The ID of the message currently being
  *                            processed.
  * @param string $entity_id   The entity ID of the message currently
  *                            being processed when it is an attachment
  *                            to another message (otherwise, it will
  *                            be empty).
  *
  * @return array A two-element array, the first element being
  *               a boolean value that is TRUE if the sender was
  *               added to the whitelist normally and FALSE if
  *               some error occured.  The second element is a
  *               string containing a note that usually contains
  *               any error information that should be shown to
  *               the user when an error occurs.
  *
  */
function whitelist($button_name, $user, $sender, $message_id, $entity_id)
{

   // you need to supply the functionality herein
   //
   return TRUE;


   // if multiple entries into the whitelist is a possilbe problem
   // (caused by multiple clicks of a button from the message *list*
   // screen), then you make sure the user isn't already listed:
   //
   if (!is_not_whitelisted('', $user, $sender, $message_id, $entity_id))
      return array(TRUE, '');


   // here is how you can use the Server Settings plugin
   // to send the value to the server backend (assumes
   // you have a setting called "whitelist" that is
   // correctly configured therein)
   //
   include_once(SM_PATH . 'plugins/server_settings/functions.php');
   if (server_settings_update_value('whitelist', $sender, '', '', TRUE))
      return array(TRUE, '');
   else
      return array(FALSE, 'ERROR attempting to whitelist ' . $sender);


   // here is how you can retrieve additional headers from
   // the current message, in case you need them...
   //
   // this retrieves the message's Subject header in the format
   // array(0 => 'Subject:', 1 => 'This is my message subject')
   //
   $subject = sb_get_message_header($message_id, $entity_id, 'Subject');

}



/**
  * Example custom button handler function
  *
  * Blacklist the given sender.
  *
  * Implementation is left to your imagination.
  *
  * @param string $button_name The name of the button or link
  *                            (with non-alphanumerics having
  *                            been replaced with underscores).
  * @param string $user        The user whose blacklist we are adding to.
  * @param string $sender      The sender to blacklist.
  * @param string $message_id  The ID of the message currently being
  *                            processed.
  * @param string $entity_id   The entity ID of the message currently
  *                            being processed when it is an attachment
  *                            to another message (otherwise, it will
  *                            be empty).
  *
  * @return array A two-element array, the first element being
  *               a boolean value that is TRUE if the sender was
  *               added to the blacklist normally and FALSE if
  *               some error occured.  The second element is a
  *               string containing a note that usually contains
  *               any error information that should be shown to
  *               the user when an error occurs.
  *
  */
function blacklist($button_name, $user, $sender, $message_id, $entity_id)
{

   // you need to supply the functionality herein
   //
   return TRUE;


   // if multiple entries into the blacklist is a possilbe problem
   // (caused by multiple clicks of a button from the message *list*
   // screen), then you make sure the user isn't already listed:
   //
   if (!is_not_blacklisted('', $user, $sender, $message_id, $entity_id))
      return array(TRUE, '');


   // here is how you can use the Server Settings plugin
   // to send the value to the server backend (assumes
   // you have a setting called "blacklist" that is
   // correctly configured therein)
   //
   include_once(SM_PATH . 'plugins/server_settings/functions.php');
   if (server_settings_update_value('blacklist', $sender, '', '', TRUE))
      return array(TRUE, '');
   else
      return array(FALSE, 'ERROR attempting to blacklist ' . $sender);


   // here is how you can retrieve additional headers from
   // the current message, in case you need them...
   //
   // this retrieves the message's Subject header in the format
   // array(0 => 'Subject:', 1 => 'This is my message subject')
   //
   $subject = sb_get_message_header($message_id, $entity_id, 'Subject');

}



/**
  * Example custom spam report handler
  *
  * Reports one or more spam by blacklisting the message sender(s).
  *
  * This function is intended as an example for use with the
  * report-by-custom-function ($sb_report_spam_by_custom_function)
  * feature, and NOT particularly for use with the extra buttons
  * functionality, although it does also call to the example
  * blacklist() function above that is also used by the sample
  * extra button settings.
  *
  * @param array  $message_ids   An array of message IDs to be reported.
  * @param string $passed_ent_id The message entity being reported
  *                              (zero if the message itself is being
  *                              reported (only applicable when there
  *                              is just one element in the $message_ids
  *                              array))
  *
  * @return string An error message if an error occurred,
  *                empty string otherwise
  *
  */
function report_spam_by_blacklist($message_ids, $passed_ent_id)
{

   // you need to supply the functionality herein
   //
   return '';


   global $username, $sb_debug;
   $error = '';
   $me = 'report_spam_by_blacklist';

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');
   spam_buttons_init();


   // just loop through the messages, reporting one at a time
   //
   if (is_array($message_ids)) foreach ($message_ids as $messageID)
   {

      // here is how you can retrieve additional headers from
      // the message, in case you need them...
      //
      // this retrieves the message's From header in the format
      // array(0 => 'From:', 1 => '"Jose" <jose@example.org>')
      //
      $sender = sb_get_message_header($messageID, $passed_ent_id, 'From');


      // this parses out just the email address portion of the From header
      //
      if (function_exists('parseRFC822Address'))
      {
         $sender = parseRFC822Address($sender[1], 1);
         $sender = $sender[0][2] . '@' . $sender[0][3];
      }
      else
      {
         $sender = parseAddress($sender[1], 1);
         $sender = $sender[0][0];
      }


      // now, blacklist the sender
      //
      $ret = blacklist('', $username, $sender, $messageID, $passed_ent_id);
      if (is_array($ret) && empty($ret[0]) && !empty($ret[1]))
         $error = $ret[1];


      // dump stuff out if debugging
      //
      if ($sb_debug)
      {
         echo '<hr /><strong>REPORTED BY CUSTOM FUNCTION:</strong> ' . $me . '<br /><br />';
         echo '<hr /><strong>SENDER REPORTED:</strong> ' . $sender . '<br /><br />';
         echo '<hr /><strong>RESULTS FROM REPORT:</strong> (' . $error . ')<br /><br />';
         exit;
      }


      // oops, report failed - put together nicely formatted error message
      //
      if (!empty($error))
      {
         $previous_domain = sq_change_text_domain('spam_buttons');

         if (empty($passed_ent_id))
            $error = str_replace(array('%1', '%2'),
                                 array($messageID, $error),
                                 _("ERROR: Problem reporting message ID %1: %2"));
         else
            $error = str_replace(array('%1', '%2', '%3'),
                                 array($messageID, $passed_ent_id, $error),
                                 _("ERROR: Problem reporting message ID %1 (entity %2): %3"));

         sq_change_text_domain($previous_domain);

         break;  // out of foreach loop
      }

   }

   return $error;

}



/**
  * Example custom ham report handler
  *
  * Reports one or more ham by whitelisting the message sender(s).
  *
  * This function is intended as an example for use with the
  * report-by-custom-function ($sb_report_not_spam_by_custom_function)
  * feature, and NOT particularly for use with the extra buttons
  * functionality, although it does also call to the example
  * whitelist() function above that is also used by the sample
  * extra button settings.
  *
  * @param array  $message_ids   An array of message IDs to be reported.
  * @param string $passed_ent_id The message entity being reported
  *                              (zero if the message itself is being
  *                              reported (only applicable when there
  *                              is just one element in the $message_ids
  *                              array))
  *
  * @return string An error message if an error occurred,
  *                empty string otherwise
  *
  */
function report_ham_by_whitelist($message_ids, $passed_ent_id)
{

   // you need to supply the functionality herein
   //
   return '';


   global $username, $sb_debug;
   $error = '';
   $me = 'report_spam_by_whitelist';

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');
   spam_buttons_init();


   // just loop through the messages, reporting one at a time
   //
   if (is_array($message_ids)) foreach ($message_ids as $messageID)
   {

      // here is how you can retrieve additional headers from
      // the message, in case you need them...
      //
      // this retrieves the message's From header in the format
      // array(0 => 'From:', 1 => '"Jose" <jose@example.org>')
      //
      $sender = sb_get_message_header($messageID, $passed_ent_id, 'From');


      // this parses out just the email address portion of the From header
      //
      if (function_exists('parseRFC822Address'))
      {
         $sender = parseRFC822Address($sender[1], 1);
         $sender = $sender[0][2] . '@' . $sender[0][3];
      }
      else
      {
         $sender = parseAddress($sender[1], 1);
         $sender = $sender[0][0];
      }


      // now, whitelist the sender
      //
      $ret = whitelist('', $username, $sender, $messageID, $passed_ent_id);
      if (is_array($ret) && empty($ret[0]) && !empty($ret[1]))
         $error = $ret[1];


      // dump stuff out if debugging
      //
      if ($sb_debug)
      {
         echo '<hr /><strong>REPORTED BY CUSTOM FUNCTION:</strong> ' . $me . '<br /><br />';
         echo '<hr /><strong>SENDER REPORTED:</strong> ' . $sender . '<br /><br />';
         echo '<hr /><strong>RESULTS FROM REPORT:</strong> (' . $error . ')<br /><br />';
         exit;
      }


      // oops, report failed - put together nicely formatted error message
      //
      if (!empty($error))
      {
         $previous_domain = sq_change_text_domain('spam_buttons');

         if (empty($passed_ent_id))
            $error = str_replace(array('%1', '%2'),
                                 array($messageID, $error),
                                 _("ERROR: Problem reporting message ID %1: %2"));
         else
            $error = str_replace(array('%1', '%2', '%3'),
                                 array($messageID, $passed_ent_id, $error),
                                 _("ERROR: Problem reporting message ID %1 (entity %2): %3"));

         sq_change_text_domain($previous_domain);

         break;  // out of foreach loop
      }

   }

   return $error;

}



