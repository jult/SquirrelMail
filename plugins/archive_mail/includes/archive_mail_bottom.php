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
      if (isset($SQM_INTERNAL_VERSION) && $SQM_INTERNAL_VERSION[1] == 5)
         return;

      if (!defined('SM_PATH')) define('SM_PATH','../../../');

      if ($imapConnection) {
         $numMessages = sqimap_get_num_messages($imapConnection, $mailbox);
         if ($numMessages > 0) {
	    // Set domain
	    bindtextdomain ('archive_mail', SM_PATH . 'locale');
	    textdomain ('archive_mail');

            if (@!function_exists('gzcompress')) {
               print "<table align=right><tr><td>";
               echo _("Archive disabled (zlib not supported)");
               print "</td></tr></table>";
            } else {
               print "\n<tr width=\"100%\"><td><p align=right><input type=submit NAME=\"archiveButton\" value='";
               echo _("Archive");
               print "'></p></td></tr>\n";
            }

	    // Unset domain
	    bindtextdomain('squirrelmail', SM_PATH . 'locale');
	    textdomain('squirrelmail');
         }
      }
?>