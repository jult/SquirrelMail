<?php

global $forward_data, $sq_vacation_file, $vacation_file,
       $username, $suid_binary, $sq_forward_addresses_file,
       $vacation_delete, $forward_file, $maintain_forwarding,
       $vac_debug, $laf_backend, $ftp_server, $ftp_passive,
       $sq_vacation_subject_file, $ldap_lookup_ftp_server,
       $vacation_path, $laf_prefs_file, $laf_ftp_mode,
       $laf_ldap_base, $laf_ldap_server, $laf_ldap_attribute, $initialize_when_create,
       $initialize_when_change, $set_hostname, $use_ssl_ftp, 
       $local_delivery_syntax, $vacation_command_quotes, $www_initialize, 
       $vac_umask, $only_localpart_in_forward_file, $debug_suid_output_file,
       $other_forward_file_contents_prefix, $auth_user_localpart_only,
       $other_forward_file_contents_suffix, $aliases_full_email_format,
       $other_forward_file_contents_deleted, $maintain_autoresponder,
       $forward_file_format_pattern, $forward_file_format_replace,
       $vacation_subject_default, $vacation_message_default,
       $auto_enable_autoresponder, $allow_black_hole;


// Should the plugin offer autoresponder functionality to
// the user?  If not, this plugin becomes just an interface
// to manage email forwarding.
//
// NOTE that you MUST have either $maintain_forwarding
// or this setting enabled - you cannot disable both.
//
$maintain_autoresponder = 1;



// Should the plugin offer email forwarding management
// to the user?  If not, only autoreplies are managed.
//
// NOTE that you must also have specified something
// for $forward_file in order to use this feature.
//
// NOTE that you MUST have either $maintain_autoresponder
// or this setting enabled - you cannot disable both.
//
$maintain_forwarding = 1;



// Choose the method to be used when connecting to your
// server to maintain vacation and/or .forward files: "ftp" or "suid"
//
// $laf_backend = 'suid'; 
//
$laf_backend = 'ftp'; 



// If you are using the FTP backend, please specify
// your server's hostname (or IP address) here
//
// NOTE that you can have different FTP servers for
// different domains or different users if you use 
// the Login Manager (vlogin) plugin and specify 
// a value for "vlogin_local_autorespond_forward_ftp_server"
// for each domain or each user, etc.
//
$ftp_server = 'localhost'; 



// If you are using the FTP backend, turn on passive
// mode if necessary by setting this to 1
//
// $ftp_passive = 1;
//
$ftp_passive = 0;



// If you are using the FTP backend, this controls if
// transfers are made as binary or ASCII files.
//
// 0 = ASCII
// 1 = BINARY
//
$laf_ftp_mode = 1;



// If you are using the FTP backend, you may specify 
// that the connection be made via SSL FTP by setting
// this to 1 (please note that this requires that 
// OpenSSL support be enabled in your PHP build)
//
$use_ssl_ftp = 0;



// If you store users' ftp servers (server name/location)
// in LDAP, you can look up what $ftp_server should be in
// LDAP by turning this on
//
// $ldap_lookup_ftp_server = 1;
//
$ldap_lookup_ftp_server = 0;



// When using $ldap_lookup_ftp_server, set the LDAP base
// server, and attribute here ($laf_ldap_server may be a host
// name or LDAP URI)
//
$laf_ldap_base = 'ou=People,dc=DOMAIN,dc=com';
$laf_ldap_server = 'your.ldap.server.com';
$laf_ldap_attribute = 'mailhost';



// If you are using the suid backend, this is the location
// of the binary that writes vacation messages and .forward
// files to local disk.  NOTE: if you change this, don't
// forget to change the install location ("bindir" setting)
// in suid_backend/Makefile.am as well -- BEFORE you configure
// and compile the suid backend.
//
// $suid_binary = '/usr/local/sbin/squirrelmail_autoresponder_forwarder_proxy';
//
$suid_binary = $suid_binary = SM_PATH . 'plugins/local_autorespond_forward/squirrelmail_autoresponder_forwarder_proxy';



// This is the name of the file that holds user prefs
// for this plugin
//
$laf_prefs_file = '.vacation.pref'; 



// This is the name of the file that holds the 
// subject line for the vacation message
//
// Note that the default here used to be '.forward.subj',
// so you may need to change this value if you have
// been using version 2 of the Vacation Local plugin
//
$sq_vacation_subject_file = '.vacation.subj'; 



// This is the name of the file that holds the addresses to be forwarded to
//
$sq_forward_addresses_file = '.forward.fwd'; 



// This is the name of the file that holds the raw vacation message text
//
$sq_vacation_file = '.vacation.sq'; 



// This is the name of vacation file, which holds the vacation message
// in the form that it will be sent out in (with subject, etc)
//
$vacation_file = '.vacation.msg'; 



// Give the names of any supplementary (e.g. logging) files you would
// like to be deleted when the vacation message is removed
//
// Should be a list of comma-separated file names
//
$vacation_delete = '.vacation.db'; 



// You may define a default vacation subject and message for your 
// users if needed
//
// $vacation_subject_default = 'Out-of-office Reply';
// $vacation_message_default = "Thank you for your email, however I am currently out of the office.  Please be assured that I will reply as soon as I am able.\n\nThank you for your patience.\n\n";
//
$vacation_subject_default = '';
$vacation_message_default = "";



// The plugin can automatically turn the autoresponder on 
// when the user adds a new subject and/or message body 
// and likewise deactivate the autoresponder when the same
// fields are removed (cleared).
//
//    0  =  Disable automatic input sensing
//    1  =  Only the message body triggers autoresponder 
//          (de)activation
//    2  =  Both the message body and subject are needed 
//          to trigger autoresponder (de)activation
//
$auto_enable_autoresponder = 2;



// Should the plugin initialize autoresponder functionality
// when the user turns on the vacation message?  
//
// This usually corresponds to executing the vacation program 
// with the -I switch, and typically initializes the user's 
// vacation database.
//
// If you are using the suid backend, enabling this option
// requires it to be configured with "--enable-vacation".
// If you are using the FTP backend, you also need to enable
// $www_initialize below.
//
$initialize_when_create = 0;



// Should the plugin initialize autoresponder functionality
// when the user changes her vacation message?
//
// This usually corresponds to executing the vacation program
// with the -I switch, and typically initializes the user's
// vacation database.
//
// If you are using the suid backend, enabling this option
// requires it to be configured with "--enable-vacation".
// If you are using the FTP backend, you also need to enable
// $www_initialize below.
//
$initialize_when_change = 0;



// If you use the FTP backend and want to use
// $initialize_when_create, or $initialize_when_change, you
// must tell the web server what command it should run to
// do the initialization (if you use the "suid" backend, you do
// not need to worry about this setting).  See the README file
// for hints on what you may have to add to your sudoers file if
// you need to use sudo.  The string ###USERNAME### will be replaced
// with the user name if needed.
//
// This setting has no effect unless $initialize_when_create 
// or $initialize_when_change is enbaled.
//
// $www_initialize = '/usr/bin/vacation -I';
//
$www_initialize = '/usr/bin/sudo -u ###USERNAME### /usr/bin/vacation -I';



// This is the path to the vacation executable 
// on your system.  You may also add any options
// needed here, such as to change the vacation
// database interval (this example sets it to
// one day):
//
// $vacation_path = '/usr/bin/vacation -t1';
//
$vacation_path = '/usr/bin/vacation';



// By default, aliases will be added to the vacation command
// by using any of the user's multiple SquirrelMail identities,
// and adding each one using only the local part of the email
// address.  If your MTA chokes on the aliases due to problems
// with domain mismatches, you can turn this setting on to
// have the aliases listed in the vacation command in full
// email address format.
//
$aliases_full_email_format = 0;



// Some systems (read Qmail) do not like double quotes around
// the vacation command in the .forward file.  You can change
// the double quotes to anything that works for you here, including
// nothing at all (for Qmail).
//
// $vacation_command_quotes = '';
//
$vacation_command_quotes = '"';



// Some systems need the path to your users' mailbox or maildir
// to get the local delivery of messages correct (e.g., Qmail)
// The default below should work for most other cases.  The
// string ###USERNAME### will be replaced with the username.
//
// $local_delivery_syntax = '/home/###USERNAME###/Maildir/';
// $local_delivery_syntax = './Maildir/';
//
$local_delivery_syntax = '\\\\###USERNAME###';



// This is the name of forward file, which should be
// ".forward" for most systems.  If your autoresponder
// system does not use a .forward file, make sure to
// leave this blank.  If it is blank, this plugin will
// not attempt to create or maintain a .forward file.
//
// Of course, if you have enabled mail forwarding by
// turning on $maintain_forwarding, then you must also 
// make sure this is set correctly.
//
// $forward_file = ''; 
//
$forward_file = '.forward'; 



// Some systems have IMAP servers that require a full email
// address for user authentication (login), but a vacation
// program that only wants the local part of that address
// in the .forward file.  Only set this to 1 if you are sure
// you know what you are doing.
//
// This setting is only relevant if $forward_file is not empty.
//
$only_localpart_in_forward_file = 0;



// You can allow users to indicate that they do NOT want to
// keep messages locally even when they have not enabled 
// forwarding to other email addresses, thus incoming messages
// are lost.  Some users may want to do this when, for example,
// they have a autoresponse such as "Please note that [name] is 
// no longer with [department] and your email will not be forwarded".
//
//    0  =  Don't allow users to disable local delivery unless
//          forwarding is active
//    1  =  Allow "no local delivery" even when no forwarding is
//          active, but an autoreply MUST be turned on
//
$allow_black_hole = 0;



// Extra forward file contents, unrelated to autoreply
// functionality or forwarding addresses (such as calling 
// filtering applications such as procmail) can be 
// specified here, and they will be added to the beginning 
// ("prefix") or end ("suffix") of any forward file created 
// by this plugin.  
//
// When this plugin would otherwise delete the forward file, 
// it is instead saved with the contents specified in 
// $other_forward_file_contents_deleted.
//
// Each line in the file should be in a separate array
// element, and the string ###USERNAME### will be replaced 
// with the username.
//
// This setting is only relevant if $forward_file is not empty.
//
// $other_forward_file_contents_prefix = array('|/usr/bin/procmail',);
// $other_forward_file_contents_deleted = array('|/usr/bin/procmail',
//                                              '\\\\###USERNAME###',);
//
$other_forward_file_contents_prefix = array();
$other_forward_file_contents_suffix = array();
$other_forward_file_contents_deleted = array();



// In some cases, the final format of the forward file 
// that is created by this plugin may not be exactly
// what is needed.  These settings allow you to specify
// any number of regular expression search and replace
// patterns so you can rearrange the contents of the
// forward file before it is written.
//
// For more information about how to create these 
// patterns and replacements, see:
//
// http://www.php.net/manual/function.preg-replace.php
// http://www.php.net/manual/reference.pcre.pattern.syntax.php
// 
// The example below will find the local delivery line
// and place it on the same line (at the front, followed
// by a comma) as the vacation command.
//
// $forward_file_format_pattern = array('/(.*)(^.+?\/usr\/bin\/vacation.+?$)(.*?)(\\\\.+?$)(.*)/ms', '/(\n){2,}/');
// $forward_file_format_replace = array("$1\n$4, $2\n$3\n$5", "\n");
//
$forward_file_format_pattern = '';
$forward_file_format_replace = '';



// Some systems are configured such that the IMAP server
// (and thus SquirrelMail) require a full email address
// format for its login usernames, but when authenticating
// with the backend (FTP or suid), the user account name
// should only be given as the local part (without the 
// "@example.org" part) of the IMAP username.  Turn this
// setting on if that applies to you.
//
$auth_user_localpart_only = 0;



// Set the umask to be used when creating all files.
// FTP may or may not preserve these permissions; this
// has no effect on the suid backend, which uses mode
// 0600 unless you set a mode by configuring it using
// the --enable-remote-filemode option.
//
$vac_umask = 022;



// If you use the -h option for the vacation binary,
// set this to 1 and the domain from the user email 
// address will be used with -h in the .forward file
// 
$set_hostname = 0;



// Use for debugging purposes only; passwords may be exposed 
// if you leave this turned on!
//
$vac_debug = 0;



// For help debugging the suid backend, you can specify a
// file to which its output should be sent.  For this to
// have any effect, you must also turn on $vac_debug above.
//
// $debug_suid_output_file = '/tmp/squirrelmail_local_autorespond_forward_suid_debug';
//
$debug_suid_output_file = '';



