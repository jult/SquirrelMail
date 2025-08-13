<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

      global $mailbox, $imapConnection, $SQM_INTERNAL_VERSION;
      if (!isset($SQM_INTERNAL_VERSION) || $SQM_INTERNAL_VERSION[1] != 5)
         return;

      if (!defined('SM_PATH')) define('SM_PATH','../../../');

      if ($imapConnection) {
         $numMessages = sqimap_get_num_messages($imapConnection, $mailbox);
         if ($numMessages > 0) {
            if (@function_exists('gzcompress')) {
		  // Set domain
	          bindtextdomain ('archive_mail', SM_PATH . 'locale');
	          textdomain ('archive_mail');

                   echo getButton('SUBMIT', 'archiveButton',_("Archive"));

	          // Unset domain
		  bindtextdomain('squirrelmail', SM_PATH . 'locale');
	          textdomain('squirrelmail');
            }
         }
      }
?>