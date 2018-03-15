<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/


      global $squirrelmail_plugin_hooks;
      $squirrelmail_plugin_hooks['mailbox_index_after']['archive_mail'] = 'archive_mail_bottom';
      if (@!function_exists('gzcompress'))
	   return;
      $squirrelmail_plugin_hooks['mailbox_display_buttons']['archive_mail'] = 'archive_mail';
      $squirrelmail_plugin_hooks['optpage_register_block']['archive_mail'] = 'archive_mail_display_option';
      $squirrelmail_plugin_hooks['loading_prefs']['archive_mail'] = 'archive_mail_loading_prefs';
      $squirrelmail_plugin_hooks['move_before_move']['archive_mail'] = 'archive_mail_zipit';
?>