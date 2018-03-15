<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

   if (!defined('SM_PATH'))
      define('SM_PATH','../../');

   function archive_mail_version() {
      return '1.2';
   }

   function squirrelmail_plugin_init_archive_mail() {
      include_once(SM_PATH . 'plugins/archive_mail/includes/hooks.php');
   }

   function archive_mail_zipit() {
      include_once(SM_PATH . 'plugins/archive_mail/includes/zipit.php');
   }

   function archive_mail() {
      include_once(SM_PATH . 'plugins/archive_mail/includes/archive_mail.php');
   }

   function archive_mail_bottom() {
      include_once(SM_PATH . 'plugins/archive_mail/includes/archive_mail_bottom.php');
   }

   function archive_mail_display_option() {
      include_once(SM_PATH . 'plugins/archive_mail/includes/display_options.php');
   }

   function archive_mail_loading_prefs() {
      include_once(SM_PATH . 'plugins/archive_mail/includes/loading_prefs.php');
   }
?>