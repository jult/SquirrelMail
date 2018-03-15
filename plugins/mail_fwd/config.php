<?php
/// Location of the wfwd binary
/// Don't forget to change in fwdfile/config.mk aswell
global $mail_fwd_wfwd_binary;
$mail_fwd_wfwd_binary = "/usr/bin/wfwd";

/// Enables/Disables save local copy of forwarded e-mail
/// DO NOT use this when using MySQL functionality
/// Default is false
global $save_local_enabled;
// $save_local_enabled = true;
$save_local_enabled = true;

global $mysql_forwarding_enabled, $mysql_server, $mysql_manager_id,
       $mysql_manager_pwd, $mysql_database, $mysql_table, 
       $mysql_userid_field, $mysql_forward_field;
$mysql_forwarding_enabled = false;
$mysql_manager_id = 'mailadmin';
$mysql_manager_pwd = 'xxxxxxx';
$mysql_server = 'localhost';
$mysql_database = 'email';
$mysql_table = 'virtual';
$mysql_userid_field = 'address';
$mysql_forward_field = 'goto';

?>
